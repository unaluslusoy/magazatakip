<?php
namespace app\Controllers\Kullanici\FaturaTalep;

use app\Models\Kullanici;
use core\Controller;
use app\Middleware\AuthMiddleware;

class FaturaTalepController extends Controller {
    
    public function __construct() {
        // 🔒 GÜVENLIK: Kullanıcı erişim kontrolü
        AuthMiddleware::handle();
    }
    public function olustur() {
        // Fatura talep oluşturma işlemleri
    }

    public function listesi() {
        // Fatura talep listesi
    }

    public function duzenle($id) {
        // Fatura talep düzenleme
    }

    public function sil($id) {
        // Fatura talep silme
    }
}
