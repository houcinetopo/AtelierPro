<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Modification 5: Devis obligatoire avant OR ──
        Schema::table('repair_orders', function (Blueprint $table) {
            $table->foreignId('quote_id')->nullable()->after('id')
                  ->constrained('quotes')->nullOnDelete();
            $table->foreignId('expert_id')->nullable()->after('technicien_id')
                  ->constrained('experts')->nullOnDelete();
            $table->index('quote_id');
            $table->index('expert_id');
        });

        // ── Modification 7: Liaison OR-Stock-Fournisseurs ──
        Schema::table('repair_order_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('repair_order_id')
                  ->constrained('products')->nullOnDelete();
            $table->foreignId('fournisseur_id')->nullable()->after('product_id')
                  ->constrained('suppliers')->nullOnDelete();
            $table->decimal('prix_achat', 10, 2)->default(0)->after('prix_unitaire');
            $table->enum('source', ['stock', 'commande', 'manuel'])->default('manuel')->after('ordre');
            $table->index('product_id');
            $table->index('fournisseur_id');
        });

        // ── Modification 3: Notification log ──
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('notifiable'); // repair_order, invoice, etc.
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('canal', ['email', 'sms']);
            $table->string('destinataire'); // email or phone
            $table->string('sujet')->nullable();
            $table->text('message')->nullable();
            $table->enum('statut', ['envoye', 'echoue', 'en_attente'])->default('en_attente');
            $table->text('erreur')->nullable();
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');

        Schema::table('repair_order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['fournisseur_id']);
            $table->dropColumn(['product_id', 'fournisseur_id', 'prix_achat', 'source']);
        });

        Schema::table('repair_orders', function (Blueprint $table) {
            $table->dropForeign(['quote_id']);
            $table->dropForeign(['expert_id']);
            $table->dropColumn(['quote_id', 'expert_id']);
        });
    }
};
