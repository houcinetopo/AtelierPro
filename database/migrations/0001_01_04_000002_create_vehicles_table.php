<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('immatriculation', 20);
            $table->string('marque');           // Toyota, Renault, Dacia...
            $table->string('modele')->nullable();
            $table->string('couleur')->nullable();
            $table->year('annee')->nullable();
            $table->string('type_carburant')->nullable(); // Essence, Diesel, GPL, Électrique, Hybride
            $table->string('numero_chassis', 30)->nullable(); // VIN
            $table->string('puissance_fiscale', 10)->nullable();

            // Assurance
            $table->string('compagnie_assurance')->nullable();
            $table->string('numero_police_assurance')->nullable();
            $table->date('date_expiration_assurance')->nullable();

            // Contrôle technique
            $table->date('date_controle_technique')->nullable();
            $table->date('date_prochain_controle')->nullable();

            // Kilométrage
            $table->unsignedInteger('kilometrage')->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('immatriculation');
            $table->index('client_id');
            $table->index('marque');
            $table->index('numero_chassis');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
