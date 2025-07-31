<?php

namespace app\Controllers\Admin;

use core\Controller;
use app\Models\Personel;
use app\Models\Departman;
use app\Models\Pozisyon;

class PersonelController extends Controller {

    public function index() {
        $personelModel = new Personel();
        $personeller = $personelModel->getAll();
        $this->view('admin/personel_listesi', ['personeller' => $personeller]);
    }

    public function liste() {
        $personelModel = new Personel();
        $personeller = $personelModel->getAll();
        $this->view('admin/personel_listesi', ['personeller' => $personeller]);
    }

    public function detay($id) {

        $personelModel = new Personel();
        $personel = $personelModel->get($id);
        if (!$personel) {
            // Personel bulunamazsa hata mesajı göster
            $this->view('errors/404');
            return;
        }
        $profileCompletion = $this->calculateProfileCompletion($personel);
        $this->view('admin/personel_detay', ['personel' => $personel, 'profileCompletion' => $profileCompletion]);
    }


    private function calculateProfileCompletion($personel) {
        $totalFields = 24; // Toplam alan sayısı
        $filledFields = 0;

        // Dolu olan alanları say
        foreach ($personel as $key => $value) {
            if (!empty($value)) {
                $filledFields++;
            }
        }

        // Doluluk oranını hesapla
        $completionPercentage = ($filledFields / $totalFields) * 100;
        return round($completionPercentage);
    }

    public function ekle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'ad' => $_POST['ad'],
                'soyad' => $_POST['soyad'],
                'eposta' => $_POST['email'],
                'telefon' => $_POST['telefon'],
                'pozisyon' => $_POST['pozisyon']
            ];
            $personelModel = new Personel();
            if ($personelModel->exists($data['eposta'])) {
                $_SESSION['alert_message'] = [
                    'text' => 'Bu e-posta adresi zaten kayıtlı.',
                    'icon' => 'error',
                    'confirmButtonText' => 'Tamam'
                ];
            } else {
                if ($personelModel->PersonelCreate($data)) {
                    $_SESSION['alert_message'] = [
                        'text' => 'Personel başarıyla eklendi.',
                        'icon' => 'success',
                        'confirmButtonText' => 'Tamam'
                    ];
                    header('Location: /admin/personeller');
                    exit();
                } else {
                    $_SESSION['alert_message'] = [
                        'text' => 'Personel eklenirken hata oluştu.',
                        'icon' => 'error',
                        'confirmButtonText' => 'Tamam'
                    ];
                }
            }
        }
        header('Location: /admin/personeller');
    }

    public function guncelle($id) {
        $personelModel = new Personel();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'calisan_no' => $_POST['calisan_no'],
                'ad' => $_POST['ad'],
                'soyad' => $_POST['soyad'],
                'dogum_tarihi' => $_POST['dogum_tarihi'],
                'tc_no' => $_POST['tc_no'],
                'cinsiyet' => $_POST['cinsiyet'],
                'eposta' => $_POST['eposta'],
                'telefon' => $_POST['telefon'],
                'cep_telefon' => $_POST['cep_telefon'],
                'ev_adresi' => $_POST['ev_adresi'],
                'ise_baslama_tarihi' => $_POST['ise_baslama_tarihi'],
                'pozisyon' => $_POST['pozisyon'],
                'departman' => $_POST['departman'],
                'rapor_yoneticisi' => $_POST['rapor_yoneticisi'],
                'calisma_sekli' => $_POST['calisma_sekli'],
                'sozlesme_tarihi' => $_POST['sozlesme_tarihi'],
                'sozlesme_suresi' => $_POST['sozlesme_suresi'],
                'ucret' => $_POST['ucret'],
                'sgk_no' => $_POST['sgk_no'],
                'puantaj_sistemi' => $_POST['puantaj_sistemi'],
                'izin_gunleri' => $_POST['izin_gunleri'],
                'egitim_bilgileri' => $_POST['egitim_bilgileri'],
                'dil_bilgisi' => $_POST['dil_bilgisi'],
                'ozel_yetenekler' => $_POST['ozel_yetenekler'],
                'foto' => $_FILES['foto']['name'],
                'notlar' => $_POST['notlar'],
                'kullanici_adi' => $_POST['kullanici_adi'],
                'sifre' => password_hash($_POST['sifre'], PASSWORD_DEFAULT),
                'guvenlik_sorulari' => $_POST['guvenlik_sorulari'],
            ];
            move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/" . $_FILES['foto']['name']);
            $personelModel->update($id, $data);
            header('Location: /admin/personel');
        } else {
            $personel = $personelModel->get($id);
            $departmanModel = new Departman();
            $pozisyonModel = new Pozisyon();
            $departmanlar = $departmanModel->getAll();
            $pozisyonlar = $pozisyonModel->getAll();
            $this->view('admin/personel_guncelle', ['personel' => $personel, 'departmanlar' => $departmanlar, 'pozisyonlar' => $pozisyonlar]);
        }
    }

    public function sil($id) {
        $personelModel = new Personel();
        $personelModel->delete($id);
        $_SESSION['alert_message'] = [
            'text' => 'Personel başarıyla silindi.',
            'icon' => 'success',
            'confirmButtonText' => 'Tamam'
        ];
        header('Location: /admin/personeller');
    }
}
