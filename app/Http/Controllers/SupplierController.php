<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    // ══════════════════════════════════════════════
    // INDEX
    // ══════════════════════════════════════════════

    public function index(Request $request)
    {
        $query = Supplier::withCount(['products', 'purchaseOrders']);

        if ($search = $request->get('search')) {
            $query->search($search);
        }
        if ($type = $request->get('type')) {
            $query->byType($type);
        }

        $suppliers = $query->orderBy('raison_sociale')->paginate(15);

        $stats = [
            'total'     => Supplier::actifs()->count(),
            'avec_solde'=> Supplier::actifs()->where('solde_du', '>', 0)->count(),
            'solde_total'=> Supplier::actifs()->sum('solde_du'),
            'commandes_mois' => PurchaseOrder::whereMonth('date_commande', now()->month)
                ->whereYear('date_commande', now()->year)
                ->whereNotIn('statut', ['annulee'])->sum('net_a_payer'),
        ];

        return view('suppliers.index', compact('suppliers', 'stats'));
    }

    // ══════════════════════════════════════════════
    // CREATE / STORE
    // ══════════════════════════════════════════════

    public function create()
    {
        $code = Supplier::generateCode();
        return view('suppliers.create', compact('code'));
    }

    public function store(Request $request)
    {
        $data = $this->validateSupplier($request);
        $data['code'] = $data['code'] ?: Supplier::generateCode();
        $data['actif'] = $request->has('actif');

        $supplier = Supplier::create($data);
        ActivityLog::log('create', "Création fournisseur {$supplier->code} — {$supplier->raison_sociale}", $supplier);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', "Fournisseur {$supplier->raison_sociale} créé.");
    }

    // ══════════════════════════════════════════════
    // SHOW
    // ══════════════════════════════════════════════

    public function show(Supplier $supplier)
    {
        $supplier->load(['products.category', 'purchaseOrders' => fn($q) => $q->latest('date_commande')->limit(10)]);

        $statsCommandes = [
            'total_commandes' => $supplier->purchaseOrders()->count(),
            'en_cours'        => $supplier->purchaseOrders()->whereNotIn('statut', ['livree', 'annulee'])->count(),
            'total_achats'    => $supplier->purchaseOrders()->whereNotIn('statut', ['annulee'])->sum('net_a_payer'),
        ];

        return view('suppliers.show', compact('supplier', 'statsCommandes'));
    }

    // ══════════════════════════════════════════════
    // EDIT / UPDATE
    // ══════════════════════════════════════════════

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $this->validateSupplier($request, $supplier->id);
        $data['actif'] = $request->has('actif');

        $supplier->update($data);
        ActivityLog::log('update', "Modification fournisseur {$supplier->code}", $supplier);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', "Fournisseur mis à jour.");
    }

    // ══════════════════════════════════════════════
    // DESTROY
    // ══════════════════════════════════════════════

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchaseOrders()->whereNotIn('statut', ['annulee', 'livree'])->exists()) {
            return back()->with('error', 'Impossible de supprimer : commandes en cours.');
        }

        $code = $supplier->code;
        ActivityLog::log('delete', "Suppression fournisseur {$code}", $supplier);
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', "Fournisseur {$code} supprimé.");
    }

    // ══════════════════════════════════════════════
    // BON DE COMMANDE : CREATE
    // ══════════════════════════════════════════════

    public function createOrder(Supplier $supplier)
    {
        $numero = PurchaseOrder::generateNumero();
        $products = Product::where('supplier_id', $supplier->id)->actifs()->orderBy('designation')->get();
        // Also get products linked by fournisseur_nom
        $linkedProducts = Product::where('fournisseur_nom', $supplier->raison_sociale)->actifs()->orderBy('designation')->get();
        $allProducts = $products->merge($linkedProducts)->unique('id');

        return view('suppliers.create-order', compact('supplier', 'numero', 'allProducts'));
    }

    public function storeOrder(Request $request, Supplier $supplier)
    {
        $request->validate([
            'date_commande'         => ['required', 'date'],
            'date_livraison_prevue' => ['nullable', 'date'],
            'reference_fournisseur' => ['nullable', 'string', 'max:100'],
            'remise_globale'        => ['nullable', 'numeric', 'min:0'],
            'notes'                 => ['nullable', 'string', 'max:1000'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['nullable', 'exists:products,id'],
            'items.*.designation'   => ['required', 'string', 'max:255'],
            'items.*.reference'     => ['nullable', 'string', 'max:100'],
            'items.*.quantite'      => ['required', 'numeric', 'min:0.01'],
            'items.*.unite'         => ['required'],
            'items.*.prix_unitaire' => ['required', 'numeric', 'min:0'],
            'items.*.remise'        => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::beginTransaction();
        try {
            $order = PurchaseOrder::create([
                'numero'                => PurchaseOrder::generateNumero(),
                'supplier_id'           => $supplier->id,
                'created_by'            => auth()->id(),
                'date_commande'         => $request->date_commande,
                'date_livraison_prevue' => $request->date_livraison_prevue,
                'reference_fournisseur' => $request->reference_fournisseur,
                'remise_globale'        => $request->remise_globale ?? 0,
                'taux_tva'              => 20,
                'notes'                 => $request->notes,
                'statut'                => 'brouillon',
            ]);

            foreach ($request->items as $i => $item) {
                $order->items()->create([
                    'product_id'    => $item['product_id'] ?? null,
                    'designation'   => $item['designation'],
                    'reference'     => $item['reference'] ?? null,
                    'quantite'      => $item['quantite'],
                    'unite'         => $item['unite'],
                    'prix_unitaire' => $item['prix_unitaire'],
                    'remise'        => $item['remise'] ?? 0,
                    'taux_tva'      => 20,
                    'ordre'         => $i,
                ]);
            }

            DB::commit();

            ActivityLog::log('create', "BC {$order->numero} — Fournisseur {$supplier->raison_sociale}", $order);

            return redirect()->route('suppliers.order', [$supplier, $order])
                ->with('success', "Bon de commande {$order->numero} créé.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════
    // BON DE COMMANDE : SHOW
    // ══════════════════════════════════════════════

    public function showOrder(Supplier $supplier, PurchaseOrder $order)
    {
        $order->load(['items.product', 'createdBy']);
        $transitions = $order->getAvailableTransitions();

        return view('suppliers.show-order', compact('supplier', 'order', 'transitions'));
    }

    // ══════════════════════════════════════════════
    // BON DE COMMANDE : CHANGER STATUT
    // ══════════════════════════════════════════════

    public function updateOrderStatut(Request $request, Supplier $supplier, PurchaseOrder $order)
    {
        $request->validate(['statut' => ['required', Rule::in(array_keys(PurchaseOrder::STATUTS))]]);

        if (!$order->canTransitionTo($request->statut)) {
            return back()->with('error', 'Transition de statut non autorisée.');
        }

        $order->transitionTo($request->statut);
        ActivityLog::log('update', "BC {$order->numero} → {$order->statut_label}", $order);

        return back()->with('success', "Statut mis à jour : {$order->statut_label}");
    }

    // ══════════════════════════════════════════════
    // BON DE COMMANDE : RÉCEPTION
    // ══════════════════════════════════════════════

    public function receiveOrder(Request $request, Supplier $supplier, PurchaseOrder $order)
    {
        if (!in_array($order->statut, ['confirmee', 'livree_partiel'])) {
            return back()->with('error', 'La commande doit être confirmée pour être réceptionnée.');
        }

        $request->validate([
            'quantities'   => ['required', 'array'],
            'quantities.*' => ['numeric', 'min:0'],
        ]);

        $order->receiveItems($request->quantities);

        ActivityLog::log('update', "Réception BC {$order->numero}", $order);

        return back()->with('success', 'Réception enregistrée. Le stock a été mis à jour.');
    }

    // ══════════════════════════════════════════════
    // API
    // ══════════════════════════════════════════════

    public function searchApi(Request $request)
    {
        $search = $request->get('q', '');
        if (strlen($search) < 2) return response()->json([]);

        return response()->json(
            Supplier::actifs()->search($search)->limit(10)->get(['id', 'code', 'raison_sociale', 'ville', 'type'])
        );
    }

    // ══════════════════════════════════════════════
    // PRIVATE
    // ══════════════════════════════════════════════

    private function validateSupplier(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code'                   => ['nullable', 'string', 'max:20', Rule::unique('suppliers')->ignore($ignoreId)],
            'raison_sociale'         => ['required', 'string', 'max:200'],
            'nom_contact'            => ['nullable', 'string', 'max:200'],
            'telephone'              => ['nullable', 'string', 'max:20'],
            'telephone_2'            => ['nullable', 'string', 'max:20'],
            'email'                  => ['nullable', 'email', 'max:100'],
            'site_web'               => ['nullable', 'string', 'max:200'],
            'adresse'                => ['nullable', 'string', 'max:300'],
            'ville'                  => ['nullable', 'string', 'max:100'],
            'code_postal'            => ['nullable', 'string', 'max:10'],
            'ice'                    => ['nullable', 'string', 'max:20'],
            'rc'                     => ['nullable', 'string', 'max:50'],
            'if_fiscal'              => ['nullable', 'string', 'max:20'],
            'patente'                => ['nullable', 'string', 'max:20'],
            'rib'                    => ['nullable', 'string', 'max:30'],
            'mode_paiement_defaut'   => ['required', Rule::in(array_keys(Supplier::MODES_PAIEMENT))],
            'delai_paiement_jours'   => ['nullable', 'integer', 'min:0'],
            'remise_globale'         => ['nullable', 'numeric', 'min:0', 'max:100'],
            'delai_livraison_jours'  => ['nullable', 'integer', 'min:0'],
            'type'                   => ['required', Rule::in(array_keys(Supplier::TYPES))],
            'notes'                  => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
