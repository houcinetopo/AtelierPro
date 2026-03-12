<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('description')->nullable();
            $table->enum('type', ['avant_reparation', 'apres_reparation', 'dommage', 'general'])->default('general');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('vehicle_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_photos');
    }
};
