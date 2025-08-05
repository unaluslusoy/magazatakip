<?php
namespace app\Controllers\Kullanici;

use core\Controller;
use app\Middleware\AuthMiddleware;

class TokenController extends Controller {
    
    public function __construct() {
        // 🔒 GÜVENLIK: Kullanıcı erişim kontrolü
        AuthMiddleware::handle();
    }
    public function kaydet() {
        // Token kaydetme işlemleri
    }

    public function getOneSignalConfig() {
        // OneSignal konfigürasyon bilgilerini döndür
    }
}
