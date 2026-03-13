<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Définition des rôles
    |--------------------------------------------------------------------------
    */

    'roles' => [
        'admin' => [
            'label' => 'Administrateur',
            'description' => 'Accès total à toutes les fonctionnalités',
        ],
        'gestionnaire' => [
            'label' => 'Gestionnaire',
            'description' => 'Gestion quotidienne, sans accès aux paramètres sensibles',
        ],
        'comptable' => [
            'label' => 'Comptable',
            'description' => 'Accès caisse, factures, TVA',
        ],
        'technicien' => [
            'label' => 'Technicien',
            'description' => 'Accès limité aux ordres de réparation assignés',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions par rôle
    |--------------------------------------------------------------------------
    | Liste des modules accessibles par chaque rôle.
    | L'admin a automatiquement accès à tout (géré dans le middleware).
    */

    'permissions' => [
        'admin' => [
            'dashboard', 'settings', 'users', 'employees', 'clients',
            'vehicles', 'repair_orders', 'delivery_notes', 'quotes',
            'invoices', 'cash', 'stock', 'suppliers', 'tva',
            'attestations', 'activity_logs', 'reports', 'experts',
        ],
        'gestionnaire' => [
            'dashboard', 'employees', 'clients', 'vehicles',
            'repair_orders', 'delivery_notes', 'quotes', 'invoices',
            'cash', 'stock', 'suppliers', 'attestations', 'reports', 'experts',
        ],
        'comptable' => [
            'dashboard', 'clients', 'invoices', 'cash', 'tva',
            'suppliers', 'reports',
        ],
        'technicien' => [
            'dashboard', 'repair_orders', 'stock',
        ],
    ],
];
