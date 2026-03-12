<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_setting_id')->constrained('company_settings')->cascadeOnDelete();
            $table->string('nom_banque');
            $table->string('numero_compte')->nullable();
            $table->string('rib', 30)->nullable();
            $table->string('code_swift', 15)->nullable();
            $table->string('iban', 40)->nullable();
            $table->string('agence')->nullable();
            $table->string('ville_agence')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_setting_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_bank_accounts');
    }
};
