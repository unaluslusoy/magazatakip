<?php
require_once 'vendor/autoload.php';
require_once 'app/helpers/url_helper.php';
require_once 'config/database.php';


use core\Router;
use app\Models\Kullanici;


// Production ayarları
// DEBUG_MODE'u ortam değişkeninden oku (APP_DEBUG=1 ise açık)
define('DEBUG_MODE', getenv('APP_DEBUG') === '1');
// Varlık sürümü (asset cache busting) - VERSION dosyasından al
define('ASSET_VER', (function() {
    $vFile = __DIR__ . '/VERSION';
    if (is_file($vFile)) {
        $v = trim((string)@file_get_contents($vFile));
        if ($v !== '') { return $v; }
    }
    return '1';
})());
// PHP hata görüntülemeyi kapat, loglamayı aç (JSON çıktılarının bozulmaması için)
ini_set('display_errors', DEBUG_MODE ? '1' : '0');
ini_set('display_startup_errors', DEBUG_MODE ? '1' : '0');
error_reporting(DEBUG_MODE ? E_ALL : (E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT));
ini_set('log_errors', '1');
// Not: Özel error_log yolu istenirse aşağıdaki satır açılabilir ve var olan bir klasöre işaret etmeli
// ini_set('error_log', __DIR__ . '/storage/logs/php-error.log');

// Ortam değişkenlerini yükle (.env varsa)
if (class_exists('Dotenv\\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    try {
        $dotenv->load();
    } catch (Exception $e) {
        // .env olmayabilir; sessiz geç
    }
}

// Oturum ayarları
// Güvenli cookie bayrakları
if (PHP_SAPI !== 'cli') {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? '1' : '0');
    // PHP >=7.3: samesite doğrudan ini_set ile desteklenir
    @ini_set('session.cookie_samesite', 'Lax');
}

// Session handler: Redis'e taşı (config/performance.php ve redis uzantısı uygunsa)
try {
    $perfCfg = require __DIR__ . '/config/performance.php';
    $useRedisForSession = ($perfCfg['session']['use_redis'] ?? false) && extension_loaded('redis');
    if ($useRedisForSession) {
        $redisCfg = $perfCfg['redis'] ?? [];
        $host = $redisCfg['host'] ?? '127.0.0.1';
        $port = (int)($redisCfg['port'] ?? 6379);
        $timeout = (float)($redisCfg['timeout'] ?? 2.0);
        $db = (int)($redisCfg['database'] ?? 1);
        $prefix = ($redisCfg['prefix'] ?? 'magazatakip:') . 'session:';
        $params = http_build_query([
            'database' => $db,
            'timeout' => $timeout,
            'prefix' => $prefix
        ], '', '&', PHP_QUERY_RFC3986);
        ini_set('session.save_handler', 'redis');
        ini_set('session.save_path', 'tcp://' . $host . ':' . $port . '?' . $params);
        // GC ayarlarını uygula
        if (isset($perfCfg['session']['gc_probability'])) { ini_set('session.gc_probability', (string)$perfCfg['session']['gc_probability']); }
        if (isset($perfCfg['session']['gc_divisor'])) { ini_set('session.gc_divisor', (string)$perfCfg['session']['gc_divisor']); }
        if (isset($perfCfg['session']['gc_maxlifetime'])) { ini_set('session.gc_maxlifetime', (string)$perfCfg['session']['gc_maxlifetime']); }
    }
} catch (Throwable $e) { /* sessiz geç */ }
session_start();

// Global güvenlik başlıkları (olası uyumluluklar gözetilerek)
if (!headers_sent()) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), camera=(), microphone=()');
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
    // CSP: mevcut harici bağımlılıklar: OneSignal SDK, Google Fonts/Gstatic, amCharts CDN
    $csp = [];
    $csp[] = "default-src 'self'";
    $csp[] = "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.onesignal.com https://cdn.amcharts.com https://api.onesignal.com";
    $csp[] = "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com";
    $csp[] = "font-src 'self' https://fonts.gstatic.com data:";
    $csp[] = "img-src 'self' https: data:";
    $csp[] = "connect-src 'self' https: https://api.onesignal.com";
    $csp[] = "frame-ancestors 'none'";
    $csp[] = "base-uri 'self'";
    $csp[] = "form-action 'self'";
    header('Content-Security-Policy: ' . implode('; ', $csp));
}


// Composer autoload zaten yüklendi




// AuthManager ile merkezi authentication kontrolü
use core\AuthManager;

$authManager = AuthManager::getInstance();
$currentUri = isset($_SERVER['REQUEST_URI']) ? trim($_SERVER['REQUEST_URI'], '/') : '';

// Kullanıcı giriş yapmışsa ve ana sayfa/giriş sayfasındaysa yönlendir
if ($currentUri === '') {
    // Sadece ana sayfa (/) için remember token kontrolü yap
    $authResult = $authManager->authenticate();
    
    if ($authResult['authenticated']) {
        $user = $authResult['user'];
        $redirectUrl = $user['yonetici'] ? '/admin' : '/anasayfa';
        header('Location: ' . $redirectUrl);
        exit();
    }
} elseif ($currentUri === 'auth/giris') {
    // Giriş sayfasında sadece aktif session kontrolü yap
    if ($authManager->getCurrentUser() !== null) {
        $user = $authManager->getCurrentUser();
        $redirectUrl = $user['yonetici'] ? '/admin' : '/anasayfa';
        header('Location: ' . $redirectUrl);
        exit();
    }
}

// API istekleri için ayrı routing
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'] ?? '/';

if (strpos($path, '/api/') === 0) {
    // API routes
    $apiRouter = require_once 'routes/api.php';
    $apiRouter->dispatch($_SERVER['REQUEST_URI']);
} else {
    // Normal web routes
    $router = new Router();
    
    // Router'ı global olarak tanımla
    global $router;
    $GLOBALS['router'] = $router;
    
    // Route dosyalarını yükle
    require_once 'routes/web.php';
    
    // Diğer route dosyalarını kontrol ederek yükle
    if (file_exists('routes/admin.php')) {
        require_once 'routes/admin.php';
    }
    if (file_exists('routes/todo.php')) {
        require_once 'routes/todo.php';
    }
    if (file_exists('routes/modul.php')) {
        require_once 'routes/modul.php';
    }
    
    $router->dispatch($_SERVER['REQUEST_URI'] ?? '/');
}
