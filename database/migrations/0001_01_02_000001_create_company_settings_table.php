<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();

            // ── Onglet 1 : Informations Générales ──
            $table->string('raison_sociale')->nullable();
            $table->text('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('code_postal', 10)->nullable();
            $table->string('pays')->default('Maroc');
            $table->string('telephone_portable', 20)->nullable();
            $table->string('telephone_fixe', 20)->nullable();
            $table->string('email_principal')->nullable();
            $table->string('email_secondaire')->nullable();
            $table->string('site_web')->nullable();
            $table->string('logo')->nullable();           // chemin vers le fichier
            $table->string('cachet')->nullable();          // chemin vers le fichier
            $table->string('signature')->nullable();       // chemin vers le fichier

            // ── Onglet 2 : Identifiants Juridiques ──
            $table->string('forme_juridique')->nullable(); // SARL, SA, Auto-entrepreneur...
            $table->string('capital_social')->nullable();
            $table->string('registre_commerce')->nullable();  // RC
            $table->string('patente')->nullable();
            $table->string('cnss')->nullable();               // Numéro d'affiliation
            $table->string('ice', 20)->nullable();            // Identifiant Commun Entreprise
            $table->string('identifiant_fiscal')->nullable(); // IF
            $table->text('objet_societe')->nullable();        // Description activité
            $table->string('nom_responsable')->nullable();    // Gérant
            $table->string('fonction_responsable')->nullable();
            $table->string('cin_responsable', 20)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
