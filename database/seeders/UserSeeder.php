<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrateur',
                'email' => 'admin@atelier.ma',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'phone' => '0600000001',
                'is_active' => true,
            ],
            [
                'name' => 'Ahmed Gestionnaire',
                'email' => 'gestionnaire@atelier.ma',
                'password' => bcrypt('password'),
                'role' => 'gestionnaire',
                'phone' => '0600000002',
                'is_active' => true,
            ],
            [
                'name' => 'Fatima Comptable',
                'email' => 'comptable@atelier.ma',
                'password' => bcrypt('password'),
                'role' => 'comptable',
                'phone' => '0600000003',
                'is_active' => true,
            ],
            [
                'name' => 'Youssef Technicien',
                'email' => 'technicien@atelier.ma',
                'password' => bcrypt('password'),
                'role' => 'technicien',
                'phone' => '0600000004',
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
