<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();  // FA-2025-00001

            // ── Relations ──
            $table->foreignId('repair_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('delivery_note_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // ── Dates ──
            $table->date('date_facture');
            $table->date('date_echeance')->nullable();

            // ── Statut ──
            $table->enum('statut', [
                'brouillon',
                'emise',        // Facture émise / envoyée
                'payee',        // Entièrement payée
                'partielle',    // Partiellement payée
                'en_retard',    // Échéance dépassée
                'annulee',      // Annulée
            ])->default('brouillon');

            // ── Montants ──
            $table->decimal('total_ht', 12, 2)->default(0);
            $table->decimal('taux_tva', 5, 2)->default(20.00);
            $table->decimal('montant_tva', 12, 2)->default(0);
            $table->decimal('total_ttc', 12, 2)->default(0);
            $table->decimal('remise_globale', 12, 2)->default(0);
            $table->decimal('net_a_payer', 12, 2)->default(0);
            $table->decimal('total_paye', 12, 2)->default(0);
            $table->decimal('reste_a_payer', 12, 2)->default(0);

            // ── Contenu ──
            $table->text('objet')->nullable();              // Objet de la facture
            $table->text('conditions_paiement')->nullable();
            $table->text('notes')->nullable();
            $table->text('mentions_legales')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('numero');
            $table->index('statut');
            $table->index('client_id');
            $table->index('date_facture');
            $table->index('date_echeance');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
