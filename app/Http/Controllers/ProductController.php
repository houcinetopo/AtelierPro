<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    // ══════════════════════════════════════════════
    // INDEX
    // ══════════════════════════════════════════════

    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($search = $request->get('search')) {
            $query->search($search);
        }
        if ($type = $request->get('type')) {
            $query->byType($type);
        }
        if ($category = $request->get('category')) {
            $query->byCategory($category);
        }
        if ($request->get('alerte')) {
            $query->enAlerte();
        }
        if ($request->get('rupture')) {
            $query->enRupture();
        }

        $products = $query->orderBy('designation')->paginate(20);
        $categories = ProductCategory::actives()->get();

        $stats = [
            'total'        => Product::actifs()->count(),
            'valeur_stock' => Product::actifs()->selectRaw('SUM(quantite_stock * prix_achat) as total')->value('total') ?? 0,
            'en_alerte'    => Product::enAlerte()->count(),
            'en_rupture'   => Product::enRupture()->count(),
        ];

        return view('products.index', compact('products', 'categories', 'stats'));
    }

    // ══════════════════════════════════════════════
    // CREATE
    // ══════════════════════════════════════════════

    public function create()
    {
        $categories = ProductCategory::actives()->get();
        $reference = Product::generateReference();
        return view('products.create', compact('categories', 'reference'));
    }

    // ══════════════════════════════════════════════
    // STORE
    // ══════════════════════════════════════════════

    public function store(Request $request)
    {
        $data = $this->validateProduct($request);

        DB::beginTransaction();
        try {
            $data['reference'] = $data['reference'] ?: Product::generateReference($data['type'] ?? 'piece');

            // Calculer la marge si non saisie
            if (empty($data['marge_percent']) && $data['prix_achat'] > 0 && $data['prix_vente'] > 0) {
                $data['marge_percent'] = round(($data['prix_vente'] - $data['prix_achat']) / $data['prix_achat'] * 100, 2);
            }

            $product = Product::create($data);

            // Stock initial = mouvement d'entrée
            if ($product->quantite_stock > 0) {
                $product->stockMovements()->create([
                    'type'        => 'entree',
                    'motif'       => 'achat',
                    'quantite'    => $product->quantite_stock,
                    'stock_avant' => 0,
                    'stock_apres' => $product->quantite_stock,
                    'prix_unitaire' => $product->prix_achat,
                    'montant_total' => $product->quantite_stock * $product->prix_achat,
                    'recorded_by' => auth()->id(),
                    'notes'       => 'Stock initial',
                ]);
            }

            DB::commit();

            ActivityLog::log('create', "Création produit {$product->reference} — {$product->designation}", $product);

            return redirect()->route('products.show', $product)
                ->with('success', "Le produit {$product->reference} a été créé.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════
    // SHOW
    // ══════════════════════════════════════════════

    public function show(Product $product)
    {
        $product->load(['category', 'stockMovements.recordedBy', 'stockMovements.repairOrder']);

        $mouvements = $product->stockMovements()->with('recordedBy')->latest()->paginate(20);

        $statsStock = [
            'entrees_30j'  => $product->stockMovements()->where('type', 'entree')->where('created_at', '>=', now()->subDays(30))->sum('quantite'),
            'sorties_30j'  => $product->stockMovements()->where('type', 'sortie')->where('created_at', '>=', now()->subDays(30))->sum('quantite'),
            'nb_mvts_30j'  => $product->stockMovements()->where('created_at', '>=', now()->subDays(30))->count(),
        ];

        return view('products.show', compact('product', 'mouvements', 'statsStock'));
    }

    // ══════════════════════════════════════════════
    // EDIT / UPDATE
    // ══════════════════════════════════════════════

    public function edit(Product $product)
    {
        $categories = ProductCategory::actives()->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateProduct($request, $product->id);

        if (empty($data['marge_percent']) && $data['prix_achat'] > 0 && $data['prix_vente'] > 0) {
            $data['marge_percent'] = round(($data['prix_vente'] - $data['prix_achat']) / $data['prix_achat'] * 100, 2);
        }

        // Ne pas modifier le stock via update — utiliser mouvements
        unset($data['quantite_stock']);

        $product->update($data);

        ActivityLog::log('update', "Modification produit {$product->reference}", $product);

        return redirect()->route('products.show', $product)
            ->with('success', "Le produit {$product->reference} a été mis à jour.");
    }

    // ══════════════════════════════════════════════
    // DESTROY
    // ══════════════════════════════════════════════

    public function destroy(Product $product)
    {
        if ($product->quantite_stock > 0) {
            return back()->with('error', 'Impossible de supprimer un produit avec du stock. Ajustez le stock à 0 d\'abord.');
        }

        $ref = $product->reference;
        ActivityLog::log('delete', "Suppression produit {$ref} — {$product->designation}", $product);
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', "Le produit {$ref} a été supprimé.");
    }

    // ══════════════════════════════════════════════
    // MOUVEMENT DE STOCK
    // ══════════════════════════════════════════════

    public function addMovement(Request $request, Product $product)
    {
        $data = $request->validate([
            'type'               => ['required', Rule::in(['entree', 'sortie', 'ajustement'])],
            'motif'              => ['required', Rule::in(array_keys(StockMovement::allMotifs()))],
            'quantite'           => ['required', 'numeric', 'min:0.01'],
            'prix_unitaire'      => ['nullable', 'numeric', 'min:0'],
            'reference_document' => ['nullable', 'string', 'max:100'],
            'notes'              => ['nullable', 'string', 'max:500'],
        ]);

        $extra = [
            'prix_unitaire'      => $data['prix_unitaire'] ?? null,
            'montant_total'      => isset($data['prix_unitaire']) ? round($data['quantite'] * $data['prix_unitaire'], 2) : null,
            'reference_document' => $data['reference_document'] ?? null,
            'notes'              => $data['notes'] ?? null,
        ];

        if ($data['type'] === 'entree') {
            $product->addStock($data['quantite'], $data['motif'], $extra);
            $label = 'Entrée';
        } elseif ($data['type'] === 'sortie') {
            if ($data['quantite'] > $product->quantite_stock) {
                return back()->with('error', "Stock insuffisant. Disponible : {$product->quantite_stock} {$product->unite}");
            }
            $product->removeStock($data['quantite'], $data['motif'], $extra);
            $label = 'Sortie';
        } else {
            $product->adjustStock($data['quantite'], $data['notes'] ?? '');
            $label = 'Ajustement';
        }

        ActivityLog::log('create', "{$label} stock {$product->reference} : {$data['quantite']} {$product->unite}", $product);

        return back()->with('success', "{$label} de {$data['quantite']} {$product->unite} enregistrée.");
    }

    // ══════════════════════════════════════════════
    // ALERTES
    // ══════════════════════════════════════════════

    public function alerts()
    {
        $enAlerte = Product::enAlerte()->with('category')
            ->where('quantite_stock', '>', 0)
            ->orderBy('quantite_stock')
            ->get();

        $enRupture = Product::enRupture()->with('category')
            ->orderBy('designation')
            ->get();

        $aCommander = Product::aCommander()->with('category')
            ->orderBy('quantite_stock')
            ->get();

        return view('products.alerts', compact('enAlerte', 'enRupture', 'aCommander'));
    }

    // ══════════════════════════════════════════════
    // CATÉGORIES (mini CRUD)
    // ══════════════════════════════════════════════

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'nom'         => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:300'],
            'couleur'     => ['nullable', 'string', 'max:7'],
        ]);

        ProductCategory::create($data);

        return back()->with('success', "Catégorie \"{$data['nom']}\" créée.");
    }

    public function destroyCategory(ProductCategory $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer une catégorie avec des produits.');
        }
        $category->delete();
        return back()->with('success', 'Catégorie supprimée.');
    }

    // ══════════════════════════════════════════════
    // API : recherche produits (pour formulaires OR/Devis)
    // ══════════════════════════════════════════════

    public function searchApi(Request $request)
    {
        $search = $request->get('q', '');
        if (strlen($search) < 2) return response()->json([]);

        $products = Product::actifs()
            ->search($search)
            ->limit(10)
            ->get(['id', 'reference', 'designation', 'marque', 'prix_vente', 'quantite_stock', 'unite', 'type']);

        return response()->json($products);
    }

    // ══════════════════════════════════════════════
    // PRIVATE
    // ══════════════════════════════════════════════

    private function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'reference'              => ['nullable', 'string', 'max:50', Rule::unique('products')->ignore($ignoreId)],
            'code_barre'             => ['nullable', 'string', 'max:50', Rule::unique('products')->ignore($ignoreId)],
            'category_id'            => ['nullable', 'exists:product_categories,id'],
            'designation'            => ['required', 'string', 'max:255'],
            'description'            => ['nullable', 'string', 'max:1000'],
            'marque'                 => ['nullable', 'string', 'max:100'],
            'modele_compatible'      => ['nullable', 'string', 'max:300'],
            'type'                   => ['required', Rule::in(array_keys(Product::TYPES))],
            'prix_achat'             => ['required', 'numeric', 'min:0'],
            'prix_vente'             => ['required', 'numeric', 'min:0'],
            'taux_tva'               => ['nullable', 'numeric', 'min:0', 'max:30'],
            'marge_percent'          => ['nullable', 'numeric'],
            'quantite_stock'         => ['nullable', 'numeric', 'min:0'],
            'seuil_alerte'           => ['nullable', 'numeric', 'min:0'],
            'seuil_commande'         => ['nullable', 'numeric', 'min:0'],
            'quantite_max'           => ['nullable', 'numeric', 'min:0'],
            'unite'                  => ['required', Rule::in(array_keys(Product::UNITES))],
            'emplacement'            => ['nullable', 'string', 'max:100'],
            'fournisseur_nom'        => ['nullable', 'string', 'max:200'],
            'fournisseur_ref'        => ['nullable', 'string', 'max:100'],
            'delai_livraison_jours'  => ['nullable', 'integer', 'min:0'],
            'actif'                  => ['nullable'],
            'notes'                  => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
