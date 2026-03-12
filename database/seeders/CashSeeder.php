<?php

namespace Database\Seeders;

use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class CashSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        // Créer 15 sessions de caisse sur les 20 derniers jours
        $solde = 5000.00;

        $depenses = [
            ['categorie' => 'achat_pieces', 'libelles' => ['Achat disques de frein Brembo', 'Filtres à huile lot x20', 'Kit courroie distribution', 'Bougies NGK x8', 'Amortisseurs AR Dacia']],
            ['categorie' => 'charges', 'libelles' => ['Facture LYDEC — eau', 'Facture LYDEC — électricité', 'Facture téléphone/internet']],
            ['categorie' => 'loyer', 'libelles' => ['Loyer atelier mois']],
            ['categorie' => 'carburant', 'libelles' => ['Gasoil dépanneuse', 'Gasoil véhicule de service']],
            ['categorie' => 'outillage', 'libelles' => ['Clé dynamométrique Facom', 'Jeu de douilles']],
            ['categorie' => 'frais_divers', 'libelles' => ['Produits nettoyage atelier', 'Café et fournitures pause', 'Timbres et papeterie']],
            ['categorie' => 'salaire', 'libelles' => ['Avance salaire Mohamed', 'Avance salaire Youssef']],
        ];

        $recettes = [
            ['categorie' => 'paiement_client', 'libelles' => ['Paiement facture FA-2025', 'Encaissement réparation carrosserie', 'Règlement OR client Bennani', 'Paiement vidange + filtres']],
            ['categorie' => 'acompte', 'libelles' => ['Acompte réparation Megane', 'Acompte peinture complète']],
            ['categorie' => 'autre_entree', 'libelles' => ['Vente pièces occasion', 'Remboursement fournisseur']],
        ];

        for ($i = 19; $i >= 0; $i--) {
            $date = now()->subDays($i);
            // Skip dimanche
            if ($date->isSunday()) continue;

            $session = CashSession::create([
                'date_session'     => $date->toDateString(),
                'opened_by'        => $admin?->id,
                'solde_ouverture'  => round($solde, 2),
                'total_entrees'    => 0,
                'total_sorties'    => 0,
                'solde_theorique'  => round($solde, 2),
                'statut'           => $i > 0 ? 'cloturee' : 'ouverte',
                'heure_ouverture'  => $date->copy()->setTime(8, rand(0, 30)),
            ]);

            // Entrées (2-4)
            $nbEntrees = rand(2, 4);
            for ($j = 0; $j < $nbEntrees; $j++) {
                $rec = $recettes[array_rand($recettes)];
                $montant = rand(200, 4000) + rand(0, 99) / 100;
                $modes = ['especes', 'cheque', 'virement', 'carte'];
                CashMovement::create([
                    'cash_session_id' => $session->id,
                    'recorded_by'     => $admin?->id,
                    'type'            => 'entree',
                    'categorie'       => $rec['categorie'],
                    'libelle'         => $rec['libelles'][array_rand($rec['libelles'])],
                    'montant'         => round($montant, 2),
                    'mode_paiement'   => $modes[array_rand($modes)],
                    'beneficiaire'    => $rec['categorie'] === 'paiement_client' ? 'Client ' . ['Alami', 'Bennani', 'El Fassi', 'Tazi', 'Berrada'][rand(0,4)] : null,
                ]);
            }

            // Sorties (1-3)
            $nbSorties = rand(1, 3);
            for ($j = 0; $j < $nbSorties; $j++) {
                $dep = $depenses[array_rand($depenses)];
                $montant = rand(100, 2500) + rand(0, 99) / 100;
                CashMovement::create([
                    'cash_session_id' => $session->id,
                    'recorded_by'     => $admin?->id,
                    'type'            => 'sortie',
                    'categorie'       => $dep['categorie'],
                    'libelle'         => $dep['libelles'][array_rand($dep['libelles'])],
                    'montant'         => round($montant, 2),
                    'mode_paiement'   => 'especes',
                    'beneficiaire'    => $dep['categorie'] === 'achat_pieces' ? ['Auto-Pièces Agadir', 'Souk Pièces', 'Pièces Express', 'Garage Pro'][rand(0,3)] : null,
                ]);
            }

            // Recalculate after all movements
            $session->recalculate();
            $solde = (float) $session->solde_theorique;

            // Clôturer les sessions passées
            if ($i > 0) {
                $ecart = rand(0, 5) === 0 ? rand(-50, 50) : 0;
                $session->update([
                    'solde_reel'    => round($solde + $ecart, 2),
                    'ecart'         => $ecart,
                    'closed_by'     => $admin?->id,
                    'heure_cloture' => $date->copy()->setTime(18, rand(0, 30)),
                ]);
            }
        }
    }
}
