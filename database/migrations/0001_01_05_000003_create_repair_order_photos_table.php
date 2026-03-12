<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_order_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_order_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('caption')->nullable();
            $table->enum('moment', ['avant', 'pendant', 'apres'])->default('avant');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('repair_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_order_photos');
    }
};
