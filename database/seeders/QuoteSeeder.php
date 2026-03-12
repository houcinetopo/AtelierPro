<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Quote;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuoteSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $clients = Client::with('vehicles')->get();

        if ($clients->isEmpty()) return;

        $devis = [
            [
                'description'   => 'Réparation carrosserie suite accident — choc latéral droit',
                'duree'         => 7,
                'statut'        => 'accepte',
                'days_ago'      => 15,
                'items'         => [
                    ['type' => 'piece', 'designation' => 'Aile avant droite', 'reference' => 'CAR-AV-DR', 'quantite' => 1, 'unite' => 'u', 'prix' => 1200],
                    ['type' => 'piece', 'designation' => 'Porte avant droite', 'reference' => 'PRT-AV-DR', 'quantite' => 1, 'unite' => 'u', 'prix' => 2800],
                    ['type' => 'main_oeuvre', 'designation' => 'Débosselage et redressage', 'quantite' => 4, 'unite' => 'h', 'prix' => 200],
                    ['type' => 'main_oeuvre', 'designation' => 'Peinture complète côté droit', 'quantite' => 6, 'unite' => 'h', 'prix' => 180],
                    ['type' => 'fourniture', 'designation' => 'Kit peinture RAL assorti', 'quantite' => 1, 'unite' => 'forfait', 'prix' => 950],
                ],
            ],
            [
                'description'   => 'Remplacement pare-brise avec calibrage caméra ADAS',
                'duree'         => 2,
                'statut'        => 'envoye',
                'days_ago'      => 3,
                'items'         => [
                    ['type' => 'piece', 'designation' => 'Pare-brise avec capteur pluie', 'reference' => 'PB-CP-01', 'quantite' => 1, 'unite' => 'u', 'prix' => 3200],
                    ['type' => 'main_oeuvre', 'designation' => 'Dépose/pose pare-brise', 'quantite' => 2, 'unite' => 'h', 'prix' => 200],
                    ['type' => 'main_oeuvre', 'designation' => 'Calibrage caméra ADAS', 'quantite' => 1, 'unite' => 'forfait', 'prix' => 600],
                    ['type' => 'fourniture', 'designation' => 'Kit joints et colle polyuréthane', 'quantite' => 1, 'unite' => 'u', 'prix' => 180],
                ],
            ],
            [
                'description'   => 'Réfection complète du système de freinage',
                'duree'         => 3,
                'statut'        => 'refuse',
                'days_ago'      => 20,
                'motif'         => 'Client a trouvé moins cher chez un concurrent',
                'items'         => [
                    ['type' => 'piece', 'designation' => 'Disques de frein AV (x2)', 'reference' => 'FR-DIS-AV', 'quantite' => 2, 'unite' => 'u', 'prix' => 450],
                    ['type' => 'piece', 'designation' => 'Plaquettes de frein AV', 'reference' => 'FR-PLQ-AV', 'quantite' => 1, 'unite' => 'u', 'prix' => 280],
                    ['type' => 'piece', 'designation' => 'Disques de frein AR (x2)', 'reference' => 'FR-DIS-AR', 'quantite' => 2, 'unite' => 'u', 'prix' => 380],
                    ['type' => 'piece', 'designation' => 'Plaquettes de frein AR', 'reference' => 'FR-PLQ-AR', 'quantite' => 1, 'unite' => 'u', 'prix' => 220],
                    ['type' => 'main_oeuvre', 'designation' => 'Remplacement freinage complet', 'quantite' => 3, 'unite' => 'h', 'prix' => 200],
                    ['type' => 'fourniture', 'designation' => 'Liquide de frein DOT4 (1L)', 'quantite' => 2, 'unite' => 'l', 'prix' => 85],
                ],
            ],
            [
                'description'   => 'Traitement anti-corrosion et protection carrosserie',
                'duree'         => 4,
                'statut'        => 'brouillon',
                'days_ago'      => 1,
                'items'         => [
                    ['type' => 'main_oeuvre', 'designation' => 'Ponçage zones corrodées', 'quantite' => 5, 'unite' => 'h', 'prix' => 180],
                    ['type' => 'main_oeuvre', 'designation' => 'Application traitement anti-rouille', 'quantite' => 3, 'unite' => 'h', 'prix' => 180],
                    ['type' => 'fourniture', 'designation' => 'Produit anti-corrosion professionnel', 'quantite' => 2, 'unite' => 'l', 'prix' => 320],
                    ['type' => 'fourniture', 'designation' => 'Apprêt et peinture retouche', 'quantite' => 1, 'unite' => 'forfait', 'prix' => 580],
                ],
            ],
            [
                'description'   => 'Révision complète 100 000 km — distribution + courroie accessoires',
                'duree'         => 2,
                'statut'        => 'converti',
                'days_ago'      => 30,
                'items'         => [
                    ['type' => 'piece', 'designation' => 'Kit distribution complet', 'reference' => 'KIT-DIST-01', 'quantite' => 1, 'unite' => 'u', 'prix' => 1800],
                    ['type' => 'piece', 'designation' => 'Pompe à eau', 'reference' => 'PMP-EAU-01', 'quantite' => 1, 'unite' => 'u', 'prix' => 650],
                    ['type' => 'piece', 'designation' => 'Courroie accessoires', 'reference' => 'CRR-ACC-01', 'quantite' => 1, 'unite' => 'u', 'prix' => 180],
                    ['type' => 'main_oeuvre', 'designation' => 'Remplacement distribution', 'quantite' => 4, 'unite' => 'h', 'prix' => 200],
                    ['type' => 'fourniture', 'designation' => 'Liquide de refroidissement (5L)', 'quantite' => 1, 'unite' => 'u', 'prix' => 120],
                ],
            ],
            [
                'description'   => 'Remise en état optiques avant — polissage et remplacement',
                'duree'         => 1,
                'statut'        => 'expire',
                'days_ago'      => 45,
                'items'         => [
                    ['type' => 'piece', 'designation' => 'Optique avant gauche', 'reference' => 'OPT-AVG-01', 'quantite' => 1, 'unite' => 'u', 'prix' => 1400],
                    ['type' => 'main_oeuvre', 'designation' => 'Polissage optique droite', 'quantite' => 1, 'unite' => 'h', 'prix' => 150],
                    ['type' => 'main_oeuvre', 'designation' => 'Dépose/pose optique gauche', 'quantite' => 1, 'unite' => 'h', 'prix' => 200],
                ],
            ],
        ];

        foreach ($devis as $index => $d) {
            $client = $clients->random();
            $vehicle = $client->vehicles->isNotEmpty() ? $client->vehicles->random() : null;

            $dateDevis = now()->subDays($d['days_ago']);
            $dateValidite = $dateDevis->copy()->addDays(30);

            $quote = Quote::create([
                'numero'               => Quote::generateNumero(),
                'client_id'            => $client->id,
                'vehicle_id'           => $vehicle?->id,
                'created_by'           => $admin?->id,
                'date_devis'           => $dateDevis,
                'date_validite'        => $dateValidite,
                'date_acceptation'     => $d['statut'] === 'accepte' ? $dateDevis->copy()->addDays(rand(1, 5)) : null,
                'statut'               => $d['statut'],
                'description_travaux'  => $d['description'],
                'conditions'           => 'Paiement à la livraison du véhicule. Devis valable 30 jours.',
                'notes'                => null,
                'motif_refus'          => $d['motif'] ?? null,
                'duree_estimee_jours'  => $d['duree'],
                'taux_tva'             => 20,
                'remise_globale'       => $index % 3 === 0 ? rand(50, 200) : 0,
            ]);

            foreach ($d['items'] as $i => $item) {
                $quote->items()->create([
                    'type'          => $item['type'],
                    'designation'   => $item['designation'],
                    'reference'     => $item['reference'] ?? null,
                    'quantite'      => $item['quantite'],
                    'unite'         => $item['unite'],
                    'prix_unitaire' => $item['prix'],
                    'remise'        => 0,
                    'taux_tva'      => 20,
                    'ordre'         => $i,
                ]);
            }
        }
    }
}
