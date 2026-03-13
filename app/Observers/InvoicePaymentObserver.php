<?php

namespace App\Observers;

use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\InvoicePayment;

class InvoicePaymentObserver
{
    /**
     * Modification 1 : Liaison Paiement → Caisse
     *
     * Lorsqu'un paiement est enregistré sur une facture,
     * créer automatiquement un mouvement d'entrée en caisse.
     */
    public function created(InvoicePayment $payment): void
    {
        $this->createCashEntry($payment);
    }

    /**
     * Si un paiement est supprimé, inverser le mouvement de caisse.
     */
    public function deleted(InvoicePayment $payment): void
    {
        // Supprimer le mouvement de caisse lié
        CashMovement::where('invoice_payment_id', $payment->id)->delete();
    }

    /**
     * Créer un mouvement d'entrée en caisse pour ce paiement
     */
    private function createCashEntry(InvoicePayment $payment): void
    {
        $invoice = $payment->invoice;
        if (!$invoice) return;

        // Trouver ou créer la session de caisse du jour
        $session = CashSession::where('date_session', now()->toDateString())
            ->where('statut', 'ouverte')
            ->first();

        // Si pas de session ouverte, en créer une automatiquement
        if (!$session) {
            $session = CashSession::openToday();
        }

        $clientName = $invoice->client?->display_name ?? 'Client';

        CashMovement::create([
            'cash_session_id'    => $session->id,
            'recorded_by'        => $payment->recorded_by ?? auth()->id(),
            'invoice_id'         => $invoice->id,
            'invoice_payment_id' => $payment->id,
            'type'               => 'entree',
            'categorie'          => 'paiement_client',
            'libelle'            => "Paiement facture {$invoice->numero} — {$clientName}",
            'montant'            => $payment->montant,
            'mode_paiement'      => $payment->mode,
            'reference'          => $payment->reference,
            'beneficiaire'       => $clientName,
            'notes'              => "Paiement auto-enregistré depuis la facture {$invoice->numero}",
        ]);
    }
}
