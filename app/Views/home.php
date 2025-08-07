<?php
// Session kontrolü (session zaten index.php'de başlatılmış)
if (session_status() == PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Eğer kullanıcı zaten giriş yapmışsa anasayfaya yönlendir
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    if (!headers_sent()) {
        header('Location: /anasayfa');
        exit();
    }
}

// Giriş yapmamış kullanıcıları login sayfasına yönlendir
if (!headers_sent()) {
    header('Location: /auth/giris');
    exit();
}
?>
