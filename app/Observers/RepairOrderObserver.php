<?php

namespace App\Observers;

use App\Models\NotificationLog;
use App\Models\RepairOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RepairOrderObserver
{
    /**
     * Modification 3 : Notification SMS et Email
     *
     * Lorsque le statut passe à "terminé", notifier le client
     * par email et SMS automatiquement.
     */
    public function updated(RepairOrder $order): void
    {
        // Vérifier si le statut vient de changer vers 'termine'
        if ($order->isDirty('status') && $order->status === 'termine') {
            $this->notifyClient($order);
        }
    }

    private function notifyClient(RepairOrder $order): void
    {
        $client = $order->client;
        if (!$client) return;

        $vehicle = $order->vehicle;
        $vehicleLabel = $vehicle
            ? trim("{$vehicle->marque} {$vehicle->modele}") . " ({$vehicle->immatriculation})"
            : 'votre véhicule';

        // ── Envoi Email ──
        if ($client->email) {
            $this->sendEmail($order, $client, $vehicleLabel);
        }

        // ── Envoi SMS ──
        if ($client->telephone) {
            $this->sendSms($order, $client, $vehicleLabel);
        }
    }

    private function sendEmail(RepairOrder $order, $client, string $vehicleLabel): void
    {
        $clientName = $client->display_name;
        $subject = "Votre véhicule est prêt — {$vehicleLabel}";

        $body = "Bonjour {$clientName},\n\n"
            . "Nous avons le plaisir de vous informer que la réparation de {$vehicleLabel} est terminée.\n\n"
            . "Ordre de réparation : {$order->numero}\n"
            . "Description : {$order->description_panne}\n\n"
            . "Vous pouvez venir récupérer votre véhicule à notre atelier.\n\n"
            . "Merci de votre confiance.\n"
            . "— L'équipe Atelier Pro";

        try {
            Mail::raw($body, function ($message) use ($client, $subject) {
                $message->to($client->email)
                        ->subject($subject);
            });

            NotificationLog::create([
                'notifiable_type' => RepairOrder::class,
                'notifiable_id'   => $order->id,
                'client_id'       => $client->id,
                'canal'           => 'email',
                'destinataire'    => $client->email,
                'sujet'           => $subject,
                'message'         => $body,
                'statut'          => 'envoye',
            ]);
        } catch (\Exception $e) {
            Log::error("Notification email échouée pour OR {$order->numero}: " . $e->getMessage());

            NotificationLog::create([
                'notifiable_type' => RepairOrder::class,
                'notifiable_id'   => $order->id,
                'client_id'       => $client->id,
                'canal'           => 'email',
                'destinataire'    => $client->email,
                'sujet'           => $subject,
                'message'         => $body,
                'statut'          => 'echoue',
                'erreur'          => $e->getMessage(),
            ]);
        }
    }

    private function sendSms(RepairOrder $order, $client, string $vehicleLabel): void
    {
        $clientName = $client->display_name;

        // SMS court (max 160 caractères)
        $smsMessage = "Bonjour {$clientName}, la réparation de {$vehicleLabel} est terminée. Vous pouvez le récupérer. — Atelier Pro";

        // Tronquer si nécessaire
        if (mb_strlen($smsMessage) > 160) {
            $smsMessage = "Bonjour, votre véhicule {$vehicleLabel} est prêt. Venez le récupérer. — Atelier Pro";
        }

        try {
            // ══════════════════════════════════════════════
            // TODO: Intégrer ici l'API SMS (Twilio/Infobip)
            // ══════════════════════════════════════════════
            //
            // Exemple avec Twilio :
            // $twilio = new \Twilio\Rest\Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
            // $twilio->messages->create($client->telephone, [
            //     'from' => env('TWILIO_FROM'),
            //     'body' => $smsMessage,
            // ]);
            //
            // Pour l'instant, on log le SMS comme "en_attente"
            // jusqu'à ce que l'API SMS soit configurée.

            NotificationLog::create([
                'notifiable_type' => RepairOrder::class,
                'notifiable_id'   => $order->id,
                'client_id'       => $client->id,
                'canal'           => 'sms',
                'destinataire'    => $client->telephone,
                'message'         => $smsMessage,
                'statut'          => 'en_attente', // Changera en 'envoye' quand l'API SMS sera configurée
            ]);

            Log::info("SMS en attente pour OR {$order->numero} → {$client->telephone}");

        } catch (\Exception $e) {
            Log::error("Notification SMS échouée pour OR {$order->numero}: " . $e->getMessage());

            NotificationLog::create([
                'notifiable_type' => RepairOrder::class,
                'notifiable_id'   => $order->id,
                'client_id'       => $client->id,
                'canal'           => 'sms',
                'destinataire'    => $client->telephone,
                'message'         => $smsMessage,
                'statut'          => 'echoue',
                'erreur'          => $e->getMessage(),
            ]);
        }
    }
}
