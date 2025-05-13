<?php

require_once 'config.php';

class LeadService
{
    public function sendLeadToDiscord(array $user): bool
    {
        if (!$user) return false;

        $commonData = [
            "name" => $user['name'],
            "mobile_number" => $user['phone'],
            "email" => $user['email'],
            "source_url" => config('services.discord.source_url', 'https://launchgovtest.homes/'),
        ];

        $additionalData = [
            ["key" => "Who will be included in your EC purchase?", "value" => $user['household']],
            ["key" => "Does your household include a Singapore Citizen?", "value" => $user['citizenship']],
            ["key" => "Are you and/or your co-applicants of eligible age?", "value" => $user['requirement']],
            ["key" => "Is your combined household income below \$16,000?", "value" => $user['household_income']],
            ["key" => "Do you or any co-applicants currently own an HDB flat that has completed the MOP?", "value" => $user['ownership_status']],
            ["key" => "Have you or any co-applicants owned/disposed of private property in last 30 months?", "value" => $user['private_property_ownership']],
            ["key" => "Is this your first application for HDB/EC?", "value" => $user['first_time_applicant']],
        ];

        $commonData['additional_data'] = $additionalData;
        $leadData = $commonData;

        $jsonData = json_encode($leadData);
        $checkJunk = $this->checkJunk($jsonData);
        $ip = $this->fetchIp();

        $webhookData = [
            'client_id' => null,
            'project_id' => null,
            'ip_address' => $ip,
            'is_verified' => 0,
            'status' => 'clear',
            'is_send_discord' => 1,
        ];

        if (!empty($checkJunk['Terms'] ?? [])) {
            $webhookData['status'] = 'junk';
            $webhookData['is_send_discord'] = 0;
        } else {
            $this->sendFrequencyLead($leadData);
            $_SESSION['lead_sent'] = true;
        }

        $webhookData = array_merge($webhookData, $_POST);
        $this->sendData($webhookData);

        return true;
    }

    private function sendData(array $data): string|false
    {
        $curl = curl_init('http://janicez87.sg-host.com/endpoint.php');

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic am9tZWpvdXJuZXl3ZWJzaXRlQGdtYWlsLmNvbTpQQCQkd29yZDA5MDIxOGxlYWRzISM='
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    private function fetchIp(): string|false
    {
        $response = file_get_contents('https://api.ipify.org/?format=json');
        $data = json_decode($response, true);
        return $data['ip'] ?? false;
    }

    private function checkJunk(string $text): array
    {
        $curl = curl_init('https://jomejourney.cognitiveservices.azure.com/contentmoderator/moderate/v1.0/ProcessText/Screen');

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $text,
            CURLOPT_HTTPHEADER => [
                'Content-Type: text/plain',
                "Ocp-Apim-Subscription-Key: 453fe3c404554800bc2c22d7ef681542"
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true) ?? [];
    }

    private function sendDiscordMsg(string $message): string|false
    {
        $payload = json_encode([
            'content' => $message,
            'embeds' => null,
            'attachments' => []
        ]);

        $curl = curl_init(config('services.discord.webhook_url', ''),);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    private function sendFrequencyLead(array $data): string|false
    {
        $curl = curl_init('https://roundrobin.datapoco.ai/api/lead_frequency/add_lead');
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode('Client Management Portal:123456')
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
