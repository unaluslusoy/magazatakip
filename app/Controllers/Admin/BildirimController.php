<?php
namespace app\Controllers\Admin;

use core\Controller;
use app\Models\OneSignalAyarlar;
use app\Models\Kullanici;
use app\Services\BildirimService;
use app\Middleware\AdminMiddleware;
use app\Models\Bildirim;

class BildirimController extends Controller
{
    private $bildirimModel;
    private $ayarlarModel;
    private $kullaniciModel;
    private $bildirimService;

    public function __construct()
    {
        // 🔒 GÜVENLIK: Admin erişim kontrolü
        AdminMiddleware::handle();
        
        // Veritabanı bağlantısı
        $config = require 'config/database.php';
        $this->db = new \PDO(
            "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
            $config['username'],
            $config['password'],
            [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"]
        );
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        $this->ayarlarModel = new OneSignalAyarlar();
        $this->kullaniciModel = new Kullanici();
        $this->bildirimService = new BildirimService();
        $this->bildirimModel = new Bildirim();
    }

    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = $_GET['per_page'] ?? 10;
        $dateFilter = $_GET['date_filter'] ?? 'all';
        $aliciFilter = $_GET['alici_filter'] ?? 'all';
        $durumFilter = $_GET['durum_filter'] ?? 'all';
        $searchTerm = $_GET['search'] ?? '';

        $data = [
            'title' => 'Gönderilen Bildirimler',
            'link' => 'Bildirimler',
            'page' => $page,
            'perPage' => $perPage,
            'dateFilter' => $dateFilter,
            'aliciFilter' => $aliciFilter,
            'durumFilter' => $durumFilter,
            'searchTerm' => $searchTerm,
            'bildirimler' => $this->bildirimModel->getPaginatedNotifications($page, $perPage, $dateFilter, $aliciFilter, $durumFilter, $searchTerm),
            'notificationCounts' => $this->bildirimModel->getNotificationCounts()
        ];

        $this->view('admin/bildirimler/index', $data);
    }

    public function delete($id)
    {
        if ($this->bildirimModel->deleteBildirim($id)) {
            $_SESSION['message'] = "Bildirim başarıyla silindi.";
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = "Bildirim silinirken bir hata oluştu.";
            $_SESSION['message_type'] = 'error';
        }

        header('Location: /admin/bildirimler');
        exit();
    }

    public function markAsRead($id)
    {
        if ($this->bildirimModel->markAsRead($id)) {
            $_SESSION['message'] = "Bildirim okundu olarak işaretlendi.";
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = "Bildirim işaretlenirken bir hata oluştu.";
            $_SESSION['message_type'] = 'error';
        }

        header('Location: /admin/bildirimler');
        exit();
    }

    public function bildirimiGonderForm()
    {
        $kullanicilar = $this->kullaniciModel->getAllUsers();
        $data = [
            'kullanicilar' => $kullanicilar,
            'aliciTipleri' => ['tum' => 'Tüm Kullanıcılar', 'bireysel' => 'Bireysel', 'grup' => 'Grup'],
            'gonderimKanallari' => ['web' => 'Web', 'mobil' => 'Mobil', 'email' => 'E-posta'],
            'oncelikler' => ['dusuk' => 'Düşük', 'normal' => 'Normal', 'yuksek' => 'Yüksek']
        ];
        $this->view('admin/bildirimler/gonder', $data);
    }

    public function bildirimiGonder()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $bildirimData = [
                    'kullanici_id' => $_SESSION['user_id'] ?? null,
                    'baslik' => $_POST['baslik'] ?? '',
                    'mesaj' => $_POST['mesaj'] ?? '',
                    'url' => $_POST['url'] ?? '',
                    'icon' => $_POST['icon'] ?? null,
                    'alici_tipi' => $_POST['alici_tipi'] ?? 'tum',
                    'gonderim_kanali' => $_POST['gonderim_kanali'] ?? 'web',
                    'oncelik' => $_POST['oncelik'] ?? 'normal',
                    'etiketler' => $_POST['etiketler'] ?? null,
                    'ekstra_veri' => json_encode($_POST['ekstra_veri'] ?? []),
                    'durum' => 'beklemede'
                ];

                $secilenKullanicilar = $_POST['kullanicilar'] ?? [];

                if ($bildirimData['alici_tipi'] == 'tum') {
                    $kullanicilar = $this->kullaniciModel->getBildirimIzinliKullanicilar();
                } else {
                    $kullanicilar = $this->kullaniciModel->getSelectedUsers($secilenKullanicilar);
                }

                $basarili = 0;
                $hatali = 0;
                $hataliTokenlar = [];

                foreach ($kullanicilar as $kullanici) {
                    $bildirimData['hedef_kullanici_id'] = $kullanici['id'];

                    if ($this->create($bildirimData)) {
                        $basarili++;

                        // Bildirimi gerçek zamanlı gönderme işlemi
                        $sonuc = $this->bildirimService->tekBildirimGonder(
                            $kullanici,
                            $bildirimData['baslik'],
                            $bildirimData['mesaj'],
                            $bildirimData['gonderim_kanali'],
                            $bildirimData['url']
                        );

                        if (!$sonuc) {
                            $hatali++;
                            $hataliTokenlar[] = $kullanici['cihaz_token']; // Hatalı tokenı ekle
                            
                            // Bildirim durumunu başarısız olarak güncelle
                            $this->db->prepare("UPDATE bildirimler SET durum = 'basarisiz' WHERE hedef_kullanici_id = ? AND gonderim_tarihi = ?")->execute([$kullanici['id'], $data['gonderim_tarihi']]);
                        } else {
                            // Bildirim durumunu gönderildi olarak güncelle
                            $this->db->prepare("UPDATE bildirimler SET durum = 'gonderildi' WHERE hedef_kullanici_id = ? AND gonderim_tarihi = ?")->execute([$kullanici['id'], $data['gonderim_tarihi']]);
                        }
                    } else {
                        $hatali++;
                    }
                }

                if ($basarili > 0) {
                    $_SESSION['message'] = "{$basarili} bildirim başarıyla gönderildi. {$hatali} bildirim gönderilemedi.";
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = "Bildirim gönderilemedi. Lütfen ayarlarınızı kontrol edin.";
                    $_SESSION['message_type'] = 'error';
                }

                // Hatalı tokenları bir alert ile göster
                if (!empty($hataliTokenlar)) {
                    echo '<script>alert("Hatalı Tokenlar: ' . implode(', ', $hataliTokenlar) . '");</script>';
                }

                header('Location: /admin/bildirim_gonder');
                exit();
            } catch (\Exception $e) {
                error_log("Bildirim gönderim hatası: " . $e->getMessage());
                $_SESSION['message'] = "Bildirim gönderimi sırasında bir hata oluştu. Hata: " . $e->getMessage();
                $_SESSION['message_type'] = 'error';
                header('Location: admin/bildirim_gonder');
                exit();
            }
        }
    }
    public function bildirimiListele()
    {
        $bildirimler = $this->kullaniciModel->getBildirimler();
        $this->view('/admin/onesignal/bildirim_listele', ['bildirimler' => $bildirimler]);
    }

    public function create($data)
    {
        try {
            $data['gonderim_tarihi'] = date('Y-m-d H:i:s');
            
            $columns = implode(", ", array_keys($data));
            $placeholders = ":" . implode(", :", array_keys($data));
            
            $sql = "INSERT INTO bildirimler ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($data);
        } catch (\PDOException $e) {
            error_log("Bildirim oluşturma hatası: " . $e->getMessage());
            return false;
        }
    }

    public function detay($id)
    {
        $bildirim = $this->bildirimModel->getBildirimById($id);
        if (!$bildirim) {
            $_SESSION['message'] = "Bildirim bulunamadı.";
            $_SESSION['message_type'] = 'error';
            header('Location: /admin/bildirimler');
            exit();
        }

        $data = [
            'title' => 'Bildirim Detayı',
            'link' => 'Bildirim Detayı',
            'bildirim' => $bildirim
        ];

        $this->view('admin/bildirimler/detay', $data);
    }

}
