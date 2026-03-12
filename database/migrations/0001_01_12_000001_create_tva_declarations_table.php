<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tva_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();

            // ── Période ──
            $table->enum('regime', ['mensuel', 'trimestriel'])->default('mensuel');
            $table->integer('annee');
            $table->integer('mois')->nullable();       // 1-12 pour mensuel
            $table->integer('trimestre')->nullable();   // 1-4 pour trimestriel
            $table->date('date_debut');
            $table->date('date_fin');

            // ── TVA Collectée (ventes) ──
            $table->decimal('ca_ht_20', 12, 2)->default(0);   // CA HT à 20%
            $table->decimal('ca_ht_14', 12, 2)->default(0);   // CA HT à 14%
            $table->decimal('ca_ht_10', 12, 2)->default(0);   // CA HT à 10%
            $table->decimal('ca_ht_7', 12, 2)->default(0);    // CA HT à 7%
            $table->decimal('ca_ht_exonere', 12, 2)->default(0); // CA exonéré
            $table->decimal('tva_collectee_20', 12, 2)->default(0);
            $table->decimal('tva_collectee_14', 12, 2)->default(0);
            $table->decimal('tva_collectee_10', 12, 2)->default(0);
            $table->decimal('tva_collectee_7', 12, 2)->default(0);
            $table->decimal('total_tva_collectee', 12, 2)->default(0);

            // ── TVA Déductible (achats) ──
            $table->decimal('achats_ht_20', 12, 2)->default(0);
            $table->decimal('achats_ht_14', 12, 2)->default(0);
            $table->decimal('achats_ht_10', 12, 2)->default(0);
            $table->decimal('achats_ht_7', 12, 2)->default(0);
            $table->decimal('tva_deductible_20', 12, 2)->default(0);
            $table->decimal('tva_deductible_14', 12, 2)->default(0);
            $table->decimal('tva_deductible_10', 12, 2)->default(0);
            $table->decimal('tva_deductible_7', 12, 2)->default(0);
            $table->decimal('total_tva_deductible', 12, 2)->default(0);

            // ── Solde ──
            $table->decimal('credit_tva_anterieur', 12, 2)->default(0); // Report crédit
            $table->decimal('tva_due', 12, 2)->default(0);              // collectée - déductible - crédit
            $table->decimal('credit_tva', 12, 2)->default(0);           // Si TVA due < 0

            // ── Statut & paiement ──
            $table->enum('statut', [
                'brouillon',    // En cours de préparation
                'calculee',     // Montants calculés automatiquement
                'validee',      // Validée par le gestionnaire
                'declaree',     // Déclarée à la DGI
                'payee',        // TVA payée
            ])->default('brouillon');

            $table->date('date_declaration')->nullable();
            $table->date('date_paiement')->nullable();
            $table->string('reference_paiement')->nullable();  // N° quittance DGI
            $table->decimal('montant_paye', 12, 2)->nullable();
            $table->decimal('penalites', 12, 2)->default(0);   // Pénalités retard

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['annee', 'mois'], 'tva_unique_mensuel');
            $table->unique(['annee', 'trimestre'], 'tva_unique_trimestriel');
            $table->index('statut');
            $table->index('annee');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tva_declarations');
    }
};
