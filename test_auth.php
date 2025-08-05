<?php
echo 'PHP versiyonu: ' . PHP_VERSION . PHP_EOL;
echo 'AuthManager test...' . PHP_EOL;

try {
    require_once 'core/AuthManager.php';
    echo 'AuthManager yuklenebilir: OK' . PHP_EOL;
    
    echo 'SESSION_USER_ID sabiti: ' . core\AuthManager::SESSION_USER_ID . PHP_EOL;
    echo 'Test tamamlandi.' . PHP_EOL;
} catch (Exception $e) {
    echo 'HATA: ' . $e->getMessage() . PHP_EOL;
}
?>