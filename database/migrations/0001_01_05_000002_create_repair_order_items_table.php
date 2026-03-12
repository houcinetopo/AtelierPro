<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_order_id')->constrained()->cascadeOnDelete();

            $table->enum('type', ['main_oeuvre', 'piece', 'fourniture', 'sous_traitance'])->default('main_oeuvre');
            $table->string('designation');
            $table->string('reference')->nullable();  // Référence pièce
            $table->text('description')->nullable();
            $table->decimal('quantite', 10, 2)->default(1);
            $table->string('unite')->default('u');    // u, h, forfait, m, kg
            $table->decimal('prix_unitaire', 10, 2)->default(0);
            $table->decimal('remise', 5, 2)->default(0);  // % de remise
            $table->decimal('montant_ht', 12, 2)->default(0);
            $table->decimal('taux_tva', 5, 2)->default(20.00);
            $table->decimal('montant_ttc', 12, 2)->default(0);
            $table->integer('ordre')->default(0);  // Ordre d'affichage

            $table->timestamps();

            $table->index('repair_order_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_order_items');
    }
};
