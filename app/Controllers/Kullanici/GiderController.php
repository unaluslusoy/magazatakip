<?php
namespace app\Controllers\Kullanici;

use core\Controller;
use app\Models\Kullanici;
use app\Models\Magaza;
use app\Models\Gider;
use app\Middleware\AuthMiddleware;
use Exception;

class GiderController extends Controller {
    
    public function __construct() {
        // 🔒 GÜVENLIK: Kullanıcı erişim kontrolü
        AuthMiddleware::handle();
    }
    
    public function listesi() {
        try {
            // Kullanıcı bilgilerini veritabanından al
            $kullaniciModel = new Kullanici();
            $kullanici = $kullaniciModel->get($_SESSION['user_id']);
            
            if (!$kullanici) {
                header('Location: /auth/giris');
                exit();
            }
            
            // Gider bilgilerini çek
            $giderModel = new Gider();
            $giderler = $giderModel->getAll($kullanici['magaza_id']);
            
            // Mağaza bilgilerini çek
            $magazaModel = new Magaza();
            $magazalar = $magazaModel->getAll();
            
            $this->view('kullanici/gider/listesi', [
                'kullanici' => $kullanici,
                'magazalar' => $magazalar,
                'giderler' => $giderler
            ]);
            
        } catch (Exception $e) {
            error_log("GiderController hata: " . $e->getMessage());
            echo "Bir hata oluştu: " . $e->getMessage();
        }
    }
    
    public function ekle() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // POST işlemi - gider ekleme
                $kullaniciModel = new Kullanici();
                $kullanici = $kullaniciModel->get($_SESSION['user_id']);
                
                if (!$kullanici) {
                    header('Location: /auth/giris');
                    exit();
                }
                
                // Para birimi formatını düzelt (Türk formatından float'a çevir)
                $miktar = $this->formatMoney($_POST['miktar']);
                
                $giderModel = new Gider();
                $giderData = [
                    'magaza_id' => $kullanici['magaza_id'],
                    'baslik' => trim($_POST['baslik']),
                    'miktar' => $miktar,
                    'aciklama' => trim($_POST['aciklama'] ?? ''),
                    'tarih' => $_POST['tarih'],
                    'kategori' => $_POST['kategori'] ?? 'Genel',
                    'gorsel' => null
                ];
                
                // Görsel yükleme işlemi
                if (isset($_FILES['gider_gorsel']) && $_FILES['gider_gorsel']['error'] === UPLOAD_ERR_OK) {
                    $uploadedFile = $_FILES['gider_gorsel'];
                    $gorselPath = $this->uploadImage($uploadedFile, $kullanici['magaza_id']);
                    
                    if ($gorselPath) {
                        $giderData['gorsel'] = $gorselPath;
                    }
                }
                
                if ($giderModel->create($giderData)) {
                    $_SESSION['message'] = 'Gider başarıyla eklendi.';
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = 'Gider eklenirken hata oluştu.';
                    $_SESSION['message_type'] = 'error';
                }
                
                header('Location: /gider/listesi');
                exit();
            }
            
            // GET işlemi - form gösterme
            $kullaniciModel = new Kullanici();
            $kullanici = $kullaniciModel->get($_SESSION['user_id']);
            
            if (!$kullanici) {
                header('Location: /auth/giris');
                exit();
            }
            
            $this->view('kullanici/gider/ekle', [
                'kullanici' => $kullanici
            ]);
            
        } catch (Exception $e) {
            error_log("GiderController hata: " . $e->getMessage());
            echo "Bir hata oluştu: " . $e->getMessage();
        }
    }
    
    public function duzenle($id = null) {
        try {
            if (!$id) {
                header('Location: /gider/listesi');
                exit();
            }
            
            $kullaniciModel = new Kullanici();
            $kullanici = $kullaniciModel->get($_SESSION['user_id']);
            
            if (!$kullanici) {
                header('Location: /auth/giris');
                exit();
            }
            
            $giderModel = new Gider();
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // POST işlemi - gider güncelleme
                $miktar = $this->formatMoney($_POST['miktar']);
                
                $giderData = [
                    'baslik' => trim($_POST['baslik']),
                    'miktar' => $miktar,
                    'aciklama' => trim($_POST['aciklama'] ?? ''),
                    'tarih' => $_POST['tarih'],
                    'kategori' => $_POST['kategori'] ?? 'Genel'
                ];
                
                // Görsel yükleme işlemi
                if (isset($_FILES['gider_gorsel']) && $_FILES['gider_gorsel']['error'] === UPLOAD_ERR_OK) {
                    $uploadedFile = $_FILES['gider_gorsel'];
                    $gorselPath = $this->uploadImage($uploadedFile, $kullanici['magaza_id']);
                    
                    if ($gorselPath) {
                        $giderData['gorsel'] = $gorselPath;
                    }
                }
                
                if ($giderModel->update($id, $giderData)) {
                    $_SESSION['message'] = 'Gider başarıyla güncellendi.';
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = 'Gider güncellenirken hata oluştu.';
                    $_SESSION['message_type'] = 'error';
                }
                
                header('Location: /gider/listesi');
                exit();
            }
            
            // GET işlemi - form gösterme
            $gider = $giderModel->get($id);
            
            if (!$gider || $gider['magaza_id'] != $kullanici['magaza_id']) {
                $_SESSION['message'] = 'Gider bulunamadı.';
                $_SESSION['message_type'] = 'error';
                header('Location: /gider/listesi');
                exit();
            }
            
            $this->view('kullanici/gider/duzenle', [
                'kullanici' => $kullanici,
                'gider' => $gider
            ]);
            
        } catch (Exception $e) {
            error_log("GiderController hata: " . $e->getMessage());
            echo "Bir hata oluştu: " . $e->getMessage();
        }
    }
    
    public function sil($id = null) {
        try {
            if (!$id) {
                header('Location: /gider/listesi');
                exit();
            }
            
            $kullaniciModel = new Kullanici();
            $kullanici = $kullaniciModel->get($_SESSION['user_id']);
            
            if (!$kullanici) {
                header('Location: /auth/giris');
                exit();
            }
            
            $giderModel = new Gider();
            $gider = $giderModel->get($id);
            
            if (!$gider || $gider['magaza_id'] != $kullanici['magaza_id']) {
                $_SESSION['message'] = 'Gider bulunamadı.';
                $_SESSION['message_type'] = 'error';
                header('Location: /gider/listesi');
                exit();
            }
            
            if ($giderModel->delete($id)) {
                // Görsel dosyasını da sil
                if ($gider['gorsel'] && file_exists($gider['gorsel'])) {
                    unlink($gider['gorsel']);
                }
                
                $_SESSION['message'] = 'Gider başarıyla silindi.';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Gider silinirken hata oluştu.';
                $_SESSION['message_type'] = 'error';
            }
            
            header('Location: /gider/listesi');
            exit();
            
        } catch (Exception $e) {
            error_log("GiderController hata: " . $e->getMessage());
            echo "Bir hata oluştu: " . $e->getMessage();
        }
    }
    
    /**
     * Türk para birimi formatını float'a çevirir
     * Örnek: "1.234,56" -> 1234.56
     */
    private function formatMoney($value) {
        if (empty($value)) return 0.0;
        
        // Türk formatından temizle (1.234,56 -> 1234.56)
        $value = str_replace('.', '', $value); // Binlik ayırıcıyı kaldır
        $value = str_replace(',', '.', $value); // Virgülü noktaya çevir
        
        return floatval($value);
    }
    
    /**
     * Görsel yükleme işlemi
     */
    private function uploadImage($file, $magazaId) {
        try {
            // Güvenlik kontrolleri
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Geçersiz dosya tipi. Sadece JPG, PNG, GIF dosyaları kabul edilir.');
            }
            
            if ($file['size'] > $maxSize) {
                throw new Exception('Dosya boyutu çok büyük. Maksimum 5MB olmalıdır.');
            }
            
            // Dosya adını güvenli hale getir
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'gider_' . $magazaId . '_' . time() . '_' . uniqid() . '.' . $extension;
            
            // Upload dizinini oluştur
            $uploadDir = __DIR__ . '/../../../public/uploads/giderler/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $filepath = $uploadDir . $filename;
            
            // Dosyayı yükle
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                return '/uploads/giderler/' . $filename;
            } else {
                throw new Exception('Dosya yüklenirken hata oluştu.');
            }
            
        } catch (Exception $e) {
            error_log("Görsel yükleme hatası: " . $e->getMessage());
            return null;
        }
    }
} 