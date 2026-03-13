<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experts', function (Blueprint $table) {
            $table->id();
            $table->string('nom_complet');
            $table->string('cabinet')->nullable();
            $table->string('telephone')->nullable();
            $table->string('telephone_2')->nullable();
            $table->string('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('code_postal')->nullable();
            $table->boolean('actif')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('expert_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('label')->nullable(); // principal, secondaire, cabinet
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index('expert_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_emails');
        Schema::dropIfExists('experts');
    }
};
