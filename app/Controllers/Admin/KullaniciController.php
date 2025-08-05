<?php

namespace app\Controllers\Admin;

use core\Controller;
use app\Models\Kullanici;
use app\Models\Magaza;
use app\Middleware\AdminMiddleware;

class KullaniciController extends Controller {
    
    public function __construct() {
        // 🔒 GÜVENLIK: Admin erişim kontrolü
        AdminMiddleware::handle();
    }
    public function index() {
        $kullaniciModel = new Kullanici();
        $kullanicilar = $kullaniciModel->getAll();
        $magazaModel = new Magaza();
        $magazalar = $magazaModel->getAll();
        $this->view('admin/kullanicilar', ['kullanicilar' => $kullanicilar,'magazalar' => $magazalar]);
    }

    public function create() {
        $magazaModel = new Magaza();
        $magazalar = $magazaModel->getAll();
        $this->view('admin/kullanici_ekle', ['magazalar' => $magazalar]);
    }

    public function store() {
        $ad = $_POST['ad'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $rol = $_POST['rol'];
        $magaza_id = $_POST['magaza_id']; // Mağaza ID'sini alın

        // Şifreyi bcrypt ile hash'le
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Kullanıcı modelini oluştur ve veritabanına ekle
        $kullaniciModel = new Kullanici();
        $kullaniciModel->create([
            'ad' => $ad,
            'email' => $email,
            'sifre' => $hashed_password,
            'yonetici' => $rol,
            'magaza_id' => $magaza_id // Mağaza ID'sini ekle
        ]);

        // Kullanıcılar sayfasına yönlendir
        header('Location: /admin/kullanicilar');
    }

    public function edit($id) {
        // Debug log
        error_log("KullaniciController::edit çağrıldı ID: " . print_r($id, true));
        
        // ID'yi kontrol et ve temizle
        if (is_array($id)) {
            error_log("HATA: Edit metoduna array ID geldi");
            $_SESSION['error'] = 'Geçersiz kullanıcı ID\'si.';
            header('Location: /admin/kullanicilar');
            exit();
        }
        
        $id = intval($id);
        if ($id <= 0) {
            error_log("HATA: Geçersiz ID: " . $id);
            $_SESSION['error'] = 'Geçersiz kullanıcı ID\'si.';
            header('Location: /admin/kullanicilar');
            exit();
        }
        
        try {
            $kullaniciModel = new Kullanici();
            $kullanici = $kullaniciModel->get($id);
            
            // Kullanıcı bulunamadıysa hata ver
            if (!$kullanici || !is_array($kullanici)) {
                error_log("KULLANICI BULUNAMADI: ID $id");
                $_SESSION['error'] = 'Kullanıcı bulunamadı.';
                header('Location: /admin/kullanicilar');
                exit();
            }
            
            // Mağaza listesini al
            $magazaModel = new Magaza();
            $magazalar = $magazaModel->getAll();
            
            error_log("View'a gönderilen kullanıcı: " . print_r($kullanici, true));
            
            $this->view('admin/kullanici_duzenle', [
                'kullanici' => $kullanici,
                'magazalar' => $magazalar ?: []
            ]);
            
        } catch (Exception $e) {
            error_log("KullaniciController::edit Exception: " . $e->getMessage());
            $_SESSION['error'] = 'Bir hata oluştu: ' . $e->getMessage();
            header('Location: /admin/kullanicilar');
            exit();
        }
    }

    public function update($id)
    {
        // Basit veri doğrulama
        if (empty($_POST['ad']) || empty($_POST['email'])) {
            $_SESSION['error'] = 'Ad ve email alanları zorunludur.';
            header('Location: /admin/kullanicilar/edit/' . $id);
            exit();
        }

        $ad = htmlspecialchars($_POST['ad'], ENT_QUOTES, 'UTF-8');
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $yonetici = isset($_POST['yonetici']) ? 1 : 0;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Geçersiz email adresi.';
            header('Location: /admin/kullanicilar/edit/' . $id);
            exit();
        }

        $magaza_id = !empty($_POST['magaza_id']) ? $_POST['magaza_id'] : null;
        
        $data = [
            'ad' => $ad,
            'email' => $email,
            'magaza_id' => $magaza_id,
            'yonetici' => $yonetici
        ];

        // Şifre değişikliği kontrolü ve işlemi
        if (!empty($_POST['password'])) {
            $password = $_POST['password'];
            if (strlen($password) < 8) {
                $_SESSION['error'] = 'Şifre en az 8 karakter olmalıdır.';
                header('Location: /admin/kullanicilar/edit/' . $id);
                exit();
            }
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $data['sifre'] = $hashed_password;
        }

        try {
            $kullaniciModel = new Kullanici();
            $result = $kullaniciModel->update($id, $data);

            if ($result) {
                $_SESSION['success'] = 'Kullanıcı başarıyla güncellendi.';
            } else {
                $_SESSION['error'] = 'Kullanıcı güncellenirken bir hata oluştu.';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
        }

        header('Location: /admin/kullanicilar');
        exit();
    }

    
    public function delete($id) {
        $kullaniciModel = new Kullanici();
        $kullaniciModel->delete($id);

        header('Location: /admin/kullanicilar');
    }

    /**
     * API - Kullanıcı verilerini getir (JSON)
     */
    public function apiGet($id) {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $id = intval($id);
            if ($id <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Geçersiz kullanıcı ID\'si'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $kullaniciModel = new Kullanici();
            $kullanici = $kullaniciModel->get($id);
            
            if (!$kullanici) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı bulunamadı'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $magazaModel = new Magaza();
            $magazalar = $magazaModel->getAll();

            $personelModel = new \app\Models\Personel();
            $personeller = $personelModel->getAll();

            echo json_encode([
                'success' => true,
                'data' => [
                    'kullanici' => $kullanici,
                    'magazalar' => $magazalar ?: [],
                    'personeller' => $personeller ?: []
                ],
                'message' => 'Kullanıcı detayları başarıyla getirildi'
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            error_log("KullaniciController::apiGet Exception: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Sunucu hatası: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * API - Kullanıcı güncelle (JSON)
     */
    public function apiUpdate($id) {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $id = intval($id);
            if ($id <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Geçersiz kullanıcı ID\'si'
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

            $kullaniciModel = new Kullanici();
            $existingUser = $kullaniciModel->get($id);
            if (!$existingUser) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı bulunamadı'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $data = [
                'ad' => htmlspecialchars($input['ad'], ENT_QUOTES, 'UTF-8'),
                'email' => filter_var($input['email'], FILTER_SANITIZE_EMAIL),
                'magaza_id' => !empty($input['magaza_id']) ? intval($input['magaza_id']) : null,
                'yonetici' => isset($input['yonetici']) && $input['yonetici'] ? 1 : 0
            ];

            if (!empty($input['password'])) {
                if (strlen($input['password']) < 8) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Şifre en az 8 karakter olmalıdır'
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }
                $data['sifre'] = password_hash($input['password'], PASSWORD_BCRYPT);
            }

            $result = $kullaniciModel->update($id, $data);

            if ($result) {
                // Personel ataması işlemleri
                if (isset($input['personel_id'])) {
                    $personelModel = new \app\Models\Personel();
                    
                    // Önceki personel atamalarını temizle
                    $personelModel->clearKullaniciAtamalari($id);
                    
                    // Yeni personel ataması varsa yap
                    if (!empty($input['personel_id'])) {
                        $personelModel->update($input['personel_id'], ['kullanici_id' => $id]);
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Kullanıcı başarıyla güncellendi'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı güncellenirken hata oluştu'
                ], JSON_UNESCAPED_UNICODE);
            }

        } catch (Exception $e) {
            error_log("KullaniciController::apiUpdate Exception: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Sunucu hatası: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * API - Kullanıcı listesi (JSON)
     */
    public function apiList() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $kullaniciModel = new Kullanici();
            $kullanicilar = $kullaniciModel->getAll();
            
            echo json_encode([
                'success' => true,
                'data' => $kullanicilar,
                'message' => 'Kullanıcı listesi başarıyla getirildi'
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            error_log("KullaniciController::apiList Exception: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Sunucu hatası: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * API - Kullanıcı sil (JSON)
     */
    public function apiDelete($id) {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $id = intval($id);
            if ($id <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Geçersiz kullanıcı ID\'si'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $kullaniciModel = new Kullanici();
            $result = $kullaniciModel->delete($id);

            if ($result) {
                // Personel atamalarını da temizle
                $personelModel = new \app\Models\Personel();
                $personelModel->clearKullaniciAtamalari($id);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Kullanıcı başarıyla silindi'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı silinirken hata oluştu'
                ], JSON_UNESCAPED_UNICODE);
            }

        } catch (Exception $e) {
            error_log("KullaniciController::apiDelete Exception: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Sunucu hatası: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * API - Kullanıcı oluştur (JSON)
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

            if (empty($input['ad']) || empty($input['email']) || empty($input['password'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Ad, email ve şifre alanları zorunludur'
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

            if (strlen($input['password']) < 8) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Şifre en az 8 karakter olmalıdır'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $kullaniciModel = new Kullanici();
            
            // Email kontrolü
            $existingUser = $kullaniciModel->getByEmail($input['email']);
            if ($existingUser) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Bu email adresi zaten kayıtlı'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $data = [
                'ad' => htmlspecialchars($input['ad'], ENT_QUOTES, 'UTF-8'),
                'email' => filter_var($input['email'], FILTER_SANITIZE_EMAIL),
                'sifre' => password_hash($input['password'], PASSWORD_BCRYPT),
                'magaza_id' => !empty($input['magaza_id']) ? intval($input['magaza_id']) : null,
                'yonetici' => isset($input['yonetici']) && $input['yonetici'] ? 1 : 0
            ];

            $result = $kullaniciModel->create($data);

            if ($result) {
                // Personel ataması varsa
                if (!empty($input['personel_id'])) {
                    $personelModel = new \app\Models\Personel();
                    $newUser = $kullaniciModel->getByEmail($input['email']);
                    if ($newUser) {
                        $personelModel->update($input['personel_id'], ['kullanici_id' => $newUser['id']]);
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Kullanıcı başarıyla oluşturuldu'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı oluşturulurken hata oluştu'
                ], JSON_UNESCAPED_UNICODE);
            }

        } catch (Exception $e) {
            error_log("KullaniciController::apiCreate Exception: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Sunucu hatası: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
