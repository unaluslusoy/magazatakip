<?php
namespace app\Controllers\Kullanici\IsEmri;

use app\Models\Kullanici\IsEmri\IsEmriModel;
use app\Models\Kullanici;
use app\Models\Magaza;
use core\Controller;
use core\Database;

class IsEmriController extends Controller {
    public function olustur() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $kullanici_id = $_SESSION['user_id'] ?? null;
                
                // Kullanıcı bilgilerini al
                $kullaniciModel = new Kullanici();
                $kullanici = $kullaniciModel->get($kullanici_id);
                
                // Zorunlu alan kontrolü
                if (empty($kullanici_id) || empty($_POST['baslik']) || empty($_POST['aciklama'])) {
                    throw new \Exception('Zorunlu alanlar boş bırakılamaz.');
                }

                $baslik = $_POST['baslik'];
                $aciklama = $_POST['aciklama'];
                
                // Kullanıcının mağaza ID'sini otomatik olarak al
                $magaza_id = $kullanici['magaza_id'];
                $magaza_ad = $kullanici['magaza_isim'] ?? '';
                $derece = $_POST['derece'] ?? 'ORTA';

                $data = [
                    'kullanici_id' => $kullanici_id,
                    'baslik' => $baslik,
                    'aciklama' => $aciklama,
                    'magaza_id' => $magaza_id,
                    'magaza' => $magaza_ad,
                    'derece' => $derece,
                    'durum' => 'Yeni',
                    'tarih' => date('Y-m-d H:i:s')
                ];

                // Dosya yükleme
                $uploadedFiles = [];
                if (!empty($_FILES['dosyalar']['name'][0])) {
                    $uploadedFiles = $this->uploadDosyalar(null); // Henüz ID yok
                }

                $isEmriModel = new IsEmriModel();
                $istek_id = $isEmriModel->create($data);

                // Dosyaları güncelle ve ilişkilendir
                if (!empty($uploadedFiles)) {
                    // Dosyaları veritabanına kaydet ve istek_id ile ilişkilendir
                    foreach ($uploadedFiles as &$dosya) {
                        $dosya['istek_id'] = $istek_id;
                    }
                    
                    // Dosyaları JSON olarak kaydet
                    $data['dosyalar_json'] = json_encode($uploadedFiles);
                    
                    // İş emrini dosya bilgileriyle güncelle
                    $isEmriModel->update($istek_id, $data);
                    
                    // Dosya bilgilerini ayrıca kaydet
                    $this->saveDosyaBilgileri($istek_id, $uploadedFiles);
                }

                // Başarılı kayıt sonrası yönlendirme
                $_SESSION['success_message'] = 'İş emri başarıyla oluşturuldu.';
                header('Location: /isemri/listesi');
                exit();

            } catch (\Exception $e) {
                // Hata durumunda kullanıcıya bilgi ver
                $magazaModel = new Magaza();
                $magazalar = $magazaModel->getAll();
                
                $this->view('kullanici/isemri/olustur', [
                    'magazalar' => $magazalar,
                    'hata' => $e->getMessage()
                ]);
                exit();
            }
        } else {
            $magazaModel = new Magaza();
            $magazalar = $magazaModel->getAll();
            
            // Kullanıcı bilgilerini al
            $kullanici_id = $_SESSION['user_id'];
            $kullaniciModel = new Kullanici();
            $kullanici = $kullaniciModel->get($kullanici_id);
            
            $this->view('kullanici/isemri/olustur', [
                'magazalar' => $magazalar,
                'kullanici_magaza_id' => $kullanici['magaza_id']
            ]);
        }
    }

    private function updateDosyalarWithIstekId($istek_id, $dosyalar) {
        $db = Database::getInstance()->getConnection();
        
        // istek_dosyalari tablosundaki kayıtları güncelle
        $updateSql = "UPDATE istek_dosyalari SET istek_id = :istek_id WHERE dosya_yolu = :dosya_yolu";
        $stmt = $db->prepare($updateSql);
        
        foreach ($dosyalar as $dosya) {
            $stmt->execute([
                ':istek_id' => $istek_id,
                ':dosya_yolu' => $dosya['dosya_yolu']
            ]);
        }
    }

    private function uploadDosyalar($istek_id = null) {
        $uploadDir = 'public/uploads/isemri/';
        
        // Dizin yoksa oluştur
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploadedFiles = [];
        $fileCount = count($_FILES['dosyalar']['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['dosyalar']['error'][$i] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['dosyalar']['tmp_name'][$i];
                $originalName = basename($_FILES['dosyalar']['name'][$i]);
                $fileType = $_FILES['dosyalar']['type'][$i];
                $fileSize = $_FILES['dosyalar']['size'][$i];
                
                // Benzersiz dosya adı oluştur
                $fileName = uniqid() . '_' . $originalName;
                $uploadPath = $uploadDir . $fileName;

                if (move_uploaded_file($tmpName, $uploadPath)) {
                    // Dosya bilgilerini kaydet
                    $uploadedFiles[] = [
                        'dosya_adi' => $originalName,
                        'dosya_yolu' => $fileName,
                        'dosya_turu' => $fileType,
                        'boyut' => $fileSize,
                        'istek_id' => $istek_id
                    ];
                }
            }
        }

        return $uploadedFiles;
    }

    private function saveDosyaBilgileri($istek_id, $dosyalar) {
        $db = Database::getInstance()->getConnection();
        
        $dosyaEklemeSql = "INSERT INTO istek_dosyalari (istek_id, dosya_yolu, dosya_adi, dosya_turu, boyut) 
                           VALUES (:istek_id, :dosya_yolu, :dosya_adi, :dosya_turu, :boyut)";
        
        $stmt = $db->prepare($dosyaEklemeSql);
        
        foreach ($dosyalar as $dosya) {
            $stmt->execute([
                ':istek_id' => $istek_id,
                ':dosya_yolu' => $dosya['dosya_yolu'],
                ':dosya_adi' => $dosya['dosya_adi'],
                ':dosya_turu' => $dosya['dosya_turu'],
                ':boyut' => $dosya['boyut']
            ]);
        }
    }

    public function listesi($params = []) {
        $kullaniciModel = new Kullanici();
        $kullanici_id = $_SESSION['user_id'];
        $kullanici = $kullaniciModel->get($kullanici_id);

        $magaza_id = $kullanici['magaza_id'];
       
        $isEmriModel = new IsEmriModel();
        
        // Filtre parametreleri
        $filters = [
            'durum' => $params['durum'] ?? null,
            'derece' => $params['derece'] ?? null,
            'tarih_baslangic' => $params['tarih_baslangic'] ?? null,
            'tarih_bitis' => $params['tarih_bitis'] ?? null
        ];
        
        // Filtrelenmiş iş emirlerini al
        $isEmirleri = $this->filterIsEmirleri($isEmriModel, $magaza_id, $filters);
        
        // Derece seçenekleri
        $dereceler = ['ACİL', 'KRİTİK', 'YÜKSEK', 'ORTA', 'DÜŞÜK', 'İNCELENİYOR'];
        
        $this->view('kullanici/isemri/listesi', [
            'isEmirleri' => $isEmirleri, 
            'seciliDurum' => $filters['durum'],
            'seciliDerece' => $filters['derece'],
            'dereceler' => $dereceler,
            'tarih_baslangic' => $filters['tarih_baslangic'],
            'tarih_bitis' => $filters['tarih_bitis']
        ]);
    }

    private function filterIsEmirleri($isEmriModel, $magaza_id, $filters) {
        // Veritabanı bağlantısını doğrudan Database sınıfından al
        $db = Database::getInstance()->getConnection();

        $query = "SELECT * FROM istekler WHERE magaza_id = :magaza_id";
        $params = [':magaza_id' => $magaza_id];

        // Durum filtresi
        if (!empty($filters['durum'])) {
            $query .= " AND durum = :durum";
            $params[':durum'] = $filters['durum'];
        }

        // Derece filtresi
        if (!empty($filters['derece'])) {
            $query .= " AND derece = :derece";
            $params[':derece'] = $filters['derece'];
        }

        // Tarih aralığı filtresi
        if (!empty($filters['tarih_baslangic'])) {
            $query .= " AND tarih >= :tarih_baslangic";
            $params[':tarih_baslangic'] = $filters['tarih_baslangic'];
        }

        if (!empty($filters['tarih_bitis'])) {
            $query .= " AND tarih <= :tarih_bitis";
            $params[':tarih_bitis'] = $filters['tarih_bitis'];
        }

        $query .= " ORDER BY id DESC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function duzenle($params = []) {
        // Hata ayıklama: Gelen parametreleri logla
        error_log("Düzenleme isteği - Parametreler: " . json_encode($params));

        // ID'yi parametrelerden veya GET'ten al
        $id = is_array($params) && isset($params[0]) ? $params[0] : 
              (isset($params['id']) ? $params['id'] : 
              ($_GET['id'] ?? null));

        // Hata ayıklama: ID'yi logla
        error_log("Çıkarılan ID: " . $id);

        // Oturum kontrolü
        if (!isset($_SESSION['user_id'])) {
            error_log("Oturum açılmamış - Giriş sayfasına yönlendiriliyor");
            header('Location: /auth/giris');
            exit();
        }

        $isEmriModel = new IsEmriModel();
        $magazaModel = new Magaza();
        $kullaniciModel = new Kullanici();
        
        // Kullanıcı bilgilerini al
        $kullanici_id = $_SESSION['user_id'];
        $kullanici = $kullaniciModel->get($kullanici_id);
        
        // Hata ayıklama: Kullanıcı ve mağaza bilgilerini logla
        error_log("Kullanıcı ID: " . $kullanici_id);
        error_log("Kullanıcı Mağaza ID: " . $kullanici['magaza_id']);

        // İş emri ve mağaza bilgilerini al
        $isEmri = $isEmriModel->getByIdAndMagaza($id, $kullanici['magaza_id']);
        $magazalar = $magazaModel->getAll();

        // Hata ayıklama: İş emri bilgilerini logla
        error_log("İş Emri Bilgileri: " . json_encode($isEmri));

        // İş emri bulunamazsa veya yetkisiz erişim varsa
        if (!$isEmri) {
            error_log("İş emri bulunamadı veya erişim izni yok - ID: " . $id);
            $_SESSION['hata'] = 'İş emri bulunamadı veya erişim izniniz yok.';
            header('Location: /isemri/listesi');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Zorunlu alan kontrolü
                if (empty($_POST['baslik']) || empty($_POST['aciklama']) || empty($_POST['magaza_id'])) {
                    throw new \Exception('Zorunlu alanlar boş bırakılamaz.');
                }

                $data = [
                    'baslik' => $_POST['baslik'],
                    'aciklama' => $_POST['aciklama'],
                    'magaza_id' => $_POST['magaza_id'],
                    'derece' => $_POST['derece'] ?? 'ORTA'
                ];

                // Mevcut dosyaları al ve güvenli bir şekilde işle
                $mevcutDosyalar = json_decode($isEmri['dosyalar_json'] ?? '[]', true);
                $mevcutDosyalar = is_array($mevcutDosyalar) ? 
                    array_filter($mevcutDosyalar, function($dosya) {
                        return !empty($dosya['dosya_adi']) && !empty($dosya['dosya_yolu']);
                    }) : 
                    [];

                // Dosya yükleme
                $uploadedFiles = [];
                if (!empty($_FILES['dosyalar']['name'][0])) {
                    $uploadedFiles = $this->uploadDosyalar($id);
                    // Yüklenen dosyaları filtrele
                    $uploadedFiles = array_filter($uploadedFiles, function($dosya) {
                        return !empty($dosya['dosya_adi']) && !empty($dosya['dosya_yolu']);
                    });
                }

                // Mevcut dosyaları ve yeni dosyaları birleştir
                $tumDosyalar = array_merge($mevcutDosyalar, $uploadedFiles);

                // Dosyaları JSON olarak kaydet (boş değilse)
                if (!empty($tumDosyalar)) {
                    $data['dosyalar_json'] = json_encode($tumDosyalar);
                }

                // İş emrini güncelle
                $isEmriModel->update($id, $data);

                // Başarılı güncelleme sonrası yönlendirme
                $_SESSION['success_message'] = 'İş emri başarıyla güncellendi.';
                header('Location: /isemri/listesi');
                exit();

            } catch (\Exception $e) {
                // Hata durumunda kullanıcıya bilgi ver
                $this->view('kullanici/isemri/duzenle', [
                    'isEmri' => $isEmri,
                    'magazalar' => $magazalar,
                    'hata' => $e->getMessage()
                ]);
                exit();
            }
        } else {
            // GET isteği - düzenleme formunu göster
            $this->view('kullanici/isemri/duzenle', [
                'isEmri' => $isEmri,
                'magazalar' => $magazalar
            ]);
        }
    }

    public function sil($params = []) {
        // ID'yi parametrelerden veya GET'ten al
        $id = is_array($params) && isset($params[0]) ? $params[0] : 
              (isset($params['id']) ? $params['id'] : 
              ($_GET['id'] ?? null));

        // Oturum kontrolü
        if (!isset($_SESSION['user_id'])) {
            error_log("Oturum açılmamış - Giriş sayfasına yönlendiriliyor");
            header('Location: /auth/giris');
            exit();
        }

        $isEmriModel = new IsEmriModel();
        $kullaniciModel = new Kullanici();
        
        // Kullanıcı bilgilerini al
        $kullanici_id = $_SESSION['user_id'];
        $kullanici = $kullaniciModel->get($kullanici_id);

        // İş emri bilgilerini al
        $isEmri = $isEmriModel->getByIdAndMagaza($id, $kullanici['magaza_id']);

        // İş emri bulunamazsa veya yetkisiz erişim varsa
        if (!$isEmri) {
            error_log("Silinmek istenen iş emri bulunamadı veya erişim izni yok - ID: " . $id);
            $_SESSION['hata'] = 'İş emri bulunamadı veya erişim izniniz yok.';
            header('Location: /isemri/listesi');
            exit();
        }

        try {
            // Dosyaları sil
            $this->silDosyalar($id);

            // İş emrini sil
            $silindi = $isEmriModel->delete($id);

            if ($silindi) {
                // Başarılı silme sonrası mesaj
                $_SESSION['success_message'] = 'İş emri başarıyla silindi.';
            } else {
                // Silme başarısız olursa
                $_SESSION['hata'] = 'İş emri silinemedi. Lütfen tekrar deneyin.';
            }

            header('Location: /isemri/listesi');
            exit();

        } catch (\Exception $e) {
            // Hata durumunda log ve yönlendirme
            error_log("İş emri silme hatası: " . $e->getMessage());
            $_SESSION['hata'] = 'İş emri silinirken bir hata oluştu.';
            header('Location: /isemri/listesi');
            exit();
        }
    }

    private function silDosyalar($istek_id) {
        $db = Database::getInstance()->getConnection();
        
        // İlk olarak dosya bilgilerini al
        $dosyaSorgu = "SELECT dosya_yolu FROM istek_dosyalari WHERE istek_id = :istek_id";
        $stmt = $db->prepare($dosyaSorgu);
        $stmt->execute([':istek_id' => $istek_id]);
        $dosyalar = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        // Fiziksel dosyaları sil
        $uploadDir = 'public/uploads/isemri/';
        foreach ($dosyalar as $dosya) {
            $dosyaYolu = $uploadDir . $dosya;
            if (file_exists($dosyaYolu)) {
                unlink($dosyaYolu);
            }
        }

        // Veritabanından dosya kayıtlarını sil
        $silSorgu = "DELETE FROM istek_dosyalari WHERE istek_id = :istek_id";
        $stmt = $db->prepare($silSorgu);
        $stmt->execute([':istek_id' => $istek_id]);
    }
}
