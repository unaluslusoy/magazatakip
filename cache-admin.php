<?php
// Cache Admin Panel - System yönetimi için
session_start();

// Admin kontrolü
if (!isset($_SESSION['user_id']) || !($_SESSION['is_admin'] ?? false)) {
    header('Location: /auth/giris');
    exit();
}

// Autoloader setup
require_once 'vendor/autoload.php';
require_once 'core/Controller.php';
require_once 'core/Database.php';
require_once 'app/helpers/url_helper.php';
require_once 'config/database.php';

spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    $file_path = __DIR__ . '/' . $class . '.php';
    
    if (!file_exists($file_path)) {
        $legacy_path = __DIR__ . '/app/Models/' . basename($class) . '.php';
        if (file_exists($legacy_path)) {
            $file_path = $legacy_path;
        }
    }
    
    if (file_exists($file_path)) {
        require_once $file_path;
    }
});

use core\CacheManager;

$action = $_GET['action'] ?? 'stats';
$cache = CacheManager::getInstance();

?><!DOCTYPE html>
<html>
<head>
    <title>🚀 Cache Admin Panel</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; margin: -30px -30px 30px -30px; border-radius: 10px 10px 0 0; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0; }
        .stat-card { background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #007bff; }
        .btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #007bff; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .alert { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .code { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; overflow-x: auto; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>🚀 Cache Management System</h1>
        <p>Performans optimizasyonu ve cache yönetimi</p>
    </div>

    <div style="margin-bottom: 20px;">
        <a href="?action=stats" class="btn btn-primary">📊 İstatistikler</a>
        <a href="?action=flush" class="btn btn-danger" onclick="return confirm('Tüm cache silinecek, emin misiniz?')">🗑️ Cache Temizle</a>
        <a href="?action=cleanup" class="btn btn-warning">🧹 Eski Cache Temizle</a>
        <a href="?action=test" class="btn btn-success">🔧 Test Et</a>
        <a href="/admin" class="btn btn-primary">← Admin Panel</a>
    </div>

    <?php
    
    switch ($action) {
        case 'flush':
            $result = $cache->flush();
            echo '<div class="alert alert-success">✅ Tüm cache temizlendi!</div>';
            break;
            
        case 'cleanup':
            $cleaned = $cache->cleanup();
            echo '<div class="alert alert-success">✅ ' . $cleaned . ' adet süresi dolmuş cache dosyası temizlendi!</div>';
            break;
            
        case 'test':
            // Cache test
            $testKey = 'test_' . time();
            $testData = ['test' => true, 'timestamp' => time(), 'random' => rand(1000, 9999)];
            
            echo '<div class="alert alert-info"><h3>🔧 Cache Test Sonuçları</h3>';
            
            // Set test
            $setResult = $cache->set($testKey, $testData, 60);
            echo "✅ Cache Set: " . ($setResult ? 'Başarılı' : 'Başarısız') . "<br>";
            
            // Get test
            $getValue = $cache->get($testKey);
            echo "✅ Cache Get: " . ($getValue !== null ? 'Başarılı' : 'Başarısız') . "<br>";
            
            // Exists test
            $existsResult = $cache->exists($testKey);
            echo "✅ Cache Exists: " . ($existsResult ? 'Başarılı' : 'Başarısız') . "<br>";
            
            // Delete test
            $deleteResult = $cache->delete($testKey);
            echo "✅ Cache Delete: " . ($deleteResult ? 'Başarılı' : 'Başarısız') . "<br>";
            
            echo '</div>';
            break;
    }
    
    // İstatistikler
    $stats = $cache->getStats();
    ?>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>📊 Cache Tipi</h3>
            <p><strong><?= $stats['type'] ?></strong></p>
            <?php if ($cache->isRedisAvailable()): ?>
                <p style="color: green;">✅ Redis Aktif</p>
            <?php else: ?>
                <p style="color: orange;">⚠️ File Cache Kullanılıyor</p>
            <?php endif; ?>
        </div>

        <?php if (isset($stats['keys_count'])): ?>
        <div class="stat-card">
            <h3>🔢 Toplam Key Sayısı</h3>
            <p><strong><?= number_format($stats['keys_count']) ?></strong></p>
        </div>
        <?php endif; ?>

        <?php if (isset($stats['cache_files_count'])): ?>
        <div class="stat-card">
            <h3>📁 Cache Dosya Sayısı</h3>
            <p><strong><?= number_format($stats['cache_files_count']) ?></strong></p>
        </div>
        
        <div class="stat-card">
            <h3>💾 Toplam Boyut</h3>
            <p><strong><?= number_format($stats['total_size'] / 1024, 2) ?> KB</strong></p>
        </div>
        <?php endif; ?>

        <div class="stat-card">
            <h3>⏱️ Son Güncelleme</h3>
            <p><strong><?= date('Y-m-d H:i:s') ?></strong></p>
        </div>

        <div class="stat-card">
            <h3>📂 Cache Dizini</h3>
            <p><strong><?= isset($stats['cache_directory']) ? $stats['cache_directory'] : 'Redis Memory' ?></strong></p>
        </div>
    </div>

    <div class="alert alert-info">
        <h3>ℹ️ Cache Bilgileri</h3>
        <ul>
            <li><strong>AuthManager:</strong> Kullanıcı verilerini 5 dakika cache'ler</li>
            <li><strong>Session Cache:</strong> Session verilerini 30 dakika cache'ler</li>
            <li><strong>Redis:</strong> <?= extension_loaded('redis') ? '✅ Destekleniyor' : '❌ Desteklenmiyor' ?></li>
            <li><strong>File Cache:</strong> ✅ Destekleniyor (fallback)</li>
        </ul>
    </div>

    <?php if (isset($stats['info']) && is_array($stats['info'])): ?>
    <div class="code">
        <h3>🔍 Redis Memory Info</h3>
        <pre><?= print_r($stats['info'], true) ?></pre>
    </div>
    <?php endif; ?>

    <div style="margin-top: 30px; text-align: center; color: #666;">
        <p>🚀 Cache Management System v1.0 | Powered by Redis & File Cache</p>
    </div>

</div>

</body>
</html>