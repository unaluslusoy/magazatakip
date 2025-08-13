<?php

namespace app\Controllers\Api;

use core\Controller;
use app\Models\GetirAyarlar;
use app\Models\GetirLog;

class GetirWebhookController extends Controller
{
    public function newOrder()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $headers = array_change_key_case(getallheaders() ?: [], CASE_LOWER);
        } catch (\Throwable $e) {
            $headers = [];
        }
        $apiKeyHeader = $headers['x-api-key'] ?? '';

        $ayar = (new GetirAyarlar())->getAyarlar();
        $expectedKey = $ayar['webhook_api_key'] ?? '';
        $enabled = !empty($ayar['enabled']);

        if (!$enabled) {
            http_response_code(503);
            echo json_encode([ 'success' => false, 'error' => 'service_disabled' ]);
            return;
        }

        if (empty($expectedKey) || $apiKeyHeader !== $expectedKey) {
            http_response_code(401);
            echo json_encode([ 'success' => false, 'error' => 'invalid_api_key' ]);
            return;
        }

        $raw = file_get_contents('php://input');
        $json = json_decode($raw ?: '{}', true);

        // Logla
        try {
            (new GetirLog())->add([
                'type' => 'webhook',
                'direction' => 'in',
                'method' => 'POST',
                'url' => '/api/getir/newOrder',
                'status' => 200,
                'body_preview' => mb_substr($raw, 0, 2000),
                'message' => null
            ]);
        } catch (\Throwable $e) {}

        // TODO: sipariş kaydetme/iş akışı (faz-2)

        echo json_encode([ 'success' => true, 'received' => is_array($json) ? array_keys($json) : null ]);
    }
}


