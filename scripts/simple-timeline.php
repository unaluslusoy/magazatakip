<?php
/**
 * Basit Timeline Manager
 * Shell komutları olmadan çalışır
 */

class SimpleTimeline {
    private $timelineFile = 'scripts/timeline.json';
    
    public function __construct() {
        // Scripts dizinini oluştur
        if (!is_dir('scripts')) {
            mkdir('scripts', 0755, true);
        }
    }
    
    /**
     * Basit checkpoint oluştur
     */
    public function createCheckpoint($description = '') {
        $timestamp = date('Y-m-d H:i:s');
        $checkpointId = date('Ymd_His');
        
        $checkpoint = [
            'id' => $checkpointId,
            'timestamp' => $timestamp,
            'description' => $description ?: "Checkpoint - {$timestamp}",
            'type' => 'manual',
            'status' => 'active',
            'rollback_count' => 0,
            'created_by' => 'system'
        ];
        
        // Timeline dosyasını güncelle
        $timeline = $this->getTimeline();
        array_unshift($timeline, $checkpoint);
        
        // Son 50 checkpoint'i sakla
        $timeline = array_slice($timeline, 0, 50);
        
        $this->saveTimeline($timeline);
        
        return $checkpointId;
    }
    
    /**
     * Timeline listesini döndür
     */
    public function getTimeline() {
        if (!file_exists($this->timelineFile)) {
            return [];
        }
        
        $content = file_get_contents($this->timelineFile);
        return json_decode($content, true) ?: [];
    }
    
    /**
     * Timeline'ı kaydet
     */
    private function saveTimeline($timeline) {
        file_put_contents(
            $this->timelineFile,
            json_encode($timeline, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
    
    /**
     * Checkpoint'i rollback olarak işaretle
     */
    public function markRollback($checkpointId) {
        $timeline = $this->getTimeline();
        
        foreach ($timeline as &$checkpoint) {
            if ($checkpoint['id'] === $checkpointId) {
                $checkpoint['rollback_count']++;
                $checkpoint['last_rollback'] = date('Y-m-d H:i:s');
                break;
            }
        }
        
        $this->saveTimeline($timeline);
        return true;
    }
    
    /**
     * Timeline'ı göster
     */
    public function showTimeline() {
        $timeline = $this->getTimeline();
        
        echo "📅 Basit Timeline:\n";
        echo str_repeat("=", 60) . "\n";
        
        if (empty($timeline)) {
            echo "⚠️ Henüz checkpoint oluşturulmamış.\n";
            return;
        }
        
        foreach ($timeline as $index => $checkpoint) {
            $current = ($index === 0) ? " 👈 GÜNCEL" : "";
            $rollbacks = $checkpoint['rollback_count'] ? " (🔄 {$checkpoint['rollback_count']}x)" : "";
            
            echo sprintf(
                "🔖 %s | %s | %s%s%s\n",
                $checkpoint['id'],
                $checkpoint['timestamp'],
                $checkpoint['description'],
                $rollbacks,
                $current
            );
        }
    }
}

// CLI kullanımı
if (php_sapi_name() === 'cli') {
    $timeline = new SimpleTimeline();
    
    $action = $argv[1] ?? 'help';
    
    switch ($action) {
        case 'create':
            $description = $argv[2] ?? '';
            $checkpointId = $timeline->createCheckpoint($description);
            echo "✅ Basit checkpoint oluşturuldu: {$checkpointId}\n";
            break;
            
        case 'list':
            $timeline->showTimeline();
            break;
            
        case 'rollback':
            $checkpointId = $argv[2] ?? '';
            if (!$checkpointId) {
                echo "❌ Checkpoint ID belirtmelisiniz.\n";
                break;
            }
            $timeline->markRollback($checkpointId);
            echo "✅ Rollback işaretlendi: {$checkpointId}\n";
            echo "⚠️  Not: Gerçek rollback için manuel işlem gerekli.\n";
            break;
            
        default:
            echo "📝 Basit Timeline Kullanımı:\n";
            echo "php simple-timeline.php create [açıklama]  - Yeni checkpoint\n";
            echo "php simple-timeline.php list              - Timeline listesi\n";
            echo "php simple-timeline.php rollback [id]     - Rollback işaretle\n";
            break;
    }
}
?>