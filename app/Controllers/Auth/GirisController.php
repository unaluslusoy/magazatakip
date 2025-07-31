<?php

namespace app\Controllers\Auth;

use core\Controller;
use app\Models\Kullanici;

class GirisController extends Controller {

    public function index($error = null) {
        // Eğer kullanıcı oturum açmışsa, yetkisine göre yönlendir
        if (isset($_SESSION['user_id'])) {
            if ($_SESSION['is_admin']) {
                header('Location: /admin');
            } else {
                header('Location: /anasayfa');
            }
            exit();
        }
        
        // Hata mesajını güvenli bir şekilde hazırla
        $errorMessage = is_array($error) ? 
            array_filter($error, 'is_string') : 
            (is_string($error) ? $error : null);
        
        $this->view('auth/giris', ['error' => $errorMessage]);
    }

    public function login() {
        if (!isset($_POST['email']) || !isset($_POST['password'])) {
            $this->index("Email veya şifre eksik.");
            return;
        }

        $email = $_POST['email'];
        $password = $_POST['password'];
        $remember = isset($_POST['remember']);
        $kullaniciModel = new Kullanici();
        $user = $kullaniciModel->getByEmail($email);

        // Hata ayıklama için loglama
        error_log("Giriş denemesi: $email");

        if ($user) {
            // Hata ayıklama için loglama
            error_log("Kullanıcı bulundu, hash: " . substr($user['sifre'], 0, 10) . "...");

            $verify_result = password_verify($password, $user['sifre']);

            // Hata ayıklama için loglama
            error_log("Şifre doğrulama sonucu: " . ($verify_result ? 'doğru' : 'yanlış'));

            if ($verify_result) {
                // Giriş başarılı
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['is_admin'] = $user['yonetici'];
                $_SESSION['user_name'] = $user['ad'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['yonetici'];
                $_SESSION['last_activity'] = time();

                if ($remember) {
                    $token = bin2hex(random_bytes(16));
                    $kullaniciModel->setToken($user['id'], $token);
                    setcookie('remember_me', $token, time() + (86400 * 120), "/", "", true, true);
                }

                // Hata ayıklama için loglama
                error_log("Giriş başarılı: " . $user['id']);

                if ($user['yonetici']) {
                    header('Location: /admin');
                } else {
                    header('Location: /anasayfa');
                }
                exit();
            } else {
                // Hata ayıklama için loglama
                error_log("Şifre doğrulama başarısız: " . $user['id']);
                $this->index("Hatalı email veya şifre. Lütfen tekrar deneyin.");
            }
        } else {
            // Hata ayıklama için loglama
            error_log("Kullanıcı bulunamadı: $email");
            $this->index("Hatalı email veya şifre. Lütfen tekrar deneyin.");
        }
    }

    public function logout() {
        $kullaniciModel = new Kullanici();
        if (isset($_COOKIE['remember_me'])) {
            $token = $_COOKIE['remember_me'];
            $kullaniciModel->setToken(null, $token); // token'ı sıfırla
            setcookie('remember_me', '', time() - 3600, "/"); // çerezi sil
        }
        session_unset();
        session_destroy();
        header('Location: /auth/giris');
        exit(); // yönlendirmeden sonra script çalışmasını durdurmak için
    }
}
