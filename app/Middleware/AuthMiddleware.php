<?php

namespace app\Middleware;

class AuthMiddleware {
    public static function handle() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Oturumun son aktivite zamanını kontrol et
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 50000)) {
            // Son aktivite belirli bir süreden uzun süre önce olduysa oturumu sonlandır
            session_unset();     // tüm oturum verilerini temizle
            session_destroy();   // oturumu sonlandır
            header('Location: /auth/giris'); // Kullanıcıyı giriş sayfasına yönlendir
            exit();
        }

        // Son aktivite zamanını güncelle
        $_SESSION['LAST_ACTIVITY'] = time();

        // Kullanıcı giriş kontrolü
        if (!isset($_SESSION['user_id'])) {
            // Cookie kontrolü
            if (!isset($_COOKIE['remember_me'])) {
                header('Location: /auth/giris'); // Kullanıcı oturum açmamışsa giriş sayfasına yönlendir
                exit();
            } else {
                // Çerezleri kontrol et ve oturumu yenile
                $token = $_COOKIE['remember_me'];
                $kullaniciModel = new \App\Models\Kullanici();
                $user = $kullaniciModel->getByToken($token);

                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['is_admin'] = $user['yonetici'];
                    $_SESSION['user_name'] = $user['ad'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['rol'];
                    $_SESSION['last_activity'] = time();
                } else {
                    header('Location: /auth/giris'); // Geçersiz token, giriş sayfasına yönlendir
                    exit();
                }
            }
        }
    }


}
