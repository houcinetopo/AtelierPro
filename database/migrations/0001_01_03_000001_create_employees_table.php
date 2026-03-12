<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // ── Informations personnelles ──
            $table->string('nom_complet');
            $table->string('cin', 20)->nullable()->unique();
            $table->string('photo')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('telephone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('contact_urgence')->nullable();
            $table->string('telephone_urgence', 20)->nullable();

            // ── Informations professionnelles ──
            $table->string('poste');
            $table->string('type_contrat')->default('CDI');
            $table->date('date_embauche')->nullable();

            // ── Rémunération ──
            $table->decimal('salaire_base', 10, 2)->default(0);
            $table->integer('jours_travail_mois')->default(26);
            $table->string('cnss', 30)->nullable();

            // ── Divers ──
            $table->text('notes')->nullable();
            $table->enum('statut', ['actif', 'inactif'])->default('actif');

            $table->timestamps();
            $table->softDeletes();

            $table->index('poste');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
