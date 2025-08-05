<?php
/**
 * Timeline Manager - Sistem Durumları Yönetimi
 * Hata durumunda kolayca geri alma sistemi
 */

class TimelineManager {
    private $timelineFile = 'scripts/timeline.json';
    private $backupDir = 'backups/';
    
    public function __construct() {
        // Backup dizinini oluştur
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    /**
     * Yeni bir checkpoint oluştur
     */
    public function createCheckpoint($description = '', $type = 'manual') {
        $timestamp = date('Y-m-d H:i:s');
        $checkpointId = date('Ymd_His');
        
        // Git commit hash'i al (shell_exec kullanılamıyorsa boş bırak)
        $gitHash = '';
        $gitBranch = '';
        
        if (function_exists('shell_exec')) {
            $gitHash = trim(shell_exec('git rev-parse HEAD') ?? '');
            $gitBranch = trim(shell_exec('git branch --show-current') ?? '');
        }
        
        // Veritabanı backup oluştur
        $dbBackupFile = $this->createDatabaseBackup($checkpointId);
        
        // Dosya backup oluştur
        $fileBackupFile = $this->createFileBackup($checkpointId);
        
        $checkpoint = [
            'id' => $checkpointId,
            'timestamp' => $timestamp,
            'description' => $description ?: "Otomatik checkpoint - {$timestamp}",
            'type' => $type, // manual, auto, pre-update, critical
            'git_hash' => $gitHash,
            'git_branch' => $gitBranch,
            'database_backup' => $dbBackupFile,
            'files_backup' => $fileBackupFile,
            'status' => 'active',
            'rollback_count' => 0
        ];
        
        // Timeline dosyasını güncelle
        $timeline = $this->getTimeline();
        array_unshift($timeline, $checkpoint);
        
        // Son 20 checkpoint'i sakla
        $timeline = array_slice($timeline, 0, 20);
        
        $this->saveTimeline($timeline);
        
        return $checkpointId;
    }
    
    /**
     * Belirli bir checkpoint'e geri dön
     */
    public function rollbackToCheckpoint($checkpointId) {
        $timeline = $this->getTimeline();
        $checkpoint = null;
        
        foreach ($timeline as $cp) {
            if ($cp['id'] === $checkpointId) {
                $checkpoint = $cp;
                break;
            }
        }
        
        if (!$checkpoint) {
            throw new Exception("Checkpoint bulunamadı: {$checkpointId}");
        }
        
        echo "🔄 Rollback başlatılıyor: {$checkpoint['description']}\n";
        
        // Git checkout (shell_exec kullanılabilirse)
        if ($checkpoint['git_hash'] && function_exists('shell_exec')) {
            echo "📦 Git checkout: {$checkpoint['git_hash']}\n";
            shell_exec("git checkout {$checkpoint['git_hash']}");
        }
        
        // Veritabanı geri yükleme
        if ($checkpoint['database_backup'] && file_exists($checkpoint['database_backup'])) {
            echo "🗄️ Veritabanı geri yükleniyor...\n";
            $this->restoreDatabase($checkpoint['database_backup']);
        }
        
        // Dosyalar geri yükleme
        if ($checkpoint['files_backup'] && file_exists($checkpoint['files_backup'])) {
            echo "📁 Dosyalar geri yükleniyor...\n";
            $this->restoreFiles($checkpoint['files_backup']);
        }
        
        // Rollback sayısını artır
        foreach ($timeline as &$cp) {
            if ($cp['id'] === $checkpointId) {
                $cp['rollback_count']++;
                break;
            }
        }
        
        $this->saveTimeline($timeline);
        
        echo "✅ Rollback tamamlandı!\n";
        
        return true;
    }
    
    /**
     * Timeline listesini göster
     */
    public function showTimeline() {
        $timeline = $this->getTimeline();
        
        echo "📅 SİSTEM TİMELİNE:\n";
        echo str_repeat("=", 80) . "\n";
        
        if (empty($timeline)) {
            echo "⚠️ Henüz checkpoint oluşturulmamış.\n";
            return;
        }
        
        foreach ($timeline as $index => $checkpoint) {
            $current = ($index === 0) ? " 👈 GÜNCEL" : "";
            $rollbacks = $checkpoint['rollback_count'] ? " (🔄 {$checkpoint['rollback_count']}x kullanıldı)" : "";
            
            echo sprintf(
                "🔖 %s | %s | %s%s%s\n",
                $checkpoint['id'],
                $checkpoint['timestamp'],
                $checkpoint['description'],
                $rollbacks,
                $current
            );
            
            if ($checkpoint['git_hash']) {
                echo "   📦 Git: {$checkpoint['git_branch']} ({$checkpoint['git_hash']})\n";
            }
            
            echo "\n";
        }
    }
    
    /**
     * Kritik operasyon öncesi otomatik checkpoint
     */
    public function preOperationCheckpoint($operation) {
        return $this->createCheckpoint(
            "🛡️ {$operation} öncesi güvenlik checkpoint'i",
            'pre-operation'
        );
    }
    
    /**
     * Veritabanı backup oluştur
     */
    private function createDatabaseBackup($checkpointId) {
        $backupFile = $this->backupDir . "db_backup_{$checkpointId}.sql";
        
        // Veritabanı bilgilerini config'den al
        $dbConfig = [
            'host' => 'localhost',
            'username' => 'magazatakip_user', // Adjust as needed
            'password' => 'your_password', // Adjust as needed
            'database' => 'magazatakip_db' // Adjust as needed
        ];
        
        if (function_exists('shell_exec')) {
            $command = sprintf(
                "mysqldump -h%s -u%s -p%s %s > %s",
                $dbConfig['host'],
                $dbConfig['username'],
                $dbConfig['password'],
                $dbConfig['database'],
                $backupFile
            );
            
            shell_exec($command);
        }
        
        return file_exists($backupFile) ? $backupFile : null;
    }
    
    /**
     * Dosya backup oluştur
     */
    private function createFileBackup($checkpointId) {
        $backupFile = $this->backupDir . "files_backup_{$checkpointId}.tar.gz";
        
        // Kritik dosyaları backup'la
        if (function_exists('shell_exec')) {
            $command = "tar -czf {$backupFile} " .
                      "--exclude='backups' " .
                      "--exclude='.git' " .
                      "--exclude='public/uploads' " .
                      "--exclude='vendor' " .
                      "--exclude='node_modules' " .
                      ". 2>/dev/null";
            
            shell_exec($command);
        }
        
        return file_exists($backupFile) ? $backupFile : null;
    }
    
    /**
     * Veritabanını geri yükle
     */
    private function restoreDatabase($backupFile) {
        $dbConfig = [
            'host' => 'localhost',
            'username' => 'magazatakip_user',
            'password' => 'your_password',
            'database' => 'magazatakip_db'
        ];
        
        if (function_exists('shell_exec')) {
            $command = sprintf(
                "mysql -h%s -u%s -p%s %s < %s",
                $dbConfig['host'],
                $dbConfig['username'],
                $dbConfig['password'],
                $dbConfig['database'],
                $backupFile
            );
            
            shell_exec($command);
        }
    }
    
    /**
     * Dosyaları geri yükle
     */
    private function restoreFiles($backupFile) {
        if (function_exists('shell_exec')) {
            shell_exec("tar -xzf {$backupFile}");
        }
    }
    
    /**
     * Timeline dosyasını oku
     */
    private function getTimeline() {
        if (!file_exists($this->timelineFile)) {
            return [];
        }
        
        $content = file_get_contents($this->timelineFile);
        return json_decode($content, true) ?: [];
    }
    
    /**
     * Timeline dosyasını kaydet
     */
    private function saveTimeline($timeline) {
        file_put_contents(
            $this->timelineFile,
            json_encode($timeline, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
    
    /**
     * Eski backup'ları temizle
     */
    public function cleanup($keepDays = 7) {
        $cutoff = time() - ($keepDays * 24 * 60 * 60);
        $files = glob($this->backupDir . '*');
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                echo "🗑️ Eski backup silindi: " . basename($file) . "\n";
            }
        }
    }
}

// CLI kullanımı
if (php_sapi_name() === 'cli') {
    $timeline = new TimelineManager();
    
    $action = $argv[1] ?? 'help';
    
    switch ($action) {
        case 'create':
            $description = $argv[2] ?? '';
            $checkpointId = $timeline->createCheckpoint($description);
            echo "✅ Checkpoint oluşturuldu: {$checkpointId}\n";
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
            try {
                $timeline->rollbackToCheckpoint($checkpointId);
            } catch (Exception $e) {
                echo "❌ Hata: " . $e->getMessage() . "\n";
            }
            break;
            
        case 'cleanup':
            $days = $argv[2] ?? 7;
            $timeline->cleanup($days);
            break;
            
        default:
            echo "🕐 Timeline Manager Kullanımı:\n";
            echo "php timeline-manager.php create [açıklama]  - Yeni checkpoint oluştur\n";
            echo "php timeline-manager.php list              - Timeline'ı listele\n";
            echo "php timeline-manager.php rollback [id]     - Checkpoint'e geri dön\n";
            echo "php timeline-manager.php cleanup [gün]     - Eski backup'ları temizle\n";
            break;
    }
}
?>