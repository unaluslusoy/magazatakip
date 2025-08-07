<?php

namespace app\Controllers;

use app\Models\Kullanici;
use app\Models\Ciro;
use app\Models\Gider;
use core\Controller;
use app\Models\Kullanici\IsEmri\IsEmriModel;
use app\Middleware\AuthMiddleware;
use Exception;

class AnasayfaController extends Controller {
    
    public function __construct() {
        // 🔒 GÜVENLIK: Kullanıcı erişim kontrolü
        AuthMiddleware::handle();
    }
    
    public function index() {
        // Önbellek önleme header'ları
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        try {
            // Kullanıcı bilgilerini veritabanından al
            $kullaniciModel = new Kullanici();
            $kullanici = $kullaniciModel->get($_SESSION['user_id']);
            
            if (!$kullanici) {
                header('Location: /auth/giris');
                exit();
            }
            
            $magaza_id = $kullanici['magaza_id'];
            
            // Aylık ciro toplamı
            $ciroModel = new Ciro();
            $aylikCiro = $ciroModel->getMonthlyTotal($magaza_id);
            
            // Aylık gider toplamı
            $giderModel = new Gider();
            $aylikGider = $giderModel->getMonthlyTotal($magaza_id);
            
            // Net kar hesaplama
            $netKar = $aylikCiro - $aylikGider;
            
            // İş emirleri istatistikleri - gerçek verileri çek
            $isEmriModel = new IsEmriModel();
            $istek = [
                'acikGorevler' => $isEmriModel->getPendingCount($magaza_id),
                'kapaliGorevler' => $isEmriModel->getCompletedCount($magaza_id),
                'devamEdenGorevler' => $isEmriModel->getInProgressCount($magaza_id)
            ];

            $this->view('kullanici/anasayfa', [
                'kullanici' => $kullanici, 
                'istek' => $istek,
                'aylikCiro' => $aylikCiro,
                'aylikGider' => $aylikGider,
                'netKar' => $netKar
            ]);
            
        } catch (Exception $e) {
            error_log("AnasayfaController hata: " . $e->getMessage());
            echo "Bir hata oluştu: " . $e->getMessage();
        }
    }
}
