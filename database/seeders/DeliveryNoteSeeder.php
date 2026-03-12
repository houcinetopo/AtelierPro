<?php

namespace Database\Seeders;

use App\Models\DeliveryNote;
use App\Models\RepairOrder;
use App\Models\User;
use Illuminate\Database\Seeder;

class DeliveryNoteSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        // Créer des BL pour les ordres terminés et livrés
        $eligibleOrders = RepairOrder::with(['client', 'vehicle', 'items'])
            ->whereIn('status', ['termine', 'livre'])
            ->get();

        foreach ($eligibleOrders as $order) {
            // Vérifier qu'un BL n'existe pas déjà
            if (DeliveryNote::where('repair_order_id', $order->id)->exists()) {
                continue;
            }

            $totalTtc = (float) $order->net_a_payer;
            $isPaid = rand(0, 3) > 0; // 75% payés
            $montantPaye = $isPaid ? $totalTtc : round($totalTtc * rand(0, 70) / 100, 2);
            $resteAPayer = max(0, $totalTtc - $montantPaye);

            $dateLivraison = $order->date_livraison_effective
                ?? ($order->date_prevue_livraison ?? $order->date_reception->addDays(rand(3, 10)));

            $modes = ['especes', 'cheque', 'virement', 'carte'];
            $receptionnaires = [
                ['nom' => $order->client?->nom_complet, 'cin' => $order->client?->cin],
                ['nom' => 'Ahmed Bennani', 'cin' => 'BK789012'],
                ['nom' => 'Youssef El Amri', 'cin' => 'BA345678'],
            ];
            $recept = $receptionnaires[array_rand($receptionnaires)];

            $travaux = $order->items->map(fn($i) => "- {$i->designation}")->implode("\n");

            $recommandations = collect([
                'Prochaine vidange dans 10 000 km',
                'Vérifier la pression des pneus dans 1 mois',
                'Contrôle technique à prévoir avant le ' . now()->addMonths(3)->format('d/m/Y'),
                'Retour atelier dans 2 semaines pour vérification',
                null,
            ])->random();

            DeliveryNote::create([
                'numero'              => DeliveryNote::generateNumero(),
                'repair_order_id'     => $order->id,
                'client_id'           => $order->client_id,
                'vehicle_id'          => $order->vehicle_id,
                'created_by'          => $admin?->id,
                'date_livraison'      => $dateLivraison,
                'heure_livraison'     => sprintf('%02d:%02d', rand(8, 18), rand(0, 59)),
                'kilometrage_sortie'  => $order->kilometrage_entree ? $order->kilometrage_entree + rand(5, 50) : null,
                'niveau_carburant'    => ['1/4', '1/2', '3/4'][array_rand(['1/4', '1/2', '3/4'])],
                'travaux_effectues'   => $travaux,
                'observations_sortie' => 'Véhicule en bon état à la remise. Nettoyage effectué.',
                'reserves_client'     => rand(0, 4) === 0 ? 'Légère différence de teinte sur aile droite' : null,
                'recommandations'     => $recommandations,
                'nom_receptionnaire'  => $recept['nom'],
                'cin_receptionnaire'  => $recept['cin'],
                'signe_atelier'       => true,
                'signe_client'        => rand(0, 5) > 0,
                'total_ttc'           => $totalTtc,
                'montant_paye'        => $montantPaye,
                'reste_a_payer'       => $resteAPayer,
                'mode_paiement'       => $resteAPayer > 0 ? 'credit' : $modes[array_rand($modes)],
                'statut'              => 'valide',
                'notes'               => null,
            ]);
        }
    }
}
