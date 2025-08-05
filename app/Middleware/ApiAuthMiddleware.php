<?php

namespace app\Middleware;

use core\AuthManager;

class ApiAuthMiddleware
{
    public static function handle()
    {
        // CORS headers for API
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        $authManager = AuthManager::getInstance();
        $authResult = $authManager->authenticate();
        
        if (!$authResult['authenticated']) {
            self::unauthorizedResponse('Authentication gerekli');
            return;
        }

        $currentUser = $authManager->getCurrentUser();
        if (!$currentUser || empty($currentUser['id'])) {
            self::unauthorizedResponse('Session geçersiz');
            return;
        }

        // Admin kontrolü (API endpoint'lerine göre ayarlanabilir)
        if (!$authManager->isAdmin()) {
            self::forbiddenResponse('Admin yetkisi gerekli');
            return;
        }

        // Activity güncelle
        $authManager->updateActivity();
        
        error_log("API Access: " . $currentUser['email'] . " - " . $_SERVER['REQUEST_URI']);
    }

    private static function unauthorizedResponse($message)
    {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $message,
            'code' => 401
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    private static function forbiddenResponse($message)
    {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $message,
            'code' => 403
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
}