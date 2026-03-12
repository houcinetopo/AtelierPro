<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeePayment;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            [
                'nom_complet'       => 'Hassan Benmoussa',
                'cin'               => 'BH456789',
                'poste'             => 'chef_atelier',
                'date_embauche'     => '2019-03-15',
                'type_contrat'      => 'CDI',
                'salaire_base'      => 8000,
                'jours_travail_mois'=> 26,
                'telephone'         => '0661112233',
                'adresse'           => '45, Rue Allal Ben Abdellah',
                'ville'             => 'Casablanca',
                'cnss'              => '2345678',
                'statut'            => 'actif',
            ],
            [
                'nom_complet'       => 'Karim El Fassi',
                'cin'               => 'AB789012',
                'poste'             => 'mecanicien',
                'date_embauche'     => '2020-07-01',
                'type_contrat'      => 'CDI',
                'salaire_base'      => 5500,
                'jours_travail_mois'=> 26,
                'telephone'         => '0662223344',
                'adresse'           => '12, Derb Lhajja',
                'ville'             => 'Casablanca',
                'cnss'              => '3456789',
                'statut'            => 'actif',
            ],
            [
                'nom_complet'       => 'Omar Benjelloun',
                'cin'               => 'BK345678',
                'poste'             => 'carrossier',
                'date_embauche'     => '2021-01-10',
                'type_contrat'      => 'CDI',
                'salaire_base'      => 6000,
                'jours_travail_mois'=> 26,
                'telephone'         => '0663334455',
                'ville'             => 'Casablanca',
                'cnss'              => '4567890',
                'statut'            => 'actif',
            ],
            [
                'nom_complet'       => 'Rachid Amrani',
                'cin'               => 'BE567890',
                'poste'             => 'peintre',
                'date_embauche'     => '2021-06-15',
                'type_contrat'      => 'CDI',
                'salaire_base'      => 5000,
                'jours_travail_mois'=> 26,
                'telephone'         => '0664445566',
                'ville'             => 'Mohammedia',
                'statut'            => 'actif',
            ],
            [
                'nom_complet'       => 'Saïd Ouazzani',
                'cin'               => 'BJ234567',
                'poste'             => 'electricien',
                'date_embauche'     => '2022-09-01',
                'type_contrat'      => 'CDD',
                'salaire_base'      => 4500,
                'jours_travail_mois'=> 26,
                'telephone'         => '0665556677',
                'ville'             => 'Casablanca',
                'statut'            => 'actif',
            ],
            [
                'nom_complet'       => 'Amina Tazi',
                'cin'               => 'BM678901',
                'poste'             => 'secretaire',
                'date_embauche'     => '2020-02-01',
                'type_contrat'      => 'CDI',
                'salaire_base'      => 4000,
                'jours_travail_mois'=> 26,
                'telephone'         => '0666667788',
                'email'             => 'amina@atelierpro.ma',
                'ville'             => 'Casablanca',
                'statut'            => 'actif',
            ],
            [
                'nom_complet'       => 'Mustapha Idrissi',
                'cin'               => 'BC901234',
                'poste'             => 'apprenti',
                'date_embauche'     => '2024-01-15',
                'type_contrat'      => 'Stage',
                'salaire_base'      => 2500,
                'jours_travail_mois'=> 26,
                'telephone'         => '0667778899',
                'ville'             => 'Casablanca',
                'statut'            => 'actif',
            ],
        ];

        foreach ($employees as $data) {
            $employee = Employee::firstOrCreate(
                ['cin' => $data['cin']],
                $data
            );

            // Ajouter quelques paiements de démonstration pour les 3 derniers mois
            if ($employee->wasRecentlyCreated && $employee->statut === 'actif') {
                for ($i = 2; $i >= 0; $i--) {
                    $month = now()->subMonths($i);
                    EmployeePayment::create([
                        'employee_id'    => $employee->id,
                        'periode'        => $month->format('Y-m'),
                        'montant'        => $employee->salaire_base,
                        'date_paiement'  => $month->endOfMonth()->format('Y-m-d'),
                        'mode_paiement'  => 'especes',
                        'prime'          => 0,
                        'deduction'      => 0,
                        'net_paye'       => $employee->salaire_base,
                        'created_by'     => 1,
                    ]);
                }
            }
        }
    }
}
