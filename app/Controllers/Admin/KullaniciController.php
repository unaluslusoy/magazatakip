<?php

namespace app\Controllers\Admin;

use core\Controller;
use app\Models\Kullanici;
use app\Models\Magaza;
class KullaniciController extends Controller {
    public function index() {
        $kullaniciModel = new Kullanici();
        $kullanicilar = $kullaniciModel->getAll();
        $magazaModel = new Magaza();
        $magazalar = $magazaModel->getAll();
        $this->view('admin/kullanicilar', ['kullanicilar' => $kullanicilar,'magazalar' => $magazalar]);
    }

    public function create() {
        $magazaModel = new Magaza();
        $magazalar = $magazaModel->getAll();
        $this->view('admin/kullanici_ekle', ['magazalar' => $magazalar]);
    }

    public function store() {
        $ad = $_POST['ad'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $rol = $_POST['rol'];
        $magaza_id = $_POST['magaza_id']; // Mağaza ID'sini alın

        // Şifreyi bcrypt ile hash'le
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Kullanıcı modelini oluştur ve veritabanına ekle
        $kullaniciModel = new Kullanici();
        $kullaniciModel->create([
            'ad' => $ad,
            'email' => $email,
            'sifre' => $hashed_password,
            'yonetici' => $rol,
            'magaza_id' => $magaza_id // Mağaza ID'sini ekle
        ]);

        // Kullanıcılar sayfasına yönlendir
        header('Location: /admin/kullanicilar');
    }

    public function edit($id) {
        $kullaniciModel = new Kullanici();
        $kullanici = $kullaniciModel->get($id);
        $this->view('admin/kullanici_duzenle', ['kullanici' => $kullanici]);
    }

    public function update($id)
    {
        // Basit veri doğrulama
        if (empty($_POST['ad']) || empty($_POST['email'])) {
            $_SESSION['error'] = 'Ad ve email alanları zorunludur.';
            header('Location: /admin/kullanicilar/edit/' . $id);
            exit();
        }

        $ad = htmlspecialchars($_POST['ad'], ENT_QUOTES, 'UTF-8');
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $yonetici = isset($_POST['yonetici']) ? 1 : 0;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Geçersiz email adresi.';
            header('Location: /admin/kullanicilar/edit/' . $id);
            exit();
        }

        $data = [
            'ad' => $ad,
            'email' => $email,
            'yonetici' => $yonetici
        ];

        // Şifre değişikliği kontrolü ve işlemi
        if (!empty($_POST['password'])) {
            $password = $_POST['password'];
            if (strlen($password) < 8) {
                $_SESSION['error'] = 'Şifre en az 8 karakter olmalıdır.';
                header('Location: /admin/kullanicilar/edit/' . $id);
                exit();
            }
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $data['sifre'] = $hashed_password;
        }

        try {
            $kullaniciModel = new Kullanici();
            $result = $kullaniciModel->update($id, $data);

            if ($result) {
                $_SESSION['success'] = 'Kullanıcı başarıyla güncellendi.';
            } else {
                $_SESSION['error'] = 'Kullanıcı güncellenirken bir hata oluştu.';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
        }

        header('Location: /admin/kullanicilar');
        exit();
    }
    public function delete($id) {
        $kullaniciModel = new Kullanici();
        $kullaniciModel->delete($id);

        header('Location: /admin/kullanicilar');
    }
}
