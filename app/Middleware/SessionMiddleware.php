<?php

namespace app\Middleware;

class SessionMiddleware {
    public static function handle() {
        session_start();

        // Oturumun son aktivite zamanını kontrol et
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
            // Son aktivite 30 dakikadan uzun süre önce olduysa oturumu sonlandır
            session_unset();     // tüm oturum verilerini temizle
            session_destroy();   // oturumu sonlandır
            header('Location: /auth/giris'); // Kullanıcıyı giriş sayfasına yönlendir
            exit();
        }

        // Son aktivite zamanını güncelle
        $_SESSION['LAST_ACTIVITY'] = time();

        // Kullanıcı giriş kontrolü
        if (!isset($_SESSION['kullanici_id'])) {
            header('Location: admin/anasayfa'); // Kullanıcı oturum açmamışsa giriş sayfasına yönlendir
            exit();
        }
    }
}
