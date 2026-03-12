<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\RepairOrder;
use App\Models\RepairOrderItem;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class RepairOrderSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::with('vehicles')->get();
        if ($clients->isEmpty()) return;

        $techniciens = User::where('role', 'technicien')->pluck('id')->toArray();
        $admin = User::where('role', 'admin')->first();

        $orders = [
            [
                'description_panne' => 'Choc avant côté droit suite à un accrochage. Pare-chocs, aile et optique endommagés.',
                'diagnostic'        => 'Remplacement pare-chocs AV, redressage aile AVD, remplacement optique AVD',
                'status'            => 'en_cours',
                'days_ago'          => 5,
                'delivery_days'     => 7,
                'items' => [
                    ['type' => 'piece', 'designation' => 'Pare-chocs avant', 'quantite' => 1, 'prix_unitaire' => 1800, 'unite' => 'u'],
                    ['type' => 'piece', 'designation' => 'Optique avant droit', 'quantite' => 1, 'prix_unitaire' => 950, 'unite' => 'u'],
                    ['type' => 'main_oeuvre', 'designation' => 'Redressage aile avant droite', 'quantite' => 4, 'prix_unitaire' => 200, 'unite' => 'h'],
                    ['type' => 'main_oeuvre', 'designation' => 'Montage et réglage', 'quantite' => 2, 'prix_unitaire' => 200, 'unite' => 'h'],
                    ['type' => 'fourniture', 'designation' => 'Peinture et apprêt', 'quantite' => 1, 'prix_unitaire' => 600, 'unite' => 'forfait'],
                ],
            ],
            [
                'description_panne' => 'Peinture complète suite à corrosion avancée sur capot et portières.',
                'diagnostic'        => 'Traitement anti-rouille + peinture complète',
                'status'            => 'termine',
                'days_ago'          => 12,
                'delivery_days'     => 8,
                'items' => [
                    ['type' => 'main_oeuvre', 'designation' => 'Préparation et ponçage carrosserie', 'quantite' => 8, 'prix_unitaire' => 180, 'unite' => 'h'],
                    ['type' => 'main_oeuvre', 'designation' => 'Peinture complète véhicule', 'quantite' => 10, 'prix_unitaire' => 200, 'unite' => 'h'],
                    ['type' => 'fourniture', 'designation' => 'Peinture base + vernis', 'quantite' => 1, 'prix_unitaire' => 2500, 'unite' => 'forfait'],
                    ['type' => 'fourniture', 'designation' => 'Traitement anti-rouille', 'quantite' => 1, 'prix_unitaire' => 400, 'unite' => 'forfait'],
                ],
            ],
            [
                'description_panne' => 'Remplacement pare-brise fissuré.',
                'diagnostic'        => 'Pare-brise à remplacer, joints OK',
                'status'            => 'livre',
                'days_ago'          => 20,
                'delivery_days'     => 2,
                'items' => [
                    ['type' => 'piece', 'designation' => 'Pare-brise avant', 'quantite' => 1, 'prix_unitaire' => 1200, 'unite' => 'u'],
                    ['type' => 'main_oeuvre', 'designation' => 'Dépose et pose pare-brise', 'quantite' => 2, 'prix_unitaire' => 200, 'unite' => 'h'],
                    ['type' => 'fourniture', 'designation' => 'Colle et joint', 'quantite' => 1, 'prix_unitaire' => 150, 'unite' => 'forfait'],
                ],
            ],
            [
                'description_panne' => 'Bruit moteur anormal, fumée blanche à l\'échappement.',
                'diagnostic'        => 'Joint de culasse à remplacer',
                'status'            => 'en_attente',
                'days_ago'          => 3,
                'delivery_days'     => 10,
                'items' => [
                    ['type' => 'piece', 'designation' => 'Joint de culasse', 'quantite' => 1, 'prix_unitaire' => 450, 'unite' => 'u'],
                    ['type' => 'main_oeuvre', 'designation' => 'Dépose/repose culasse', 'quantite' => 6, 'prix_unitaire' => 200, 'unite' => 'h'],
                    ['type' => 'fourniture', 'designation' => 'Liquide de refroidissement', 'quantite' => 5, 'prix_unitaire' => 80, 'unite' => 'l'],
                ],
            ],
            [
                'description_panne' => 'Révision complète + vidange + freins.',
                'diagnostic'        => null,
                'status'            => 'brouillon',
                'days_ago'          => 0,
                'delivery_days'     => 3,
                'items' => [
                    ['type' => 'main_oeuvre', 'designation' => 'Révision générale', 'quantite' => 3, 'prix_unitaire' => 200, 'unite' => 'h'],
                    ['type' => 'piece', 'designation' => 'Filtre à huile', 'quantite' => 1, 'prix_unitaire' => 80, 'unite' => 'u'],
                    ['type' => 'piece', 'designation' => 'Filtre à air', 'quantite' => 1, 'prix_unitaire' => 120, 'unite' => 'u'],
                    ['type' => 'fourniture', 'designation' => 'Huile moteur 5W30', 'quantite' => 5, 'prix_unitaire' => 90, 'unite' => 'l'],
                    ['type' => 'piece', 'designation' => 'Plaquettes de frein avant', 'quantite' => 1, 'prix_unitaire' => 350, 'unite' => 'u'],
                ],
            ],
        ];

        foreach ($orders as $i => $orderData) {
            $client = $clients->random();
            $vehicle = $client->vehicles->isNotEmpty() ? $client->vehicles->random() : null;
            if (!$vehicle) continue;

            $reception = now()->subDays($orderData['days_ago']);

            $order = RepairOrder::create([
                'numero'                => RepairOrder::generateNumero(),
                'client_id'             => $client->id,
                'vehicle_id'            => $vehicle->id,
                'technicien_id'         => !empty($techniciens) ? $techniciens[array_rand($techniciens)] : null,
                'created_by'            => $admin?->id,
                'date_reception'        => $reception,
                'date_prevue_livraison' => $reception->copy()->addDays($orderData['delivery_days']),
                'date_livraison_effective' => $orderData['status'] === 'livre' ? $reception->copy()->addDays($orderData['delivery_days'] - 1) : null,
                'description_panne'     => $orderData['description_panne'],
                'diagnostic'            => $orderData['diagnostic'],
                'status'                => $orderData['status'],
                'kilometrage_entree'    => rand(20000, 180000),
                'niveau_carburant'      => ['1/4', '1/2', '3/4', 'plein'][array_rand(['1/4', '1/2', '3/4', 'plein'])],
                'source_ordre'          => ['direct', 'telephone', 'assurance'][array_rand(['direct', 'telephone', 'assurance'])],
                'taux_tva'              => 20,
            ]);

            foreach ($orderData['items'] as $j => $itemData) {
                $order->items()->create([
                    'type'          => $itemData['type'],
                    'designation'   => $itemData['designation'],
                    'quantite'      => $itemData['quantite'],
                    'unite'         => $itemData['unite'],
                    'prix_unitaire' => $itemData['prix_unitaire'],
                    'remise'        => 0,
                    'taux_tva'      => 20,
                    'ordre'         => $j,
                ]);
            }
        }
    }
}
