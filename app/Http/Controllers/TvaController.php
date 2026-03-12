<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\TvaDeclaration;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TvaController extends Controller
{
    // ══════════════════════════════════════════════
    // INDEX — Vue d'ensemble des déclarations TVA
    // ══════════════════════════════════════════════

    public function index(Request $request)
    {
        $annee = $request->get('annee', now()->year);
        $statut = $request->get('statut');

        $declarations = TvaDeclaration::with(['createdBy'])
            ->byAnnee($annee)
            ->byStatut($statut)
            ->orderByDesc('date_fin')
            ->paginate(15);

        $annees = TvaDeclaration::selectRaw('DISTINCT annee')
            ->orderByDesc('annee')
            ->pluck('annee')
            ->when(fn($c) => !$c->contains(now()->year), fn($c) => $c->prepend(now()->year))
            ->sort()->reverse()->values();

        // Stats annuelles
        $stats = TvaDeclaration::getAnnualSummary((int) $annee);

        // Alertes : déclarations en retard
        $enRetard = TvaDeclaration::whereIn('statut', ['brouillon', 'calculee', 'validee'])
            ->get()
            ->filter(fn($d) => $d->is_overdue)
            ->count();

        return view('tva.index', compact('declarations', 'annees', 'annee', 'statut', 'stats', 'enRetard'));
    }

    // ══════════════════════════════════════════════
    // CREATE
    // ══════════════════════════════════════════════

    public function create()
    {
        return view('tva.create');
    }

    // ══════════════════════════════════════════════
    // STORE
    // ══════════════════════════════════════════════

    public function store(Request $request)
    {
        $data = $request->validate([
            'regime'    => ['required', Rule::in(array_keys(TvaDeclaration::REGIMES))],
            'annee'     => ['required', 'integer', 'min:2020', 'max:' . (now()->year + 1)],
            'mois'      => ['required_if:regime,mensuel', 'nullable', 'integer', 'min:1', 'max:12'],
            'trimestre' => ['required_if:regime,trimestriel', 'nullable', 'integer', 'min:1', 'max:4'],
            'notes'     => ['nullable', 'string', 'max:1000'],
        ]);

        // Check uniqueness
        $periodeNum = $data['regime'] === 'mensuel' ? $data['mois'] : $data['trimestre'];
        $existing = TvaDeclaration::where('annee', $data['annee']);
        if ($data['regime'] === 'mensuel') {
            $existing->where('mois', $data['mois']);
        } else {
            $existing->where('trimestre', $data['trimestre']);
        }
        if ($existing->exists()) {
            return back()->withInput()->with('error', 'Une déclaration existe déjà pour cette période.');
        }

        [$dateDebut, $dateFin] = TvaDeclaration::getDatesForPeriod(
            $data['regime'], $data['annee'], $periodeNum
        );

        $declaration = TvaDeclaration::create([
            'regime'     => $data['regime'],
            'annee'      => $data['annee'],
            'mois'       => $data['regime'] === 'mensuel' ? $data['mois'] : null,
            'trimestre'  => $data['regime'] === 'trimestriel' ? $data['trimestre'] : null,
            'date_debut' => $dateDebut,
            'date_fin'   => $dateFin,
            'created_by' => auth()->id(),
            'statut'     => 'brouillon',
            'notes'      => $data['notes'],
        ]);

        ActivityLog::log('create', "Déclaration TVA créée — {$declaration->periode_label}", $declaration);

        return redirect()->route('tva.show', $declaration)
            ->with('success', "Déclaration TVA {$declaration->periode_label} créée.");
    }

    // ══════════════════════════════════════════════
    // SHOW
    // ══════════════════════════════════════════════

    public function show(TvaDeclaration $tva)
    {
        $tva->load(['createdBy', 'validatedBy']);

        $transitions = $tva->getAvailableTransitions();
        $invoices = $tva->getInvoicesForPeriod();
        $purchases = $tva->getPurchasesForPeriod();

        return view('tva.show', compact('tva', 'transitions', 'invoices', 'purchases'));
    }

    // ══════════════════════════════════════════════
    // CALCULER — Calcul automatique des montants
    // ══════════════════════════════════════════════

    public function calculate(TvaDeclaration $tva)
    {
        if (!$tva->is_editable) {
            return back()->with('error', 'Cette déclaration ne peut plus être modifiée.');
        }

        $tva->calculateFromData();

        ActivityLog::log('update', "Calcul TVA — {$tva->periode_label} : Collectée {$tva->total_tva_collectee} DH, Déductible {$tva->total_tva_deductible} DH, Due {$tva->tva_due} DH", $tva);

        return back()->with('success', 'Montants calculés automatiquement depuis les factures et achats de la période.');
    }

    // ══════════════════════════════════════════════
    // UPDATE — Mise à jour manuelle des montants
    // ══════════════════════════════════════════════

    public function update(Request $request, TvaDeclaration $tva)
    {
        if (!$tva->is_editable) {
            return back()->with('error', 'Cette déclaration ne peut plus être modifiée.');
        }

        $data = $request->validate([
            'ca_ht_20'       => ['nullable', 'numeric', 'min:0'],
            'ca_ht_14'       => ['nullable', 'numeric', 'min:0'],
            'ca_ht_10'       => ['nullable', 'numeric', 'min:0'],
            'ca_ht_7'        => ['nullable', 'numeric', 'min:0'],
            'ca_ht_exonere'  => ['nullable', 'numeric', 'min:0'],
            'achats_ht_20'   => ['nullable', 'numeric', 'min:0'],
            'achats_ht_14'   => ['nullable', 'numeric', 'min:0'],
            'achats_ht_10'   => ['nullable', 'numeric', 'min:0'],
            'achats_ht_7'    => ['nullable', 'numeric', 'min:0'],
            'credit_tva_anterieur' => ['nullable', 'numeric', 'min:0'],
            'penalites'      => ['nullable', 'numeric', 'min:0'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ]);

        // Recalculate TVA from updated HT
        $data['tva_collectee_20'] = round(($data['ca_ht_20'] ?? 0) * 0.20, 2);
        $data['tva_collectee_14'] = round(($data['ca_ht_14'] ?? 0) * 0.14, 2);
        $data['tva_collectee_10'] = round(($data['ca_ht_10'] ?? 0) * 0.10, 2);
        $data['tva_collectee_7']  = round(($data['ca_ht_7'] ?? 0) * 0.07, 2);
        $data['total_tva_collectee'] = round(
            $data['tva_collectee_20'] + $data['tva_collectee_14']
            + $data['tva_collectee_10'] + $data['tva_collectee_7'], 2
        );

        $data['tva_deductible_20'] = round(($data['achats_ht_20'] ?? 0) * 0.20, 2);
        $data['tva_deductible_14'] = round(($data['achats_ht_14'] ?? 0) * 0.14, 2);
        $data['tva_deductible_10'] = round(($data['achats_ht_10'] ?? 0) * 0.10, 2);
        $data['tva_deductible_7']  = round(($data['achats_ht_7'] ?? 0) * 0.07, 2);
        $data['total_tva_deductible'] = round(
            $data['tva_deductible_20'] + $data['tva_deductible_14']
            + $data['tva_deductible_10'] + $data['tva_deductible_7'], 2
        );

        $credit = $data['credit_tva_anterieur'] ?? 0;
        $solde = round($data['total_tva_collectee'] - $data['total_tva_deductible'] - $credit, 2);

        $data['tva_due'] = max(0, $solde);
        $data['credit_tva'] = $solde < 0 ? abs($solde) : 0;

        $tva->update($data);

        ActivityLog::log('update', "Modification manuelle TVA — {$tva->periode_label}", $tva);

        return back()->with('success', 'Déclaration mise à jour.');
    }

    // ══════════════════════════════════════════════
    // CHANGER STATUT
    // ══════════════════════════════════════════════

    public function updateStatut(Request $request, TvaDeclaration $tva)
    {
        $request->validate([
            'statut'              => ['required', Rule::in(array_keys(TvaDeclaration::STATUTS))],
            'date_declaration'    => ['nullable', 'date'],
            'date_paiement'       => ['nullable', 'date'],
            'reference_paiement'  => ['nullable', 'string', 'max:100'],
            'montant_paye'        => ['nullable', 'numeric', 'min:0'],
        ]);

        if (!$tva->canTransitionTo($request->statut)) {
            return back()->with('error', 'Transition de statut non autorisée.');
        }

        $updateData = ['statut' => $request->statut];

        if ($request->statut === 'validee') {
            $updateData['validated_by'] = auth()->id();
        }
        if ($request->statut === 'declaree') {
            $updateData['date_declaration'] = $request->date_declaration ?? now();
        }
        if ($request->statut === 'payee') {
            $updateData['date_paiement'] = $request->date_paiement ?? now();
            $updateData['reference_paiement'] = $request->reference_paiement;
            $updateData['montant_paye'] = $request->montant_paye ?? $tva->tva_due + $tva->penalites;
        }

        $tva->update($updateData);

        ActivityLog::log('update', "TVA {$tva->periode_label} → {$tva->statut_label}", $tva);

        return back()->with('success', "Statut mis à jour : {$tva->statut_label}");
    }

    // ══════════════════════════════════════════════
    // DELETE
    // ══════════════════════════════════════════════

    public function destroy(TvaDeclaration $tva)
    {
        if (in_array($tva->statut, ['declaree', 'payee'])) {
            return back()->with('error', 'Impossible de supprimer une déclaration déclarée ou payée.');
        }

        $label = $tva->periode_label;
        ActivityLog::log('delete', "Suppression déclaration TVA — {$label}", $tva);
        $tva->delete();

        return redirect()->route('tva.index')
            ->with('success', "Déclaration TVA {$label} supprimée.");
    }
}
