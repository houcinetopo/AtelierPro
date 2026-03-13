<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('repair_order_id')->nullable()->after('supplier_id')
                  ->constrained('repair_orders')->nullOnDelete();
            $table->index('repair_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['repair_order_id']);
            $table->dropColumn('repair_order_id');
        });
    }
};
