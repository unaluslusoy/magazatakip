<?php
namespace app\Controllers\Kullanici;

use app\Models\Kullanici;

class ProfilController {
    public function index() {
        $kullanici = (new Kullanici())->getKullaniciDetay();
        require_once 'app/Views/kullanici/profil.php';
    }

    public function guncelle() {
        $kullanici = (new Kullanici())->getKullaniciDetay();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Güncelleme işlemleri
        }
        
        require_once 'app/Views/kullanici/profil_guncelle.php';
    }
} 