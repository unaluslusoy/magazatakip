<?php

namespace app\Controllers\Auth;

use core\Controller;
use core\AuthManager;
use app\Models\Kullanici;

class GirisController extends Controller {

    public function index($error = null) {
        $authManager = AuthManager::getInstance();
        
        // Sadece aktif session kontrolü yap - remember token ile otomatik giriş yapma
        if ($authManager->getCurrentUser() !== null) {
            $user = $authManager->getCurrentUser();
            $redirectUrl = $user['yonetici'] ? '/admin' : '/anasayfa';
            header('Location: ' . $redirectUrl);
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
        
        $authManager = AuthManager::getInstance();
        $loginResult = $authManager->login($email, $password, $remember);
        
        if ($loginResult['success']) {
            // İsteğe bağlı: İlk girişte bilgilendirme göstermek istemiyorsak mesajı kaldır
            unset($_SESSION['alert_message']);
            header('Location: ' . $loginResult['redirect']);
            exit();
        } else {
            $this->index($loginResult['message']);
        }
    }

    public function logout() {
        $authManager = AuthManager::getInstance();
        $authManager->logout();
        
        header('Location: /auth/giris');
        exit();
    }
}
