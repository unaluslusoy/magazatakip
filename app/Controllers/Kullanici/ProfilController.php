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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /kullanici/profil');
            exit();
        }

        try {
            $kullaniciModel = new Kullanici();
            $personelModel = new Personel();
            
            $userId = $_SESSION['user_id'];
            
            // Kullanıcı bilgilerini güncelle
            $kullaniciData = [
                'ad' => $_POST['ad'] ?? '',
                'soyad' => $_POST['soyad'] ?? '',
                'email' => $_POST['email'] ?? '',
                'telefon' => $_POST['telefon'] ?? ''
            ];
            
            $result = $kullaniciModel->update($userId, $kullaniciData);
            
            // Personel bilgilerini güncelle (varsa)
            $personel = $personelModel->getByKullaniciId($userId);
            if ($personel) {
                $personelData = [
                    'cep_telefonu' => $_POST['cep_telefonu'] ?? '',
                    'dogum_tarihi' => $_POST['dogum_tarihi'] ?? '',
                    'tc_kimlik_no' => $_POST['tc_kimlik_no'] ?? '',
                    'cinsiyet' => $_POST['cinsiyet'] ?? '',
                    'ev_adresi' => $_POST['ev_adresi'] ?? ''
                ];
                
                $personelResult = $personelModel->update($personel['id'], $personelData);
            }
            
            if ($result) {
                $_SESSION['message'] = 'Profil bilgileri başarıyla güncellendi.';
                $_SESSION['message_type'] = 'success';
                
                // Session'daki kullanıcı bilgilerini güncelle
                $_SESSION['user_name'] = $kullaniciData['ad'];
                $_SESSION['user_surname'] = $kullaniciData['soyad'];
                $_SESSION['user_email'] = $kullaniciData['email'];
            } else {
                $_SESSION['message'] = 'Profil güncellenirken bir hata oluştu.';
                $_SESSION['message_type'] = 'error';
            }
            
        } catch (Exception $e) {
            error_log("Profil güncelleme hatası: " . $e->getMessage());
            $_SESSION['message'] = 'Profil güncellenirken bir hata oluştu.';
            $_SESSION['message_type'] = 'error';
        }
        
        header('Location: /kullanici/profil');
        exit();
    }

    public function sifreDegistir() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /kullanici/profil');
            exit();
        }

        try {
            $kullaniciModel = new Kullanici();
            $userId = $_SESSION['user_id'];
            
            $mevcutSifre = $_POST['mevcut_sifre'] ?? '';
            $yeniSifre = $_POST['yeni_sifre'] ?? '';
            $yeniSifreTekrar = $_POST['yeni_sifre_tekrar'] ?? '';
            
            // Validasyon
            if (empty($mevcutSifre) || empty($yeniSifre) || empty($yeniSifreTekrar)) {
                $_SESSION['message'] = 'Tüm alanları doldurunuz.';
                $_SESSION['message_type'] = 'error';
                header('Location: /kullanici/profil');
                exit();
            }
            
            if ($yeniSifre !== $yeniSifreTekrar) {
                $_SESSION['message'] = 'Yeni şifreler eşleşmiyor.';
                $_SESSION['message_type'] = 'error';
                header('Location: /kullanici/profil');
                exit();
            }
            
            if (strlen($yeniSifre) < 6) {
                $_SESSION['message'] = 'Şifre en az 6 karakter olmalıdır.';
                $_SESSION['message_type'] = 'error';
                header('Location: /kullanici/profil');
                exit();
            }
            
            // Mevcut kullanıcı bilgilerini al
            $kullanici = $kullaniciModel->get($userId);
            if (!$kullanici) {
                $_SESSION['message'] = 'Kullanıcı bulunamadı.';
                $_SESSION['message_type'] = 'error';
                header('Location: /kullanici/profil');
                exit();
            }
            
            // Mevcut şifreyi kontrol et
            if (!password_verify($mevcutSifre, $kullanici['sifre'])) {
                $_SESSION['message'] = 'Mevcut şifre yanlış.';
                $_SESSION['message_type'] = 'error';
                header('Location: /kullanici/profil');
                exit();
            }
            
            // Yeni şifreyi hash'le ve güncelle
            $yeniSifreHash = password_hash($yeniSifre, PASSWORD_BCRYPT);
            $result = $kullaniciModel->update($userId, ['sifre' => $yeniSifreHash]);
            
            if ($result) {
                $_SESSION['message'] = 'Şifreniz başarıyla değiştirildi.';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Şifre değiştirilirken bir hata oluştu.';
                $_SESSION['message_type'] = 'error';
            }
            
        } catch (Exception $e) {
            error_log("Şifre değiştirme hatası: " . $e->getMessage());
            $_SESSION['message'] = 'Şifre değiştirilirken bir hata oluştu.';
            $_SESSION['message_type'] = 'error';
        }
        
        header('Location: /kullanici/profil');
        exit();
    }
} 