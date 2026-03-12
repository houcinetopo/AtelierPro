<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->date('date_paiement');
            $table->decimal('montant', 12, 2);
            $table->enum('mode', ['especes', 'cheque', 'virement', 'carte', 'effet'])->default('especes');
            $table->string('reference')->nullable();  // N° chèque, réf virement, etc.
            $table->string('banque')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('invoice_id');
            $table->index('date_paiement');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};
