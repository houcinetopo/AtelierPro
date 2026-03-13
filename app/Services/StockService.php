<?php

namespace App\Services;

use App\Models\Product;
use App\Models\RepairOrder;
use App\Models\RepairOrderItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Ajouter une pièce du stock à un Ordre de Réparation.
     *
     * @param RepairOrder $order
     * @param Product $product
     * @param float $quantite
     * @param array $extra Données supplémentaires (prix_unitaire, remise, etc.)
     * @return array ['success' => bool, 'message' => string, 'item' => ?RepairOrderItem]
     */
    public function addProductToOrder(RepairOrder $order, Product $product, float $quantite, array $extra = []): array
    {
        // Vérifier disponibilité en stock
        $isAvailable = $product->quantite_stock >= $quantite;

        return DB::transaction(function () use ($order, $product, $quantite, $extra, $isAvailable) {

            // Créer la ligne dans l'OR
            $item = $order->items()->create([
                'product_id'     => $product->id,
                'fournisseur_id' => $product->supplier_id,
                'type'           => $product->type === 'outillage' ? 'fourniture' : ($product->type ?? 'piece'),
                'designation'    => $product->designation,
                'reference'      => $product->reference,
                'description'    => $product->description,
                'quantite'       => $quantite,
                'unite'          => $product->unite ?? 'u',
                'prix_unitaire'  => $extra['prix_unitaire'] ?? $product->prix_vente,
                'prix_achat'     => $product->prix_achat ?? 0,
                'remise'         => $extra['remise'] ?? 0,
                'taux_tva'       => $extra['taux_tva'] ?? $order->taux_tva,
                'source'         => $isAvailable ? 'stock' : 'commande',
                'ordre'          => $order->items()->max('ordre') + 1,
            ]);

            if ($isAvailable) {
                // Décrémenter le stock
                $this->decrementStock($product, $quantite, $order);

                return [
                    'success' => true,
                    'message' => "Pièce « {$product->designation} » ajoutée depuis le stock.",
                    'item'    => $item,
                    'stock_alert' => $product->fresh()->quantite_stock <= $product->seuil_alerte,
                ];
            } else {
                return [
                    'success' => true,
                    'message' => "Pièce « {$product->designation} » ajoutée (stock insuffisant : {$product->quantite_stock} disponible sur {$quantite} demandé). Un bon de commande est recommandé.",
                    'item'    => $item,
                    'needs_order' => true,
                    'stock_available' => (float) $product->quantite_stock,
                    'stock_needed' => $quantite,
                ];
            }
        });
    }

    /**
     * Décrémenter le stock d'un produit et enregistrer le mouvement.
     */
    public function decrementStock(Product $product, float $quantite, RepairOrder $order): void
    {
        $product->decrement('quantite_stock', $quantite);

        StockMovement::create([
            'product_id'  => $product->id,
            'type'        => 'sortie',
            'quantite'    => $quantite,
            'motif'       => 'consommation_or',
            'reference_document' => "OR {$order->numero}",
            'notes'       => "Utilisé dans l'ordre de réparation {$order->numero} — {$order->client_name}",
            'recorded_by' => auth()->id(),
            'prix_unitaire' => $product->prix_achat,
            'stock_avant' => $product->quantite_stock + $quantite,
            'stock_apres' => $product->quantite_stock,
        ]);
    }

    /**
     * Annuler le prélèvement d'une pièce (retour au stock).
     */
    public function returnToStock(RepairOrderItem $item, RepairOrder $order): void
    {
        if (!$item->product_id || $item->source !== 'stock') return;

        $product = $item->product;
        if (!$product) return;

        $product->increment('quantite_stock', $item->quantite);

        StockMovement::create([
            'product_id'  => $product->id,
            'type'        => 'entree',
            'quantite'    => $item->quantite,
            'motif'       => 'retour_client',
            'reference_document' => "OR {$order->numero}",
            'notes'       => "Retour stock — annulation dans OR {$order->numero}",
            'recorded_by' => auth()->id(),
            'prix_unitaire' => $product->prix_achat,
            'stock_avant' => $product->quantite_stock - $item->quantite,
            'stock_apres' => $product->quantite_stock,
        ]);
    }
}
