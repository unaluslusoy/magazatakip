<?php
require_once 'vendor/autoload.php';
require_once 'app/helpers/url_helper.php';
require_once 'config/database.php';


use core\Router;
use app\Models\Kullanici;


// Production ayarları
define('DEBUG_MODE', false);

// Oturum ayarları
session_start();


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
