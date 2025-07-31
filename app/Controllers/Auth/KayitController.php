<?php
namespace App\Controllers\Auth;

use Core\Controller;

class KayitController extends Controller {
    public function index() {
        $this->view('auth/kayit');
    }

    public function register() {
        // Yeni kullanıcıyı kaydet
    }
}
