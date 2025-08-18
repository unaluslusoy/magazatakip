<?php

namespace core;

use app\Models\Kullanici;

// CacheManager'ı manuel olarak require et
if (!class_exists('core\CacheManager')) {
    require_once __DIR__ . '/CacheManager.php';
}
use core\CacheManager;

/**
 * Centralized Authentication Manager
 * Tüm kimlik doğrulama işlemlerini tek yerden yönetir
 * Updated: 2024-08-04 17:25 - Full rewrite to fix cache issues
 */
class AuthManager {
    
    // Session sabitleri (geriye dönük uyumluluk için)
    const SESSION_USER_ID = 'user_id';
    const SESSION_ADMIN = 'is_admin';
    const SESSION_NAME = 'user_name';
    const SESSION_EMAIL = 'user_email';
    const SESSION_ROLE = 'user_role';
    const SESSION_LOGIN_TIME = 'login_time';
    const SESSION_ACTIVITY = 'last_activity';
    
    private static $instance = null;
    private $kullaniciModel;
    private $config;
    private $cache;
    
    private function __construct() {
        // Config yükleme - error handling ile
        $configPath = __DIR__ . '/../config/auth.php';
        if (!file_exists($configPath)) {
            throw new \Exception("Auth config file not found: $configPath");
        }
        
        $this->config = require $configPath;
        if (!is_array($this->config)) {
            throw new \Exception("Invalid auth config format");
        }
        
        // Kullanici model'i lazy loading ile yükle (gerektiğinde)
        $this->kullaniciModel = null;
        
        // Cache manager'ı başlat
        $this->cache = CacheManager::getInstance();
        
        $this->startSession();
    }
    
    /**
     * Singleton pattern - tek instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Kullanici model'ini lazy loading ile al
     */
    private function getKullaniciModel() {
        if ($this->kullaniciModel === null) {
            $this->kullaniciModel = new Kullanici();
        }
        return $this->kullaniciModel;
    }
    
    /**
     * Session başlatma
     */
    private function startSession() {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }
    
    /**
     * Ana authentication kontrolü
     * @return array ['authenticated' => bool, 'user' => array|null, 'redirect' => string|null]
     */
    public function authenticate() {
        $this->updateActivity();
        
        // 1. Session timeout kontrolü
        if ($this->isSessionExpired()) {
            $this->logout();
            return [
                'authenticated' => false,
                'user' => null,
                'redirect' => '/auth/giris'
            ];
        }
        
        // 2. Aktif oturum kontrolü
        if ($this->hasActiveSession()) {
            $user = $this->getCurrentUser();
            return [
                'authenticated' => true,
                'user' => $user,
                'redirect' => null
            ];
        }
        
        // 3. Remember token kontrolü
        if ($this->hasRememberToken()) {
            $autoLoginResult = $this->attemptAutoLogin();
            if ($autoLoginResult['success']) {
                return [
                    'authenticated' => true,
                    'user' => $autoLoginResult['user'],
                    'redirect' => null
                ];
            } else {
                $this->clearRememberToken();
            }
        }
        
        // 4. Kimlik doğrulama gerekli
        return [
            'authenticated' => false,
            'user' => null,
            'redirect' => '/auth/giris'
        ];
    }
    
    /**
     * Email ve şifre ile giriş
     */
    public function login($email, $password, $remember = false) {
        $this->logActivity("Giriş denemesi: $email");

        // Brute-force koruması
        try {
            $sec = $this->config['security'] ?? [];
            $maxAttempts = (int)($sec['max_login_attempts'] ?? 5);
            $lockSeconds = (int)($sec['lockout_duration'] ?? 900);
            if (!class_exists('core\\Request')) { require_once __DIR__ . '/Request.php'; }
            $ip = \core\Request::getClientIp();
            $key = 'login_attempts:' . sha1(strtolower($email) . '|' . $ip);
            $attempt = $this->cache->get($key) ?: ['count'=>0,'first'=>time()];
            $now = time();
            if ($attempt['count'] >= $maxAttempts && ($now - $attempt['first']) < $lockSeconds) {
                return [ 'success' => false, 'message' => 'Çok fazla deneme. Lütfen sonra tekrar deneyin.' ];
            }
        } catch (\Throwable $e) { /* sessiz geç */ }
        
        $user = $this->getKullaniciModel()->getByEmail($email);
        
        if (!$user) {
            $this->logActivity("Kullanıcı bulunamadı: $email");
            $this->recordLoginAttempt($email, isset($attempt) ? $attempt : null);
            return [
                'success' => false,
                'message' => 'Hatalı email veya şifre.'
            ];
        }
        
        if (!password_verify($password, $user['sifre'])) {
            $this->logActivity("Hatalı şifre: " . $user['id']);
            $this->recordLoginAttempt($email, isset($attempt) ? $attempt : null);
            return [
                'success' => false,
                'message' => 'Hatalı email veya şifre.'
            ];
        }
        
        // Başarılı giriş
        $this->createSession($user);
        // Başarılı girişte denemeleri temizle
        try {
            if (!class_exists('core\\Request')) { require_once __DIR__ . '/Request.php'; }
            $rip = \core\Request::getClientIp();
            $this->cache->delete('login_attempts:' . sha1(strtolower($email) . '|' . $rip));
        } catch (\Throwable $e) {}
        
        // "Beni Hatırla" mantığı
        if ($remember) {
            // Beni hatırla seçili: 1 yıl token oluştur
            $this->setRememberToken($user['id']);
            $this->logActivity("Uzun süreli giriş (1 yıl): " . $user['id']);
        } else {
            // Beni hatırla seçili değil: hiç token oluşturma, sadece session
            $this->clearRememberToken(); // Mevcut token'ları temizle
            $this->logActivity("Normal session giriş (30 dakika): " . $user['id']);
        }
        
        $this->logActivity("Başarılı giriş: " . $user['id']);
        
        return [
            'success' => true,
            'user' => $user,
            'redirect' => $this->getRedirectUrl($user)
        ];
    }

    private function recordLoginAttempt($email, $attempt)
    {
        try {
            $sec = $this->config['security'] ?? [];
            $lockSeconds = (int)($sec['lockout_duration'] ?? 900);
            if (!class_exists('core\\Request')) { require_once __DIR__ . '/Request.php'; }
            $ip = \core\Request::getClientIp();
            $key = 'login_attempts:' . sha1(strtolower($email) . '|' . $ip);
            $now = time();
            $data = $attempt ?: ['count'=>0,'first'=>$now];
            if (($now - $data['first']) >= $lockSeconds) { $data = ['count'=>0,'first'=>$now]; }
            $data['count'] = ($data['count'] ?? 0) + 1;
            $this->cache->set($key, $data, $lockSeconds);
        } catch (\Throwable $e) { /* sessiz */ }
    }
    
    /**
     * Güçlendirilmiş çıkış işlemi (Tam temizlik)
     */
    public function logout() {
        $userIdKey = $this->config['session']['user_id_key'];
        $userId = $_SESSION[$userIdKey] ?? null;
        
        $this->logActivity("Çıkış yapıldı: " . ($userId ?? 'bilinmeyen'));
        
        // Cache'den kullanıcı verilerini temizle
        if ($userId) {
            $this->cache->clearUserCache($userId);
        }
        
        // Veritabanından remember token'ları temizle
        if ($userId) {
            $this->getKullaniciModel()->setToken($userId, null);
        }
        
        // Tüm session verilerini temizle
        $_SESSION = array();
        
        // Session cookie'sini temizle
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }
        
        // Remember token cookie'sini temizle
        $this->clearRememberToken();
        
        // Diğer auth cookie'lerini temizle
        $authCookies = ['remember_me', 'auth_token', 'user_session'];
        foreach ($authCookies as $cookieName) {
            if (isset($_COOKIE[$cookieName])) {
                setcookie($cookieName, '', time() - 3600, '/');
                setcookie($cookieName, '', time() - 3600, '/', $_SERVER['HTTP_HOST'] ?? '');
                unset($_COOKIE[$cookieName]);
            }
        }
        
        // Session'ı tamamen yok et
        session_destroy();
        
        // Yeni temiz session başlat
        session_start();
        session_regenerate_id(true);
        
        $this->logActivity("Logout tamamlandı - session ve cookie'ler temizlendi");
    }
    
    /**
     * Session timeout kontrolü
     */
    private function isSessionExpired() {
        $activityKey = $this->config['session']['activity_key'];
        if (!isset($_SESSION[$activityKey])) {
            return false;
        }
        
        $timeSinceActivity = time() - $_SESSION[$activityKey];
        return $timeSinceActivity > $this->config['session']['timeout'];
    }
    
    /**
     * Aktif session var mı kontrolü
     */
    private function hasActiveSession() {
        $userIdKey = $this->config['session']['user_id_key'];
        return isset($_SESSION[$userIdKey]) && !empty($_SESSION[$userIdKey]);
    }
    
    /**
     * Remember token var mı kontrolü
     */
    private function hasRememberToken() {
        $cookieName = $this->config['remember']['cookie_name'];
        return isset($_COOKIE[$cookieName]) && !empty($_COOKIE[$cookieName]);
    }
    
    /**
     * Otomatik giriş denemesi
     */
    private function attemptAutoLogin() {
        $cookieName = $this->config['remember']['cookie_name'];
        $token = $_COOKIE[$cookieName];
        $user = $this->getKullaniciModel()->getByToken($token);
        
        if ($user) {
            $this->createSession($user);
            $this->logActivity("Otomatik giriş başarılı: " . $user['id']);
            
            return [
                'success' => true,
                'user' => $user
            ];
        }
        
        return ['success' => false];
    }
    
    /**
     * Session oluşturma
     */
    private function createSession($user) {
        $session = $this->config['session'];
        $_SESSION[$session['user_id_key']] = $user['id'];
        $_SESSION[$session['admin_key']] = (bool)$user['yonetici'];
        $_SESSION[$session['name_key']] = $user['ad'];
        $_SESSION[$session['email_key']] = $user['email'];
        $_SESSION[$session['role_key']] = $user['yonetici'] ? 'admin' : 'user';
        $_SESSION[$session['login_time_key']] = time();
        $_SESSION[$session['activity_key']] = time();
    }
    
    /**
     * Aktivite güncelleme
     */
    public function updateActivity() {
        $_SESSION[$this->config['session']['activity_key']] = time();
    }
    
    /**
     * Remember token ayarlama (sadece "Beni Hatırla" seçili olduğunda)
     */
    private function setRememberToken($userId) {
        $remember = $this->config['remember'];
        $token = bin2hex(random_bytes($remember['token_length']));
        
        // Token'ı veritabanına kaydet
        $this->getKullaniciModel()->setToken($userId, $token);
        
        // 1 yıl süre (sadece "Beni Hatırla" seçildiğinde çağrılır)
        $duration = $remember['long_duration'];
        $this->logActivity("Uzun süreli token ayarlandı: $userId (1 yıl)");
        
        setcookie(
            $remember['cookie_name'],
            $token,
            time() + $duration,
            "/",
            "",
            $remember['secure'],
            $remember['httponly']
        );
    }
    
    /**
     * Remember token temizleme
     */
    private function clearRememberToken() {
        $cookieName = $this->config['remember']['cookie_name'];
        if (isset($_COOKIE[$cookieName])) {
            setcookie($cookieName, '', time() - 3600, "/");
            unset($_COOKIE[$cookieName]);
        }
    }
    
    /**
     * Mevcut kullanıcı bilgisini alma (Cache ile optimized)
     */
    public function getCurrentUser() {
        if (!$this->hasActiveSession()) {
            return null;
        }
        
        $session = $this->config['session'];
        $userId = $_SESSION[$session['user_id_key']];
        
        // Cache'den kullanıcı verilerini kontrol et
        $cachedUser = $this->cache->getCachedUserData($userId);
        if ($cachedUser !== null) {
            return $cachedUser;
        }
        
        // Cache'de yoksa session'dan al ve cache'le
        $userData = [
            'id' => $userId,
            'ad' => $_SESSION[$session['name_key']] ?? '',
            'email' => $_SESSION[$session['email_key']] ?? '',
            'yonetici' => $_SESSION[$session['admin_key']] ?? false,
            'role' => $_SESSION[$session['role_key']] ?? 'user'
        ];
        
        // Cache'e kaydet (5 dakika)
        $this->cache->cacheUserData($userId, $userData, 300);
        
        return $userData;
    }
    
    /**
     * Admin kontrolü
     */
    public function isAdmin() {
        return $this->hasActiveSession() && ($_SESSION[$this->config['session']['admin_key']] ?? false);
    }
    
    /**
     * Kullanıcı kontrolü
     */
    public function isUser() {
        return $this->hasActiveSession() && !$this->isAdmin();
    }
    
    /**
     * Yönlendirme URL'i belirleme
     */
    private function getRedirectUrl($user) {
        return $user['yonetici'] ? '/admin' : '/anasayfa';
    }
    
    /**
     * Route erişim kontrolü
     */
    public function checkRouteAccess($route) {
        // RBAC ile merkezi kontrol
        if (!class_exists('app\\Controllers\\Auth\\RBAC')) {
            require_once __DIR__ . '/../app/Controllers/Auth/RBAC.php';
        }

        // Public rotalar
        $publicRoutes = ['/auth/giris', '/auth/kayit', '/'];
        if (in_array($route, $publicRoutes, true)) {
            return ['allowed' => true];
        }

        // Oturum gerekli
        if (!$this->hasActiveSession()) {
            return [
                'allowed' => false,
                'redirect' => '/auth/giris',
                'message' => 'Bu sayfaya erişmek için giriş yapmalısınız.'
            ];
        }

        // RBAC route bazlı izin kontrolü
        $allowedByRole = \app\Controllers\Auth\RBAC::checkRouteAccess($route);
        if (!$allowedByRole) {
            // Admin değilse kullanıcı dashboard'a; admin değilse anasayfa
            $redirect = $this->isAdmin() ? '/admin' : '/anasayfa';
            return [
                'allowed' => false,
                'redirect' => $redirect,
                'message' => 'Bu sayfaya erişim yetkiniz yok.'
            ];
        }

        return ['allowed' => true];
    }
    
    /**
     * Session durumu bilgisi (API için)
     */
    public function getSessionInfo() {
        $session = $this->config['session'];
        return [
            'authenticated' => $this->hasActiveSession(),
            'user' => $this->getCurrentUser(),
            'is_admin' => $this->isAdmin(),
            'last_activity' => $_SESSION[$session['activity_key']] ?? null,
            'login_time' => $_SESSION[$session['login_time_key']] ?? null,
            'session_timeout' => $session['timeout'],
            'time_remaining' => $this->hasActiveSession() ? 
                $session['timeout'] - (time() - $_SESSION[$session['activity_key']]) : 0
        ];
    }
    
    /**
     * Aktivite loglama
     */
    private function logActivity($message) {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        error_log("[$timestamp] [IP: $ip] $message - UserAgent: " . substr($userAgent, 0, 100));
    }
}
?>