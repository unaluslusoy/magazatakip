<?php

namespace app\Controllers\Api;

use core\AuthManager;

class AuthController
{
    public function __construct()
    {
        // Sadece response tipi; CORS ve OPTIONS ApiAuthMiddleware tarafından yönetilir
        header('Content-Type: application/json; charset=utf-8');
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    /**
     * POST /api/auth/login
     */
    public function login()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || empty($input['email']) || empty($input['password'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Email ve şifre zorunludur']);
                return;
            }

            $email = (string)$input['email'];
            $password = (string)$input['password'];
            $remember = isset($input['remember']) ? (bool)$input['remember'] : false;

            $auth = AuthManager::getInstance();
            $result = $auth->login($email, $password, $remember);

            if (!($result['success'] ?? false)) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => $result['message'] ?? 'Giriş başarısız']);
                return;
            }

            echo json_encode([
                'success' => true,
                'user' => $result['user'] ?? null,
                'redirect' => $result['redirect'] ?? null,
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }

    /**
     * POST /api/auth/logout
     */
    public function logout()
    {
        try {
            $auth = AuthManager::getInstance();
            $auth->logout();
            echo json_encode(['success' => true]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Çıkış hatası: ' . $e->getMessage()]);
        }
    }

    /**
     * GET /api/auth/me
     */
    public function me()
    {
        try {
            $auth = AuthManager::getInstance();
            $info = $auth->getSessionInfo();
            if (!($info['authenticated'] ?? false)) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Oturum bulunamadı']);
                return;
            }

            echo json_encode([
                'success' => true,
                'data' => $info,
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }
}





