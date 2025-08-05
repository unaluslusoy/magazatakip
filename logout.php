<?php
/**
 * Basit ve Güvenilir Logout Endpoint
 * Tarayıcı cache problemlerine karşı alternatif çözüm
 */

session_start();

// AuthManager ile logout
try {
    require_once 'core/AuthManager.php';
    $authManager = core\AuthManager::getInstance();
    $authManager->logout();
    
    // Başarı mesajı
    $message = "Çıkış işlemi başarılı";
    
} catch (Exception $e) {
    // Hata olsa bile manuel temizlik yap
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }
    session_destroy();
    
    $message = "Manuel çıkış yapıldı";
}

// Başarı sayfası göster
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Çıkış Yapıldı</title>
    <meta http-equiv="refresh" content="2;url=/auth/giris">
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .success { color: #28a745; font-size: 18px; }
        .redirect { color: #6c757d; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="success">
        ✅ <?= htmlspecialchars($message) ?>
    </div>
    <div class="redirect">
        2 saniye içinde giriş sayfasına yönlendirileceksiniz...
    </div>
    
    <script>
        // Ek temizlik
        if (localStorage) {
            localStorage.removeItem('email');
            localStorage.removeItem('rememberEmail');
        }
        if (sessionStorage) {
            sessionStorage.clear();
        }
        
        // Güvenilir yönlendirme
        setTimeout(() => {
            window.location.replace('/auth/giris');
        }, 2000);
    </script>
</body>
</html>