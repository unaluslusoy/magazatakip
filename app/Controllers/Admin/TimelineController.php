<?php

namespace app\Controllers\Admin;

require_once 'app/Middleware/AdminMiddleware.php';
require_once 'scripts/simple-timeline.php';

use app\Middleware\AdminMiddleware;

class TimelineController {
    private $timeline;
    
    public function __construct() {
        AdminMiddleware::handle();
        $this->timeline = new \SimpleTimeline();
    }
    
    /**
     * Timeline listesini API olarak döndür
     */
    public function apiList() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $timelineData = $this->timeline->getTimeline();
            
            echo json_encode([
                'success' => true,
                'timeline' => $timelineData,
                'count' => count($timelineData)
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            error_log("TimelineController::apiList Exception: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Timeline yüklenirken hata oluştu: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Yeni checkpoint oluştur
     */
    public function apiCreate() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $description = $input['description'] ?? '';
            
            if (empty($description)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Checkpoint açıklaması gerekli'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $checkpointId = $this->timeline->createCheckpoint($description, 'manual');
            
            echo json_encode([
                'success' => true,
                'checkpoint_id' => $checkpointId,
                'message' => 'Checkpoint başarıyla oluşturuldu'
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            error_log("TimelineController::apiCreate Exception: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Checkpoint oluşturulamadı: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Checkpoint'e rollback işaretle
     */
    public function apiRollback() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $checkpointId = $input['checkpoint_id'] ?? '';
            
            if (empty($checkpointId)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Checkpoint ID gerekli'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $result = $this->timeline->markRollback($checkpointId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Rollback işaretlendi - Manuel işlem gerekli'
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            error_log("TimelineController::apiRollback Exception: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Rollback hatası: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Otomatik günlük backup oluştur
     */
    public function apiAutoBackup() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $checkpointId = $this->timeline->createCheckpoint('📅 Günlük otomatik backup - ' . date('Y-m-d'));
            
            echo json_encode([
                'success' => true,
                'checkpoint_id' => $checkpointId,
                'message' => 'Günlük backup oluşturuldu'
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            error_log("TimelineController::apiAutoBackup Exception: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Auto backup hatası: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Timeline sayfa görünümü
     */
    public function index() {
        require_once 'app/Views/admin/timeline.php';
    }
    
    /**
     * Acil rollback sayfası
     */
    public function emergency() {
        // Acil durumlar için basit HTML sayfası
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>🚨 Acil Rollback</title>
            <meta charset="utf-8">
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
                .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
                .alert { padding: 15px; margin: 10px 0; border-radius: 4px; }
                .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
                .btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; }
                .btn-danger { background: #dc3545; color: white; }
                .btn-primary { background: #007bff; color: white; }
                .checkpoint { padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>🚨 Acil Rollback Sistemi</h1>
                <div class="alert alert-danger">
                    <strong>DİKKAT:</strong> Bu sayfa sadece acil durumlar için kullanılmalıdır!
                </div>
                
                <div id="checkpoints">
                    <!-- JavaScript ile doldurulacak -->
                </div>
                
                <script>
                // Son 5 checkpoint'i yükle ve göster
                fetch('/api/timeline/list')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const container = document.getElementById('checkpoints');
                        const checkpoints = data.timeline.slice(0, 5);
                        
                        let html = '<h3>Son 5 Checkpoint:</h3>';
                        
                        checkpoints.forEach((cp, index) => {
                            html += `
                                <div class="checkpoint">
                                    <strong>${cp.description}</strong><br>
                                    <small>📅 ${cp.timestamp} | 🆔 ${cp.id}</small><br>
                                    ${index > 0 ? `<button class="btn btn-danger" onclick="rollback('${cp.id}')">Bu Noktaya Geri Dön</button>` : '<span style="color: #28a745;">✅ GÜNCEL</span>'}
                                </div>
                            `;
                        });
                        
                        container.innerHTML = html;
                    }
                });
                
                function rollback(checkpointId) {
                    if (confirm('⚠️ Bu işlem geri alınamaz! Devam etmek istiyor musunuz?')) {
                        fetch('/api/timeline/rollback', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ checkpoint_id: checkpointId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('✅ Rollback tamamlandı! Sayfa yenilenecek...');
                                location.reload();
                            } else {
                                alert('❌ Rollback hatası: ' + data.message);
                            }
                        });
                    }
                }
                </script>
            </div>
        </body>
        </html>
        <?php
    }
}
?>