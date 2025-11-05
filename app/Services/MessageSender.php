<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class MessageSender
{
    protected Client $client;
    protected string $from;

    public function __construct()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $this->from = config('services.twilio.whatsapp_from'); // e.g. 'whatsapp:+1415XXXXXXX' or '+1XXX...'
        $this->client = new Client($sid, $token);
    }

    /**
     * Send WhatsApp message via Twilio.
     *
     * @param string $to E.164 phone number like '+91XXXXXXXXXX' or 'whatsapp:+91XXXXXXXXXX' depending on from
     * @param string $message
     * @return bool
     */
    public function sendWhatsApp(string $to, string $message): bool
    {
        try {
            // Use whatsapp: prefix for both from and to if you use Twilio WhatsApp sandbox
            $toWithPrefix = str_starts_with($to, 'whatsapp:') ? $to : 'whatsapp:' . $to;
            $fromWithPrefix = str_starts_with($this->from, 'whatsapp:') ? $this->from : 'whatsapp:' . $this->from;

            $this->client->messages->create(
                $toWithPrefix,
                [
                    'from' => $fromWithPrefix,
                    'body' => $message,
                ]
            );

            return true;
        } catch (\Throwable $e) {
            Log::error('WhatsApp send failed: ' . $e->getMessage(), [
                'to' => $to,
                'message' => $message,
            ]);
            return false;
        }
    }

    /**
     * Fallback: send SMS (if WhatsApp unavailable).
     */
    public function sendSms(string $to, string $message): bool
    {
        try {
            $this->client->messages->create(
                $to,
                [
                    'from' => $this->from,
                    'body' => $message,
                ]
            );
            return true;
        } catch (\Throwable $e) {
            Log::error('SMS send failed: ' . $e->getMessage(), [
                'to' => $to,
                'message' => $message,
            ]);
            return false;
        }
    }
}
