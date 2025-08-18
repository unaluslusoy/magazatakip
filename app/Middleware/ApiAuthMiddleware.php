<?php

namespace app\Middleware;

use core\AuthManager;

class ApiAuthMiddleware
{
    public static function handle()
    {
        // Rate limit
        self::rateLimitGuard();
        // CORS headers for API
        // PWA (standalone) için çerezli isteklerde origin'i kısıtlayın
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if ($origin && preg_match('#^https?://(www\.)?magazatakip\.com\.tr$#', $origin)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin');
        } else {
            header('Access-Control-Allow-Origin: https://magazatakip.com.tr');
        }
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
        
        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            header('Content-Length: 0');
            exit();
        }

        $path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');

        // Public (whitelist) API uçları - authentication gerektirmez
        $publicEndpoints = [
            'api/auth/login',
            'api/onesignal/config',
            'api/onesignal/status',
            'api/getir/newOrder',
        ];

        if (in_array($path, $publicEndpoints, true)) {
            return; // Whitelist: geçişe izin ver
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

        // Admin gerektiren API prefix'leri
        $adminRequiredPrefixes = [
            'api/kullanicilar',
            'api/kullanici',
            'api/personeller',
            'api/personel',
            'api/magazalar',
            'api/magaza',
            'api/notification', // test bildirimleri
        ];

        if (self::pathStartsWith($path, $adminRequiredPrefixes)) {
            if (!$authManager->isAdmin()) {
                self::forbiddenResponse('Admin yetkisi gerekli');
                return;
            }
        }

        // Activity güncelle
        $authManager->updateActivity();
        // Session kilidini serbest bırak (API işlemleri uzun sürebilir)
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_write_close();
        }
        
        error_log("API Access: " . $currentUser['email'] . " - " . $_SERVER['REQUEST_URI']);
    }

    private static function rateLimitGuard(): void
    {
        try {
            if (!class_exists('core\\Request')) { require_once __DIR__ . '/../../core/Request.php'; }
            $path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
            $ip = \core\Request::getClientIp();
            $cfgPath = __DIR__ . '/../../config/rate_limit.php';
            $cfg = file_exists($cfgPath) ? (require $cfgPath) : ['enabled'=>false];
            if (empty($cfg['enabled'])) { return; }

            $window = (int)($cfg['window_seconds'] ?? 60);
            $max = (int)($cfg['default']['max_requests'] ?? 60);
            if (!empty($cfg['overrides'][$path]['max_requests'])) {
                $max = (int)$cfg['overrides'][$path]['max_requests'];
            }

            $key = 'rl:' . sha1($ip . '|' . $path);
            $cache = \core\CacheManager::getInstance();
            $info = $cache->get($key) ?: ['count'=>0,'start'=>time()];
            $now = time();
            if ($now - ($info['start'] ?? 0) >= $window) { $info = ['count'=>0,'start'=>$now]; }
            $info['count'] = ($info['count'] ?? 0) + 1;
            $cache->set($key, $info, $window);
            if ($info['count'] > $max) {
                http_response_code(429);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => 'Çok fazla istek. Lütfen daha sonra tekrar deneyin.',
                    'retry_after' => max(0, $window - ($now - ($info['start'] ?? $now)))
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }
        } catch (\Throwable $e) {
            // sessiz geç
        }
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

    private static function pathStartsWith($path, array $prefixes)
    {
        foreach ($prefixes as $prefix) {
            if (strpos($path, $prefix) === 0) {
                return true;
            }
        }
        return false;
    }
}