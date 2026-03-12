<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\InvoicePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CashController extends Controller
{
    // ══════════════════════════════════════════════
    // INDEX — Vue d'ensemble de la caisse
    // ══════════════════════════════════════════════

    public function index(Request $request)
    {
        // Session en cours
        $currentSession = CashSession::where('date_session', now()->toDateString())->first();

        // Historique
        $query = CashSession::with(['openedBy', 'closedBy'])->withCount('movements');

        if ($month = $request->get('month')) {
            [$y, $m] = explode('-', $month);
            $query->whereYear('date_session', $y)->whereMonth('date_session', $m);
        }

        $sessions = $query->orderByDesc('date_session')->paginate(15);

        // Stats du mois
        $cm = now();
        $monthSessions = CashSession::whereMonth('date_session', $cm->month)
            ->whereYear('date_session', $cm->year);

        $stats = [
            'solde_actuel'    => $currentSession ? $currentSession->solde_theorique : ($this->getLastSolde()),
            'entrees_mois'    => (clone $monthSessions)->sum('total_entrees'),
            'sorties_mois'    => (clone $monthSessions)->sum('total_sorties'),
            'sessions_mois'   => (clone $monthSessions)->count(),
            'ecart_total_mois'=> CashSession::where('statut', 'cloturee')
                                    ->whereMonth('date_session', $cm->month)
                                    ->whereYear('date_session', $cm->year)
                                    ->sum('ecart'),
        ];

        return view('cash.index', compact('currentSession', 'sessions', 'stats'));
    }

    // ══════════════════════════════════════════════
    // OUVRIR LA CAISSE
    // ══════════════════════════════════════════════

    public function open(Request $request)
    {
        $existing = CashSession::where('date_session', now()->toDateString())->first();
        if ($existing) {
            return redirect()->route('cash.session', $existing)
                ->with('info', 'La caisse est déjà ouverte pour aujourd\'hui.');
        }

        $data = $request->validate([
            'solde_ouverture' => ['nullable', 'numeric', 'min:0'],
            'notes_ouverture' => ['nullable', 'string', 'max:500'],
        ]);

        $session = CashSession::openToday(
            $data['solde_ouverture'] ?? 0,
            $data['notes_ouverture'] ?? null
        );

        ActivityLog::log('create', "Ouverture caisse — Solde : " . number_format($session->solde_ouverture, 2) . " DH", $session);

        return redirect()->route('cash.session', $session)
            ->with('success', 'Caisse ouverte avec succès.');
    }

    // ══════════════════════════════════════════════
    // VUE SESSION (journal de caisse)
    // ══════════════════════════════════════════════

    public function session(CashSession $cashSession)
    {
        $cashSession->load(['movements.recordedBy', 'movements.invoice', 'openedBy', 'closedBy']);

        $entrees = $cashSession->movements->where('type', 'entree');
        $sorties = $cashSession->movements->where('type', 'sortie');

        // Résumé par catégorie
        $parCategorie = $cashSession->movements
            ->groupBy('categorie')
            ->map(fn($group) => [
                'label'   => $group->first()->categorie_label,
                'type'    => $group->first()->type,
                'count'   => $group->count(),
                'total'   => $group->sum('montant'),
            ])
            ->sortByDesc('total');

        return view('cash.session', compact('cashSession', 'entrees', 'sorties', 'parCategorie'));
    }

    // ══════════════════════════════════════════════
    // CLÔTURER LA CAISSE
    // ══════════════════════════════════════════════

    public function close(Request $request, CashSession $cashSession)
    {
        if (!$cashSession->is_open) {
            return back()->with('error', 'Cette session est déjà clôturée.');
        }

        $data = $request->validate([
            'solde_reel'     => ['required', 'numeric', 'min:0'],
            'notes_cloture'  => ['nullable', 'string', 'max:500'],
        ]);

        $cashSession->close($data['solde_reel'], $data['notes_cloture'] ?? null);

        ActivityLog::log('update', "Clôture caisse — Solde réel : " . number_format($data['solde_reel'], 2) . " DH, Écart : " . number_format($cashSession->ecart, 2) . " DH", $cashSession);

        return redirect()->route('cash.index')
            ->with('success', 'Caisse clôturée. Écart : ' . number_format($cashSession->ecart, 2, ',', ' ') . ' DH');
    }

    // ══════════════════════════════════════════════
    // AJOUTER UN MOUVEMENT
    // ══════════════════════════════════════════════

    public function addMovement(Request $request, CashSession $cashSession)
    {
        if (!$cashSession->is_open) {
            return back()->with('error', 'Impossible d\'ajouter un mouvement sur une session clôturée.');
        }

        $allCategories = array_keys(CashMovement::allCategories());

        $data = $request->validate([
            'type'           => ['required', Rule::in(['entree', 'sortie'])],
            'categorie'      => ['required', Rule::in($allCategories)],
            'libelle'        => ['required', 'string', 'max:255'],
            'montant'        => ['required', 'numeric', 'min:0.01'],
            'mode_paiement'  => ['required', Rule::in(array_keys(CashMovement::MODES_PAIEMENT))],
            'reference'      => ['nullable', 'string', 'max:100'],
            'beneficiaire'   => ['nullable', 'string', 'max:200'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        $data['cash_session_id'] = $cashSession->id;
        $data['recorded_by'] = auth()->id();

        CashMovement::create($data);

        $label = $data['type'] === 'entree' ? 'Entrée' : 'Sortie';
        ActivityLog::log('create', "{$label} caisse : {$data['montant']} DH — {$data['libelle']}", $cashSession);

        return back()->with('success', "{$label} de " . number_format($data['montant'], 2, ',', ' ') . ' DH enregistrée.');
    }

    // ══════════════════════════════════════════════
    // SUPPRIMER UN MOUVEMENT
    // ══════════════════════════════════════════════

    public function deleteMovement(CashSession $cashSession, CashMovement $movement)
    {
        if (!$cashSession->is_open) {
            return back()->with('error', 'Impossible de modifier une session clôturée.');
        }

        if ($movement->cash_session_id !== $cashSession->id) {
            abort(403);
        }

        // Empêcher suppression si lié à un paiement facture
        if ($movement->invoice_payment_id) {
            return back()->with('error', 'Ce mouvement est lié à un paiement de facture et ne peut être supprimé ici.');
        }

        $montant = $movement->montant;
        $type = $movement->type === 'entree' ? 'Entrée' : 'Sortie';
        $movement->delete();

        ActivityLog::log('delete', "Suppression {$type} caisse : {$montant} DH", $cashSession);

        return back()->with('success', 'Mouvement supprimé.');
    }

    // ══════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════

    private function getLastSolde(): float
    {
        $last = CashSession::orderByDesc('date_session')->first();
        return $last ? (float) $last->solde_theorique : 0;
    }
}
