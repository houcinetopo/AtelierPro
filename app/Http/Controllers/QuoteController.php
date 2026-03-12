<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuoteController extends Controller
{
    // ══════════════════════════════════════════════
    // INDEX
    // ══════════════════════════════════════════════

    public function index(Request $request)
    {
        $query = Quote::with(['client', 'vehicle'])->withCount('items');

        if ($search = $request->get('search')) {
            $query->search($search);
        }
        if ($statut = $request->get('statut')) {
            $query->byStatut($statut);
        }
        if ($from = $request->get('date_from')) {
            $query->whereDate('date_devis', '>=', $from);
        }
        if ($to = $request->get('date_to')) {
            $query->whereDate('date_devis', '<=', $to);
        }

        $quotes = $query->orderByDesc('created_at')->paginate(15);

        // Auto-expirer les devis envoyés dont la date de validité est passée
        Quote::where('statut', 'envoye')
            ->where('date_validite', '<', now())
            ->update(['statut' => 'expire']);

        $stats = [
            'total'       => Quote::count(),
            'en_attente'  => Quote::enAttente()->count(),
            'acceptes'    => Quote::where('statut', 'accepte')->count(),
            'ce_mois'     => Quote::whereMonth('date_devis', now()->month)->whereYear('date_devis', now()->year)->count(),
            'montant_mois'=> Quote::where('statut', 'accepte')->whereMonth('date_acceptation', now()->month)->sum('net_a_payer'),
        ];

        return view('quotes.index', compact('quotes', 'stats'));
    }

    // ══════════════════════════════════════════════
    // CREATE
    // ══════════════════════════════════════════════

    public function create(Request $request)
    {
        $clients = Client::orderBy('nom_complet')->get();
        $vehicles = collect();
        $numero = Quote::generateNumero();

        $selectedClient = null;
        if ($clientId = $request->get('client_id')) {
            $selectedClient = Client::find($clientId);
            if ($selectedClient) {
                $vehicles = $selectedClient->vehicles()->orderBy('immatriculation')->get();
            }
        }

        return view('quotes.create', compact('clients', 'vehicles', 'numero', 'selectedClient'));
    }

    // ══════════════════════════════════════════════
    // STORE
    // ══════════════════════════════════════════════

    public function store(Request $request)
    {
        $data = $this->validateQuote($request);

        DB::beginTransaction();
        try {
            $quote = Quote::create([
                'numero'               => Quote::generateNumero(),
                'client_id'            => $data['client_id'],
                'vehicle_id'           => $data['vehicle_id'] ?? null,
                'created_by'           => auth()->id(),
                'date_devis'           => $data['date_devis'],
                'date_validite'        => $data['date_validite'],
                'description_travaux'  => $data['description_travaux'] ?? null,
                'conditions'           => $data['conditions'] ?? null,
                'notes'                => $data['notes'] ?? null,
                'duree_estimee_jours'  => $data['duree_estimee_jours'] ?? null,
                'taux_tva'             => $data['taux_tva'] ?? 20,
                'remise_globale'       => $data['remise_globale'] ?? 0,
                'statut'               => 'brouillon',
            ]);

            $this->syncItems($quote, $data['items'] ?? []);

            DB::commit();

            ActivityLog::log('create', "Création du devis {$quote->numero}", $quote);

            return redirect()->route('quotes.show', $quote)
                ->with('success', "Le devis {$quote->numero} a été créé.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════
    // SHOW
    // ══════════════════════════════════════════════

    public function show(Quote $quote)
    {
        $quote->load(['client', 'vehicle', 'createdBy', 'items', 'repairOrder']);

        $transitions = collect(Quote::STATUTS)->filter(
            fn($label, $statut) => $quote->canTransitionTo($statut)
        );

        return view('quotes.show', compact('quote', 'transitions'));
    }

    // ══════════════════════════════════════════════
    // EDIT
    // ══════════════════════════════════════════════

    public function edit(Quote $quote)
    {
        if (in_array($quote->statut, ['converti', 'annule'])) {
            return back()->with('error', 'Ce devis ne peut plus être modifié.');
        }

        $quote->load('items');
        $clients = Client::orderBy('nom_complet')->get();
        $vehicles = Vehicle::where('client_id', $quote->client_id)->orderBy('immatriculation')->get();

        return view('quotes.edit', compact('quote', 'clients', 'vehicles'));
    }

    // ══════════════════════════════════════════════
    // UPDATE
    // ══════════════════════════════════════════════

    public function update(Request $request, Quote $quote)
    {
        if (in_array($quote->statut, ['converti', 'annule'])) {
            return back()->with('error', 'Ce devis ne peut plus être modifié.');
        }

        $data = $this->validateQuote($request);

        DB::beginTransaction();
        try {
            $quote->update([
                'client_id'            => $data['client_id'],
                'vehicle_id'           => $data['vehicle_id'] ?? null,
                'date_devis'           => $data['date_devis'],
                'date_validite'        => $data['date_validite'],
                'description_travaux'  => $data['description_travaux'] ?? null,
                'conditions'           => $data['conditions'] ?? null,
                'notes'                => $data['notes'] ?? null,
                'duree_estimee_jours'  => $data['duree_estimee_jours'] ?? null,
                'taux_tva'             => $data['taux_tva'] ?? 20,
                'remise_globale'       => $data['remise_globale'] ?? 0,
            ]);

            $this->syncItems($quote, $data['items'] ?? []);

            DB::commit();

            ActivityLog::log('update', "Modification du devis {$quote->numero}", $quote);

            return redirect()->route('quotes.show', $quote)
                ->with('success', "Le devis {$quote->numero} a été mis à jour.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════
    // DESTROY
    // ══════════════════════════════════════════════

    public function destroy(Quote $quote)
    {
        if ($quote->statut === 'converti') {
            return back()->with('error', 'Un devis converti ne peut pas être supprimé.');
        }

        $numero = $quote->numero;
        ActivityLog::log('delete', "Suppression du devis {$numero}", $quote);
        $quote->delete();

        return redirect()->route('quotes.index')
            ->with('success', "Le devis {$numero} a été supprimé.");
    }

    // ══════════════════════════════════════════════
    // CHANGEMENT DE STATUT
    // ══════════════════════════════════════════════

    public function updateStatut(Request $request, Quote $quote)
    {
        $request->validate([
            'statut'      => ['required', Rule::in(array_keys(Quote::STATUTS))],
            'motif_refus' => ['nullable', 'string', 'max:500'],
        ]);

        $newStatut = $request->input('statut');
        $motifRefus = $request->input('motif_refus');

        if (!$quote->transitionTo($newStatut, $motifRefus)) {
            return back()->with('error', "Transition impossible : {$quote->statut_label} → " . (Quote::STATUTS[$newStatut] ?? $newStatut));
        }

        return back()->with('success', "Statut mis à jour : {$quote->statut_label}");
    }

    // ══════════════════════════════════════════════
    // CONVERTIR EN OR
    // ══════════════════════════════════════════════

    public function convertToRepairOrder(Quote $quote)
    {
        if (!$quote->is_convertible) {
            return back()->with('error', 'Ce devis ne peut pas être converti. Il doit être accepté et non encore converti.');
        }

        $order = $quote->convertToRepairOrder();

        if (!$order) {
            return back()->with('error', 'Erreur lors de la conversion.');
        }

        return redirect()->route('repair-orders.show', $order)
            ->with('success', "Le devis {$quote->numero} a été converti en ordre de réparation {$order->numero}.");
    }

    // ══════════════════════════════════════════════
    // DUPLIQUER
    // ══════════════════════════════════════════════

    public function duplicate(Quote $quote)
    {
        $quote->load('items');

        DB::beginTransaction();
        try {
            $newQuote = $quote->replicate(['numero', 'statut', 'date_acceptation', 'repair_order_id', 'motif_refus']);
            $newQuote->numero = Quote::generateNumero();
            $newQuote->statut = 'brouillon';
            $newQuote->date_devis = now();
            $newQuote->date_validite = now()->addDays(30);
            $newQuote->created_by = auth()->id();
            $newQuote->save();

            foreach ($quote->items as $i => $item) {
                $newQuote->items()->create([
                    'type'          => $item->type,
                    'designation'   => $item->designation,
                    'reference'     => $item->reference,
                    'description'   => $item->description,
                    'quantite'      => $item->quantite,
                    'unite'         => $item->unite,
                    'prix_unitaire' => $item->prix_unitaire,
                    'remise'        => $item->remise,
                    'taux_tva'      => $item->taux_tva,
                    'ordre'         => $i,
                ]);
            }

            DB::commit();

            ActivityLog::log('create', "Duplication du devis {$quote->numero} → {$newQuote->numero}", $newQuote);

            return redirect()->route('quotes.edit', $newQuote)
                ->with('success', "Le devis a été dupliqué : {$newQuote->numero}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════
    // API : Véhicules par client
    // ══════════════════════════════════════════════

    public function vehiclesByClient(Request $request)
    {
        $clientId = $request->get('client_id');
        if (!$clientId) return response()->json([]);

        return response()->json(
            Vehicle::where('client_id', $clientId)
                ->orderBy('immatriculation')
                ->get(['id', 'immatriculation', 'marque', 'modele', 'couleur'])
        );
    }

    // ══════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════

    private function validateQuote(Request $request): array
    {
        return $request->validate([
            'client_id'            => ['required', 'exists:clients,id'],
            'vehicle_id'           => ['nullable', 'exists:vehicles,id'],
            'date_devis'           => ['required', 'date'],
            'date_validite'        => ['required', 'date', 'after_or_equal:date_devis'],
            'description_travaux'  => ['nullable', 'string', 'max:3000'],
            'conditions'           => ['nullable', 'string', 'max:1000'],
            'notes'                => ['nullable', 'string', 'max:1000'],
            'duree_estimee_jours'  => ['nullable', 'integer', 'min:1', 'max:365'],
            'taux_tva'             => ['nullable', 'numeric', 'min:0', 'max:30'],
            'remise_globale'       => ['nullable', 'numeric', 'min:0'],

            'items'                    => ['nullable', 'array'],
            'items.*.type'             => ['required', Rule::in(array_keys(QuoteItem::TYPES))],
            'items.*.designation'      => ['required', 'string', 'max:255'],
            'items.*.reference'        => ['nullable', 'string', 'max:100'],
            'items.*.quantite'         => ['required', 'numeric', 'min:0.01'],
            'items.*.unite'            => ['required', Rule::in(array_keys(QuoteItem::UNITES))],
            'items.*.prix_unitaire'    => ['required', 'numeric', 'min:0'],
            'items.*.remise'           => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.taux_tva'         => ['nullable', 'numeric', 'min:0', 'max:30'],
        ]);
    }

    private function syncItems(Quote $quote, array $items): void
    {
        $quote->items()->delete();
        foreach ($items as $i => $itemData) {
            $quote->items()->create([
                'type'          => $itemData['type'],
                'designation'   => $itemData['designation'],
                'reference'     => $itemData['reference'] ?? null,
                'quantite'      => $itemData['quantite'],
                'unite'         => $itemData['unite'],
                'prix_unitaire' => $itemData['prix_unitaire'],
                'remise'        => $itemData['remise'] ?? 0,
                'taux_tva'      => $itemData['taux_tva'] ?? $quote->taux_tva,
                'ordre'         => $i,
            ]);
        }
    }
}
