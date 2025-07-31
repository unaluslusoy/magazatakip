<?php
namespace app\Services;

use GuzzleHttp\Client;

class WhatsAppService
{
    private $apiUrl;
    private $apiKey;

    public function __construct()
    {
        $this->apiUrl = 'https://graph.facebook.com/v14.0/whatsapp-business-account-id/messages'; // WhatsApp Business API URL

        $this->apiKey = '471011825670657|jvFd33uIZO8x_j4pVQSnqtUTDBM'; // Access Token

    }

    public function sendMessage($phoneNumber, $message, $pdfUrl)
    {
        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ];

        $body = [
            'messaging_product' => 'whatsapp',
            'to' => $phoneNumber,
            'type' => 'document',
            'document' => [
                'link' => $pdfUrl,
                'caption' => $message
            ]
        ];

        try {
            $response = $client->post($this->apiUrl, [
                'headers' => $headers,
                'body' => json_encode($body)
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody(), true);

            return $statusCode === 200 && isset($responseBody['messages']);
        } catch (\Exception $e) {
            error_log("WhatsApp API Error: " . $e->getMessage());
            return false;
        }
    }
}
