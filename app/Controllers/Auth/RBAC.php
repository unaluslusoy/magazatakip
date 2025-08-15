<?php

namespace app\Controllers\Auth;

use core\AuthManager;

/**
 * İyileştirilmiş Role-Based Access Control
 * AuthManager ile entegre çalışır
 */
class RBAC {
    
    private static $permissions = [
        // Guest (oturum açmamış) kullanıcı
        'guest' => [
            'auth.login',
            'auth.register',
            'public.view'
        ],
        
        // Normal kullanıcı
        'user' => [
            'dashboard.view',
            'profile.view',
            'profile.edit',
            'istek.create',
            'istek.list',
            'istek.edit.own',
            'isemri.create',
            'isemri.list.own',
            'isemri.edit.own',
            'ciro.create',
            'ciro.list.own',
            'ciro.edit.own',
            'fatura_talep.create',
            'fatura_talep.list.own',
            'fatura_talep.edit.own'
        ],
        
        // Admin kullanıcı
        'admin' => [
            // Tüm user yetkilerini dahil et
            'dashboard.view',
            'profile.view',
            'profile.edit',
            
            // Admin özel yetkileri
            'admin.dashboard',
            'admin.users.list',
            'admin.users.create',
            'admin.users.edit',
            'admin.users.delete',
            'admin.magazalar.list',
            'admin.magazalar.create',
            'admin.magazalar.edit',
            'admin.magazalar.delete',
            'admin.istekler.list',
            'admin.istekler.edit',
            'admin.istekler.delete',
            'admin.personel.list',
            'admin.personel.create',
            'admin.personel.edit',
            'admin.personel.delete',
            'admin.bildirimler.send',
            'admin.bildirimler.list',
            'admin.reports.view',
            'admin.settings.edit',
            
        ]
    ];
    
    /**
     * Kullanıcının belirli bir yetkisi var mı kontrol et
     */
    public static function hasPermission($permission, $userId = null) {
        $authManager = AuthManager::getInstance();
        $user = $authManager->getCurrentUser();
        
        if (!$user) {
            // Oturum açmamış kullanıcı - guest yetkileri
            return in_array($permission, self::$permissions['guest']);
        }
        
        $role = $user['yonetici'] ? 'admin' : 'user';
        return in_array($permission, self::$permissions[$role]);
    }
    
    /**
     * Route-permission mapping
     */
    private static $routePermissions = [
        // Auth routes
        '/auth/giris' => 'auth.login',
        '/auth/kayit' => 'auth.register',
        
        // User routes
        '/anasayfa' => 'dashboard.view',
        '/profil' => 'profile.view',
        '/istek/olustur' => 'istek.create',
        '/istekler' => 'istek.list',
        '/isemri/olustur' => 'isemri.create',
        '/isemri/listesi' => 'isemri.list.own',
        '/ciro/ekle' => 'ciro.create',
        '/ciro/listele' => 'ciro.list.own',
        '/fatura_talep/olustur' => 'fatura_talep.create',
        '/fatura_talep/listesi' => 'fatura_talep.list.own',
        
        // Admin routes
        '/admin' => 'admin.dashboard',
        '/admin/kullanicilar' => 'admin.users.list',
        '/admin/kullanici_ekle' => 'admin.users.create',
        '/admin/magazalar' => 'admin.magazalar.list',
        '/admin/magaza/ekle' => 'admin.magazalar.create',
        '/admin/istekler' => 'admin.istekler.list',
        '/admin/personeller' => 'admin.personel.list',
        '/admin/personel/ekle' => 'admin.personel.create',
        '/admin/bildirimler' => 'admin.bildirimler.list',
        '/admin/bildirim_gonder' => 'admin.bildirimler.send',
        
    ];
    
    /**
     * Route bazlı yetki kontrolü
     */
    public static function checkRouteAccess($route) {
        // Exact match ara
        if (isset(self::$routePermissions[$route])) {
            $permission = self::$routePermissions[$route];
            return self::hasPermission($permission);
        }
        
        // Pattern matching (örn: /admin/kullanici/edit/123)
        foreach (self::$routePermissions as $pattern => $permission) {
            if (self::matchRoute($pattern, $route)) {
                return self::hasPermission($permission);
            }
        }
        
        // Admin route kontrolü (genel)
        if (strpos($route, '/admin') === 0) {
            return self::hasPermission('admin.dashboard');
        }
        
        // Default: authenticated user gerekir
        $authManager = AuthManager::getInstance();
        return $authManager->getCurrentUser() !== null;
    }
    
    /**
     * Route pattern matching
     */
    private static function matchRoute($pattern, $route) {
        // {id} gibi parametreleri handle et
        $pattern = preg_replace('/\{[^}]+\}/', '[^/]+', $pattern);
        $pattern = str_replace('/', '\/', $pattern);
        return preg_match('/^' . $pattern . '$/', $route);
    }
    
    /**
     * Kullanıcının rolünü al
     */
    public static function getUserRole() {
        $authManager = AuthManager::getInstance();
        $user = $authManager->getCurrentUser();
        
        if (!$user) {
            return 'guest';
        }
        
        return $user['yonetici'] ? 'admin' : 'user';
    }
    
    /**
     * Belirli bir rol için tüm yetkileri al
     */
    public static function getRolePermissions($role) {
        return self::$permissions[$role] ?? [];
    }
    
    /**
     * Middleware için hızlı yetki kontrolü
     */
    public static function requirePermission($permission, $redirectUrl = '/auth/giris') {
        if (!self::hasPermission($permission)) {
            header('Location: ' . $redirectUrl);
            exit();
        }
    }
    
    /**
     * Admin yetkisi gerektiren işlemler için
     */
    public static function requireAdmin($redirectUrl = '/anasayfa') {
        if (self::getUserRole() !== 'admin') {
            header('Location: ' . $redirectUrl);
            exit();
        }
    }
}