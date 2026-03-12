<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();               // FRS-XXXXX
            $table->string('raison_sociale');
            $table->string('nom_contact')->nullable();
            $table->string('telephone')->nullable();
            $table->string('telephone_2')->nullable();
            $table->string('email')->nullable();
            $table->string('site_web')->nullable();

            // ── Adresse ──
            $table->string('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('code_postal', 10)->nullable();

            // ── Infos commerciales ──
            $table->string('ice', 20)->nullable();          // Identifiant Commun Entreprise
            $table->string('rc')->nullable();                // Registre de Commerce
            $table->string('if_fiscal', 20)->nullable();     // Identifiant Fiscal
            $table->string('patente', 20)->nullable();
            $table->string('rib')->nullable();               // Compte bancaire

            // ── Conditions ──
            $table->enum('mode_paiement_defaut', [
                'especes', 'cheque', 'virement', 'effet', 'credit'
            ])->default('cheque');
            $table->integer('delai_paiement_jours')->default(30);
            $table->decimal('remise_globale', 5, 2)->default(0); // % remise accordée
            $table->integer('delai_livraison_jours')->default(3);

            // ── Catégorisation ──
            $table->enum('type', [
                'pieces',        // Pièces de rechange
                'peinture',      // Peinture & consommables
                'outillage',     // Outillage
                'general',       // Fournisseur général
            ])->default('general');

            // ── État ──
            $table->boolean('actif')->default(true);
            $table->decimal('solde_du', 12, 2)->default(0);   // Ce qu'on lui doit
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('code');
            $table->index('raison_sociale');
            $table->index('ville');
            $table->index('type');
            $table->index('actif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
