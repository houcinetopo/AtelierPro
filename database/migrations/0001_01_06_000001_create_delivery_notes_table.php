<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();  // BL-2025-00001

            // ── Relations ──
            $table->foreignId('repair_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // ── Dates ──
            $table->date('date_livraison');
            $table->time('heure_livraison')->nullable();

            // ── Véhicule à la sortie ──
            $table->integer('kilometrage_sortie')->nullable();
            $table->string('niveau_carburant', 20)->nullable();

            // ── État du véhicule ──
            $table->text('travaux_effectues')->nullable();      // Résumé des travaux
            $table->text('observations_sortie')->nullable();    // État à la sortie
            $table->text('reserves_client')->nullable();        // Réserves émises par le client
            $table->text('recommandations')->nullable();        // Recommandations au client

            // ── Signatures ──
            $table->boolean('signe_atelier')->default(false);
            $table->boolean('signe_client')->default(false);
            $table->string('nom_receptionnaire')->nullable();   // Qui a récupéré le véhicule
            $table->string('cin_receptionnaire')->nullable();
            $table->string('signature_client_path')->nullable(); // Signature numérique

            // ── Montants (repris de l'OR) ──
            $table->decimal('total_ttc', 12, 2)->default(0);
            $table->decimal('montant_paye', 12, 2)->default(0);
            $table->decimal('reste_a_payer', 12, 2)->default(0);
            $table->enum('mode_paiement', ['especes', 'cheque', 'virement', 'carte', 'credit', 'mixte'])->nullable();

            $table->enum('statut', ['brouillon', 'valide', 'annule'])->default('brouillon');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('numero');
            $table->index('repair_order_id');
            $table->index('client_id');
            $table->index('date_livraison');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_notes');
    }
};
