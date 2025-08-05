<?php

namespace app\Controllers\Api;

use app\Models\Personel;
use app\Middleware\ApiAuthMiddleware;

class PersonelController
{
    public function __construct()
    {
        // API authentication middleware
        ApiAuthMiddleware::handle();
    }

    /**
     * Personel listesi (JSON)
     */
    public function index()
    {
        try {
            $personelModel = new Personel();
            $personeller = $personelModel->getAll();
            
            $this->jsonResponse([
                'success' => true,
                'data' => $personeller,
                'message' => 'Personel listesi başarıyla getirildi'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Personel listesi alınırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tek personel detayı (JSON)
     */
    public function show($id)
    {
        try {
            $id = intval($id);
            if ($id <= 0) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Geçersiz personel ID\'si'
                ], 400);
                return;
            }

            $personelModel = new Personel();
            $personel = $personelModel->get($id);
            
            if (!$personel) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Personel bulunamadı'
                ], 404);
                return;
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $personel,
                'message' => 'Personel detayları başarıyla getirildi'
            ]);

        } catch (Exception $e) {
            error_log("API PersonelController::show Exception: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Personel detayları alınırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Personele kullanıcı ataması güncelle
     */
    public function updateKullaniciAtama($personelId, $kullaniciId)
    {
        try {
            $personelId = intval($personelId);
            $kullaniciId = $kullaniciId ? intval($kullaniciId) : null;
            
            if ($personelId <= 0) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Geçersiz personel ID\'si'
                ], 400);
                return;
            }

            $personelModel = new Personel();
            $result = $personelModel->update($personelId, ['kullanici_id' => $kullaniciId]);

            if ($result) {
                $message = $kullaniciId ? 
                    'Personel-kullanıcı ataması başarıyla güncellendi' : 
                    'Personel-kullanıcı ataması başarıyla kaldırıldı';
                    
                $this->jsonResponse([
                    'success' => true,
                    'message' => $message
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Personel ataması güncellenirken hata oluştu'
                ], 500);
            }

        } catch (Exception $e) {
            error_log("API PersonelController::updateKullaniciAtama Exception: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Personel ataması güncellenirken hata oluştu: ' . $e->getMessage()
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