<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'code'                  => 'FRS-00001',
                'raison_sociale'        => 'Auto Pièces Maroc',
                'nom_contact'           => 'Mohamed Alami',
                'telephone'             => '0522-456-789',
                'telephone_2'           => '0661-234-567',
                'email'                 => 'contact@autopiecesmaroc.ma',
                'adresse'               => '123, Zone Industrielle Ain Sebaâ',
                'ville'                 => 'Casablanca',
                'code_postal'           => '20250',
                'ice'                   => '001234567000089',
                'rc'                    => 'RC-CASA-123456',
                'if_fiscal'             => '12345678',
                'mode_paiement_defaut'  => 'cheque',
                'delai_paiement_jours'  => 30,
                'remise_globale'        => 5,
                'delai_livraison_jours' => 2,
                'type'                  => 'pieces',
                'actif'                 => true,
                'solde_du'              => 15400.00,
                'notes'                 => 'Fournisseur principal pièces de rechange. Livraison rapide.',
            ],
            [
                'code'                  => 'FRS-00002',
                'raison_sociale'        => 'Peinture Pro SARL',
                'nom_contact'           => 'Youssef Bennani',
                'telephone'             => '0528-334-455',
                'email'                 => 'commandes@peinturepro.ma',
                'adresse'               => '45, Bd Mohammed V',
                'ville'                 => 'Agadir',
                'code_postal'           => '80000',
                'ice'                   => '002345678000012',
                'mode_paiement_defaut'  => 'virement',
                'delai_paiement_jours'  => 60,
                'remise_globale'        => 8,
                'delai_livraison_jours' => 1,
                'type'                  => 'peinture',
                'actif'                 => true,
                'solde_du'              => 8750.00,
                'notes'                 => 'Distributeur agréé Sikkens et Standox.',
            ],
            [
                'code'                  => 'FRS-00003',
                'raison_sociale'        => 'Outillage Atlas',
                'nom_contact'           => 'Rachid El Fassi',
                'telephone'             => '0535-667-788',
                'email'                 => 'info@outillageatlas.ma',
                'adresse'               => '78, Quartier Industriel Sidi Brahim',
                'ville'                 => 'Fès',
                'code_postal'           => '30000',
                'mode_paiement_defaut'  => 'effet',
                'delai_paiement_jours'  => 90,
                'delai_livraison_jours' => 5,
                'type'                  => 'outillage',
                'actif'                 => true,
                'solde_du'              => 0,
            ],
            [
                'code'                  => 'FRS-00004',
                'raison_sociale'        => 'Souss Auto Distribution',
                'nom_contact'           => 'Fatima Zahra Idrissi',
                'telephone'             => '0528-223-344',
                'telephone_2'           => '0666-987-654',
                'email'                 => 'f.idrissi@soussauto.ma',
                'adresse'               => '12, Zone Industrielle Tassila',
                'ville'                 => 'Agadir',
                'code_postal'           => '80000',
                'ice'                   => '003456789000034',
                'rc'                    => 'RC-AGADIR-78901',
                'if_fiscal'             => '34567890',
                'patente'               => '45678901',
                'rib'                   => '007 780 0012345678901234 56',
                'mode_paiement_defaut'  => 'cheque',
                'delai_paiement_jours'  => 45,
                'remise_globale'        => 3,
                'delai_livraison_jours' => 1,
                'type'                  => 'general',
                'actif'                 => true,
                'solde_du'              => 4200.00,
                'notes'                 => 'Fournisseur local polyvalent. Bon stock de pièces courantes.',
            ],
            [
                'code'                  => 'FRS-00005',
                'raison_sociale'        => 'Maghreb Pare-Brise',
                'nom_contact'           => 'Karim Tahiri',
                'telephone'             => '0522-889-900',
                'email'                 => 'ktahiri@maghrebpb.ma',
                'adresse'               => '56, Ain Borja',
                'ville'                 => 'Casablanca',
                'code_postal'           => '20300',
                'mode_paiement_defaut'  => 'credit',
                'delai_paiement_jours'  => 30,
                'delai_livraison_jours' => 3,
                'type'                  => 'pieces',
                'actif'                 => false,
                'solde_du'              => 0,
                'notes'                 => 'Anciennement actif. Délais de livraison trop longs.',
            ],
        ];

        foreach ($suppliers as $data) {
            Supplier::create($data);
        }

        // Link some products to suppliers
        $autopieces = Supplier::where('code', 'FRS-00001')->first();
        $peinture = Supplier::where('code', 'FRS-00002')->first();

        if ($autopieces) {
            Product::where('type', 'piece')->limit(3)->update(['supplier_id' => $autopieces->id]);
        }
        if ($peinture) {
            Product::where('type', 'fourniture')->limit(2)->update(['supplier_id' => $peinture->id]);
        }

        // Create sample purchase orders
        if ($autopieces) {
            $order = PurchaseOrder::create([
                'numero'                => 'BC-' . now()->year . '-00001',
                'supplier_id'           => $autopieces->id,
                'created_by'            => 1,
                'date_commande'         => now()->subDays(10),
                'date_livraison_prevue' => now()->subDays(3),
                'date_reception'        => now()->subDays(4),
                'statut'                => 'livree',
                'taux_tva'              => 20,
                'remise_globale'        => 100,
                'reference_fournisseur' => 'BL-APM-2025-4521',
                'notes'                 => 'Commande urgente pour réparation accident.',
            ]);

            $pieces = Product::where('type', 'piece')->limit(3)->get();
            foreach ($pieces as $i => $p) {
                $order->items()->create([
                    'product_id'    => $p->id,
                    'designation'   => $p->designation,
                    'reference'     => $p->reference,
                    'quantite'      => rand(1, 4),
                    'quantite_recue'=> rand(1, 4),
                    'unite'         => 'u',
                    'prix_unitaire' => $p->prix_achat,
                    'taux_tva'      => 20,
                    'ordre'         => $i,
                ]);
            }
            $order->recalculateTotals();

            // Another order - in progress
            $order2 = PurchaseOrder::create([
                'numero'                => 'BC-' . now()->year . '-00002',
                'supplier_id'           => $autopieces->id,
                'created_by'            => 1,
                'date_commande'         => now()->subDays(2),
                'date_livraison_prevue' => now()->addDays(3),
                'statut'                => 'confirmee',
                'taux_tva'              => 20,
            ]);

            $order2->items()->create([
                'designation'   => 'Filtre à huile universel',
                'reference'     => 'FH-UNI-001',
                'quantite'      => 10,
                'quantite_recue'=> 0,
                'unite'         => 'u',
                'prix_unitaire' => 45.00,
                'taux_tva'      => 20,
                'ordre'         => 0,
            ]);
            $order2->items()->create([
                'designation'   => 'Plaquettes de frein AV',
                'reference'     => 'PF-AV-2023',
                'quantite'      => 5,
                'quantite_recue'=> 0,
                'unite'         => 'u',
                'prix_unitaire' => 180.00,
                'taux_tva'      => 20,
                'ordre'         => 1,
            ]);
            $order2->recalculateTotals();
        }

        if ($peinture) {
            $order3 = PurchaseOrder::create([
                'numero'                => 'BC-' . now()->year . '-00003',
                'supplier_id'           => $peinture->id,
                'created_by'            => 1,
                'date_commande'         => now(),
                'date_livraison_prevue' => now()->addDays(1),
                'statut'                => 'brouillon',
                'taux_tva'              => 20,
                'notes'                 => 'Stock de peinture à réapprovisionner.',
            ]);

            $order3->items()->create([
                'designation'   => 'Peinture base Sikkens 1L',
                'reference'     => 'SK-BASE-1L',
                'quantite'      => 6,
                'unite'         => 'u',
                'prix_unitaire' => 320.00,
                'taux_tva'      => 20,
                'ordre'         => 0,
            ]);
            $order3->items()->create([
                'designation'   => 'Vernis anti-rayures 500ml',
                'reference'     => 'SK-VRN-500',
                'quantite'      => 4,
                'unite'         => 'u',
                'prix_unitaire' => 185.00,
                'taux_tva'      => 20,
                'ordre'         => 1,
            ]);
            $order3->items()->create([
                'designation'   => 'Durcisseur rapide 250ml',
                'reference'     => 'SK-DUR-250',
                'quantite'      => 8,
                'unite'         => 'u',
                'prix_unitaire' => 95.00,
                'taux_tva'      => 20,
                'ordre'         => 2,
            ]);
            $order3->recalculateTotals();
        }
    }
}
