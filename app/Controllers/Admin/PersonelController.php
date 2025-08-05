<?php

namespace app\Controllers\Admin;

use core\Controller;
use app\Models\Personel;

use app\Middleware\AdminMiddleware;

class PersonelController extends Controller {
    
    public function __construct() {
        // 🔒 GÜVENLIK: Admin erişim kontrolü
        AdminMiddleware::handle();
    }

    public function index() {
        $personelModel = new Personel();
        $personeller = $personelModel->getAll();
        $this->view('admin/personel_listesi', ['personeller' => $personeller]);
    }

    public function liste() {
        $personelModel = new Personel();
        $personeller = $personelModel->getAll();
        $this->view('admin/personel_listesi', ['personeller' => $personeller]);
    }

    /**
     * JSON API için personel listesi
     */
    public function listeJson() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $personelModel = new Personel();
            $personeller = $personelModel->getAll();
            
            echo json_encode([
                'success' => true,
                'data' => $personeller ?: [],
                'message' => 'Personel listesi başarıyla getirildi'
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            error_log("PersonelController::listeJson Exception: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Sunucu hatası: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function detay($id) {

        $personelModel = new Personel();
        $personel = $personelModel->get($id);
        if (!$personel) {
            // Personel bulunamazsa hata mesajı göster
            $this->view('errors/404');
            return;
        }
        $profileCompletion = $this->calculateProfileCompletion($personel);
        $this->view('admin/personel_detay', ['personel' => $personel, 'profileCompletion' => $profileCompletion]);
    }


    private function calculateProfileCompletion($personel) {
        $totalFields = 24; // Toplam alan sayısı
        $filledFields = 0;

        // Dolu olan alanları say
        foreach ($personel as $key => $value) {
            if (!empty($value)) {
                $filledFields++;
            }
        }

        // Doluluk oranını hesapla
        $completionPercentage = ($filledFields / $totalFields) * 100;
        return round($completionPercentage);
    }

    public function ekle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'ad' => $_POST['ad'],
                'soyad' => $_POST['soyad'],
                'eposta' => $_POST['email'],
                'telefon' => $_POST['telefon'],
                'pozisyon' => $_POST['pozisyon']
            ];
            $personelModel = new Personel();
            if ($personelModel->exists($data['eposta'])) {
                $_SESSION['alert_message'] = [
                    'text' => 'Bu e-posta adresi zaten kayıtlı.',
                    'icon' => 'error',
                    'confirmButtonText' => 'Tamam'
                ];
            } else {
                if ($personelModel->PersonelCreate($data)) {
                    $_SESSION['alert_message'] = [
                        'text' => 'Personel başarıyla eklendi.',
                        'icon' => 'success',
                        'confirmButtonText' => 'Tamam'
                    ];
                    header('Location: /admin/personeller');
                    exit();
                } else {
                    $_SESSION['alert_message'] = [
                        'text' => 'Personel eklenirken hata oluştu.',
                        'icon' => 'error',
                        'confirmButtonText' => 'Tamam'
                    ];
                }
            }
        }
        header('Location: /admin/personeller');
    }

    public function guncelle($id) {
        $personelModel = new Personel();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'calisan_no' => $_POST['calisan_no'],
                'ad' => $_POST['ad'],
                'soyad' => $_POST['soyad'],
                'dogum_tarihi' => $_POST['dogum_tarihi'],
                'tc_no' => $_POST['tc_no'],
                'cinsiyet' => $_POST['cinsiyet'],
                'eposta' => $_POST['eposta'],
                'telefon' => $_POST['telefon'],
                'cep_telefon' => $_POST['cep_telefon'],
                'ev_adresi' => $_POST['ev_adresi'],
                'ise_baslama_tarihi' => $_POST['ise_baslama_tarihi'],
                'pozisyon' => $_POST['pozisyon'],
                'departman' => $_POST['departman'],
                'rapor_yoneticisi' => $_POST['rapor_yoneticisi'],
                'calisma_sekli' => $_POST['calisma_sekli'],
                'sozlesme_tarihi' => $_POST['sozlesme_tarihi'],
                'sozlesme_suresi' => $_POST['sozlesme_suresi'],
                'ucret' => $_POST['ucret'],
                'sgk_no' => $_POST['sgk_no'],
                'puantaj_sistemi' => $_POST['puantaj_sistemi'],
                'izin_gunleri' => $_POST['izin_gunleri'],
                'egitim_bilgileri' => $_POST['egitim_bilgileri'],
                'dil_bilgisi' => $_POST['dil_bilgisi'],
                'ozel_yetenekler' => $_POST['ozel_yetenekler'],
                'foto' => $_FILES['foto']['name'],
                'notlar' => $_POST['notlar'],
                'kullanici_adi' => $_POST['kullanici_adi'],
                'sifre' => password_hash($_POST['sifre'], PASSWORD_DEFAULT),
                'guvenlik_sorulari' => $_POST['guvenlik_sorulari'],
            ];
            move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/" . $_FILES['foto']['name']);
            $personelModel->update($id, $data);
            header('Location: /admin/personel');
        } else {
            $personel = $personelModel->get($id);
            $this->view('admin/personel_guncelle', ['personel' => $personel]);
        }
    }

    public function sil($id) {
        $personelModel = new Personel();
        $personelModel->delete($id);
        $_SESSION['alert_message'] = [
            'text' => 'Personel başarıyla silindi.',
            'icon' => 'success',
            'confirmButtonText' => 'Tamam'
        ];
        header('Location: /admin/personeller');
    }

    /**
     * API - Personel verilerini getir (JSON)
     */
    public function apiGet($id) {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $id = intval($id);
            if ($id <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Geçersiz personel ID\'si'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $personelModel = new Personel();
            $personel = $personelModel->get($id);
            
            if (!$personel) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Personel bulunamadı'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            echo json_encode([
                'success' => true,
                'data' => $personel,
                'message' => 'Personel detayları başarıyla getirildi'
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            error_log("PersonelController::apiGet Exception: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Sunucu hatası: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * API - Personel güncelle (JSON)
     */
    public function apiUpdate($id) {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $id = intval($id);
            if ($id <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Geçersiz personel ID\'si'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Geçersiz JSON verisi'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if (empty($input['ad']) || empty($input['eposta'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Ad ve email alanları zorunludur'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if (!filter_var($input['eposta'], FILTER_VALIDATE_EMAIL)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Geçersiz email adresi'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $personelModel = new Personel();
            $existingPersonel = $personelModel->get($id);
            if (!$existingPersonel) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Personel bulunamadı'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $data = [
                'ad' => htmlspecialchars($input['ad'], ENT_QUOTES, 'UTF-8'),
                'soyad' => htmlspecialchars($input['soyad'] ?? '', ENT_QUOTES, 'UTF-8'),
                'eposta' => filter_var($input['eposta'], FILTER_SANITIZE_EMAIL),
                'telefon' => htmlspecialchars($input['telefon'] ?? '', ENT_QUOTES, 'UTF-8'),
                'pozisyon' => htmlspecialchars($input['pozisyon'] ?? '', ENT_QUOTES, 'UTF-8'),
                'ise_baslama_tarihi' => $input['ise_baslama_tarihi'] ?? null
            ];

            $result = $personelModel->update($id, $data);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Personel başarıyla güncellendi'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Personel güncellenirken hata oluştu'
                ], JSON_UNESCAPED_UNICODE);
            }

        } catch (Exception $e) {
            error_log("PersonelController::apiUpdate Exception: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Sunucu hatası: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * API - Personel listesi (JSON)
     */
    public function apiList() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $personelModel = new Personel();
            $personeller = $personelModel->getAll();
            
            echo json_encode([
                'success' => true,
                'data' => $personeller ?: [],
                'message' => 'Personel listesi başarıyla getirildi'
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            error_log("PersonelController::apiList Exception: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Sunucu hatası: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * API - Personel oluştur (JSON)
     */
    public function apiCreate() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Geçersiz JSON verisi'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if (empty($input['ad']) || empty($input['email'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Ad ve email alanları zorunludur'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Geçersiz email adresi'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $personelModel = new Personel();
            
            // Email kontrolü
            $existingPersonel = $personelModel->getByEmail($input['email']);
            if ($existingPersonel) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Bu email adresi zaten kayıtlı'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $data = [
                'ad' => htmlspecialchars($input['ad'], ENT_QUOTES, 'UTF-8'),
                'soyad' => htmlspecialchars($input['soyad'] ?? '', ENT_QUOTES, 'UTF-8'),
                'eposta' => filter_var($input['email'], FILTER_SANITIZE_EMAIL),
                'telefon' => htmlspecialchars($input['telefon'] ?? '', ENT_QUOTES, 'UTF-8'),
                'pozisyon' => htmlspecialchars($input['pozisyon'] ?? '', ENT_QUOTES, 'UTF-8'),
                'ise_baslama_tarihi' => $input['ise_baslama_tarihi'] ?? null
            ];

            $result = $personelModel->create($data);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Personel başarıyla oluşturuldu'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Personel oluşturulurken hata oluştu'
                ], JSON_UNESCAPED_UNICODE);
            }

        } catch (Exception $e) {
            error_log("PersonelController::apiCreate Exception: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Sunucu hatası: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * API - Personel sil (JSON)
     */
    public function apiDelete($id) {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $id = intval($id);
            if ($id <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Geçersiz personel ID\'si'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $personelModel = new Personel();
            $result = $personelModel->delete($id);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Personel başarıyla silindi'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Personel silinirken hata oluştu'
                ], JSON_UNESCAPED_UNICODE);
            }

        } catch (Exception $e) {
            error_log("PersonelController::apiDelete Exception: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Sunucu hatası: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
