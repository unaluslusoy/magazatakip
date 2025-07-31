<?php
require_once 'vendor/autoload.php';
require_once 'core/Router.php';
require_once 'core/Controller.php';
require_once 'core/Database.php';
require_once 'app/helpers/url_helper.php';
require_once 'error_handler.php';
require_once 'config/database.php';


use core\Router;
use app\Models\Kullanici;

use Dotenv\Dotenv;

// Dotenv'i yükle
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();


// Hata raporlama ayarları
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Hata günlüğü ayarları
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

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




// Oturum süresi kontrolü
$oturum_suresi = 1800; // 30 dakika
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $oturum_suresi)) {
    // Son aktivite zamanı belirlenen süreden uzunsa oturumu sonlandır
    session_unset();
    session_destroy();
    header('Location: /auth/giris');
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // Son aktivite zamanını güncelle

// Kullanıcı oturumunu kontrol et
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    $kullaniciModel = new Kullanici();
    $user = $kullaniciModel->getByToken($_COOKIE['remember_me']);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['is_admin'] = $user['yonetici'];
        $_SESSION['user_name'] = $user['ad'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['yonetici'];
        $_SESSION['last_activity'] = time();
    }
}

// Eğer kullanıcı oturum açmışsa ve giriş sayfasına veya ana sayfaya gidiyorsa, panele yönlendir
$currentUri = trim($_SERVER['REQUEST_URI'], '/');
if (isset($_SESSION['user_id']) && ($currentUri === '' || $currentUri === 'auth/giris')) {
    if ($_SESSION['is_admin']) {
        header('Location: /admin');
    } else {
        header('Location: /anasayfa');
    }
    exit();
}

$router = new Router();
require_once 'routes/web.php';
require_once 'routes/admin.php';
require_once 'routes/todo.php';
require_once 'routes/modul.php';

$router->dispatch($_SERVER['REQUEST_URI']);
