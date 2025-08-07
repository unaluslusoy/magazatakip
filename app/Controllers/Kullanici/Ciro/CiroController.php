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
        $this->ciroModel = new CiroModel();
    }
    public function listele() {
        // Basit view render - veriler API'den gelecek
        $this->view('kullanici/ciro/listele');
    }
    public function ekle() {
        // Önbellek önleme header'ları
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Kullanıcı bilgilerini al
        $kullaniciModel = new Kullanici();
        $kullanici = $kullaniciModel->get($_SESSION['user_id']);
        
        if (!$kullanici) {
            $_SESSION['message'] = 'Kullanıcı bilgileri bulunamadı.';
            $_SESSION['message_type'] = 'danger';
            header('Location: /ciro/listele');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Form verilerini al ve temizle
            $data = [
                'magaza_id'     => $kullanici['magaza_id'], // Kullanıcının mağaza ID'si otomatik
                'magaza_ad'     => $kullanici['magaza_isim'], // Kullanıcının mağaza adı otomatik
                'gun'           => trim($_POST['gun']),
                'nakit'         => $this->formatMoney(trim($_POST['nakit'])),
                'kredi_karti'   => $this->formatMoney(trim($_POST['kredi_karti'])),
                'carliston'     => $this->formatMoney(trim($_POST['carliston'])),
                'getir_carsi'   => $this->formatMoney(trim($_POST['getir_carsi'])),
                'trendyolgo'    => $this->formatMoney(trim($_POST['trendyolgo'])),
                'multinet'      => $this->formatMoney(trim($_POST['multinet'])),
                'sodexo'        => $this->formatMoney(trim($_POST['sodexo'])),
                'edenred'       => $this->formatMoney(trim($_POST['edenred'])),
                'setcard'       => $this->formatMoney(trim($_POST['setcard'])),
                'tokenflex'     => $this->formatMoney(trim($_POST['tokenflex'])),
                'iwallet'       => $this->formatMoney(trim($_POST['iwallet'])),
                'metropol'      => $this->formatMoney(trim($_POST['metropol'])),
                'ticket'        => $this->formatMoney(trim($_POST['ticket'])),
                'didi'          => $this->formatMoney(trim($_POST['didi'])),
                'toplam'        => $this->formatMoney(trim($_POST['toplam'])),
                'aciklama'      => trim($_POST['aciklama']),
                'gorsel'        => null,
                'ekleme_tarihi' => date('Y-m-d') // Ekleme tarihini bugünün tarihi olarak ayarlama
            ];

            // Görsel yükleme işlemi
            if (isset($_FILES['ciro_gorsel']) && $_FILES['ciro_gorsel']['error'] === UPLOAD_ERR_OK) {
                $uploadedFile = $_FILES['ciro_gorsel'];
                $gorselPath = $this->uploadImage($uploadedFile, $kullanici['magaza_id']);
                
                if ($gorselPath) {
                    $data['gorsel'] = $gorselPath;
                }
            }

            // Form doğrulaması (örneğin, boş alan kontrolü)
            if (empty($data['gun']) || empty($data['nakit']) || empty($data['kredi_karti'])) {
                $_SESSION['message'] = 'Lütfen tüm gerekli alanları doldurunuz.';
                $_SESSION['message_type'] = 'danger';
                $this->view('kullanici/ciro/ekle', ['kullanici' => $kullanici]);
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
                $this->view('kullanici/ciro/ekle', ['kullanici' => $kullanici]);
            }
        } else {
            // GET isteği - form gösterimi
            $this->view('kullanici/ciro/ekle', ['kullanici' => $kullanici]);
        }
    }
    
    /**
     * Para birimi formatını düzelt (1.234,56 -> 1234.56)
     */
    private function formatMoney($value) {
        if (empty($value)) return 0;
        
        // Türkçe para birimi formatını temizle
        $value = str_replace(['₺', 'TL', ' '], '', $value);
        $value = str_replace('.', '', $value); // Binlik ayırıcıyı kaldır
        $value = str_replace(',', '.', $value); // Virgülü noktaya çevir
        
        $result = floatval($value);
        
        // NaN kontrolü
        if (is_nan($result)) {
            return 0;
        }
        
        return $result;
    }

    /**
     * Görsel yükleme işlemi
     */
    private function uploadImage($uploadedFile, $magaza_id) {
        try {
            // Dosya türü kontrolü
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!in_array($uploadedFile['type'], $allowedTypes)) {
                error_log("Geçersiz dosya türü: " . $uploadedFile['type']);
                return false;
            }

            // Dosya boyutu kontrolü (5MB)
            if ($uploadedFile['size'] > 5 * 1024 * 1024) {
                error_log("Dosya boyutu çok büyük: " . $uploadedFile['size']);
                return false;
            }

            // Upload dizini oluştur
            $uploadDir = 'uploads/ciro/' . $magaza_id . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Benzersiz dosya adı oluştur
            $extension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
            $filename = 'ciro_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.' . $extension;
            $filepath = $uploadDir . $filename;

            // Dosyayı yükle
            if (move_uploaded_file($uploadedFile['tmp_name'], $filepath)) {
                return $filepath;
            } else {
                error_log("Dosya yükleme hatası: " . $uploadedFile['tmp_name'] . " -> " . $filepath);
                return false;
            }

        } catch (Exception $e) {
            error_log("Görsel yükleme hatası: " . $e->getMessage());
            return false;
        }
    }


    public function duzenle($id) {
        // Önbellek önleme header'ları
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // ID kontrolü
        if (!$id || !is_numeric($id)) {
            $_SESSION['message'] = 'Geçersiz ciro ID\'si.';
            $_SESSION['message_type'] = 'danger';
            header('Location: /ciro/listele');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // POST verilerini kontrol et
            if (empty($_POST['magaza_id']) || empty($_POST['gun'])) {
                $_SESSION['message'] = 'Lütfen gerekli alanları doldurun.';
                $_SESSION['message_type'] = 'danger';
                $ciro = $this->ciroModel->ciroGetir($id);
                $this->view('kullanici/ciro/duzenle', ['ciro' => $ciro]);
                return;
            }

            $veriler = [
                'magaza_id' => $_POST['magaza_id'],
                'gun' => $_POST['gun'],
                'nakit' => $this->formatMoney($_POST['nakit']),
                'kredi_karti' => $this->formatMoney($_POST['kredi_karti']),
                'carliston' => $this->formatMoney($_POST['carliston']),
                'getir_carsi' => $this->formatMoney($_POST['getir_carsi']),
                'trendyolgo' => $this->formatMoney($_POST['trendyolgo']),
                'multinet' => $this->formatMoney($_POST['multinet']),
                'sodexo' => $this->formatMoney($_POST['sodexo']),
                'edenred' => $this->formatMoney($_POST['edenred']),
                'setcard' => $this->formatMoney($_POST['setcard']),
                'tokenflex' => $this->formatMoney($_POST['tokenflex']),
                'iwallet' => $this->formatMoney($_POST['iwallet']),
                'metropol' => $this->formatMoney($_POST['metropol']),
                'ticket' => $this->formatMoney($_POST['ticket']),
                'didi' => $this->formatMoney($_POST['didi']),
                'toplam' => $this->formatMoney($_POST['toplam']),
                'aciklama' => trim($_POST['aciklama']),
                'gorsel' => null
            ];

            // Görsel yükleme işlemi
            if (isset($_FILES['ciro_gorsel']) && $_FILES['ciro_gorsel']['error'] === UPLOAD_ERR_OK) {
                $uploadedFile = $_FILES['ciro_gorsel'];
                $gorselPath = $this->uploadImage($uploadedFile, $_POST['magaza_id']);
                
                if ($gorselPath) {
                    $veriler['gorsel'] = $gorselPath;
                }
            }

            // Güncelleme işlemini gerçekleştir
            $ciroGuncelle = $this->ciroModel->ciroGuncelle($id, $veriler);

            if ($ciroGuncelle) {
                $_SESSION['message'] = 'Ciro başarıyla güncellendi!';
                $_SESSION['message_type'] = 'success';
                header('Location: /ciro/listele');
                exit();
            } else {
                $_SESSION['message'] = 'Ciro güncellenirken bir hata oluştu.';
                $_SESSION['message_type'] = 'danger';
                $ciro = $this->ciroModel->ciroGetir($id);
                $this->view('kullanici/ciro/duzenle', ['ciro' => $ciro]);
            }
        } else {
            // GET isteği - ciro verilerini getir
            $ciro = $this->ciroModel->ciroGetir($id);
            
            if (!$ciro) {
                $_SESSION['message'] = 'Ciro kaydı bulunamadı. ID: ' . $id;
                $_SESSION['message_type'] = 'danger';
                header('Location: /ciro/listele');
                exit();
            }
            
            $this->view('kullanici/ciro/duzenle', ['ciro' => $ciro]);
        }
    }
    public function sil($id) {
        // Önbellek önleme header'ları
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // ID kontrolü
        if (!$id || !is_numeric($id)) {
            $_SESSION['message'] = 'Geçersiz ciro ID\'si.';
            $_SESSION['message_type'] = 'danger';
            header('Location: /ciro/listele');
            exit();
        }

        // Ciro kaydının var olup olmadığını kontrol et
        $ciro = $this->ciroModel->ciroGetir($id);
        if (!$ciro) {
            $_SESSION['message'] = 'Ciro kaydı bulunamadı.';
            $_SESSION['message_type'] = 'danger';
            header('Location: /ciro/listele');
            exit();
        }

        // Silme işlemini gerçekleştir
        $silmeSonucu = $this->ciroModel->ciroSil($id);

        if ($silmeSonucu) {
            $_SESSION['message'] = 'Ciro kaydı başarıyla silindi!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Ciro kaydı silinirken bir hata oluştu.';
            $_SESSION['message_type'] = 'danger';
        }

        header('Location: /ciro/listele');
        exit();
    }
    
    public function listeleRefresh() {
        // Önbellek önleme header'ları - daha güçlü
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0, private');
        header('Pragma: no-cache');
        header('Expires: -1');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('ETag: "' . md5(time()) . '"');
        header('X-Accel-Buffering: no'); // Nginx için
        header('X-Content-Type-Options: nosniff');
        
        // Session cache temizleme
        if (isset($_SESSION['ciro_cache'])) {
            unset($_SESSION['ciro_cache']);
        }
        
        // Veritabanı bağlantısını yenile
        $this->ciroModel = new CiroModel();
        
        if ($this->ciroModel->ciroVarMi()) {
            $ciroListesi = $this->ciroModel->ciroListele();
            // Debug için veri sayısını logla
            error_log("Ciro listesi yenilendi. Kayıt sayısı: " . count($ciroListesi));
            $this->view('kullanici/ciro/listele', ['ciroListesi' => $ciroListesi]);
        } else {
            error_log("Ciro listesi boş - hiç kayıt bulunamadı (refresh)");
            $this->view('kullanici/ciro/listele', ['mesaj' => 'Henüz kayıtlı bir ciro bulunmamaktadır.']);
        }
    }
    
    public function apiTest() {
        $this->view('kullanici/ciro/api-test');
    }
    
    public function apiDocs() {
        $this->view('kullanici/api-docs');
    }


}
