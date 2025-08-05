<?php
require_once 'vendor/autoload.php';
require_once 'core/Router.php';
require_once 'core/Controller.php';
require_once 'core/Database.php';
require_once 'app/helpers/url_helper.php';
require_once 'config/database.php';


use core\Router;
use app\Models\Kullanici;


// Production ayarları

// Oturum ayarları
session_start();


// Autoload fonksiyonu
spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    $file_path = __DIR__ . '/' . $class . '.php';
    
    // Eğer dosya yoksa, eski yol için de kontrol et
    if (!file_exists($file_path)) {
        $legacy_path = __DIR__ . '/app/Models/' . basename($class) . '.php';
        if (file_exists($legacy_path)) {
            $file_path = $legacy_path;
        }
    }
    
    if (file_exists($file_path)) {
        require_once $file_path;
    }
});




// AuthManager ile merkezi authentication kontrolü
use core\AuthManager;

$authManager = AuthManager::getInstance();
$currentUri = trim($_SERVER['REQUEST_URI'], '/');

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
$requestUri = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'];

if (strpos($path, '/api/') === 0) {
    // API routes
    $apiRouter = require_once 'routes/api.php';
    $apiRouter->dispatch($_SERVER['REQUEST_URI']);
} else {
    // Normal web routes
    $router = new Router();
    require_once 'routes/web.php';
    require_once 'routes/admin.php';
    require_once 'routes/todo.php';
    require_once 'routes/modul.php';
    $router->dispatch($_SERVER['REQUEST_URI']);
}
