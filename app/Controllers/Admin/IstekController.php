<?php
namespace app\Controllers\Admin;

use app\Models\Istek;
use core\Controller;
use app\Models\Personel;
use app\Middleware\AdminMiddleware;

class IstekController extends Controller
{
    public function __construct() {
        // 🔒 GÜVENLIK: Admin erişim kontrolü
        AdminMiddleware::handle();
    }
    public function liste() {
        $istekModel = new Istek();
        $istekler = $istekModel->getAll();

        $personelModel = new Personel();
        $personeller = $personelModel->getAll(); // Personelleri alın

        $this->view('admin/istek_listesi', ['istekler' => $istekler, 'personeller' => $personeller]);
    }

    public function guncelle($id) {
        $istekModel = new Istek();
        $personelModel = new Personel();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'durum' => $_POST['durum'],
                'personel_id' => $_POST['personel_id'],
                'is_aciklamasi' => $_POST['is_aciklamasi'],
                'baslangic_tarihi' => $_POST['baslangic_tarihi'],
                'bitis_tarihi' => $_POST['bitis_tarihi']
            ];
            $istekModel->update($id, $data);
            header('Location: /admin/istekler');
            exit();
        } else {
            $istek = $istekModel->get($id);
            $personeller = $personelModel->getAll();
            $this->view('admin/istek_detay', ['istek' => $istek, 'personeller' => $personeller]);
        }
    }
    public function ekle() {
        $istekModel = new Istek();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'kullanici_id' => $_POST['kullanici_id'],
                'baslik' => $_POST['baslik'],
                'aciklama' => $_POST['aciklama'],
                'magaza' => $_POST['magaza'],
                'derece' => $_POST['derece'],
                'personel_id' => $_POST['personel_id'],
                'is_aciklamasi' => $_POST['is_aciklamasi'],
                'baslangic_tarihi' => $_POST['baslangic_tarihi'],
                'bitis_tarihi' => $_POST['bitis_tarihi']
            ];
            $istekModel->create($data);
            header('Location: /admin/istekler');
            exit();
        } else {
            $this->view('admin/istek_ekle');
        }
    }
    public function sil($id) {
        $istekModel = new Istek();
        $istekModel->delete($id);
        header('Location: /admin/istekler');
        exit();
    }
}
