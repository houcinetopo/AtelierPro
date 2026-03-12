<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();              // BC-2025-00001

            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // ── Dates ──
            $table->date('date_commande');
            $table->date('date_livraison_prevue')->nullable();
            $table->date('date_reception')->nullable();

            // ── Statut ──
            $table->enum('statut', [
                'brouillon',
                'envoyee',        // Envoyée au fournisseur
                'confirmee',      // Confirmée par le fournisseur
                'livree_partiel', // Livraison partielle
                'livree',         // Entièrement livrée
                'annulee',
            ])->default('brouillon');

            // ── Montants ──
            $table->decimal('total_ht', 12, 2)->default(0);
            $table->decimal('taux_tva', 5, 2)->default(20.00);
            $table->decimal('montant_tva', 12, 2)->default(0);
            $table->decimal('total_ttc', 12, 2)->default(0);
            $table->decimal('remise_globale', 12, 2)->default(0);
            $table->decimal('net_a_payer', 12, 2)->default(0);

            // ── Référence fournisseur ──
            $table->string('reference_fournisseur')->nullable(); // N° du fournisseur
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('numero');
            $table->index('supplier_id');
            $table->index('statut');
            $table->index('date_commande');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
