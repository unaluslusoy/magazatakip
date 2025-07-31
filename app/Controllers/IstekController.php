<?php

namespace app\Controllers;

use app\Models\Istek;
use core\Controller;

class IstekController extends Controller {
    public function olustur() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'kullanici_id' => $_SESSION['user_id'],
                'baslik' => $_POST['baslik'],
                'aciklama' => $_POST['aciklama'],
                'magaza' => $_POST['magaza'],
                'derece' => $_POST['derece']
            ];

            $istekModel = new Istek();
            $istekModel->create($data);

            header('Location: /istekler');
            exit();
        } else {
            include 'app/Views/istek_form.php';
        }
    }

    public function liste() {
        $istekModel = new Istek();
        $istekler = $istekModel->getAll();

        include 'app/Views/istek_listesi.php';
    }
}
