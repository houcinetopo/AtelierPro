<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\NotificationLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentService
{
    /**
     * Documents supportés et leur configuration
     */
    public const DOCUMENT_TYPES = [
        'devis'         => ['view' => 'pdf.devis',         'prefix' => 'Devis',         'model' => 'Quote'],
        'facture'       => ['view' => 'pdf.facture',       'prefix' => 'Facture',       'model' => 'Invoice'],
        'bon_commande'  => ['view' => 'pdf.bon-commande',  'prefix' => 'BC',            'model' => 'PurchaseOrder'],
        'bon_livraison' => ['view' => 'pdf.bon-livraison', 'prefix' => 'BL',            'model' => 'DeliveryNote'],
        'attestation'   => ['view' => 'pdf.attestation',   'prefix' => 'Attestation',   'model' => 'RepairOrder'],
        'ordre_reparation' => ['view' => 'pdf.ordre-reparation', 'prefix' => 'OR', 'model' => 'RepairOrder'],
    ];

    /**
     * Générer un PDF pour un document
     *
     * @param string $type Type de document (devis, facture, etc.)
     * @param Model $document Le modèle Eloquent
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generatePdf(string $type, Model $document)
    {
        $config = self::DOCUMENT_TYPES[$type] ?? null;
        if (!$config) {
            throw new \InvalidArgumentException("Type de document non supporté : {$type}");
        }

        $company = \App\Models\CompanySetting::first();
        $data = [
            'document' => $document,
            'company'  => $company,
            'type'     => $type,
        ];

        // Charger les relations selon le type
        $this->loadRelations($type, $document);

        $pdf = Pdf::loadView($config['view'], $data);
        $pdf->setPaper('a4');

        return $pdf;
    }

    /**
     * Télécharger le PDF
     */
    public function downloadPdf(string $type, Model $document)
    {
        $pdf = $this->generatePdf($type, $document);
        $filename = $this->generateFilename($type, $document);

        return $pdf->download($filename);
    }

    /**
     * Afficher le PDF dans le navigateur (pour impression)
     */
    public function streamPdf(string $type, Model $document)
    {
        $pdf = $this->generatePdf($type, $document);
        $filename = $this->generateFilename($type, $document);

        return $pdf->stream($filename);
    }

    /**
     * Envoyer le document par email
     *
     * @param string $type Type de document
     * @param Model $document Le modèle
     * @param string $email Adresse email du destinataire
     * @param string $recipientType client|expert|fournisseur
     * @param string|null $recipientName Nom du destinataire
     * @return bool
     */
    public function sendByEmail(
        string $type,
        Model $document,
        string $email,
        string $recipientType = 'client',
        ?string $recipientName = null
    ): bool {
        $config = self::DOCUMENT_TYPES[$type] ?? null;
        if (!$config) return false;

        $pdf = $this->generatePdf($type, $document);
        $filename = $this->generateFilename($type, $document);
        $numero = $document->numero ?? $document->id;
        $subject = "{$config['prefix']} {$numero} — Atelier Pro";

        $body = $this->buildEmailBody($type, $document, $recipientName ?? 'Client');

        try {
            Mail::raw($body, function ($message) use ($email, $subject, $pdf, $filename) {
                $message->to($email)
                        ->subject($subject)
                        ->attachData($pdf->output(), $filename, [
                            'mime' => 'application/pdf',
                        ]);
            });

            // Log la notification
            $clientId = $document->client_id ?? null;
            NotificationLog::create([
                'notifiable_type' => get_class($document),
                'notifiable_id'   => $document->id,
                'client_id'       => $clientId,
                'canal'           => 'email',
                'destinataire'    => $email,
                'sujet'           => $subject,
                'message'         => "Document {$config['prefix']} {$numero} envoyé à {$recipientType}",
                'statut'          => 'envoye',
            ]);

            ActivityLog::log('email', "{$config['prefix']} {$numero} envoyé par email à {$email} ({$recipientType})", $document);

            return true;
        } catch (\Exception $e) {
            Log::error("Envoi email {$type} {$numero} échoué : " . $e->getMessage());

            NotificationLog::create([
                'notifiable_type' => get_class($document),
                'notifiable_id'   => $document->id,
                'client_id'       => $document->client_id ?? null,
                'canal'           => 'email',
                'destinataire'    => $email,
                'sujet'           => $subject,
                'statut'          => 'echoue',
                'erreur'          => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Charger les relations nécessaires selon le type de document
     */
    private function loadRelations(string $type, Model $document): void
    {
        $relations = match($type) {
            'devis'            => ['client', 'vehicle', 'items', 'createdBy'],
            'facture'          => ['client', 'vehicle', 'items', 'repairOrder', 'createdBy', 'payments'],
            'bon_commande'     => ['supplier', 'items'],
            'bon_livraison'    => ['client', 'vehicle', 'repairOrder'],
            'attestation'      => ['client', 'vehicle', 'technicien'],
            'ordre_reparation' => ['client', 'vehicle', 'technicien', 'items', 'createdBy'],
            default            => [],
        };

        $document->loadMissing($relations);
    }

    /**
     * Générer le nom de fichier PDF
     */
    private function generateFilename(string $type, Model $document): string
    {
        $config = self::DOCUMENT_TYPES[$type];
        $numero = $document->numero ?? $document->id;
        $clean = preg_replace('/[^a-zA-Z0-9_-]/', '_', $numero);

        return "{$config['prefix']}_{$clean}.pdf";
    }

    /**
     * Construire le corps de l'email
     */
    private function buildEmailBody(string $type, Model $document, string $recipientName): string
    {
        $config = self::DOCUMENT_TYPES[$type];
        $numero = $document->numero ?? $document->id;
        $typeName = mb_strtolower($config['prefix']);

        return "Bonjour {$recipientName},\n\n"
            . "Veuillez trouver ci-joint le {$typeName} n° {$numero}.\n\n"
            . "N'hésitez pas à nous contacter pour toute question.\n\n"
            . "Cordialement,\n"
            . "L'équipe Atelier Pro";
    }
}
