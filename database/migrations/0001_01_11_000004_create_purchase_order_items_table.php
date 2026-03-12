<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            $table->string('designation');
            $table->string('reference')->nullable();
            $table->decimal('quantite', 10, 2)->default(1);
            $table->decimal('quantite_recue', 10, 2)->default(0);
            $table->string('unite')->default('u');
            $table->decimal('prix_unitaire', 10, 2)->default(0);
            $table->decimal('remise', 5, 2)->default(0);
            $table->decimal('montant_ht', 12, 2)->default(0);
            $table->decimal('taux_tva', 5, 2)->default(20.00);
            $table->decimal('montant_ttc', 12, 2)->default(0);
            $table->integer('ordre')->default(0);

            $table->timestamps();

            $table->index('purchase_order_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
