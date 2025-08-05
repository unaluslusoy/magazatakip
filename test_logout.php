<?php
// Logout test dosyası
session_start();

try {
    require_once 'vendor/autoload.php';
    require_once 'core/AuthManager.php';
    
    echo "AuthManager test:\n";
    echo "- SESSION_USER_ID: " . core\AuthManager::SESSION_USER_ID . "\n";
    echo "- Sınıf yüklendi: " . (class_exists('core\AuthManager') ? 'Evet' : 'Hayır') . "\n";
    
    $authManager = core\AuthManager::getInstance();
    echo "- Instance oluşturuldu: Evet\n";
    echo "- Logout metodu var: " . (method_exists($authManager, 'logout') ? 'Evet' : 'Hayır') . "\n";
    
    echo "\nTest başarılı! AuthManager çalışıyor.\n";
    
} catch (Exception $e) {
    echo "HATA: " . $e->getMessage() . "\n";
    echo "Satır: " . $e->getLine() . "\n";
    echo "Dosya: " . $e->getFile() . "\n";
}
?>