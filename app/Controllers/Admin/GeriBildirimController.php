<?php

namespace app\Controllers\Admin;

use core\Controller;
use app\Models\GeriBildirim;

class GeriBildirimController extends Controller {

    public function index() {
        $geriBildirimModel = new GeriBildirim();
        $geriBildiriler = $geriBildirimModel->getAll();
        $this->view('admin/geri_bildirimler', ['geriBildiriler' => $geriBildiriler]);
    }

    public function ekle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'baslik' => $_POST['baslik'],
                'icerik' => $_POST['icerik'],
                'kategori' => $_POST['kategori'],
                'durum' => 'yeni', // Varsayılan durum
                'olusturma_tarihi' => date('Y-m-d H:i:s')
            ];
            $geriBildirimModel = new GeriBildirim();
            $geriBildirimModel->create($data);
            header('Location: /admin/geri_bildirimler');
        } else {
            $this->view('admin/geri_bildirim_ekle');
        }
    }

    public function detay($id) {
        $geriBildirimModel = new GeriBildirim();
        $geriBildirim = $geriBildirimModel->get($id);
        $this->view('admin/geri_bildirim_detay', ['geriBildirim' => $geriBildirim]);
    }

    public function sil($id) {
        $geriBildirimModel = new GeriBildirim();
        $geriBildirimModel->delete($id);
        header('Location: /admin/geri_bildirimler');
    }

    public function guncelleDurum() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $durum = $_POST['durum'];
            $geriBildirimModel = new GeriBildirim();
            $geriBildirimModel->updateStatus($id, $durum);
            header('Location: /admin/geri_bildirimler');
        }
    }
}
