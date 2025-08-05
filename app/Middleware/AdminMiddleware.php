<?php

namespace app\Middleware;

use core\AuthManager;

/**
 * Yeniden düzenlenmiş AdminMiddleware
 * AuthManager ile entegre çalışır
 */
class AdminMiddleware {
    
    /**
     * Güçlendirilmiş Admin erişim kontrolü
     */
    public static function handle() {
        $authManager = AuthManager::getInstance();
        
        // 1. Temel authentication kontrol et
        $authResult = $authManager->authenticate();
        
        if (!$authResult['authenticated']) {
            // Session temizle ve giriş sayfasına yönlendir
            self::forceLogout('Authentication gerekli');
            return;
        }
        
        // 2. Admin yetkisi kontrol et
        if (!$authManager->isAdmin()) {
            // Non-admin kullanıcıyı anasayfaya yönlendir
            header('Location: /anasayfa');
            exit();
        }
        
        // 3. Session geçerliliğini kontrol et
        $currentUser = $authManager->getCurrentUser();
        if (!$currentUser || empty($currentUser['id'])) {
            self::forceLogout('Session geçersiz');
            return;
        }
        
        // 4. Security headers ekle
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        
        // 5. Aktiviteyi güncelle
        $authManager->updateActivity();
        
        // 6. Log admin access
        error_log("Admin access: " . $currentUser['email'] . " - " . $_SERVER['REQUEST_URI']);
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
     * Admin route kontrolü (daha spesifik)
     */
    public static function requireAdmin($redirectUrl = '/anasayfa') {
        $authManager = AuthManager::getInstance();
        
        if (!$authManager->isAdmin()) {
            header('Location: ' . $redirectUrl);
            exit();
        }
    }
}
