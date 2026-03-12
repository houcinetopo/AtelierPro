<?php

namespace Database\Seeders;

use App\Models\CompanyBankAccount;
use App\Models\CompanySetting;
use Illuminate\Database\Seeder;

class CompanySettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = CompanySetting::firstOrCreate([], [
            // Informations Générales
            'raison_sociale'     => 'Atelier Pro Auto SARL',
            'adresse'            => '123, Zone Industrielle Aïn Sebaâ, Lot 45',
            'ville'              => 'Casablanca',
            'code_postal'        => '20580',
            'pays'               => 'Maroc',
            'telephone_portable' => '0661234567',
            'telephone_fixe'     => '0522123456',
            'email_principal'    => 'contact@atelierpro.ma',
            'email_secondaire'   => 'info@atelierpro.ma',
            'site_web'           => 'https://www.atelierpro.ma',

            // Identifiants Juridiques
            'forme_juridique'      => 'SARL',
            'capital_social'       => '100 000',
            'registre_commerce'    => '123456',
            'patente'              => '12345678',
            'cnss'                 => '1234567',
            'ice'                  => '001234567000012',
            'identifiant_fiscal'   => '12345678',
            'objet_societe'        => 'Réparation et entretien de véhicules automobiles : carrosserie, mécanique générale, peinture, électricité automobile.',
            'nom_responsable'      => 'Mohammed Alami',
            'fonction_responsable' => 'Gérant',
            'cin_responsable'      => 'AB123456',
        ]);

        // Comptes bancaires de démonstration
        CompanyBankAccount::firstOrCreate(
            ['company_setting_id' => $settings->id, 'nom_banque' => 'Attijariwafa Bank'],
            [
                'numero_compte' => '0011223344556677',
                'rib'           => '007 810 0000012345678901 23',
                'code_swift'    => 'BCMAMAMC',
                'iban'          => 'MA64 007 810 0000012345678901 23',
                'agence'        => 'Ain Sebaâ',
                'ville_agence'  => 'Casablanca',
                'is_default'    => true,
            ]
        );

        CompanyBankAccount::firstOrCreate(
            ['company_setting_id' => $settings->id, 'nom_banque' => 'BMCE Bank (Bank of Africa)'],
            [
                'numero_compte' => '9988776655443322',
                'rib'           => '011 780 0000098765432101 45',
                'code_swift'    => 'BMCEMAMC',
                'agence'        => 'Boulevard Hassan II',
                'ville_agence'  => 'Casablanca',
                'is_default'    => false,
            ]
        );
    }
}
