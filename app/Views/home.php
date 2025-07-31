<?php
// Session kontrolü (session zaten index.php'de başlatılmış)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Eğer kullanıcı zaten giriş yapmışsa anasayfaya yönlendir
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header('Location: /anasayfa');
    exit();
}

// Giriş yapmamış kullanıcıları login sayfasına yönlendir
header('Location: /auth/giris');
exit();
?>
