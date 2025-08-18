<?php
namespace app\Controllers\Api;

use app\Models\Kullanici\IsEmri\IsEmriModel;
use app\Services\ActivityNotifier;
use core\Controller;

class IsEmriApiController extends Controller {
    
    private $isEmriModel;
    
    public function __construct() {
        $this->isEmriModel = new IsEmriModel();
        
        // Response tipi
        header('Content-Type: application/json; charset=utf-8');
        
        // Cache prevention headers
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0, private');
        header('Pragma: no-cache');
        header('Expires: -1');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('ETag: "' . md5(time()) . '"');
        
        // OPTIONS ApiAuthMiddleware tarafından handle edilir
    }
    
    /**
     * Tüm iş emirlerini getir
     */
    public function getIsEmriListesi() {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı girişi gerekli',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $isEmriListesi = $this->isEmriModel->getAll();
            
            $response = [
                'success' => true,
                'data' => $isEmriListesi,
                'count' => count($isEmriListesi),
                'timestamp' => time(),
                'message' => 'İş emri listesi başarıyla getirildi'
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Veri getirme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Tek bir iş emrini getir
     */
    public function getIsEmri($id) {
        try {
            $isEmri = $this->isEmriModel->get($id);
            
            if (!$isEmri) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'İş emri bulunamadı',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $response = [
                'success' => true,
                'data' => $isEmri,
                'timestamp' => time(),
                'message' => 'İş emri başarıyla getirildi'
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Veri getirme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * İş emri oluştur
     */
    public function createIsEmri() {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı girişi gerekli',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Geçersiz JSON verisi',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // Veri doğrulama
            $requiredFields = ['baslik', 'aciklama', 'oncelik'];
            foreach ($requiredFields as $field) {
                if (empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => "Gerekli alan eksik: $field",
                        'timestamp' => time()
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }
            }
            
            $input['kullanici_id'] = $_SESSION['user_id'];
            $input['durum'] = 'bekliyor';
            $input['olusturma_tarihi'] = date('Y-m-d H:i:s');
            
            $result = $this->isEmriModel->create($input);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'İş emri başarıyla oluşturuldu',
                    'timestamp' => time()
                ];

                try {
                    $userId = $_SESSION['user_id'] ?? null;
                    if ($userId) {
                        (new ActivityNotifier())->recordAndNotify((int)$userId, 'create', 'is_emri', null, [
                            'baslik' => $input['baslik'] ?? null,
                            'oncelik' => $input['oncelik'] ?? null
                        ]);
                    }
                } catch (\Throwable $t) { error_log('İşEmri create notify/log error: ' . $t->getMessage()); }
            } else {
                http_response_code(500);
                $response = [
                    'success' => false,
                    'message' => 'İş emri oluşturulurken hata oluştu',
                    'timestamp' => time()
                ];
            }
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Oluşturma hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * İş emrini güncelle
     */
    public function updateIsEmri($id) {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı girişi gerekli',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Geçersiz JSON verisi',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $input['guncelleme_tarihi'] = date('Y-m-d H:i:s');
            
            $result = $this->isEmriModel->update($id, $input);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'İş emri başarıyla güncellendi',
                    'timestamp' => time()
                ];

                try {
                    $userId = $_SESSION['user_id'] ?? null;
                    if ($userId) {
                        (new ActivityNotifier())->recordAndNotify((int)$userId, 'update', 'is_emri', (int)$id, [
                            'degisen_alanlar' => array_keys($input)
                        ]);
                    }
                } catch (\Throwable $t) { error_log('İşEmri update notify/log error: ' . $t->getMessage()); }
            } else {
                http_response_code(500);
                $response = [
                    'success' => false,
                    'message' => 'İş emri güncellenirken hata oluştu',
                    'timestamp' => time()
                ];
            }
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Güncelleme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * İş emrini sil
     */
    public function deleteIsEmri($id) {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı girişi gerekli',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $result = $this->isEmriModel->delete($id);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'İş emri başarıyla silindi',
                    'timestamp' => time()
                ];

                try {
                    $userId = $_SESSION['user_id'] ?? null;
                    if ($userId) {
                        (new ActivityNotifier())->recordAndNotify((int)$userId, 'delete', 'is_emri', (int)$id);
                    }
                } catch (\Throwable $t) { error_log('İşEmri delete notify/log error: ' . $t->getMessage()); }
            } else {
                http_response_code(500);
                $response = [
                    'success' => false,
                    'message' => 'İş emri silinirken hata oluştu',
                    'timestamp' => time()
                ];
            }
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Silme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * İş emri durumunu güncelle
     */
    public function updateIsEmriStatus($id) {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı girişi gerekli',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['durum'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Durum bilgisi gerekli',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $updateData = [
                'durum' => $input['durum'],
                'guncelleme_tarihi' => date('Y-m-d H:i:s')
            ];
            
            if ($input['durum'] === 'tamamlandi') {
                $updateData['tamamlanma_tarihi'] = date('Y-m-d H:i:s');
            }
            
            $result = $this->isEmriModel->update($id, $updateData);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'İş emri durumu başarıyla güncellendi',
                    'timestamp' => time()
                ];
            } else {
                http_response_code(500);
                $response = [
                    'success' => false,
                    'message' => 'Durum güncellenirken hata oluştu',
                    'timestamp' => time()
                ];
            }
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Durum güncelleme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * İş emri istatistiklerini getir
     */
    public function getIsEmriStats() {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Kullanıcı girişi gerekli',
                    'timestamp' => time()
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $stats = [
                'bekleyen' => $this->isEmriModel->getPendingCount(),
                'devam_eden' => $this->isEmriModel->getInProgressCount(),
                'tamamlanan' => $this->isEmriModel->getCompletedCount(),
                'iptal_edilen' => $this->isEmriModel->getCancelledCount(),
                'toplam' => $this->isEmriModel->getTotalCount()
            ];
            
            $response = [
                'success' => true,
                'data' => $stats,
                'timestamp' => time(),
                'message' => 'İş emri istatistikleri başarıyla getirildi'
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'İstatistik getirme hatası: ' . $e->getMessage(),
                'timestamp' => time()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
} 