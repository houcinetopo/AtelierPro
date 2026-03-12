<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('periode');  // ex: "2024-01" pour Janvier 2024
            $table->decimal('montant', 10, 2);
            $table->date('date_paiement');
            $table->enum('mode_paiement', ['especes', 'cheque', 'virement', 'autre'])->default('especes');
            $table->string('reference')->nullable(); // N° chèque, réf virement...
            $table->text('notes')->nullable();
            $table->decimal('prime', 10, 2)->default(0);
            $table->decimal('deduction', 10, 2)->default(0);
            $table->decimal('net_paye', 10, 2); // montant + prime - deduction
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('employee_id');
            $table->index('periode');
            $table->index('date_paiement');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_payments');
    }
};
