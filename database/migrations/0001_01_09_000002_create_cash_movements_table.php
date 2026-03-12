<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();

            // ── Lien optionnel ──
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_payment_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('type', ['entree', 'sortie']);
            $table->enum('categorie', [
                // Entrées
                'paiement_client',     // Encaissement facture
                'acompte',             // Acompte reçu
                'autre_entree',        // Autre recette
                // Sorties
                'achat_pieces',        // Achat pièces / fournitures
                'salaire',             // Paiement employé
                'loyer',               // Loyer atelier
                'charges',             // Eau, électricité, etc.
                'carburant',           // Carburant véhicules
                'outillage',           // Achat outils
                'frais_divers',        // Autres dépenses
                'remboursement',       // Remboursement client
            ]);

            $table->string('libelle');
            $table->decimal('montant', 12, 2);
            $table->enum('mode_paiement', ['especes', 'cheque', 'virement', 'carte', 'effet'])->default('especes');
            $table->string('reference')->nullable();      // N° pièce, chèque, etc.
            $table->string('beneficiaire')->nullable();    // Nom du bénéficiaire / payeur
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('cash_session_id');
            $table->index('type');
            $table->index('categorie');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};
