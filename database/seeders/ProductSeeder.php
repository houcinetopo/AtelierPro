<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        // ── Catégories ──
        $categories = [
            ['nom' => 'Carrosserie', 'slug' => 'carrosserie', 'couleur' => '#EF4444', 'ordre' => 1],
            ['nom' => 'Peinture', 'slug' => 'peinture', 'couleur' => '#8B5CF6', 'ordre' => 2],
            ['nom' => 'Freinage', 'slug' => 'freinage', 'couleur' => '#F59E0B', 'ordre' => 3],
            ['nom' => 'Moteur', 'slug' => 'moteur', 'couleur' => '#3B82F6', 'ordre' => 4],
            ['nom' => 'Éclairage', 'slug' => 'eclairage', 'couleur' => '#10B981', 'ordre' => 5],
            ['nom' => 'Consommables', 'slug' => 'consommables', 'couleur' => '#6B7280', 'ordre' => 6],
            ['nom' => 'Outillage', 'slug' => 'outillage', 'couleur' => '#0EA5E9', 'ordre' => 7],
        ];

        foreach ($categories as $cat) {
            ProductCategory::create($cat);
        }

        $catMap = ProductCategory::pluck('id', 'slug');

        // ── Produits ──
        $products = [
            // Carrosserie
            ['ref' => 'PR-00001', 'designation' => 'Aile avant droite universelle', 'type' => 'piece', 'cat' => 'carrosserie', 'marque' => 'Générique', 'pa' => 450, 'pv' => 750, 'stock' => 8, 'alerte' => 3, 'cmd' => 5, 'fourn' => 'Auto Parts Casablanca', 'fref' => 'AP-AV-001', 'delai' => 3],
            ['ref' => 'PR-00002', 'designation' => 'Pare-chocs avant peint', 'type' => 'piece', 'cat' => 'carrosserie', 'marque' => 'OEM', 'pa' => 800, 'pv' => 1350, 'stock' => 4, 'alerte' => 2, 'cmd' => 4, 'fourn' => 'Auto Parts Casablanca', 'fref' => 'AP-PC-002', 'delai' => 5],
            ['ref' => 'PR-00003', 'designation' => 'Porte avant gauche (tôle nue)', 'type' => 'piece', 'cat' => 'carrosserie', 'marque' => 'Générique', 'pa' => 1200, 'pv' => 2000, 'stock' => 2, 'alerte' => 2, 'cmd' => 3, 'fourn' => 'Tanger Pièces', 'fref' => 'TP-PRT-003', 'delai' => 7],
            ['ref' => 'PR-00004', 'designation' => 'Capot moteur', 'type' => 'piece', 'cat' => 'carrosserie', 'marque' => 'OEM', 'pa' => 1500, 'pv' => 2500, 'stock' => 1, 'alerte' => 1, 'cmd' => 2, 'fourn' => 'Tanger Pièces', 'fref' => 'TP-CAP-004', 'delai' => 7],
            ['ref' => 'PR-00005', 'designation' => 'Rétroviseur extérieur droit', 'type' => 'piece', 'cat' => 'carrosserie', 'marque' => 'Valeo', 'pa' => 280, 'pv' => 480, 'stock' => 6, 'alerte' => 3, 'cmd' => 5, 'fourn' => 'Valeo Maroc', 'fref' => 'VAL-RET-005', 'delai' => 4],

            // Peinture
            ['ref' => 'FN-00001', 'designation' => 'Peinture base eau RAL 9005 (noir)', 'type' => 'fourniture', 'cat' => 'peinture', 'marque' => 'Spies Hecker', 'pa' => 320, 'pv' => 450, 'stock' => 12, 'alerte' => 5, 'cmd' => 8, 'unite' => 'l', 'fourn' => 'Peinture Pro Agadir', 'fref' => 'SH-9005', 'delai' => 2],
            ['ref' => 'FN-00002', 'designation' => 'Vernis brillant 2K', 'type' => 'fourniture', 'cat' => 'peinture', 'marque' => 'Spies Hecker', 'pa' => 280, 'pv' => 400, 'stock' => 8, 'alerte' => 4, 'cmd' => 6, 'unite' => 'l', 'fourn' => 'Peinture Pro Agadir', 'fref' => 'SH-VB-2K', 'delai' => 2],
            ['ref' => 'FN-00003', 'designation' => 'Apprêt époxy 2K gris', 'type' => 'fourniture', 'cat' => 'peinture', 'marque' => 'Sikkens', 'pa' => 180, 'pv' => 280, 'stock' => 6, 'alerte' => 3, 'cmd' => 5, 'unite' => 'l', 'fourn' => 'Peinture Pro Agadir', 'fref' => 'SK-APR-GR', 'delai' => 2],
            ['ref' => 'FN-00004', 'designation' => 'Mastic polyester + durcisseur', 'type' => 'fourniture', 'cat' => 'peinture', 'marque' => 'Teroson', 'pa' => 85, 'pv' => 140, 'stock' => 15, 'alerte' => 5, 'cmd' => 10, 'unite' => 'kg', 'fourn' => 'Peinture Pro Agadir', 'fref' => 'TER-MST-01', 'delai' => 2],
            ['ref' => 'FN-00005', 'designation' => 'Diluant rapide', 'type' => 'fourniture', 'cat' => 'peinture', 'marque' => 'Spies Hecker', 'pa' => 65, 'pv' => 95, 'stock' => 20, 'alerte' => 8, 'cmd' => 15, 'unite' => 'l', 'fourn' => 'Peinture Pro Agadir', 'fref' => 'SH-DIL-R', 'delai' => 2],

            // Freinage
            ['ref' => 'PR-00006', 'designation' => 'Disques de frein AV (jeu x2)', 'type' => 'piece', 'cat' => 'freinage', 'marque' => 'Brembo', 'pa' => 350, 'pv' => 580, 'stock' => 10, 'alerte' => 4, 'cmd' => 6, 'unite' => 'jeu', 'fourn' => 'Feu Vert Pro', 'fref' => 'BRM-DIS-AV', 'delai' => 3],
            ['ref' => 'PR-00007', 'designation' => 'Plaquettes de frein AV', 'type' => 'piece', 'cat' => 'freinage', 'marque' => 'TRW', 'pa' => 180, 'pv' => 300, 'stock' => 12, 'alerte' => 5, 'cmd' => 8, 'unite' => 'jeu', 'fourn' => 'Feu Vert Pro', 'fref' => 'TRW-PLQ-AV', 'delai' => 3],
            ['ref' => 'FN-00006', 'designation' => 'Liquide de frein DOT4 (1L)', 'type' => 'fourniture', 'cat' => 'freinage', 'marque' => 'Bosch', 'pa' => 55, 'pv' => 95, 'stock' => 18, 'alerte' => 6, 'cmd' => 10, 'unite' => 'l', 'fourn' => 'Feu Vert Pro', 'fref' => 'BSH-DOT4', 'delai' => 2],

            // Moteur
            ['ref' => 'PR-00008', 'designation' => 'Kit distribution complet', 'type' => 'piece', 'cat' => 'moteur', 'marque' => 'Gates', 'pa' => 850, 'pv' => 1400, 'stock' => 3, 'alerte' => 2, 'cmd' => 3, 'unite' => 'kit', 'fourn' => 'Motor Parts Rabat', 'fref' => 'GAT-KIT-D', 'delai' => 5],
            ['ref' => 'PR-00009', 'designation' => 'Filtre à huile', 'type' => 'piece', 'cat' => 'moteur', 'marque' => 'Mann', 'pa' => 35, 'pv' => 65, 'stock' => 25, 'alerte' => 10, 'cmd' => 15, 'fourn' => 'Motor Parts Rabat', 'fref' => 'MAN-FH-01', 'delai' => 3],
            ['ref' => 'FN-00007', 'designation' => 'Huile moteur 5W40 (5L)', 'type' => 'fourniture', 'cat' => 'moteur', 'marque' => 'Total', 'pa' => 180, 'pv' => 280, 'stock' => 14, 'alerte' => 5, 'cmd' => 8, 'unite' => 'u', 'fourn' => 'Total Energies', 'fref' => 'TOT-5W40-5L', 'delai' => 2],
            ['ref' => 'FN-00008', 'designation' => 'Liquide de refroidissement (5L)', 'type' => 'fourniture', 'cat' => 'moteur', 'marque' => 'Total', 'pa' => 65, 'pv' => 110, 'stock' => 10, 'alerte' => 4, 'cmd' => 6, 'fourn' => 'Total Energies', 'fref' => 'TOT-LR-5L', 'delai' => 2],

            // Éclairage
            ['ref' => 'PR-00010', 'designation' => 'Optique avant H7 (phare)', 'type' => 'piece', 'cat' => 'eclairage', 'marque' => 'Valeo', 'pa' => 650, 'pv' => 1100, 'stock' => 4, 'alerte' => 2, 'cmd' => 3, 'fourn' => 'Valeo Maroc', 'fref' => 'VAL-OPT-H7', 'delai' => 5],
            ['ref' => 'PR-00011', 'designation' => 'Feu arrière complet', 'type' => 'piece', 'cat' => 'eclairage', 'marque' => 'Générique', 'pa' => 220, 'pv' => 380, 'stock' => 6, 'alerte' => 3, 'cmd' => 4, 'fourn' => 'Auto Parts Casablanca', 'fref' => 'AP-FAR-01', 'delai' => 3],

            // Consommables
            ['ref' => 'FN-00009', 'designation' => 'Papier abrasif P800 (rouleau)', 'type' => 'fourniture', 'cat' => 'consommables', 'marque' => '3M', 'pa' => 45, 'pv' => 70, 'stock' => 30, 'alerte' => 10, 'cmd' => 20, 'fourn' => '3M Maroc', 'fref' => '3M-P800-R', 'delai' => 3],
            ['ref' => 'FN-00010', 'designation' => 'Ruban de masquage 48mm', 'type' => 'fourniture', 'cat' => 'consommables', 'marque' => '3M', 'pa' => 25, 'pv' => 40, 'stock' => 40, 'alerte' => 15, 'cmd' => 25, 'fourn' => '3M Maroc', 'fref' => '3M-MSQ-48', 'delai' => 3],
            ['ref' => 'FN-00011', 'designation' => 'Colle polyuréthane pare-brise', 'type' => 'fourniture', 'cat' => 'consommables', 'marque' => 'Sikaflex', 'pa' => 120, 'pv' => 190, 'stock' => 8, 'alerte' => 3, 'cmd' => 5, 'fourn' => 'Sika Maroc', 'fref' => 'SIKA-PU-PB', 'delai' => 4],

            // Outillage
            ['ref' => 'OT-00001', 'designation' => 'Kit tas et marteaux carrosserie', 'type' => 'outillage', 'cat' => 'outillage', 'marque' => 'Facom', 'pa' => 850, 'pv' => 1200, 'stock' => 2, 'alerte' => 1, 'cmd' => 1, 'fourn' => 'Facom Pro', 'fref' => 'FAC-KIT-MC', 'delai' => 7],
            ['ref' => 'OT-00002', 'designation' => 'Pistolet peinture HVLP 1.3mm', 'type' => 'outillage', 'cat' => 'outillage', 'marque' => 'DeVilbiss', 'pa' => 1200, 'pv' => 1800, 'stock' => 3, 'alerte' => 1, 'cmd' => 1, 'fourn' => 'Equip Auto', 'fref' => 'DV-HVLP-13', 'delai' => 10],

            // Produits en alerte/rupture pour les tests
            ['ref' => 'PR-00012', 'designation' => 'Pare-brise feuilleté', 'type' => 'piece', 'cat' => 'carrosserie', 'marque' => 'Saint-Gobain', 'pa' => 1800, 'pv' => 3000, 'stock' => 0, 'alerte' => 1, 'cmd' => 2, 'fourn' => 'Vitrage Express', 'fref' => 'SG-PB-FEU', 'delai' => 5],
            ['ref' => 'PR-00013', 'designation' => 'Disques de frein AR (jeu x2)', 'type' => 'piece', 'cat' => 'freinage', 'marque' => 'Brembo', 'pa' => 300, 'pv' => 500, 'stock' => 1, 'alerte' => 3, 'cmd' => 5, 'unite' => 'jeu', 'fourn' => 'Feu Vert Pro', 'fref' => 'BRM-DIS-AR', 'delai' => 3],
        ];

        foreach ($products as $p) {
            $product = Product::create([
                'reference'              => $p['ref'],
                'designation'            => $p['designation'],
                'type'                   => $p['type'],
                'category_id'            => $catMap[$p['cat']] ?? null,
                'marque'                 => $p['marque'] ?? null,
                'prix_achat'             => $p['pa'],
                'prix_vente'             => $p['pv'],
                'taux_tva'               => 20,
                'marge_percent'          => round(($p['pv'] - $p['pa']) / $p['pa'] * 100, 2),
                'quantite_stock'         => $p['stock'],
                'seuil_alerte'           => $p['alerte'],
                'seuil_commande'         => $p['cmd'],
                'unite'                  => $p['unite'] ?? 'u',
                'fournisseur_nom'        => $p['fourn'] ?? null,
                'fournisseur_ref'        => $p['fref'] ?? null,
                'delai_livraison_jours'  => $p['delai'] ?? null,
                'actif'                  => true,
            ]);

            // Mouvement initial
            if ($product->quantite_stock > 0) {
                $product->stockMovements()->create([
                    'type'          => 'entree',
                    'motif'         => 'achat',
                    'quantite'      => $product->quantite_stock,
                    'stock_avant'   => 0,
                    'stock_apres'   => $product->quantite_stock,
                    'prix_unitaire' => $product->prix_achat,
                    'montant_total' => $product->quantite_stock * $product->prix_achat,
                    'recorded_by'   => $admin?->id,
                    'notes'         => 'Stock initial',
                ]);
            }
        }
    }
}
