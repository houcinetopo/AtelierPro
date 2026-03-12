<?php

namespace Database\Seeders;

use App\Models\TvaDeclaration;
use Illuminate\Database\Seeder;

class TvaSeeder extends Seeder
{
    public function run(): void
    {
        $year = now()->year;

        // Déclaration payée — il y a 3 mois
        $m1 = now()->subMonths(3)->month;
        [$d1, $f1] = TvaDeclaration::getDatesForPeriod('mensuel', $year, $m1);
        $decl1 = TvaDeclaration::create([
            'regime'     => 'mensuel',
            'annee'      => $year,
            'mois'       => $m1,
            'date_debut' => $d1,
            'date_fin'   => $f1,
            'created_by' => 1,
            'validated_by' => 1,
            'ca_ht_20'   => 85000.00,
            'ca_ht_14'   => 0, 'ca_ht_10' => 0, 'ca_ht_7' => 0, 'ca_ht_exonere' => 0,
            'tva_collectee_20' => 17000.00,
            'tva_collectee_14' => 0, 'tva_collectee_10' => 0, 'tva_collectee_7' => 0,
            'total_tva_collectee' => 17000.00,
            'achats_ht_20'  => 32000.00,
            'achats_ht_14'  => 0, 'achats_ht_10' => 0, 'achats_ht_7' => 0,
            'tva_deductible_20' => 6400.00,
            'tva_deductible_14' => 0, 'tva_deductible_10' => 0, 'tva_deductible_7' => 0,
            'total_tva_deductible' => 6400.00,
            'credit_tva_anterieur' => 0,
            'tva_due'    => 10600.00,
            'credit_tva' => 0,
            'statut'     => 'payee',
            'date_declaration' => $f1->copy()->addDays(15),
            'date_paiement'    => $f1->copy()->addDays(20),
            'reference_paiement' => 'QUI-DGI-' . $year . '-' . str_pad(rand(1000, 9999), 4, '0'),
            'montant_paye' => 10600.00,
        ]);

        // Déclaration déclarée — il y a 2 mois
        $m2 = now()->subMonths(2)->month;
        [$d2, $f2] = TvaDeclaration::getDatesForPeriod('mensuel', $year, $m2);
        TvaDeclaration::create([
            'regime'     => 'mensuel',
            'annee'      => $year,
            'mois'       => $m2,
            'date_debut' => $d2,
            'date_fin'   => $f2,
            'created_by' => 1,
            'validated_by' => 1,
            'ca_ht_20'   => 72000.00,
            'ca_ht_14'   => 5000.00,
            'tva_collectee_20' => 14400.00,
            'tva_collectee_14' => 700.00,
            'total_tva_collectee' => 15100.00,
            'achats_ht_20'  => 28000.00,
            'achats_ht_14'  => 3000.00,
            'tva_deductible_20' => 5600.00,
            'tva_deductible_14' => 420.00,
            'total_tva_deductible' => 6020.00,
            'credit_tva_anterieur' => 0,
            'tva_due'    => 9080.00,
            'credit_tva' => 0,
            'statut'     => 'declaree',
            'date_declaration' => $f2->copy()->addDays(18),
        ]);

        // Déclaration validée — mois précédent
        $m3 = now()->subMonth()->month;
        [$d3, $f3] = TvaDeclaration::getDatesForPeriod('mensuel', $year, $m3);
        TvaDeclaration::create([
            'regime'     => 'mensuel',
            'annee'      => $year,
            'mois'       => $m3,
            'date_debut' => $d3,
            'date_fin'   => $f3,
            'created_by' => 1,
            'validated_by' => 1,
            'ca_ht_20'   => 95000.00,
            'tva_collectee_20' => 19000.00,
            'total_tva_collectee' => 19000.00,
            'achats_ht_20'  => 45000.00,
            'tva_deductible_20' => 9000.00,
            'total_tva_deductible' => 9000.00,
            'credit_tva_anterieur' => 0,
            'tva_due'    => 10000.00,
            'credit_tva' => 0,
            'statut'     => 'validee',
        ]);

        // Déclaration brouillon — mois en cours
        $m4 = now()->month;
        [$d4, $f4] = TvaDeclaration::getDatesForPeriod('mensuel', $year, $m4);
        TvaDeclaration::create([
            'regime'     => 'mensuel',
            'annee'      => $year,
            'mois'       => $m4,
            'date_debut' => $d4,
            'date_fin'   => $f4,
            'created_by' => 1,
            'statut'     => 'brouillon',
            'notes'      => 'En attente de calcul automatique.',
        ]);
    }
}
