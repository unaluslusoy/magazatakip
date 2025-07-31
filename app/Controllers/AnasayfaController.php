<?php

namespace app\Controllers;

use app\Models\Kullanici;
use core\Controller;
use app\Models\Kullanici\IsEmri\IsEmriModel;

class AnasayfaController extends Controller {
    public function index() {
        // Session ve authentication kontrolü
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            // Session yok, login sayfasına yönlendir
            header('Location: /auth/giris');
            exit();
        }

        $kullanici_id = $_SESSION['user_id'];
        $kullaniciModel = new Kullanici();
        $kullanici = $kullaniciModel->get($kullanici_id);

        // Kullanici bulunamadı veya geçersiz
        if (!$kullanici || empty($kullanici)) {
            // Session temizle ve login'e yönlendir  
            session_destroy();
            header('Location: /auth/giris');
            exit();
        }

        // Magaza ID kontrolü
        if (!isset($kullanici['magaza_id']) || empty($kullanici['magaza_id'])) {
            // Magaza bilgisi eksik
            session_destroy();
            header('Location: /auth/giris');
            exit();
        }

        $magaza_id = $kullanici['magaza_id'];

        $gorevModel = new IsEmriModel();

        $acikGorevler = $gorevModel->getCountByStatus('Yeni', $magaza_id);
        $kapaliGorevler = $gorevModel->getCountByStatus('Tamamlandı', $magaza_id);
        $devamEdenGorevler = $gorevModel->getCountByStatus('Devam Ediyor', $magaza_id);

        $istek = [
            'acikGorevler' => $acikGorevler,
            'kapaliGorevler' => $kapaliGorevler,
            'devamEdenGorevler' => $devamEdenGorevler
        ];

        $this->view('kullanici/anasayfa', ['kullanici' => $kullanici, 'istek' => $istek]);
    }
}
