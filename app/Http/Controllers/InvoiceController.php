<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\RepairOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    // ══════════════════════════════════════════════
    // INDEX
    // ══════════════════════════════════════════════

    public function index(Request $request)
    {
        $query = Invoice::with(['client', 'vehicle', 'repairOrder'])->withCount('payments');

        if ($search = $request->get('search')) { $query->search($search); }
        if ($statut = $request->get('statut')) { $query->byStatut($statut); }
        if ($from = $request->get('date_from')) { $query->whereDate('date_facture', '>=', $from); }
        if ($to = $request->get('date_to')) { $query->whereDate('date_facture', '<=', $to); }
        if ($request->get('unpaid')) { $query->unpaid(); }

        // Auto-marquer en retard les factures émises/partielles dont l'échéance est passée
        Invoice::whereIn('statut', ['emise', 'partielle'])
            ->whereNotNull('date_echeance')
            ->where('date_echeance', '<', now())
            ->update(['statut' => 'en_retard']);

        $invoices = $query->orderByDesc('date_facture')->orderByDesc('created_at')->paginate(15);

        $currentMonth = now();
        $stats = [
            'total'           => Invoice::count(),
            'ca_mois'         => Invoice::whereIn('statut', ['emise', 'payee', 'partielle', 'en_retard'])
                                    ->whereMonth('date_facture', $currentMonth->month)
                                    ->whereYear('date_facture', $currentMonth->year)
                                    ->sum('net_a_payer'),
            'encaisse_mois'   => InvoicePayment::whereMonth('date_paiement', $currentMonth->month)
                                    ->whereYear('date_paiement', $currentMonth->year)
                                    ->sum('montant'),
            'impayes'         => Invoice::unpaid()->sum('reste_a_payer'),
            'en_retard_count' => Invoice::where('statut', 'en_retard')->count(),
        ];

        return view('invoices.index', compact('invoices', 'stats'));
    }

    // ══════════════════════════════════════════════
    // CREATE (depuis un OR ou vide)
    // ══════════════════════════════════════════════

    public function create(Request $request)
    {
        $repairOrder = null;
        $clients = Client::orderBy('nom_complet')->get();

        if ($orId = $request->get('repair_order_id')) {
            $repairOrder = RepairOrder::with(['client', 'vehicle', 'items', 'deliveryNote'])->findOrFail($orId);

            // Vérifier qu'une facture n'existe pas déjà
            $existing = Invoice::where('repair_order_id', $orId)->whereNotIn('statut', ['annulee'])->first();
            if ($existing) {
                return redirect()->route('invoices.show', $existing)
                    ->with('info', "Une facture existe déjà pour cet OR : {$existing->numero}");
            }
        }

        // OR éligibles (livrés/terminés sans facture)
        $eligibleOrders = RepairOrder::with(['client', 'vehicle'])
            ->whereIn('status', ['livre', 'termine'])
            ->whereDoesntHave('invoice', function ($q) {
                $q->whereNotIn('statut', ['annulee']);
            })
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $numero = Invoice::generateNumero();

        return view('invoices.create', compact('repairOrder', 'eligibleOrders', 'clients', 'numero'));
    }

    // ══════════════════════════════════════════════
    // STORE
    // ══════════════════════════════════════════════

    public function store(Request $request)
    {
        $data = $this->validateInvoice($request);

        DB::beginTransaction();
        try {
            $invoice = Invoice::create([
                'numero'              => Invoice::generateNumero(),
                'repair_order_id'     => $data['repair_order_id'] ?? null,
                'client_id'           => $data['client_id'],
                'vehicle_id'          => $data['vehicle_id'] ?? null,
                'delivery_note_id'    => $data['delivery_note_id'] ?? null,
                'created_by'          => auth()->id(),
                'date_facture'        => $data['date_facture'],
                'date_echeance'       => $data['date_echeance'] ?? null,
                'statut'              => 'brouillon',
                'taux_tva'            => $data['taux_tva'] ?? 20,
                'remise_globale'      => $data['remise_globale'] ?? 0,
                'objet'               => $data['objet'] ?? null,
                'conditions_paiement' => $data['conditions_paiement'] ?? null,
                'notes'               => $data['notes'] ?? null,
                'mentions_legales'    => $data['mentions_legales'] ?? Invoice::MENTIONS_LEGALES_DEFAULT,
            ]);

            $this->syncItems($invoice, $data['items'] ?? []);

            // Transition OR → facturé
            if ($invoice->repair_order_id) {
                $or = RepairOrder::find($invoice->repair_order_id);
                if ($or && $or->canTransitionTo('facture')) {
                    $or->transitionTo('facture');
                }
            }

            DB::commit();

            ActivityLog::log('create', "Facture {$invoice->numero} créée", $invoice);

            return redirect()->route('invoices.show', $invoice)
                ->with('success', "La facture {$invoice->numero} a été créée.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════
    // SHOW
    // ══════════════════════════════════════════════

    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'vehicle', 'repairOrder', 'deliveryNote', 'createdBy', 'items', 'payments.recordedBy']);

        return view('invoices.show', compact('invoice'));
    }

    // ══════════════════════════════════════════════
    // EDIT
    // ══════════════════════════════════════════════

    public function edit(Invoice $invoice)
    {
        if (in_array($invoice->statut, ['payee', 'annulee'])) {
            return back()->with('error', 'Cette facture ne peut plus être modifiée.');
        }

        $invoice->load('items');
        $clients = Client::orderBy('nom_complet')->get();

        return view('invoices.edit', compact('invoice', 'clients'));
    }

    // ══════════════════════════════════════════════
    // UPDATE
    // ══════════════════════════════════════════════

    public function update(Request $request, Invoice $invoice)
    {
        if (in_array($invoice->statut, ['payee', 'annulee'])) {
            return back()->with('error', 'Cette facture ne peut plus être modifiée.');
        }

        $data = $this->validateInvoice($request, $invoice);

        DB::beginTransaction();
        try {
            $invoice->update([
                'date_facture'        => $data['date_facture'],
                'date_echeance'       => $data['date_echeance'] ?? null,
                'taux_tva'            => $data['taux_tva'] ?? 20,
                'remise_globale'      => $data['remise_globale'] ?? 0,
                'objet'               => $data['objet'] ?? null,
                'conditions_paiement' => $data['conditions_paiement'] ?? null,
                'notes'               => $data['notes'] ?? null,
                'mentions_legales'    => $data['mentions_legales'] ?? null,
            ]);

            $this->syncItems($invoice, $data['items'] ?? []);

            DB::commit();

            ActivityLog::log('update', "Facture {$invoice->numero} modifiée", $invoice);

            return redirect()->route('invoices.show', $invoice)
                ->with('success', "La facture {$invoice->numero} a été mise à jour.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════
    // ÉMETTRE
    // ══════════════════════════════════════════════

    public function emit(Invoice $invoice)
    {
        if ($invoice->statut !== 'brouillon') {
            return back()->with('error', 'Seul un brouillon peut être émis.');
        }

        if ($invoice->items()->count() === 0) {
            return back()->with('error', 'La facture doit contenir au moins une ligne.');
        }

        $invoice->update(['statut' => 'emise']);
        ActivityLog::log('update', "Facture {$invoice->numero} émise", $invoice);

        return back()->with('success', "La facture {$invoice->numero} a été émise.");
    }

    // ══════════════════════════════════════════════
    // ANNULER
    // ══════════════════════════════════════════════

    public function cancel(Invoice $invoice)
    {
        if ($invoice->statut === 'annulee') {
            return back()->with('error', 'Cette facture est déjà annulée.');
        }
        if ($invoice->total_paye > 0) {
            return back()->with('error', 'Impossible d\'annuler une facture avec des paiements. Supprimez d\'abord les paiements.');
        }

        $invoice->update(['statut' => 'annulee']);
        ActivityLog::log('update', "Facture {$invoice->numero} annulée", $invoice);

        return back()->with('success', "La facture {$invoice->numero} a été annulée.");
    }

    // ══════════════════════════════════════════════
    // ENREGISTRER UN PAIEMENT
    // ══════════════════════════════════════════════

    public function addPayment(Request $request, Invoice $invoice)
    {
        if (in_array($invoice->statut, ['brouillon', 'annulee'])) {
            return back()->with('error', 'Impossible d\'enregistrer un paiement sur cette facture.');
        }

        $data = $request->validate([
            'date_paiement' => ['required', 'date'],
            'montant'       => ['required', 'numeric', 'min:0.01'],
            'mode'          => ['required', Rule::in(array_keys(Invoice::MODES_PAIEMENT))],
            'reference'     => ['nullable', 'string', 'max:100'],
            'banque'        => ['nullable', 'string', 'max:100'],
            'notes'         => ['nullable', 'string', 'max:500'],
        ]);

        $data['recorded_by'] = auth()->id();

        $invoice->payments()->create($data);
        // recalculatePayments() is auto-triggered via InvoicePayment::saved event

        ActivityLog::log('create', "Paiement de {$data['montant']} DH enregistré sur facture {$invoice->numero}", $invoice);

        return back()->with('success', 'Paiement enregistré avec succès.');
    }

    // ══════════════════════════════════════════════
    // SUPPRIMER UN PAIEMENT
    // ══════════════════════════════════════════════

    public function deletePayment(Invoice $invoice, InvoicePayment $payment)
    {
        if ($payment->invoice_id !== $invoice->id) {
            abort(403);
        }

        $montant = $payment->montant;
        $payment->delete();
        // recalculatePayments() is auto-triggered via InvoicePayment::deleted event

        ActivityLog::log('delete', "Paiement de {$montant} DH supprimé sur facture {$invoice->numero}", $invoice);

        return back()->with('success', 'Paiement supprimé.');
    }

    // ══════════════════════════════════════════════
    // DESTROY
    // ══════════════════════════════════════════════

    public function destroy(Invoice $invoice)
    {
        if ($invoice->statut === 'payee') {
            return back()->with('error', 'Une facture payée ne peut pas être supprimée.');
        }

        $numero = $invoice->numero;
        ActivityLog::log('delete', "Suppression facture {$numero}", $invoice);
        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', "La facture {$numero} a été supprimée.");
    }

    // ══════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════

    private function validateInvoice(Request $request, ?Invoice $invoice = null): array
    {
        return $request->validate([
            'repair_order_id'      => ['nullable', 'exists:repair_orders,id'],
            'client_id'            => [$invoice ? 'sometimes' : 'required', 'exists:clients,id'],
            'vehicle_id'           => ['nullable', 'exists:vehicles,id'],
            'delivery_note_id'     => ['nullable', 'exists:delivery_notes,id'],
            'date_facture'         => ['required', 'date'],
            'date_echeance'        => ['nullable', 'date', 'after_or_equal:date_facture'],
            'taux_tva'             => ['nullable', 'numeric', 'min:0', 'max:30'],
            'remise_globale'       => ['nullable', 'numeric', 'min:0'],
            'objet'                => ['nullable', 'string', 'max:500'],
            'conditions_paiement'  => ['nullable', 'string', 'max:1000'],
            'notes'                => ['nullable', 'string', 'max:1000'],
            'mentions_legales'     => ['nullable', 'string', 'max:2000'],

            'items'                    => ['nullable', 'array'],
            'items.*.type'             => ['required', Rule::in(array_keys(InvoiceItem::TYPES))],
            'items.*.designation'      => ['required', 'string', 'max:255'],
            'items.*.reference'        => ['nullable', 'string', 'max:100'],
            'items.*.quantite'         => ['required', 'numeric', 'min:0.01'],
            'items.*.unite'            => ['required', Rule::in(array_keys(InvoiceItem::UNITES))],
            'items.*.prix_unitaire'    => ['required', 'numeric', 'min:0'],
            'items.*.remise'           => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.taux_tva'         => ['nullable', 'numeric', 'min:0', 'max:30'],
        ]);
    }

    private function syncItems(Invoice $invoice, array $items): void
    {
        $invoice->items()->delete();
        foreach ($items as $i => $itemData) {
            $invoice->items()->create([
                'type'          => $itemData['type'],
                'designation'   => $itemData['designation'],
                'reference'     => $itemData['reference'] ?? null,
                'quantite'      => $itemData['quantite'],
                'unite'         => $itemData['unite'],
                'prix_unitaire' => $itemData['prix_unitaire'],
                'remise'        => $itemData['remise'] ?? 0,
                'taux_tva'      => $itemData['taux_tva'] ?? $invoice->taux_tva,
                'ordre'         => $i,
            ]);
        }
    }
}
