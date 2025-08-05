<?php
/**
 * Otomatik Checkpoint Creator
 * Kritik işlemler öncesi otomatik checkpoint oluşturur
 */

require_once 'timeline-manager.php';

class AutoCheckpoint {
    private $timeline;
    private $triggers = [
        'before_update' => '🔄 Sistem güncellemesi öncesi',
        'before_migration' => '🗄️ Veritabanı migration öncesi',
        'before_config_change' => '⚙️ Konfigürasyon değişikliği öncesi',
        'before_user_action' => '👤 Kritik kullanıcı işlemi öncesi',
        'daily_backup' => '📅 Günlük otomatik backup',
        'weekly_backup' => '📆 Haftalık otomatik backup'
    ];
    
    public function __construct() {
        $this->timeline = new TimelineManager();
    }
    
    /**
     * Otomatik checkpoint oluştur
     */
    public function create($trigger, $additionalInfo = '') {
        if (!isset($this->triggers[$trigger])) {
            throw new Exception("Geçersiz trigger: {$trigger}");
        }
        
        $description = $this->triggers[$trigger];
        if ($additionalInfo) {
            $description .= " - {$additionalInfo}";
        }
        
        echo "🔄 Otomatik checkpoint oluşturuluyor...\n";
        echo "📝 Açıklama: {$description}\n";
        
        $checkpointId = $this->timeline->createCheckpoint($description, 'auto');
        
        echo "✅ Checkpoint oluşturuldu: {$checkpointId}\n";
        
        // Log dosyasına kaydet
        $this->logCheckpoint($checkpointId, $trigger, $description);
        
        return $checkpointId;
    }
    
    /**
     * Günlük otomatik backup
     */
    public function dailyBackup() {
        // Son 24 saatte backup oluşturulmuş mu kontrol et
        $timeline = json_decode(file_get_contents('scripts/timeline.json'), true) ?: [];
        $lastDaily = null;
        
        foreach ($timeline as $checkpoint) {
            if (strpos($checkpoint['description'], 'Günlük otomatik backup') !== false) {
                $lastDaily = strtotime($checkpoint['timestamp']);
                break;
            }
        }
        
        if ($lastDaily && (time() - $lastDaily) < 86400) {
            echo "ℹ️ Son 24 saatte zaten günlük backup oluşturulmuş.\n";
            return null;
        }
        
        return $this->create('daily_backup', date('Y-m-d'));
    }
    
    /**
     * Haftalık otomatik backup
     */
    public function weeklyBackup() {
        // Son 7 günde backup oluşturulmuş mu kontrol et
        $timeline = json_decode(file_get_contents('scripts/timeline.json'), true) ?: [];
        $lastWeekly = null;
        
        foreach ($timeline as $checkpoint) {
            if (strpos($checkpoint['description'], 'Haftalık otomatik backup') !== false) {
                $lastWeekly = strtotime($checkpoint['timestamp']);
                break;
            }
        }
        
        if ($lastWeekly && (time() - $lastWeekly) < 604800) {
            echo "ℹ️ Son 7 günde zaten haftalık backup oluşturulmuş.\n";
            return null;
        }
        
        return $this->create('weekly_backup', 'Hafta ' . date('W'));
    }
    
    /**
     * Checkpoint log'u
     */
    private function logCheckpoint($checkpointId, $trigger, $description) {
        $logFile = 'scripts/checkpoint.log';
        $logEntry = sprintf(
            "[%s] %s | %s | %s\n",
            date('Y-m-d H:i:s'),
            $checkpointId,
            $trigger,
            $description
        );
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

// CLI kullanımı
if (php_sapi_name() === 'cli') {
    $autoCheckpoint = new AutoCheckpoint();
    
    $action = $argv[1] ?? 'help';
    
    switch ($action) {
        case 'daily':
            $autoCheckpoint->dailyBackup();
            break;
            
        case 'weekly':
            $autoCheckpoint->weeklyBackup();
            break;
            
        case 'update':
            $info = $argv[2] ?? '';
            $autoCheckpoint->create('before_update', $info);
            break;
            
        case 'migration':
            $info = $argv[2] ?? '';
            $autoCheckpoint->create('before_migration', $info);
            break;
            
        case 'config':
            $info = $argv[2] ?? '';
            $autoCheckpoint->create('before_config_change', $info);
            break;
            
        default:
            echo "🤖 Otomatik Checkpoint Kullanımı:\n";
            echo "php auto-checkpoint.php daily      - Günlük backup\n";
            echo "php auto-checkpoint.php weekly     - Haftalık backup\n";
            echo "php auto-checkpoint.php update     - Güncelleme öncesi\n";
            echo "php auto-checkpoint.php migration  - Migration öncesi\n";
            echo "php auto-checkpoint.php config     - Config değişikliği öncesi\n";
            break;
    }
}
?>