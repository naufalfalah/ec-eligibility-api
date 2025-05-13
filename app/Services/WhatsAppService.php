<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    /**
     * Send WhatsApp message to a client.
     *
     * @param string $client_number The recipient's phone number (without country code).
     * @param string $message The message text to send.
     * @return string The response from the API or error message.
     */
    public function sendMessage(string $client_number, string $message): string
    {
        if (empty($client_number) || empty($message)) {
            return "Error: client_number or message is empty.";
        }

        try {
            $api_key = config('services.2chat.api_key');
            $from_number = config('services.2chat.from_number');

            // Make the HTTP request using Laravel's Http Client
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-User-API-Key' => $api_key,
            ])->post('https://api.p.2chat.io/open/whatsapp/send-message', [
                "to_number" => '+65' . $client_number,
                "from_number" => $from_number,
                "text" => $message,
            ]);

            // Return the response as JSON
            return $response->body();
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
