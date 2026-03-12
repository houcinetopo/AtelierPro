<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class ClientVehicleSeeder extends Seeder
{
    public function run(): void
    {
        // ── Clients Particuliers ──
        $c1 = Client::firstOrCreate(['cin' => 'AB123456'], [
            'type_client'  => 'particulier',
            'nom_complet'  => 'Youssef El Amrani',
            'cin'          => 'AB123456',
            'telephone'    => '0661001122',
            'email'        => 'youssef@email.com',
            'adresse'      => '12, Rue Ibn Batouta',
            'ville'        => 'Casablanca',
            'source'       => 'direct',
        ]);

        $c2 = Client::firstOrCreate(['cin' => 'BH789012'], [
            'type_client'  => 'particulier',
            'nom_complet'  => 'Fatima Zahra Bennani',
            'cin'          => 'BH789012',
            'telephone'    => '0662112233',
            'ville'        => 'Mohammedia',
            'source'       => 'recommandation',
        ]);

        $c3 = Client::firstOrCreate(['cin' => 'BK345678'], [
            'type_client'  => 'particulier',
            'nom_complet'  => 'Khalid Ouazzani',
            'cin'          => 'BK345678',
            'telephone'    => '0663223344',
            'adresse'      => '56, Boulevard Zerktouni',
            'ville'        => 'Casablanca',
            'source'       => 'direct',
            'solde_credit' => 2500.00,
        ]);

        // ── Clients Sociétés ──
        $c4 = Client::firstOrCreate(['ice' => '001234567000001'], [
            'type_client'       => 'societe',
            'raison_sociale'    => 'Transport Atlas SARL',
            'ice'               => '001234567000001',
            'registre_commerce' => '789012',
            'contact_societe'   => 'Mohamed Alaoui',
            'telephone'         => '0522334455',
            'telephone_2'       => '0664334455',
            'email'             => 'contact@transport-atlas.ma',
            'adresse'           => 'Zone Industrielle Aïn Sebaâ',
            'ville'             => 'Casablanca',
            'source'            => 'direct',
            'plafond_credit'    => 50000,
            'solde_credit'      => 15000,
        ]);

        $c5 = Client::firstOrCreate(['ice' => '001234567000002'], [
            'type_client'       => 'societe',
            'raison_sociale'    => 'Société Immobilière Al Omrane',
            'ice'               => '001234567000002',
            'contact_societe'   => 'Ahmed Tazi',
            'telephone'         => '0522556677',
            'ville'             => 'Rabat',
            'source'            => 'assurance',
        ]);

        // ── Véhicules ──
        Vehicle::firstOrCreate(['immatriculation' => '12345-A-6'], [
            'client_id'     => $c1->id,
            'immatriculation' => '12345-A-6',
            'marque'        => 'Dacia',
            'modele'        => 'Logan',
            'couleur'       => 'Blanc',
            'annee'         => 2019,
            'type_carburant'=> 'diesel',
            'kilometrage'   => 85000,
            'compagnie_assurance'       => 'SAHAM Assurance',
            'date_expiration_assurance' => now()->addMonths(3)->format('Y-m-d'),
        ]);

        Vehicle::firstOrCreate(['immatriculation' => '67890-B-12'], [
            'client_id'     => $c1->id,
            'immatriculation' => '67890-B-12',
            'marque'        => 'Renault',
            'modele'        => 'Clio',
            'couleur'       => 'Gris',
            'annee'         => 2021,
            'type_carburant'=> 'essence',
            'kilometrage'   => 35000,
            'compagnie_assurance'       => 'Wafa Assurance',
            'date_expiration_assurance' => now()->addMonths(8)->format('Y-m-d'),
        ]);

        Vehicle::firstOrCreate(['immatriculation' => '11111-C-22'], [
            'client_id'     => $c2->id,
            'immatriculation' => '11111-C-22',
            'marque'        => 'Hyundai',
            'modele'        => 'Tucson',
            'couleur'       => 'Noir',
            'annee'         => 2022,
            'type_carburant'=> 'diesel',
            'kilometrage'   => 28000,
            'compagnie_assurance'       => 'MAMDA',
            'date_expiration_assurance' => now()->subDays(15)->format('Y-m-d'), // Expirée !
        ]);

        Vehicle::firstOrCreate(['immatriculation' => '22222-D-33'], [
            'client_id'     => $c3->id,
            'immatriculation' => '22222-D-33',
            'marque'        => 'Volkswagen',
            'modele'        => 'Golf',
            'couleur'       => 'Bleu',
            'annee'         => 2018,
            'type_carburant'=> 'diesel',
            'kilometrage'   => 120000,
        ]);

        // Véhicules société (flotte Transport Atlas)
        foreach ([
            ['33333-E-44', 'Mercedes-Benz', 'Sprinter', 'Blanc', 2020, 'diesel', 95000],
            ['44444-F-55', 'Renault', 'Master', 'Blanc', 2021, 'diesel', 72000],
            ['55555-G-66', 'Peugeot', 'Partner', 'Gris', 2019, 'diesel', 110000],
        ] as $data) {
            Vehicle::firstOrCreate(['immatriculation' => $data[0]], [
                'client_id'     => $c4->id,
                'immatriculation' => $data[0],
                'marque'        => $data[1],
                'modele'        => $data[2],
                'couleur'       => $data[3],
                'annee'         => $data[4],
                'type_carburant'=> $data[5],
                'kilometrage'   => $data[6],
            ]);
        }

        Vehicle::firstOrCreate(['immatriculation' => '66666-H-77'], [
            'client_id'     => $c5->id,
            'immatriculation' => '66666-H-77',
            'marque'        => 'Toyota',
            'modele'        => 'Hilux',
            'couleur'       => 'Gris métallisé',
            'annee'         => 2023,
            'type_carburant'=> 'diesel',
            'kilometrage'   => 18000,
            'compagnie_assurance'       => 'AXA Assurance Maroc',
            'date_expiration_assurance' => now()->addMonths(6)->format('Y-m-d'),
        ]);
    }
}
