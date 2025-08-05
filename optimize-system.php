<?php
// System Optimization Script
// Performans için sistem optimizasyonu

echo "🚀 Sistem Optimizasyonu Başlatılıyor...\n\n";

// 1. Composer optimizasyonu
echo "📦 Composer Optimizasyonu...\n";
exec('php composer.phar dump-autoload --optimize --no-dev', $output, $return_var);
if ($return_var === 0) {
    echo "✅ Composer optimize edildi\n";
} else {
    echo "⚠️ Composer optimize edilemedi\n";
}
echo "\n";

// 2. Cache dizinleri
echo "📁 Cache Dizinleri Oluşturuluyor...\n";
$dirs = ['cache', 'cache/auth', 'cache/session', 'cache/views', 'logs/performance'];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✅ $dir oluşturuldu\n";
    }
}

// 3. Dosya izinleri
echo "\n🔒 Dosya İzinleri Ayarlanıyor...\n";
chmod('cache', 0755);
chmod('logs', 0755);
echo "✅ Cache ve log dizin izinleri ayarlandı\n";

// 4. Eski dosyaları temizle
echo "\n🧹 Eski Dosyalar Temizleniyor...\n";
$tempFiles = glob('cache/*.tmp');
$oldLogs = glob('logs/*.log');

$cleaned = 0;
foreach ($tempFiles as $file) {
    unlink($file);
    $cleaned++;
}

// 7 günden eski logları temizle
foreach ($oldLogs as $file) {
    if (filemtime($file) < strtotime('-7 days')) {
        unlink($file);
        $cleaned++;
    }
}

echo "✅ $cleaned adet eski dosya temizlendi\n";

// 5. Performans test
echo "\n⚡ Performans Testi...\n";
$start = microtime(true);

// AuthManager test
require_once 'vendor/autoload.php';
require_once 'core/Controller.php';
require_once 'core/Database.php';
require_once 'core/AuthManager.php';
require_once 'core/CacheManager.php';

// Autoloader
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

// Test
try {
    $authManager = \core\AuthManager::getInstance();
    $cacheManager = \core\CacheManager::getInstance();
    
    $loadTime = round((microtime(true) - $start) * 1000, 2);
    echo "✅ AuthManager yükleme süresi: {$loadTime}ms\n";
    
    // Cache test
    $cacheStart = microtime(true);
    $testKey = 'perf_test_' . time();
    $cacheManager->set($testKey, ['test' => true], 60);
    $cacheResult = $cacheManager->get($testKey);
    $cacheTime = round((microtime(true) - $cacheStart) * 1000, 2);
    
    echo "✅ Cache operasyon süresi: {$cacheTime}ms\n";
    echo "✅ Cache tipi: " . ($cacheManager->isRedisAvailable() ? 'Redis' : 'File Cache') . "\n";
    
    // Cleanup test
    $cacheManager->delete($testKey);
    
} catch (Exception $e) {
    echo "❌ Test hatası: " . $e->getMessage() . "\n";
}

// 6. .htaccess optimizasyonu kontrolü
echo "\n⚙️ .htaccess Kontrolü...\n";
if (file_exists('.htaccess')) {
    $htaccess = file_get_contents('.htaccess');
    if (strpos($htaccess, 'mod_gzip') !== false || strpos($htaccess, 'mod_deflate') !== false) {
        echo "✅ Gzip compression aktif\n";
    } else {
        echo "⚠️ Gzip compression .htaccess'de bulunamadı\n";
    }
    
    if (strpos($htaccess, 'Expires') !== false) {
        echo "✅ Browser caching aktif\n";
    } else {
        echo "⚠️ Browser caching .htaccess'de bulunamadı\n";
    }
} else {
    echo "❌ .htaccess dosyası bulunamadı\n";
}

echo "\n🎉 Sistem Optimizasyonu Tamamlandı!\n";
echo "📊 Toplam süre: " . round((microtime(true) - $start) * 1000, 2) . "ms\n";
echo "💡 Cache admin paneli: your-domain.com/cache-admin.php\n";
?>