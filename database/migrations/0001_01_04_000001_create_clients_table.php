<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->enum('type_client', ['particulier', 'societe'])->default('particulier');

            // Particulier
            $table->string('nom_complet')->nullable();
            $table->string('cin', 20)->nullable();

            // Société
            $table->string('raison_sociale')->nullable();
            $table->string('ice', 20)->nullable();
            $table->string('registre_commerce')->nullable();
            $table->string('contact_societe')->nullable(); // Nom du contact principal

            // Communs
            $table->string('telephone', 20)->nullable();
            $table->string('telephone_2', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('code_postal', 10)->nullable();

            // Financier
            $table->decimal('solde_credit', 12, 2)->default(0); // solde impayé
            $table->decimal('plafond_credit', 12, 2)->nullable();

            // Méta
            $table->enum('source', ['direct', 'recommandation', 'publicite', 'internet', 'assurance', 'autre'])->default('direct');
            $table->text('notes')->nullable();
            $table->boolean('is_blacklisted')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('type_client');
            $table->index('telephone');
            $table->index('cin');
            $table->index('ice');
            $table->index('is_blacklisted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
