<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();         // REF-XXXXX auto ou manuelle
            $table->string('code_barre')->nullable()->unique();

            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();

            $table->string('designation');
            $table->text('description')->nullable();
            $table->string('marque')->nullable();
            $table->string('modele_compatible')->nullable(); // Modèles véhicules compatibles

            // ── Type ──
            $table->enum('type', [
                'piece',          // Pièce de rechange
                'fourniture',     // Consommable (peinture, mastic, etc.)
                'outillage',      // Outil
                'accessoire',     // Accessoire véhicule
            ])->default('piece');

            // ── Prix ──
            $table->decimal('prix_achat', 10, 2)->default(0);
            $table->decimal('prix_vente', 10, 2)->default(0);
            $table->decimal('taux_tva', 5, 2)->default(20.00);
            $table->decimal('marge_percent', 5, 2)->nullable(); // calculé ou saisi

            // ── Stock ──
            $table->decimal('quantite_stock', 10, 2)->default(0);
            $table->decimal('seuil_alerte', 10, 2)->default(5);
            $table->decimal('seuil_commande', 10, 2)->default(10); // Seuil pour réappro
            $table->decimal('quantite_max', 10, 2)->nullable();
            $table->string('unite')->default('u');                  // u, kg, l, m, boite
            $table->string('emplacement')->nullable();              // Étagère, rayon

            // ── Fournisseur (préparé pour Module 12) ──
            $table->string('fournisseur_nom')->nullable();
            $table->string('fournisseur_ref')->nullable();        // Réf. chez le fournisseur
            $table->integer('delai_livraison_jours')->nullable();

            // ── État ──
            $table->boolean('actif')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('reference');
            $table->index('designation');
            $table->index('type');
            $table->index('category_id');
            $table->index('actif');
            $table->index('quantite_stock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
