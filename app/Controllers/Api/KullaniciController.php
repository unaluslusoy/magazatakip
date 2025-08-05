<?php

namespace app\Controllers\Api;

// use app\Controllers\BaseController;
use app\Models\Kullanici;
use app\Models\Magaza;
use app\Middleware\ApiAuthMiddleware;

class KullaniciController
{
    public function __construct()
    {
        // API authentication middleware
        ApiAuthMiddleware::handle();
    }

    /**
     * Kullanıcı listesi (JSON)
     */
    public function index()
    {
        try {
            $kullaniciModel = new Kullanici();
            $kullanicilar = $kullaniciModel->getAll();
            
            $this->jsonResponse([
                'success' => true,
                'data' => $kullanicilar,
                'message' => 'Kullanıcı listesi başarıyla getirildi'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Kullanıcı listesi alınırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tek kullanıcı detayı (JSON)
     */
    public function show($id)
    {
        try {
            // ID validasyonu
            $id = intval($id);
            if ($id <= 0) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Geçersiz kullanıcı ID\'si'
                ], 400);
                return;
            }

            $kullaniciModel = new Kullanici();
            $kullanici = $kullaniciModel->get($id);
            
            if (!$kullanici) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Kullanıcı bulunamadı'
                ], 404);
                return;
            }

            // Mağaza listesini de ekle (edit form için)
            $magazaModel = new Magaza();
            $magazalar = $magazaModel->getAll();

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'kullanici' => $kullanici,
                    'magazalar' => $magazalar ?: []
                ],
                'message' => 'Kullanıcı detayları başarıyla getirildi'
            ]);

        } catch (Exception $e) {
            error_log("API KullaniciController::show Exception: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Kullanıcı detayları alınırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kullanıcı oluşturma (JSON)
     */
    public function create()
    {
        try {
            // JSON verisini al
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Geçersiz JSON verisi'
                ], 400);
                return;
            }

            // Veri validasyonu
            if (empty($input['ad']) || empty($input['email']) || empty($input['password'])) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Ad, email ve şifre alanları zorunludur'
                ], 400);
                return;
            }

            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Geçersiz email adresi'
                ], 400);
                return;
            }

            if (strlen($input['password']) < 8) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Şifre en az 8 karakter olmalıdır'
                ], 400);
                return;
            }

            $kullaniciModel = new Kullanici();
            
            // Email'in zaten kayıtlı olup olmadığını kontrol et
            $existingUser = $kullaniciModel->getByEmail($input['email']);
            if ($existingUser) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Bu email adresi zaten kayıtlı'
                ], 400);
                return;
            }

            $data = [
                'ad' => htmlspecialchars($input['ad'], ENT_QUOTES, 'UTF-8'),
                'email' => filter_var($input['email'], FILTER_SANITIZE_EMAIL),
                'sifre' => password_hash($input['password'], PASSWORD_BCRYPT),
                'magaza_id' => !empty($input['magaza_id']) ? intval($input['magaza_id']) : null,
                'yonetici' => isset($input['yonetici']) ? 1 : 0
            ];

            $result = $kullaniciModel->create($data);

            if ($result) {
                // Personel ataması varsa güncelle
                if (!empty($input['personel_id'])) {
                    // Yeni oluşturulan kullanıcıyı email ile bul
                    $newUser = $kullaniciModel->getByEmail($input['email']);
                    if ($newUser) {
                        $personelModel = new \app\Models\Personel();
                        $personelModel->update($input['personel_id'], ['kullanici_id' => $newUser['id']]);
                    }
                }
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Kullanıcı başarıyla oluşturuldu',
                    'data' => $data
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Kullanıcı oluşturulurken hata oluştu'
                ], 500);
            }

        } catch (Exception $e) {
            error_log("API KullaniciController::create Exception: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Kullanıcı oluşturulurken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kullanıcı güncelleme (JSON)
     */
    public function update($id)
    {
        try {
            // ID validasyonu
            $id = intval($id);
            if ($id <= 0) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Geçersiz kullanıcı ID\'si'
                ], 400);
                return;
            }

            // JSON verisini al
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Geçersiz JSON verisi'
                ], 400);
                return;
            }

            // Veri validasyonu
            if (empty($input['ad']) || empty($input['email'])) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Ad ve email alanları zorunludur'
                ], 400);
                return;
            }

            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Geçersiz email adresi'
                ], 400);
                return;
            }

            $kullaniciModel = new Kullanici();
            
            // Kullanıcı mevcut mu kontrol et
            $existingUser = $kullaniciModel->get($id);
            if (!$existingUser) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Kullanıcı bulunamadı'
                ], 404);
                return;
            }

            $data = [
                'ad' => htmlspecialchars($input['ad'], ENT_QUOTES, 'UTF-8'),
                'email' => filter_var($input['email'], FILTER_SANITIZE_EMAIL),
                'magaza_id' => !empty($input['magaza_id']) ? intval($input['magaza_id']) : null,
                'yonetici' => isset($input['yonetici']) ? 1 : 0
            ];

            // Şifre değişikliği varsa
            if (!empty($input['password'])) {
                if (strlen($input['password']) < 8) {
                    $this->jsonResponse([
                        'success' => false,
                        'message' => 'Şifre en az 8 karakter olmalıdır'
                    ], 400);
                    return;
                }
                $data['sifre'] = password_hash($input['password'], PASSWORD_BCRYPT);
            }

            $result = $kullaniciModel->update($id, $data);

            if ($result) {
                // Personel ataması işlemleri
                if (isset($input['personel_id'])) {
                    $personelModel = new \app\Models\Personel();
                    
                    // Önceki personel atamalarını temizle (bu kullanıcının)
                    $personelModel->clearKullaniciAtamalari($id);
                    
                    // Yeni personel ataması varsa yap
                    if (!empty($input['personel_id'])) {
                        $personelModel->update($input['personel_id'], ['kullanici_id' => $id]);
                    }
                }
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Kullanıcı başarıyla güncellendi',
                    'data' => $kullaniciModel->get($id)
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Kullanıcı güncellenirken hata oluştu'
                ], 500);
            }

        } catch (Exception $e) {
            error_log("API KullaniciController::update Exception: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Kullanıcı güncellenirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * JSON response helper
     */
    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }
}