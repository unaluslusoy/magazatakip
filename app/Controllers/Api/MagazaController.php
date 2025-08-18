<?php

namespace app\Controllers\Api;

// use app\Controllers\BaseController;
use app\Models\Magaza;
use app\Middleware\ApiAuthMiddleware;

class MagazaController
{
    public function __construct()
    {
        // API authentication middleware
        ApiAuthMiddleware::handle();
    }

    /**
     * Mağaza listesi (JSON)
     */
    public function index()
    {
        try {
            $magazaModel = new Magaza();
            $magazalar = $magazaModel->getAll();
            
            $this->jsonResponse([
                'success' => true,
                'data' => $magazalar,
                'message' => 'Mağaza listesi başarıyla getirildi'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Mağaza listesi alınırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tek mağaza detayı (JSON)
     */
    public function show($id)
    {
        try {
            $id = intval($id);
            if ($id <= 0) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Geçersiz mağaza ID\'si'
                ], 400);
                return;
            }

            $magazaModel = new Magaza();
            $magaza = $magazaModel->get($id);
            
            if (!$magaza) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Mağaza bulunamadı'
                ], 404);
                return;
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $magaza,
                'message' => 'Mağaza detayları başarıyla getirildi'
            ]);

        } catch (Exception $e) {
            error_log("API MagazaController::show Exception: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Mağaza detayları alınırken hata oluştu: ' . $e->getMessage()
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
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }
}