<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_orders', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();  // OR-2025-00001

            // ── Relations ──
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technicien_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // ── Dates ──
            $table->date('date_reception');
            $table->date('date_prevue_livraison')->nullable();
            $table->date('date_livraison_effective')->nullable();

            // ── Statut ──
            $table->enum('status', [
                'brouillon',   // Créé mais pas encore validé
                'en_cours',    // Travaux en cours
                'en_attente',  // En attente (pièce, client, etc.)
                'termine',     // Travaux terminés
                'livre',       // Véhicule livré au client
                'facture',     // Facturé
                'annule',      // Annulé
            ])->default('brouillon');

            // ── Kilomètres ──
            $table->integer('kilometrage_entree')->nullable();
            $table->integer('kilometrage_sortie')->nullable();

            // ── Montants ──
            $table->decimal('total_ht', 12, 2)->default(0);
            $table->decimal('taux_tva', 5, 2)->default(20.00);
            $table->decimal('montant_tva', 12, 2)->default(0);
            $table->decimal('total_ttc', 12, 2)->default(0);
            $table->decimal('remise_globale', 12, 2)->default(0);
            $table->decimal('net_a_payer', 12, 2)->default(0);

            // ── Description ──
            $table->text('description_panne')->nullable();
            $table->text('diagnostic')->nullable();
            $table->text('observations')->nullable();
            $table->text('notes_internes')->nullable();

            // ── Divers ──
            $table->string('niveau_carburant', 20)->nullable();   // 0, 1/4, 1/2, 3/4, plein
            $table->json('etat_vehicule')->nullable();             // rayures, bosses, etc.
            $table->string('source_ordre')->default('direct');     // direct, telephone, assurance

            $table->timestamps();
            $table->softDeletes();

            $table->index('numero');
            $table->index('status');
            $table->index('client_id');
            $table->index('vehicle_id');
            $table->index('technicien_id');
            $table->index('date_reception');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_orders');
    }
};
