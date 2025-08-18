<?php
// Basit sağlık kontrolü (geçici)
header('Content-Type: text/plain; charset=utf-8');

$__started = false;
try {
    if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); $__started = true; }
} catch (Throwable $e) {}

$results = [
    'time' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION,
    'app_root' => __DIR__,
];

// 1) PHP çalışıyor mu?
echo "PHP OK\n";

// 2) Composer autoload mevcut mu?
$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    echo "Autoload: MISSING vendor/autoload.php\n";
} else {
    echo "Autoload: OK\n";
    require_once $autoload;
}

// 3) DB bağlantısı testi
try {
    require_once __DIR__ . '/core/Database.php';
    $start = microtime(true);
    $db = core\Database::getInstance()->getConnection();
    $elapsed = round((microtime(true) - $start) * 1000);
    $stmt = $db->query('SELECT 1');
    $one = $stmt->fetchColumn();
    echo "DB: OK ({$elapsed} ms) SELECT 1 => {$one}\n";
} catch (Throwable $e) {
    echo "DB: ERROR => " . $e->getMessage() . "\n";
}

// 4) session yazma testi
try {
    if (!$__started && session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
    $_SESSION['health_check'] = time();
    echo "Session: OK\n";
    echo 'Session handler: ' . (ini_get('session.save_handler') ?: 'unknown') . "\n";
    echo 'Session save_path: ' . (ini_get('session.save_path') ?: 'unknown') . "\n";
} catch (Throwable $e) {
    echo "Session: ERROR => " . $e->getMessage() . "\n";
}

// 5) Basit dosya yazma testi (logs içine)
$logDir = __DIR__ . '/logs';
try {
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0775, true);
    }
    $testFile = $logDir . '/health_write_test.txt';
    $ok = @file_put_contents($testFile, 'ok ' . date('c'));
    if ($ok !== false) {
        echo "FS write: OK\n";
        @unlink($testFile);
    } else {
        echo "FS write: ERROR (permission?)\n";
    }
} catch (Throwable $e) {
    echo "FS write: ERROR => " . $e->getMessage() . "\n";
}

// 6) Özet
echo "--\n";
foreach ($results as $k => $v) { echo $k . ': ' . $v . "\n"; }

// 7) Cache/Redis durumu
try {
    require_once __DIR__ . '/core/CacheManager.php';
    $cm = core\CacheManager::getInstance();
    $isRedis = $cm->isRedisAvailable();
    echo 'Cache backend: ' . ($isRedis ? 'Redis' : 'File Cache') . "\n";
} catch (Throwable $e) {
    echo 'Cache: ERROR => ' . $e->getMessage() . "\n";
}

// Not: İş bittiğinde bu dosyayı silebiliriz.


