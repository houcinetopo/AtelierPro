<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\RepairOrder;
use App\Services\DocumentService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(
        private DocumentService $documentService
    ) {}

    // ══════════════════════════════════════════════
    // MAPPING type → modèle
    // ══════════════════════════════════════════════

    private function resolveDocument(string $type, int $id)
    {
        return match($type) {
            'devis'            => Quote::findOrFail($id),
            'facture'          => Invoice::findOrFail($id),
            'bon_commande'     => PurchaseOrder::findOrFail($id),
            'bon_livraison'    => DeliveryNote::findOrFail($id),
            'attestation',
            'ordre_reparation' => RepairOrder::findOrFail($id),
            default            => abort(404, "Type de document inconnu : {$type}"),
        };
    }

    // ══════════════════════════════════════════════
    // TÉLÉCHARGER PDF
    // ══════════════════════════════════════════════

    public function download(string $type, int $id)
    {
        $document = $this->resolveDocument($type, $id);
        return $this->documentService->downloadPdf($type, $document);
    }

    // ══════════════════════════════════════════════
    // IMPRIMER (afficher dans navigateur)
    // ══════════════════════════════════════════════

    public function print(string $type, int $id)
    {
        $document = $this->resolveDocument($type, $id);
        return $this->documentService->streamPdf($type, $document);
    }

    // ══════════════════════════════════════════════
    // ENVOYER PAR EMAIL
    // ══════════════════════════════════════════════

    public function sendEmail(Request $request, string $type, int $id)
    {
        $data = $request->validate([
            'recipient_type' => ['required', 'in:client,expert,fournisseur,custom'],
            'email'          => ['required_if:recipient_type,custom', 'nullable', 'email'],
        ]);

        $document = $this->resolveDocument($type, $id);

        // Résoudre l'email du destinataire
        $recipientType = $data['recipient_type'];
        $email = null;
        $recipientName = null;

        switch ($recipientType) {
            case 'client':
                $client = $document->client ?? null;
                if (!$client || !$client->email) {
                    return back()->with('error', 'Le client n\'a pas d\'adresse email.');
                }
                $email = $client->email;
                $recipientName = $client->display_name;
                break;

            case 'expert':
                $expert = null;
                // Si c'est un OR, prendre l'expert de l'OR
                if ($document instanceof RepairOrder) {
                    $expert = $document->expert;
                } elseif (method_exists($document, 'repairOrder') && $document->repairOrder?->expert) {
                    $expert = $document->repairOrder->expert;
                }
                if (!$expert || !$expert->primary_email) {
                    return back()->with('error', 'Aucun expert associé ou pas d\'email configuré.');
                }
                $email = $expert->primary_email;
                $recipientName = $expert->nom_complet;
                break;

            case 'fournisseur':
                // Pour les bons de commande
                if ($document instanceof PurchaseOrder) {
                    $supplier = $document->supplier;
                } else {
                    return back()->with('error', 'L\'envoi au fournisseur n\'est disponible que pour les bons de commande.');
                }
                if (!$supplier || !$supplier->email) {
                    return back()->with('error', 'Le fournisseur n\'a pas d\'adresse email.');
                }
                $email = $supplier->email;
                $recipientName = $supplier->raison_sociale;
                break;

            case 'custom':
                $email = $data['email'];
                $recipientName = 'Destinataire';
                break;
        }

        $success = $this->documentService->sendByEmail($type, $document, $email, $recipientType, $recipientName);

        if ($success) {
            return back()->with('success', "Le document a été envoyé par email à {$email}.");
        }

        return back()->with('error', "Échec de l'envoi de l'email à {$email}. Vérifiez la configuration email.");
    }
}
