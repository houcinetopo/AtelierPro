<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id();
            $table->date('date_session')->unique();
            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->decimal('solde_ouverture', 12, 2)->default(0);
            $table->decimal('total_entrees', 12, 2)->default(0);
            $table->decimal('total_sorties', 12, 2)->default(0);
            $table->decimal('solde_theorique', 12, 2)->default(0);    // ouverture + entrées - sorties
            $table->decimal('solde_reel', 12, 2)->nullable();         // Compté manuellement
            $table->decimal('ecart', 12, 2)->nullable();              // réel - théorique

            $table->text('notes_ouverture')->nullable();
            $table->text('notes_cloture')->nullable();

            $table->enum('statut', ['ouverte', 'cloturee'])->default('ouverte');
            $table->timestamp('heure_ouverture')->nullable();
            $table->timestamp('heure_cloture')->nullable();

            $table->timestamps();

            $table->index('date_session');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_sessions');
    }
};
