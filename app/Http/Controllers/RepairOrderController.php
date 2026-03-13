<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\DeliveryNote;
use App\Models\Expert;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\RepairOrder;
use App\Models\RepairOrderItem;
use App\Models\RepairOrderPhoto;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RepairOrderController extends Controller
{
    // ══════════════════════════════════════════════
    // INDEX
    // ══════════════════════════════════════════════

    public function index(Request $request)
    {
        $query = RepairOrder::with(['client', 'vehicle', 'technicien'])
            ->withCount('items');

        // Technicien : ne voit que ses ordres
        if (auth()->user()->isTechnicien()) {
            $query->forTechnicien(auth()->id());
        }

        // Filtres
        if ($search = $request->get('search')) {
            $query->search($search);
        }
        if ($status = $request->get('status')) {
            $query->byStatus($status);
        }
        if ($tech = $request->get('technicien_id')) {
            $query->byTechnicien($tech);
        }
        if ($from = $request->get('date_from')) {
            $query->whereDate('date_reception', '>=', $from);
        }
        if ($to = $request->get('date_to')) {
            $query->whereDate('date_reception', '<=', $to);
        }

        $orders = $query->orderByDesc('created_at')->paginate(15);

        // Stats rapides
        $baseQuery = RepairOrder::query();
        if (auth()->user()->isTechnicien()) {
            $baseQuery->forTechnicien(auth()->id());
        }
        $stats = [
            'total'    => (clone $baseQuery)->count(),
            'en_cours' => (clone $baseQuery)->whereIn('status', ['en_cours', 'en_attente'])->count(),
            'en_retard'=> (clone $baseQuery)->late()->count(),
            'termines' => (clone $baseQuery)->where('status', 'termine')->count(),
        ];

        $techniciens = User::where('role', 'technicien')->orderBy('name')->get();

        return view('repair-orders.index', compact('orders', 'stats', 'techniciens'));
    }

    // ══════════════════════════════════════════════
    // CREATE - Modification 5 : Bloqué, rediriger vers Devis
    // ══════════════════════════════════════════════

    public function create(Request $request)
    {
        // Si un quote_id est fourni (conversion depuis un devis accepté), permettre la création
        if ($request->has('from_quote')) {
            // Cette route est utilisée en interne lors de la conversion Devis → OR
            // La création directe est gérée par Quote::convertToRepairOrder()
            return redirect()->route('quotes.index')
                ->with('info', 'Veuillez créer un devis d\'abord, puis le convertir en Ordre de Réparation.');
        }

        // Modification 5 : Bloquer la création directe, rediriger vers les devis
        return redirect()->route('quotes.create')
            ->with('info', 'La création d\'un Ordre de Réparation nécessite un devis préalable. Veuillez d\'abord créer un devis, puis le convertir en OR une fois accepté.');
    }

    // ══════════════════════════════════════════════
    // STORE
    // ══════════════════════════════════════════════

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'              => ['required', 'exists:clients,id'],
            'vehicle_id'             => ['required', 'exists:vehicles,id'],
            'technicien_id'          => ['nullable', 'exists:users,id'],
            'date_reception'         => ['required', 'date'],
            'date_prevue_livraison'  => ['nullable', 'date', 'after_or_equal:date_reception'],
            'description_panne'      => ['required', 'string', 'max:2000'],
            'diagnostic'             => ['nullable', 'string', 'max:2000'],
            'observations'           => ['nullable', 'string', 'max:1000'],
            'notes_internes'         => ['nullable', 'string', 'max:1000'],
            'kilometrage_entree'     => ['nullable', 'integer', 'min:0'],
            'niveau_carburant'       => ['nullable', Rule::in(RepairOrder::NIVEAUX_CARBURANT)],
            'source_ordre'           => ['nullable', Rule::in(array_keys(RepairOrder::SOURCES))],
            'taux_tva'               => ['nullable', 'numeric', 'min:0', 'max:30'],
            'remise_globale'         => ['nullable', 'numeric', 'min:0'],
            'etat_vehicule'          => ['nullable', 'array'],

            // Lignes (items)
            'items'                    => ['nullable', 'array'],
            'items.*.type'             => ['required', Rule::in(array_keys(RepairOrderItem::TYPES))],
            'items.*.designation'      => ['required', 'string', 'max:255'],
            'items.*.reference'        => ['nullable', 'string', 'max:100'],
            'items.*.quantite'         => ['required', 'numeric', 'min:0.01'],
            'items.*.unite'            => ['required', Rule::in(array_keys(RepairOrderItem::UNITES))],
            'items.*.prix_unitaire'    => ['required', 'numeric', 'min:0'],
            'items.*.remise'           => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.taux_tva'         => ['nullable', 'numeric', 'min:0', 'max:30'],

            // Photos
            'photos'           => ['nullable', 'array'],
            'photos.*'         => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'photo_moments'    => ['nullable', 'array'],
            'photo_moments.*'  => [Rule::in(array_keys(RepairOrderPhoto::MOMENTS))],
            'photo_captions'   => ['nullable', 'array'],
            'photo_captions.*' => ['nullable', 'string', 'max:255'],
        ]);

        DB::beginTransaction();
        try {
            // Créer l'ordre
            $order = RepairOrder::create([
                'numero'                => RepairOrder::generateNumero(),
                'client_id'             => $data['client_id'],
                'vehicle_id'            => $data['vehicle_id'],
                'technicien_id'         => $data['technicien_id'] ?? null,
                'created_by'            => auth()->id(),
                'date_reception'        => $data['date_reception'],
                'date_prevue_livraison' => $data['date_prevue_livraison'] ?? null,
                'description_panne'     => $data['description_panne'],
                'diagnostic'            => $data['diagnostic'] ?? null,
                'observations'          => $data['observations'] ?? null,
                'notes_internes'        => $data['notes_internes'] ?? null,
                'kilometrage_entree'    => $data['kilometrage_entree'] ?? null,
                'niveau_carburant'      => $data['niveau_carburant'] ?? null,
                'source_ordre'          => $data['source_ordre'] ?? 'direct',
                'taux_tva'              => $data['taux_tva'] ?? 20,
                'remise_globale'        => $data['remise_globale'] ?? 0,
                'etat_vehicule'         => $data['etat_vehicule'] ?? null,
                'status'                => 'brouillon',
            ]);

            // Ajouter les lignes
            if (!empty($data['items'])) {
                foreach ($data['items'] as $i => $itemData) {
                    $order->items()->create([
                        'type'          => $itemData['type'],
                        'designation'   => $itemData['designation'],
                        'reference'     => $itemData['reference'] ?? null,
                        'quantite'      => $itemData['quantite'],
                        'unite'         => $itemData['unite'],
                        'prix_unitaire' => $itemData['prix_unitaire'],
                        'remise'        => $itemData['remise'] ?? 0,
                        'taux_tva'      => $itemData['taux_tva'] ?? $order->taux_tva,
                        'ordre'         => $i,
                    ]);
                }
            }

            // Upload photos
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $i => $photo) {
                    $path = $photo->store("repair-orders/{$order->id}", 'public');
                    $order->photos()->create([
                        'path'        => $path,
                        'moment'      => $data['photo_moments'][$i] ?? 'avant',
                        'caption'     => $data['photo_captions'][$i] ?? null,
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }

            // MAJ kilométrage véhicule
            if (!empty($data['kilometrage_entree'])) {
                $order->vehicle->update(['kilometrage' => $data['kilometrage_entree']]);
            }

            DB::commit();

            ActivityLog::log('create', "Création de l'ordre de réparation {$order->numero}", $order);

            return redirect()->route('repair-orders.show', $order)
                ->with('success', "L'ordre de réparation {$order->numero} a été créé.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════
    // SHOW
    // ══════════════════════════════════════════════

    public function show(RepairOrder $repairOrder)
    {
        $repairOrder->load([
            'client', 'vehicle', 'technicien', 'createdBy',
            'items', 'items.product', 'items.fournisseur',
            'photos', 'quote', 'expert', 'expert.emails',
            'invoice', 'deliveryNote', 'notificationLogs',
        ]);

        // Vérifier accès technicien
        if (auth()->user()->isTechnicien() && $repairOrder->technicien_id !== auth()->id()) {
            abort(403);
        }

        // Transitions possibles
        $transitions = collect(RepairOrder::STATUSES)->filter(
            fn($label, $status) => $repairOrder->canTransitionTo($status)
        );

        // Modification 8 : Résumé financier
        $resumeFinancier = $repairOrder->resume_financier;

        // Modification 7 : Produits disponibles pour ajout
        $products = Product::where('actif', true)
            ->where('quantite_stock', '>', 0)
            ->orderBy('designation')
            ->get(['id', 'designation', 'reference', 'prix_vente', 'prix_achat', 'quantite_stock', 'unite']);

        // Experts pour association
        $experts = Expert::actifs()->orderBy('nom_complet')->get();

        return view('repair-orders.show', compact(
            'repairOrder', 'transitions', 'resumeFinancier', 'products', 'experts'
        ));
    }

    // ══════════════════════════════════════════════
    // EDIT
    // ══════════════════════════════════════════════

    public function edit(RepairOrder $repairOrder)
    {
        if (in_array($repairOrder->status, ['facture', 'annule'])) {
            return back()->with('error', 'Cet ordre ne peut plus être modifié.');
        }

        $repairOrder->load(['items', 'photos']);
        $clients = Client::orderBy('nom_complet')->get();
        $vehicles = Vehicle::where('client_id', $repairOrder->client_id)->orderBy('immatriculation')->get();
        $techniciens = User::where('role', 'technicien')->orderBy('name')->get();

        return view('repair-orders.edit', compact('repairOrder', 'clients', 'vehicles', 'techniciens'));
    }

    // ══════════════════════════════════════════════
    // UPDATE
    // ══════════════════════════════════════════════

    public function update(Request $request, RepairOrder $repairOrder)
    {
        if (in_array($repairOrder->status, ['facture', 'annule'])) {
            return back()->with('error', 'Cet ordre ne peut plus être modifié.');
        }

        $data = $request->validate([
            'client_id'              => ['required', 'exists:clients,id'],
            'vehicle_id'             => ['required', 'exists:vehicles,id'],
            'technicien_id'          => ['nullable', 'exists:users,id'],
            'date_reception'         => ['required', 'date'],
            'date_prevue_livraison'  => ['nullable', 'date', 'after_or_equal:date_reception'],
            'description_panne'      => ['required', 'string', 'max:2000'],
            'diagnostic'             => ['nullable', 'string', 'max:2000'],
            'observations'           => ['nullable', 'string', 'max:1000'],
            'notes_internes'         => ['nullable', 'string', 'max:1000'],
            'kilometrage_entree'     => ['nullable', 'integer', 'min:0'],
            'kilometrage_sortie'     => ['nullable', 'integer', 'min:0'],
            'niveau_carburant'       => ['nullable', Rule::in(RepairOrder::NIVEAUX_CARBURANT)],
            'source_ordre'           => ['nullable', Rule::in(array_keys(RepairOrder::SOURCES))],
            'taux_tva'               => ['nullable', 'numeric', 'min:0', 'max:30'],
            'remise_globale'         => ['nullable', 'numeric', 'min:0'],
            'etat_vehicule'          => ['nullable', 'array'],

            // Lignes
            'items'                    => ['nullable', 'array'],
            'items.*.id'               => ['nullable', 'integer'],
            'items.*.type'             => ['required', Rule::in(array_keys(RepairOrderItem::TYPES))],
            'items.*.designation'      => ['required', 'string', 'max:255'],
            'items.*.reference'        => ['nullable', 'string', 'max:100'],
            'items.*.quantite'         => ['required', 'numeric', 'min:0.01'],
            'items.*.unite'            => ['required', Rule::in(array_keys(RepairOrderItem::UNITES))],
            'items.*.prix_unitaire'    => ['required', 'numeric', 'min:0'],
            'items.*.remise'           => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.taux_tva'         => ['nullable', 'numeric', 'min:0', 'max:30'],

            // Photos
            'photos'           => ['nullable', 'array'],
            'photos.*'         => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'photo_moments'    => ['nullable', 'array'],
            'photo_moments.*'  => [Rule::in(array_keys(RepairOrderPhoto::MOMENTS))],
            'photo_captions'   => ['nullable', 'array'],
            'photo_captions.*' => ['nullable', 'string', 'max:255'],
        ]);

        DB::beginTransaction();
        try {
            $oldValues = $repairOrder->only(['status', 'total_ttc', 'technicien_id']);

            $repairOrder->update([
                'client_id'             => $data['client_id'],
                'vehicle_id'            => $data['vehicle_id'],
                'technicien_id'         => $data['technicien_id'] ?? null,
                'date_reception'        => $data['date_reception'],
                'date_prevue_livraison' => $data['date_prevue_livraison'] ?? null,
                'description_panne'     => $data['description_panne'],
                'diagnostic'            => $data['diagnostic'] ?? null,
                'observations'          => $data['observations'] ?? null,
                'notes_internes'        => $data['notes_internes'] ?? null,
                'kilometrage_entree'    => $data['kilometrage_entree'] ?? null,
                'kilometrage_sortie'    => $data['kilometrage_sortie'] ?? null,
                'niveau_carburant'      => $data['niveau_carburant'] ?? null,
                'source_ordre'          => $data['source_ordre'] ?? 'direct',
                'taux_tva'              => $data['taux_tva'] ?? 20,
                'remise_globale'        => $data['remise_globale'] ?? 0,
                'etat_vehicule'         => $data['etat_vehicule'] ?? null,
            ]);

            // Sync items : supprimer les anciens, recréer
            $repairOrder->items()->delete();
            if (!empty($data['items'])) {
                foreach ($data['items'] as $i => $itemData) {
                    $repairOrder->items()->create([
                        'type'          => $itemData['type'],
                        'designation'   => $itemData['designation'],
                        'reference'     => $itemData['reference'] ?? null,
                        'quantite'      => $itemData['quantite'],
                        'unite'         => $itemData['unite'],
                        'prix_unitaire' => $itemData['prix_unitaire'],
                        'remise'        => $itemData['remise'] ?? 0,
                        'taux_tva'      => $itemData['taux_tva'] ?? $repairOrder->taux_tva,
                        'ordre'         => $i,
                    ]);
                }
            }

            // Upload nouvelles photos
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $i => $photo) {
                    $path = $photo->store("repair-orders/{$repairOrder->id}", 'public');
                    $repairOrder->photos()->create([
                        'path'        => $path,
                        'moment'      => $data['photo_moments'][$i] ?? 'avant',
                        'caption'     => $data['photo_captions'][$i] ?? null,
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            ActivityLog::log('update', "Modification de l'ordre {$repairOrder->numero}", $repairOrder, $oldValues);

            return redirect()->route('repair-orders.show', $repairOrder)
                ->with('success', "L'ordre {$repairOrder->numero} a été mis à jour.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════
    // DESTROY
    // ══════════════════════════════════════════════

    public function destroy(RepairOrder $repairOrder)
    {
        if (in_array($repairOrder->status, ['facture'])) {
            return back()->with('error', 'Un ordre facturé ne peut pas être supprimé.');
        }

        $numero = $repairOrder->numero;

        // Supprimer les photos physiquement
        foreach ($repairOrder->photos as $photo) {
            Storage::disk('public')->delete($photo->path);
        }

        ActivityLog::log('delete', "Suppression de l'ordre {$numero}", $repairOrder);
        $repairOrder->delete();

        return redirect()->route('repair-orders.index')
            ->with('success', "L'ordre {$numero} a été supprimé.");
    }

    // ══════════════════════════════════════════════
    // CHANGEMENT DE STATUT
    // ══════════════════════════════════════════════

    public function updateStatus(Request $request, RepairOrder $repairOrder)
    {
        $request->validate([
            'status' => ['required', Rule::in(array_keys(RepairOrder::STATUSES))],
        ]);

        $newStatus = $request->input('status');

        if (!$repairOrder->transitionTo($newStatus)) {
            return back()->with('error', "Transition de statut impossible : {$repairOrder->status_label} → " . (RepairOrder::STATUSES[$newStatus] ?? $newStatus));
        }

        return back()->with('success', "Statut mis à jour : {$repairOrder->status_label}");
    }

    // ══════════════════════════════════════════════
    // SUPPRESSION PHOTO
    // ══════════════════════════════════════════════

    public function deletePhoto(RepairOrder $repairOrder, RepairOrderPhoto $photo)
    {
        if ($photo->repair_order_id !== $repairOrder->id) abort(404);

        $photo->delete();

        return back()->with('success', 'Photo supprimée.');
    }

    // ══════════════════════════════════════════════
    // API : Véhicules par client (AJAX)
    // ══════════════════════════════════════════════

    public function vehiclesByClient(Request $request)
    {
        $clientId = $request->get('client_id');
        if (!$clientId) return response()->json([]);

        $vehicles = Vehicle::where('client_id', $clientId)
            ->orderBy('immatriculation')
            ->get(['id', 'immatriculation', 'marque', 'modele', 'couleur', 'kilometrage']);

        return response()->json($vehicles);
    }

    // ══════════════════════════════════════════════
    // Modification 7 : Ajouter une pièce du stock
    // ══════════════════════════════════════════════

    public function addProduct(Request $request, RepairOrder $repairOrder)
    {
        if (in_array($repairOrder->status, ['facture', 'annule', 'livre'])) {
            return back()->with('error', 'Impossible d\'ajouter des pièces à cet ordre.');
        }

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantite'   => ['required', 'numeric', 'min:0.01'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $stockService = new StockService();

        $result = $stockService->addProductToOrder($repairOrder, $product, $data['quantite']);

        if (!empty($result['needs_order'])) {
            return back()->with('warning', $result['message']);
        }

        $flash = 'success';
        $message = $result['message'];
        if (!empty($result['stock_alert'])) {
            $message .= ' ⚠️ Alerte : le stock de cette pièce est bas.';
            $flash = 'warning';
        }

        return back()->with($flash, $message);
    }

    // ══════════════════════════════════════════════
    // Modification 7 : Retirer une pièce (retour stock)
    // ══════════════════════════════════════════════

    public function removeProduct(RepairOrder $repairOrder, RepairOrderItem $item)
    {
        if ($item->repair_order_id !== $repairOrder->id) {
            abort(404);
        }

        $stockService = new StockService();
        $stockService->returnToStock($item, $repairOrder);

        $designation = $item->designation;
        $item->delete();

        return back()->with('success', "Pièce « {$designation} » retirée de l'OR et retournée au stock.");
    }

    // ══════════════════════════════════════════════
    // Modification 6 : Générer une facture depuis l'OR
    // ══════════════════════════════════════════════

    public function generateInvoice(RepairOrder $repairOrder)
    {
        // Vérifier qu'il n'y a pas déjà une facture active
        $existing = Invoice::where('repair_order_id', $repairOrder->id)
            ->whereNotIn('statut', ['annulee'])
            ->first();

        if ($existing) {
            return redirect()->route('invoices.show', $existing)
                ->with('info', "Une facture existe déjà pour cet OR : {$existing->numero}");
        }

        // Créer la facture depuis l'OR
        $invoice = Invoice::createFromRepairOrder($repairOrder);

        ActivityLog::log('create', "Facture {$invoice->numero} générée depuis OR {$repairOrder->numero}", $invoice);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "La facture {$invoice->numero} a été générée depuis l'OR {$repairOrder->numero}.");
    }

    // ══════════════════════════════════════════════
    // Modification 6 : Générer un bon de livraison depuis l'OR
    // ══════════════════════════════════════════════

    public function generateDeliveryNote(RepairOrder $repairOrder)
    {
        // Vérifier qu'il n'y a pas déjà un BL
        if ($repairOrder->deliveryNote) {
            return redirect()->route('delivery-notes.show', $repairOrder->deliveryNote)
                ->with('info', "Un bon de livraison existe déjà pour cet OR.");
        }

        $bl = DeliveryNote::create([
            'numero'           => DeliveryNote::generateNumero(),
            'repair_order_id'  => $repairOrder->id,
            'client_id'        => $repairOrder->client_id,
            'vehicle_id'       => $repairOrder->vehicle_id,
            'created_by'       => auth()->id(),
            'date_livraison'   => now(),
            'kilometrage_sortie' => $repairOrder->kilometrage_sortie,
            'observations'     => "Bon de livraison généré depuis OR {$repairOrder->numero}",
            'statut'           => 'brouillon',
        ]);

        ActivityLog::log('create', "BL {$bl->numero} généré depuis OR {$repairOrder->numero}", $bl);

        return redirect()->route('delivery-notes.show', $bl)
            ->with('success', "Le bon de livraison {$bl->numero} a été généré.");
    }

    // ══════════════════════════════════════════════
    // Modification 6 : Générer un bon de commande depuis l'OR
    // ══════════════════════════════════════════════

    public function generatePurchaseOrder(Request $request, RepairOrder $repairOrder)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
        ]);

        $supplier = Supplier::findOrFail($data['supplier_id']);

        // Récupérer les pièces de l'OR qui nécessitent une commande
        $itemsToOrder = $repairOrder->items()
            ->where('source', 'commande')
            ->whereNull('fournisseur_id')
            ->orWhere('fournisseur_id', $supplier->id)
            ->get();

        $po = PurchaseOrder::create([
            'numero'            => PurchaseOrder::generateNumero(),
            'supplier_id'       => $supplier->id,
            'repair_order_id'   => $repairOrder->id,
            'created_by'        => auth()->id(),
            'date_commande'     => now(),
            'statut'            => 'brouillon',
            'taux_tva'          => $repairOrder->taux_tva ?? 20,
            'notes'             => "Bon de commande généré depuis OR {$repairOrder->numero}",
        ]);

        // Copier les pièces pertinentes
        foreach ($itemsToOrder as $item) {
            $po->items()->create([
                'product_id'    => $item->product_id,
                'designation'   => $item->designation,
                'reference'     => $item->reference,
                'quantite'      => $item->quantite,
                'unite'         => $item->unite,
                'prix_unitaire' => $item->prix_achat > 0 ? $item->prix_achat : $item->prix_unitaire,
                'taux_tva'      => $item->taux_tva,
            ]);
        }

        ActivityLog::log('create', "BC {$po->numero} généré depuis OR {$repairOrder->numero}", $po);

        return redirect()->route('suppliers.order', [$supplier, $po])
            ->with('success', "Le bon de commande {$po->numero} a été généré.");
    }
}
