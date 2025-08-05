<?php
// Ciro Controllers
namespace app\Controllers\Kullanici\Ciro;

use app\Models\Kullanici\Ciro\CiroModel;
use app\Models\Kullanici;
use app\Models\Magaza;
use app\Middleware\AuthMiddleware;
use core\Controller;

class CiroController extends Controller {

    private $ciroModel;

    public function __construct() {
        // 🔒 GÜVENLIK: Kullanıcı erişim kontrolü
        AuthMiddleware::handle();
        $this->ciroModel = new Ciro();
    }
    public function listele() {
        if ($this->ciroModel->ciroVarMi()) {
            $ciroListesi = $this->ciroModel->ciroListele();
            $this->view('kullanici/ciro/listele', ['ciroListesi' => $ciroListesi]);
        } else {
            $this->view('kullanici/ciro/listele', ['mesaj' => 'Henüz kayıtlı bir ciro bulunmamaktadır.']);
        }
    }
    public function ekle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Form verilerini al ve temizle
            $data = [
                'magaza_id'     => trim($_POST['magaza_id']),
                'magaza_ad'     => trim($_POST['magaza_ad']),
                'gun'           => trim($_POST['gun']),
                'nakit'         => trim($_POST['nakit']),
                'kredi_karti'   => trim($_POST['kredi_karti']),
                'carliston'     => trim($_POST['carliston']),
                'getir_carsi'   => trim($_POST['getir_carsi']),
                'trendyolgo'    => trim($_POST['trendyolgo']),
                'multinet'      => trim($_POST['multinet']),
                'sodexo'        => trim($_POST['sodexo']),
                'edenred'       => trim($_POST['edenred']),
                'setcard'       => trim($_POST['setcard']),
                'tokenflex'     => trim($_POST['tokenflex']),
                'iwallet'       => trim($_POST['iwallet']),
                'metropol'      => trim($_POST['metropol']),
                'gider'         => trim($_POST['gider']),
                'toplam'        => trim($_POST['toplam']),
                'aciklama'      => trim($_POST['aciklama']),
                'ekleme_tarihi' => date('Y-m-d') // Ekleme tarihini bugünün tarihi olarak ayarlama
            ];


            // Form doğrulaması (örneğin, boş alan kontrolü)
            if (empty($data['magaza_id']) || empty($data['gun']) || empty($data['nakit']) || empty($data['kredi_karti'])) {
                $_SESSION['message'] = 'Lütfen tüm gerekli alanları doldurunuz.';
                $_SESSION['message_type'] = 'danger';
                $this->view('kullanici/ciro/ekle', $data);
                return;
            }

            // Veritabanına kaydetme
            if ($this->ciroModel->ciroEkle($data)) {
                $_SESSION['message'] = 'Ciro başarıyla eklendi.';
                $_SESSION['message_type'] = 'success';
                header('Location: /ciro/listele');
            } else {
                $_SESSION['message'] = 'Ciro eklenirken bir hata oluştu.';
                $_SESSION['message_type'] = 'danger';
                $this->view('kullanici/ciro/ekle', $data);
            }
        } else {
            $magazalar = $this->ciroModel->getMagazalar(); // Şube listesini veritabanından çekme
            $this->view('kullanici/ciro/ekle', compact('magazalar'));
        }
    }


    public function duzenle($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $veriler = [
                'magaza_id' => $_POST['magaza_id'],
                'ekleme_tarihi' => $_POST['ekleme_tarihi'],
                'gun' => $_POST['gun'],
                'nakit' => $_POST['nakit'],
                'kredi_karti' => $_POST['kredi_karti'],
                'carliston' => $_POST['carliston'],
                'getir_carsi' => $_POST['getir_carsi'],
                'trendyolgo' => $_POST['trendyolgo'],
                'multinet' => $_POST['multinet'],
                'sodexo' => $_POST['sodexo'],
                'ticket' => $_POST['ticket'],
                'edenred' => $_POST['edenred'],
                'setcard' => $_POST['setcard'],
                'didi' => $_POST['didi'],
                'gider' => $_POST['gider'],
                'aciklama' => $_POST['aciklama'],
                'durum' => $_POST['durum']
            ];

            $ciroGuncelle = $this->ciroModel->ciroGuncelle($id, $veriler);

            if ($ciroGuncelle) {
                $this->view('kullanici/ciro/duzenle', ['mesaj' => 'Ciro başarıyla güncellendi!', 'ciro' => $veriler]);
            } else {
                $this->view('kullanici/ciro/duzenle', ['mesaj' => 'Ciro güncellenirken bir hata oluştu.', 'ciro' => $veriler]);
            }
        } else {
            $ciro = $this->ciroModel->ciroGetir($id);
            $this->view('kullanici/ciro/duzenle', ['ciro' => $ciro]);
        }
    }
    public function sil($id) {
        $ciroSil = $this->ciroModel->ciroSil($id);

        if ($ciroSil) {
            $this->view('kullanici/ciro/listele', ['mesaj' => 'Ciro başarıyla silindi!']);
        } else {
            $this->view('kullanici/ciro/listele', ['mesaj' => 'Ciro silinirken bir hata oluştu.']);
        }
    }


}
