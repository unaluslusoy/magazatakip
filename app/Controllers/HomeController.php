<?php

namespace app\Controllers;

use core\Controller;

class HomeController extends Controller {
    public function index() {
        // Eğer kullanıcı oturum açmışsa, yetkisine göre yönlendir
        if (isset($_SESSION['user_id'])) {
            if ($_SESSION['is_admin']) {
                header('Location: /admin');
            } else {
                header('Location: /');
            }
            exit();
        }
        $this->view('home');
    }
}
