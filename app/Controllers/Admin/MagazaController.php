<?php
namespace app\Controllers\Admin;

use app\Models\Magaza;
use core\Controller;

class MagazaController extends Controller {
    public function liste() {
        $magazaModel = new Magaza();
        $magazalar = $magazaModel->getAll();
        $this->view('admin/magaza_listesi', ['magazalar' => $magazalar]);
    }

    public function ekle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'ad' => $_POST['ad'],
                'adres' => $_POST['adres'],
                'telefon' => $_POST['telefon'],
                'email' => $_POST['email']
            ];

            $magazaModel = new Magaza();
            $magazaModel->create($data);

            header('Location: /admin/magazalar');
            exit();
        } else {
            $this->view('admin/magaza_ekle');
        }
    }

    public function guncelle($id) {
        $magazaModel = new Magaza();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'ad' => $_POST['ad'],
                'adres' => $_POST['adres'],
                'telefon' => $_POST['telefon'],
                'email' => $_POST['email']
            ];
            $magazaModel->update($id, $data);
            header('Location: /admin/magazalar');
            exit();
        } else {
            $magaza = $magazaModel->get($id);
            $this->view('admin/magaza_guncelle', ['magaza' => $magaza]);
        }
    }

    public function sil($id) {
        $magazaModel = new Magaza();
        $magazaModel->delete($id);
        header('Location: /admin/magazalar');
        exit();
    }
}
