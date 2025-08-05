<?php
namespace app\Controllers\Kullanici;

use core\Controller;
use app\Models\Kullanici;
use app\Models\Personel;
use app\Middleware\AuthMiddleware;
use Exception;

class ProfilController extends Controller {
    
    public function __construct() {
        // 🔒 GÜVENLIK: Kullanıcı erişim kontrolü
        AuthMiddleware::handle();
    }
    
    public function index() {
        
        try {
            // Kullanıcı bilgilerini veritabanından al
            $kullaniciModel = new Kullanici();
            $kullanici = $kullaniciModel->get($_SESSION['user_id']);
            
            if (!$kullanici) {
                header('Location: /auth/giris');
                exit();
            }
            
            // Personel bilgilerini kullanıcı ID'si ile çek
            $personelModel = new Personel();
            $personel = $personelModel->getByKullaniciId($_SESSION['user_id']);
            
            $this->view('kullanici/profil', [
                'kullanici' => $kullanici, 
                'personel' => $personel
            ]);
            
        } catch (Exception $e) {
            error_log("ProfilController hata: " . $e->getMessage());
            echo "Bir hata oluştu: " . $e->getMessage();
        }
    }

    public function guncelle() {
        // Kullanıcı oturum kontrolü
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth/giris');
            exit();
        }
        
        $kullaniciModel = new Kullanici();
        $kullanici = $kullaniciModel->get($_SESSION['user_id']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Güncelleme işlemleri burada yapılacak
            $ad = $_POST['ad'] ?? '';
            $email = $_POST['email'] ?? '';
            
            // Güncelleme verilerini hazırla
            $updateData = [
                'ad' => $ad,
                'email' => $email
            ];
            
            // Veritabanını güncelle
            if ($kullaniciModel->update($_SESSION['user_id'], $updateData)) {
                // Session'ı da güncelle
                $_SESSION['user_name'] = $ad;
                $_SESSION['user_email'] = $email;
                
                header('Location: /profil?success=1');
                exit();
            }
        }
        
        $this->view('kullanici/profil_guncelle', ['kullanici' => $kullanici]);
    }
} 