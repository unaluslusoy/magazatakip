<?php
// PWA Analytics Endpoint
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // Get raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    // Add server-side data
    $analyticsData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'referer' => $_SERVER['HTTP_REFERER'] ?? '',
        'event_data' => $data
    ];
    
    // Log directory
    $logDir = __DIR__ . '/logs/analytics/';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Daily log files
    $logFile = $logDir . 'pwa_analytics_' . date('Y-m-d') . '.log';
    
    // Write to log file
    file_put_contents(
        $logFile, 
        json_encode($analyticsData) . "\n", 
        FILE_APPEND | LOCK_EX
    );
    
    // Optional: Send to external analytics service
    if (defined('ANALYTICS_WEBHOOK_URL') && ANALYTICS_WEBHOOK_URL) {
        sendToExternalAnalytics($analyticsData);
    }
    
    // Response
    http_response_code(200);
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

function sendToExternalAnalytics($data) {
    // Google Analytics 4 Measurement Protocol
    if (defined('GA4_MEASUREMENT_ID') && defined('GA4_API_SECRET')) {
        $ga4Data = [
            'client_id' => $data['event_data']['client_id'] ?? uniqid(),
            'events' => [
                [
                    'name' => $data['event_data']['event'] ?? 'custom_event',
                    'params' => $data['event_data']['data'] ?? []
                ]
            ]
        ];
        
        $ga4Url = 'https://www.google-analytics.com/mp/collect?measurement_id=' . 
                  GA4_MEASUREMENT_ID . '&api_secret=' . GA4_API_SECRET;
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($ga4Data)
            ]
        ]);
        
        file_get_contents($ga4Url, false, $context);
    }
    
    // Custom webhook
    if (defined('ANALYTICS_WEBHOOK_URL')) {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($data)
            ]
        ]);
        
        file_get_contents(ANALYTICS_WEBHOOK_URL, false, $context);
    }
}
?>