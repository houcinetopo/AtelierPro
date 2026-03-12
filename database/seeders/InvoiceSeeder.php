<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\RepairOrder;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        // Créer des factures depuis les OR livrés/facturés
        $orders = RepairOrder::with(['client', 'vehicle', 'items', 'deliveryNote'])
            ->whereIn('status', ['livre', 'facture'])
            ->get();

        foreach ($orders as $order) {
            if (Invoice::where('repair_order_id', $order->id)->exists()) continue;

            $totalTtc = (float) $order->net_a_payer;
            $daysAgo = rand(5, 40);
            $dateFacture = now()->subDays($daysAgo);

            $invoice = Invoice::create([
                'numero'              => Invoice::generateNumero(),
                'repair_order_id'     => $order->id,
                'client_id'           => $order->client_id,
                'vehicle_id'          => $order->vehicle_id,
                'delivery_note_id'    => $order->deliveryNote?->id,
                'created_by'          => $admin?->id,
                'date_facture'        => $dateFacture,
                'date_echeance'       => $dateFacture->copy()->addDays(30),
                'statut'              => 'emise',
                'taux_tva'            => $order->taux_tva,
                'remise_globale'      => $order->remise_globale,
                'objet'               => "Réparation véhicule — OR {$order->numero}",
                'conditions_paiement' => 'Paiement à 30 jours.',
                'mentions_legales'    => Invoice::MENTIONS_LEGALES_DEFAULT,
            ]);

            // Copier les lignes
            foreach ($order->items as $i => $item) {
                $invoice->items()->create([
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

            // Simuler des paiements variés
            $scenario = rand(0, 3);
            $modes = ['especes', 'cheque', 'virement', 'carte', 'effet'];
            $banques = ['Attijariwafa Bank', 'BMCE Bank', 'Banque Populaire', 'CIH Bank', 'Crédit du Maroc'];

            if ($scenario === 0) {
                // Entièrement payé
                InvoicePayment::create([
                    'invoice_id'     => $invoice->id,
                    'recorded_by'    => $admin?->id,
                    'date_paiement'  => $dateFacture->copy()->addDays(rand(1, 15)),
                    'montant'        => $invoice->net_a_payer,
                    'mode'           => $modes[array_rand($modes)],
                    'reference'      => 'CHQ-' . rand(100000, 999999),
                    'banque'         => $banques[array_rand($banques)],
                    'notes'          => null,
                ]);
            } elseif ($scenario === 1) {
                // Partiellement payé (2 paiements)
                $first = round($invoice->net_a_payer * rand(30, 60) / 100, 2);
                InvoicePayment::create([
                    'invoice_id'     => $invoice->id,
                    'recorded_by'    => $admin?->id,
                    'date_paiement'  => $dateFacture->copy()->addDays(rand(1, 10)),
                    'montant'        => $first,
                    'mode'           => 'especes',
                    'reference'      => null,
                    'banque'         => null,
                    'notes'          => 'Acompte',
                ]);
            } elseif ($scenario === 2) {
                // En retard (pas de paiement, échéance passée déjà gérée par date)
            }
            // scenario 3 = émise, pas encore payée

            // Transition OR
            if ($order->status === 'livre') {
                $order->update(['status' => 'facture']);
            }
        }
    }
}
