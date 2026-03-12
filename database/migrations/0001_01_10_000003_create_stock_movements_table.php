<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();

            // ── Lien optionnel ──
            $table->foreignId('repair_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('repair_order_item_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('type', ['entree', 'sortie', 'ajustement', 'inventaire']);
            $table->enum('motif', [
                // Entrées
                'achat',              // Achat fournisseur
                'retour_client',      // Retour de pièce
                'inventaire_plus',    // Correction inventaire (+)
                'transfert_in',       // Transfert reçu
                // Sorties
                'consommation_or',    // Utilisé dans un OR
                'retour_fournisseur', // Retour fournisseur
                'perte',              // Casse, péremption
                'inventaire_moins',   // Correction inventaire (-)
                'transfert_out',      // Transfert envoyé
            ]);

            $table->decimal('quantite', 10, 2);             // Positive toujours
            $table->decimal('stock_avant', 10, 2);
            $table->decimal('stock_apres', 10, 2);
            $table->decimal('prix_unitaire', 10, 2)->nullable(); // Prix au moment du mouvement
            $table->decimal('montant_total', 12, 2)->nullable();

            $table->string('reference_document')->nullable(); // N° bon de livraison fournisseur, etc.
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('product_id');
            $table->index('type');
            $table->index('motif');
            $table->index('created_at');
            $table->index('repair_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
