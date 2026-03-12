<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();  // DV-2025-00001

            // ── Relations ──
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('repair_order_id')->nullable()->constrained()->nullOnDelete(); // OR généré
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // ── Dates ──
            $table->date('date_devis');
            $table->date('date_validite');            // Date limite d'acceptation
            $table->date('date_acceptation')->nullable();

            // ── Statut ──
            $table->enum('statut', [
                'brouillon',  // En cours de rédaction
                'envoye',     // Envoyé au client
                'accepte',    // Accepté par le client
                'refuse',     // Refusé par le client
                'expire',     // Délai dépassé
                'converti',   // Converti en OR
                'annule',     // Annulé
            ])->default('brouillon');

            // ── Montants ──
            $table->decimal('total_ht', 12, 2)->default(0);
            $table->decimal('taux_tva', 5, 2)->default(20.00);
            $table->decimal('montant_tva', 12, 2)->default(0);
            $table->decimal('total_ttc', 12, 2)->default(0);
            $table->decimal('remise_globale', 12, 2)->default(0);
            $table->decimal('net_a_payer', 12, 2)->default(0);

            // ── Contenu ──
            $table->text('description_travaux')->nullable();
            $table->text('conditions')->nullable();       // Conditions de paiement
            $table->text('notes')->nullable();
            $table->text('motif_refus')->nullable();       // Si refusé, pourquoi

            // ── Durée estimée ──
            $table->integer('duree_estimee_jours')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('numero');
            $table->index('statut');
            $table->index('client_id');
            $table->index('date_devis');
            $table->index('date_validite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
