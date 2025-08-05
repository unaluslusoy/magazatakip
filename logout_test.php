<?php
session_start();

// Mevcut logout problemini test et
echo "<h2>Logout Problem Testi</h2>";
echo "<h3>Mevcut Session Bilgileri:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Mevcut Cookie Bilgileri:</h3>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

// AuthManager test
echo "<h3>AuthManager Test:</h3>";
try {
    require_once 'core/AuthManager.php';
    $authManager = core\AuthManager::getInstance();
    $currentUser = $authManager->getCurrentUser();
    echo "Mevcut kullanıcı: " . json_encode($currentUser) . "<br>";
    echo "Is Admin: " . ($authManager->isAdmin() ? "Evet" : "Hayır") . "<br>";
    echo "Has Active Session: " . ($authManager->getCurrentUser() !== null ? "Evet" : "Hayır") . "<br>";
} catch (Exception $e) {
    echo "AuthManager Hatası: " . $e->getMessage();
}

echo "<br><br><button onclick='window.location.href=\"/auth/giris\"'>Giriş Sayfasına Git</button>";
?>