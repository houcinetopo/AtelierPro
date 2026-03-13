<?php

namespace App\Providers;

use App\Models\InvoicePayment;
use App\Models\RepairOrder;
use App\Observers\InvoicePaymentObserver;
use App\Observers\RepairOrderObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Modification 1 : Liaison Paiement → Caisse automatique
        InvoicePayment::observe(InvoicePaymentObserver::class);

        // Modification 3 : Notification SMS/Email quand OR terminé
        RepairOrder::observe(RepairOrderObserver::class);
    }
}
