<?php

namespace app\Middleware;

use core\AuthManager;

/**
 * Yeniden düzenlenmiş AuthMiddleware
 * Artık centralized AuthManager kullanıyor
 */
class AuthMiddleware {
    
    /**
     * Güçlendirilmiş Authentication kontrolü
     */
    public static function handle() {
        $authManager = AuthManager::getInstance();
        $authResult = $authManager->authenticate();
        
        // 1. Kimlik doğrulama kontrolü
        if (!$authResult['authenticated']) {
            self::forceLogout('Authentication gerekli');
            return;
        }
        
        // 2. Session geçerliliğini kontrol et
        $currentUser = $authManager->getCurrentUser();
        if (!$currentUser || empty($currentUser['id'])) {
            self::forceLogout('Session geçersiz');
            return;
        }
        
        // 3. Security headers ekle
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        
        // 4. Authenticated kullanıcı için aktiviteyi güncelle
        $authManager->updateActivity();
        
        // 5. Log user access
        error_log("User access: " . $currentUser['email'] . " - " . $_SERVER['REQUEST_URI']);
    }
    
    /**
     * Zorla logout ve yönlendirme
     */
    private static function forceLogout($reason) {
        $authManager = AuthManager::getInstance();
        $authManager->logout();
        
        error_log("Force logout: $reason - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        
        // Cache-buster ile giriş sayfasına yönlendir
        header('Location: /auth/giris?t=' . time());
        exit();
    }
    
    /**
     * Route bazlı erişim kontrolü
     */
    public static function checkRoute($route) {
        $authManager = AuthManager::getInstance();
        $authResult = $authManager->authenticate();
        
        if (!$authResult['authenticated']) {
            header('Location: /auth/giris');
            exit();
        }
        
        // Route erişim kontrolü
        $accessResult = $authManager->checkRouteAccess($route);
        if (!$accessResult['allowed']) {
            if (isset($accessResult['redirect'])) {
                header('Location: ' . $accessResult['redirect']);
                exit();
            }
        }
    }
}
