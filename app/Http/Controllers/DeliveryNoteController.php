<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\DeliveryNote;
use App\Models\RepairOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeliveryNoteController extends Controller
{
    // ══════════════════════════════════════════════
    // INDEX
    // ══════════════════════════════════════════════

    public function index(Request $request)
    {
        $query = DeliveryNote::with(['client', 'vehicle', 'repairOrder']);

        if ($search = $request->get('search')) {
            $query->search($search);
        }
        if ($statut = $request->get('statut')) {
            $query->byStatut($statut);
        }
        if ($from = $request->get('date_from')) {
            $query->whereDate('date_livraison', '>=', $from);
        }
        if ($to = $request->get('date_to')) {
            $query->whereDate('date_livraison', '<=', $to);
        }
        if ($request->get('unpaid')) {
            $query->withUnpaid();
        }

        $notes = $query->orderByDesc('date_livraison')->orderByDesc('created_at')->paginate(15);

        $stats = [
            'total'      => DeliveryNote::count(),
            'valides'    => DeliveryNote::valides()->count(),
            'ce_mois'    => DeliveryNote::whereMonth('date_livraison', now()->month)->whereYear('date_livraison', now()->year)->count(),
            'impayes'    => DeliveryNote::withUnpaid()->valides()->count(),
        ];

        return view('delivery-notes.index', compact('notes', 'stats'));
    }

    // ══════════════════════════════════════════════
    // CREATE (depuis un OR)
    // ══════════════════════════════════════════════

    public function create(Request $request)
    {
        $repairOrderId = $request->get('repair_order_id');
        $repairOrder = null;
        $clients = collect();

        if ($repairOrderId) {
            $repairOrder = RepairOrder::with(['client', 'vehicle', 'items'])->findOrFail($repairOrderId);

            // Vérifier qu'un BL n'existe pas déjà pour cet OR
            $existingBl = DeliveryNote::where('repair_order_id', $repairOrderId)
                ->whereIn('statut', ['brouillon', 'valide'])
                ->first();
            if ($existingBl) {
                return redirect()->route('delivery-notes.show', $existingBl)
                    ->with('info', "Un bon de livraison existe déjà pour cet ordre : {$existingBl->numero}");
            }
        }

        // Ordres éligibles (terminé ou livré, sans BL existant)
        $eligibleOrders = RepairOrder::with(['client', 'vehicle'])
            ->whereIn('status', ['termine', 'livre'])
            ->whereDoesntHave('deliveryNote', function ($q) {
                $q->whereIn('statut', ['brouillon', 'valide']);
            })
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $numero = DeliveryNote::generateNumero();

        return view('delivery-notes.create', compact('repairOrder', 'eligibleOrders', 'numero'));
    }

    // ══════════════════════════════════════════════
    // STORE
    // ══════════════════════════════════════════════

    public function store(Request $request)
    {
        $data = $request->validate([
            'repair_order_id'     => ['required', 'exists:repair_orders,id'],
            'date_livraison'      => ['required', 'date'],
            'heure_livraison'     => ['nullable', 'date_format:H:i'],
            'kilometrage_sortie'  => ['nullable', 'integer', 'min:0'],
            'niveau_carburant'    => ['nullable', Rule::in(DeliveryNote::NIVEAUX_CARBURANT)],
            'travaux_effectues'   => ['nullable', 'string', 'max:3000'],
            'observations_sortie' => ['nullable', 'string', 'max:1000'],
            'reserves_client'     => ['nullable', 'string', 'max:1000'],
            'recommandations'     => ['nullable', 'string', 'max:1000'],
            'nom_receptionnaire'  => ['nullable', 'string', 'max:255'],
            'cin_receptionnaire'  => ['nullable', 'string', 'max:20'],
            'signe_atelier'       => ['nullable', 'boolean'],
            'signe_client'        => ['nullable', 'boolean'],
            'montant_paye'        => ['nullable', 'numeric', 'min:0'],
            'mode_paiement'       => ['nullable', Rule::in(array_keys(DeliveryNote::MODES_PAIEMENT))],
            'notes'               => ['nullable', 'string', 'max:1000'],
        ]);

        $repairOrder = RepairOrder::with('items')->findOrFail($data['repair_order_id']);

        $totalTtc = (float) $repairOrder->net_a_payer;
        $montantPaye = (float) ($data['montant_paye'] ?? 0);
        $resteAPayer = max(0, $totalTtc - $montantPaye);

        $note = DeliveryNote::create([
            'numero'              => DeliveryNote::generateNumero(),
            'repair_order_id'     => $repairOrder->id,
            'client_id'           => $repairOrder->client_id,
            'vehicle_id'          => $repairOrder->vehicle_id,
            'created_by'          => auth()->id(),
            'date_livraison'      => $data['date_livraison'],
            'heure_livraison'     => $data['heure_livraison'] ?? null,
            'kilometrage_sortie'  => $data['kilometrage_sortie'] ?? null,
            'niveau_carburant'    => $data['niveau_carburant'] ?? null,
            'travaux_effectues'   => $data['travaux_effectues'] ?? $repairOrder->items->map(fn($i) => "- {$i->designation}")->implode("\n"),
            'observations_sortie' => $data['observations_sortie'] ?? null,
            'reserves_client'     => $data['reserves_client'] ?? null,
            'recommandations'     => $data['recommandations'] ?? null,
            'nom_receptionnaire'  => $data['nom_receptionnaire'] ?? null,
            'cin_receptionnaire'  => $data['cin_receptionnaire'] ?? null,
            'signe_atelier'       => $data['signe_atelier'] ?? false,
            'signe_client'        => $data['signe_client'] ?? false,
            'total_ttc'           => $totalTtc,
            'montant_paye'        => $montantPaye,
            'reste_a_payer'       => $resteAPayer,
            'mode_paiement'       => $data['mode_paiement'] ?? null,
            'statut'              => 'brouillon',
            'notes'               => $data['notes'] ?? null,
        ]);

        // Mettre à jour l'OR : km sortie et statut → livré
        $updates = [];
        if (!empty($data['kilometrage_sortie'])) {
            $updates['kilometrage_sortie'] = $data['kilometrage_sortie'];
            $note->vehicle->update(['kilometrage' => $data['kilometrage_sortie']]);
        }
        if (!empty($updates)) {
            $repairOrder->update($updates);
        }
        if (in_array($repairOrder->status, ['termine'])) {
            $repairOrder->transitionTo('livre');
        }

        // Mettre à jour solde crédit client si payé à crédit
        if ($resteAPayer > 0 && $data['mode_paiement'] === 'credit') {
            $repairOrder->client->increment('solde_credit', $resteAPayer);
        }

        ActivityLog::log('create', "Bon de livraison {$note->numero} créé pour OR {$repairOrder->numero}", $note);

        return redirect()->route('delivery-notes.show', $note)
            ->with('success', "Le bon de livraison {$note->numero} a été créé.");
    }

    // ══════════════════════════════════════════════
    // SHOW
    // ══════════════════════════════════════════════

    public function show(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load(['client', 'vehicle', 'repairOrder.items', 'repairOrder.technicien', 'createdBy']);

        return view('delivery-notes.show', compact('deliveryNote'));
    }

    // ══════════════════════════════════════════════
    // EDIT
    // ══════════════════════════════════════════════

    public function edit(DeliveryNote $deliveryNote)
    {
        if ($deliveryNote->statut === 'annule') {
            return back()->with('error', 'Un bon annulé ne peut pas être modifié.');
        }

        $deliveryNote->load(['repairOrder.items', 'client', 'vehicle']);

        return view('delivery-notes.edit', compact('deliveryNote'));
    }

    // ══════════════════════════════════════════════
    // UPDATE
    // ══════════════════════════════════════════════

    public function update(Request $request, DeliveryNote $deliveryNote)
    {
        if ($deliveryNote->statut === 'annule') {
            return back()->with('error', 'Un bon annulé ne peut pas être modifié.');
        }

        $data = $request->validate([
            'date_livraison'      => ['required', 'date'],
            'heure_livraison'     => ['nullable', 'date_format:H:i'],
            'kilometrage_sortie'  => ['nullable', 'integer', 'min:0'],
            'niveau_carburant'    => ['nullable', Rule::in(DeliveryNote::NIVEAUX_CARBURANT)],
            'travaux_effectues'   => ['nullable', 'string', 'max:3000'],
            'observations_sortie' => ['nullable', 'string', 'max:1000'],
            'reserves_client'     => ['nullable', 'string', 'max:1000'],
            'recommandations'     => ['nullable', 'string', 'max:1000'],
            'nom_receptionnaire'  => ['nullable', 'string', 'max:255'],
            'cin_receptionnaire'  => ['nullable', 'string', 'max:20'],
            'signe_atelier'       => ['nullable', 'boolean'],
            'signe_client'        => ['nullable', 'boolean'],
            'montant_paye'        => ['nullable', 'numeric', 'min:0'],
            'mode_paiement'       => ['nullable', Rule::in(array_keys(DeliveryNote::MODES_PAIEMENT))],
            'notes'               => ['nullable', 'string', 'max:1000'],
        ]);

        $montantPaye = (float) ($data['montant_paye'] ?? 0);
        $data['reste_a_payer'] = max(0, (float)$deliveryNote->total_ttc - $montantPaye);
        $data['signe_atelier'] = $data['signe_atelier'] ?? false;
        $data['signe_client'] = $data['signe_client'] ?? false;

        $deliveryNote->update($data);

        ActivityLog::log('update', "Modification du BL {$deliveryNote->numero}", $deliveryNote);

        return redirect()->route('delivery-notes.show', $deliveryNote)
            ->with('success', "Le bon de livraison {$deliveryNote->numero} a été mis à jour.");
    }

    // ══════════════════════════════════════════════
    // VALIDER
    // ══════════════════════════════════════════════

    public function validate_note(DeliveryNote $deliveryNote)
    {
        if ($deliveryNote->statut !== 'brouillon') {
            return back()->with('error', 'Seul un brouillon peut être validé.');
        }

        $deliveryNote->update(['statut' => 'valide']);

        ActivityLog::log('update', "Validation du BL {$deliveryNote->numero}", $deliveryNote, ['statut' => 'brouillon']);

        return back()->with('success', "Le bon de livraison {$deliveryNote->numero} a été validé.");
    }

    // ══════════════════════════════════════════════
    // ANNULER
    // ══════════════════════════════════════════════

    public function cancel(DeliveryNote $deliveryNote)
    {
        if ($deliveryNote->statut === 'annule') {
            return back()->with('error', 'Ce bon est déjà annulé.');
        }

        $deliveryNote->update(['statut' => 'annule']);

        ActivityLog::log('update', "Annulation du BL {$deliveryNote->numero}", $deliveryNote);

        return back()->with('success', "Le bon de livraison {$deliveryNote->numero} a été annulé.");
    }

    // ══════════════════════════════════════════════
    // DESTROY
    // ══════════════════════════════════════════════

    public function destroy(DeliveryNote $deliveryNote)
    {
        if ($deliveryNote->statut === 'valide') {
            return back()->with('error', 'Un bon validé ne peut pas être supprimé. Annulez-le d\'abord.');
        }

        $numero = $deliveryNote->numero;
        ActivityLog::log('delete', "Suppression du BL {$numero}", $deliveryNote);
        $deliveryNote->delete();

        return redirect()->route('delivery-notes.index')
            ->with('success', "Le bon {$numero} a été supprimé.");
    }
}
