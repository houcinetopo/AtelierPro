<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CompanySettingsSeeder::class,
            EmployeeSeeder::class,
            ClientVehicleSeeder::class,
            RepairOrderSeeder::class,
            DeliveryNoteSeeder::class,
            QuoteSeeder::class,
            InvoiceSeeder::class,
            CashSeeder::class,
            ProductSeeder::class,
            SupplierSeeder::class,
            TvaSeeder::class,
            // Les seeders des modules suivants seront ajoutés ici
        ]);
    }
}
