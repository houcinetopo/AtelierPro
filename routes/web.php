<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\RepairOrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TvaController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes publiques (non authentifiées)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/', fn () => redirect()->route('login'));
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/mot-de-passe-oublie', [ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reinitialiser-mot-de-passe/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [ForgotPasswordController::class, 'reset'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Routes authentifiées
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'statsApi'])->name('dashboard.stats-api');
    Route::get('/dashboard/charts', [DashboardController::class, 'chartsApi'])->name('dashboard.charts-api');

    /*
    |----------------------------------------------------------------------
    | Administration (Admin uniquement)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        // Gestion des utilisateurs
        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Journal d'activité
        Route::get('activity-logs', [UserController::class, 'activityLogs'])->name('activity-logs');
    });

    /*
    |----------------------------------------------------------------------
    | Module 4 : Gestion des Employés
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin,gestionnaire')->group(function () {
        Route::resource('employees', EmployeeController::class);
        Route::get('employees-export', [EmployeeController::class, 'exportExcel'])->name('employees.export-excel');
        Route::post('employees/{employee}/payments', [EmployeeController::class, 'storePayment'])->name('employees.payments.store');
        Route::delete('employees/{employee}/payments/{payment}', [EmployeeController::class, 'destroyPayment'])->name('employees.payments.destroy');
    });

    /*

    // Module 3 : Paramètres société
    Route::middleware('role:admin')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [CompanySettingController::class, 'index'])->name('index');
        Route::put('/', [CompanySettingController::class, 'update'])->name('update');
        Route::post('/remove-image', [CompanySettingController::class, 'removeImage'])->name('remove-image');

        // Comptes bancaires
        Route::post('/bank-accounts', [CompanySettingController::class, 'storeBankAccount'])->name('bank-accounts.store');
        Route::put('/bank-accounts/{bankAccount}', [CompanySettingController::class, 'updateBankAccount'])->name('bank-accounts.update');
        Route::delete('/bank-accounts/{bankAccount}', [CompanySettingController::class, 'destroyBankAccount'])->name('bank-accounts.destroy');
        Route::patch('/bank-accounts/{bankAccount}/default', [CompanySettingController::class, 'setDefaultBankAccount'])->name('bank-accounts.set-default');
    });

    /*
    // Route::resource('employees', EmployeeController::class);

    /*
    |----------------------------------------------------------------------
    | Module 5 : Clients & Véhicules
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin,gestionnaire')->group(function () {
        Route::resource('clients', ClientController::class);
        Route::get('clients-export', [ClientController::class, 'export'])->name('clients.export');
        Route::get('api/clients/search', [ClientController::class, 'apiSearch'])->name('api.clients.search');

        Route::resource('vehicles', VehicleController::class);
        Route::post('vehicles/{vehicle}/photos', [VehicleController::class, 'uploadPhotos'])->name('vehicles.photos.upload');
        Route::delete('vehicles/{vehicle}/photos/{photo}', [VehicleController::class, 'deletePhoto'])->name('vehicles.photos.delete');
        Route::get('api/vehicles/by-client', [VehicleController::class, 'apiByClient'])->name('api.vehicles.by-client');
    });

    // Module 5 : Clients
    // Route::resource('clients', ClientController::class);

    // Module 6 : Ordres de réparation
    Route::get('repair-orders/vehicles-by-client', [RepairOrderController::class, 'vehiclesByClient'])->name('repair-orders.vehicles-by-client');
    Route::resource('repair-orders', RepairOrderController::class);
    Route::patch('repair-orders/{repair_order}/status', [RepairOrderController::class, 'updateStatus'])->name('repair-orders.update-status');
    Route::delete('repair-orders/{repair_order}/photos/{photo}', [RepairOrderController::class, 'deletePhoto'])->name('repair-orders.photos.delete');

    // Module 7 : Bons de livraison
    Route::resource('delivery-notes', DeliveryNoteController::class);
    Route::patch('delivery-notes/{delivery_note}/validate', [DeliveryNoteController::class, 'validate_note'])->name('delivery-notes.validate');
    Route::patch('delivery-notes/{delivery_note}/cancel', [DeliveryNoteController::class, 'cancel'])->name('delivery-notes.cancel');

    // Module 8 : Devis
    Route::get('quotes/vehicles-by-client', [QuoteController::class, 'vehiclesByClient'])->name('quotes.vehicles-by-client');
    Route::resource('quotes', QuoteController::class);
    Route::patch('quotes/{quote}/statut', [QuoteController::class, 'updateStatut'])->name('quotes.update-statut');
    Route::post('quotes/{quote}/convert', [QuoteController::class, 'convertToRepairOrder'])->name('quotes.convert');
    Route::post('quotes/{quote}/duplicate', [QuoteController::class, 'duplicate'])->name('quotes.duplicate');

    // Module 9 : Factures
    Route::resource('invoices', InvoiceController::class);
    Route::patch('invoices/{invoice}/emit', [InvoiceController::class, 'emit'])->name('invoices.emit');
    Route::patch('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'addPayment'])->name('invoices.add-payment');
    Route::delete('invoices/{invoice}/payments/{payment}', [InvoiceController::class, 'deletePayment'])->name('invoices.delete-payment');

    // Module 10 : Caisse
    Route::prefix('cash')->name('cash.')->group(function () {
        Route::get('/', [CashController::class, 'index'])->name('index');
        Route::post('/open', [CashController::class, 'open'])->name('open');
        Route::get('/session/{cashSession}', [CashController::class, 'session'])->name('session');
        Route::patch('/session/{cashSession}/close', [CashController::class, 'close'])->name('close');
        Route::post('/session/{cashSession}/movements', [CashController::class, 'addMovement'])->name('add-movement');
        Route::delete('/session/{cashSession}/movements/{movement}', [CashController::class, 'deleteMovement'])->name('delete-movement');
    });

    // Module 11 : Stock
    Route::get('products/search-api', [ProductController::class, 'searchApi'])->name('products.search-api');
    Route::get('products/alerts', [ProductController::class, 'alerts'])->name('products.alerts');
    Route::resource('products', ProductController::class);
    Route::post('products/{product}/movements', [ProductController::class, 'addMovement'])->name('products.add-movement');
    Route::post('products/categories', [ProductController::class, 'storeCategory'])->name('products.categories.store');
    Route::delete('products/categories/{category}', [ProductController::class, 'destroyCategory'])->name('products.categories.destroy');

    // Module 12 : Fournisseurs
    Route::get('suppliers/search-api', [SupplierController::class, 'searchApi'])->name('suppliers.search-api');
    Route::resource('suppliers', SupplierController::class);
    Route::get('suppliers/{supplier}/orders/create', [SupplierController::class, 'createOrder'])->name('suppliers.order.create');
    Route::post('suppliers/{supplier}/orders', [SupplierController::class, 'storeOrder'])->name('suppliers.order.store');
    Route::get('suppliers/{supplier}/orders/{order}', [SupplierController::class, 'showOrder'])->name('suppliers.order');
    Route::patch('suppliers/{supplier}/orders/{order}/statut', [SupplierController::class, 'updateOrderStatut'])->name('suppliers.order.update-statut');
    Route::post('suppliers/{supplier}/orders/{order}/receive', [SupplierController::class, 'receiveOrder'])->name('suppliers.order.receive');

    // Module 13 : TVA
    Route::prefix('tva')->name('tva.')->group(function () {
        Route::get('/', [TvaController::class, 'index'])->name('index');
        Route::get('/create', [TvaController::class, 'create'])->name('create');
        Route::post('/', [TvaController::class, 'store'])->name('store');
        Route::get('/{tva}', [TvaController::class, 'show'])->name('show');
        Route::put('/{tva}', [TvaController::class, 'update'])->name('update');
        Route::delete('/{tva}', [TvaController::class, 'destroy'])->name('destroy');
        Route::post('/{tva}/calculate', [TvaController::class, 'calculate'])->name('calculate');
        Route::patch('/{tva}/statut', [TvaController::class, 'updateStatut'])->name('update-statut');
    });
});
