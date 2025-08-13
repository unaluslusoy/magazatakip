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
    private \PDO $db;
    private $bildirimModel;
    private $ayarlarModel;
    private $kullaniciModel;
    private $bildirimService;

    public function __construct()
    {
        // 🔒 GÜVENLIK: Admin erişim kontrolü
        AdminMiddleware::handle();
        
        // Veritabanı bağlantısı (core Database üzerinden)
        $this->db = \core\Database::getInstance()->getConnection();
        
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
            $_SESSION['alert_message'] = ['text' => 'Bildirim başarıyla silindi.', 'icon' => 'success', 'confirmButtonText' => 'Tamam'];
        } else {
            $_SESSION['alert_message'] = ['text' => 'Bildirim silinirken bir hata oluştu.', 'icon' => 'error', 'confirmButtonText' => 'Tamam'];
        }

        header('Location: /admin/bildirimler');
        exit();
    }

    public function markAsRead($id)
    {
        if ($this->bildirimModel->markAsRead($id)) {
            $_SESSION['alert_message'] = ['text' => 'Bildirim okundu olarak işaretlendi.', 'icon' => 'success', 'confirmButtonText' => 'Tamam'];
        } else {
            $_SESSION['alert_message'] = ['text' => 'Bildirim işaretlenirken bir hata oluştu.', 'icon' => 'error', 'confirmButtonText' => 'Tamam'];
        }

        header('Location: /admin/bildirimler');
        exit();
    }

    public function bildirimiGonderForm()
    {
        // Yalnızca web push (cihaz_token) uygun kullanıcıları listele
        $kullanicilar = $this->kullaniciModel->getWebPushUygunKullanicilar();
        // Prefill parametreleri
        $prefillAliciTipi = $_GET['alici_tipi'] ?? null;
        $prefillKullaniciId = isset($_GET['kullanici_id']) ? (int)$_GET['kullanici_id'] : null;
        $data = [
            'kullanicilar' => $kullanicilar,
            'aliciTipleri' => ['tum' => 'Tüm Kullanıcılar', 'bireysel' => 'Bireysel', 'grup' => 'Grup'],
            'gonderimKanallari' => ['web' => 'Web', 'mobil' => 'Mobil', 'email' => 'E-posta'],
            'oncelikler' => ['dusuk' => 'Düşük', 'normal' => 'Normal', 'yuksek' => 'Yüksek'],
            'prefillAliciTipi' => $prefillAliciTipi,
            'prefillKullaniciId' => $prefillKullaniciId,
        ];
        $this->view('admin/bildirimler/gonder', $data);
    }

    public function bildirimiGonder()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // OneSignal ayar doğrulaması
                $ayarlar = $this->ayarlarModel->getAyarlar();
                if (empty($ayarlar['onesignal_app_id']) || empty($ayarlar['onesignal_api_key'])) {
                    $_SESSION['alert_message'] = ['text' => 'Bildirim gönderilemedi: OneSignal ayarları eksik.', 'icon' => 'error', 'confirmButtonText' => 'Tamam'];
                    header('Location: /admin/bildirim_gonder');
                    exit();
                }

                $bildirimData = [
                    'kullanici_id' => $_SESSION['user_id'] ?? null,
                    'baslik' => $_POST['baslik'] ?? '',
                    'mesaj' => $_POST['mesaj'] ?? '',
                    'url' => $_POST['url'] ?? '',
                    'icon' => null,
                    'alici_tipi' => $_POST['alici_tipi'] ?? 'tum',
                    'gonderim_kanali' => 'web',
                    'oncelik' => $_POST['oncelik'] ?? 'normal',
                    'etiketler' => null,
                    'ekstra_veri' => json_encode($_POST['ekstra_veri'] ?? []),
                    'durum' => 'beklemede'
                ];

                $secilenKullanicilar = $_POST['kullanicilar'] ?? [];
                $kanalInput = $_POST['gonderim_kanali'] ?? 'web';

                if ($bildirimData['alici_tipi'] == 'tum') {
                    if ($kanalInput === 'web' || $kanalInput === 'mobil') {
                        // Push için yalnızca token'ı olan ve izni açık kullanıcılar
                        $kullanicilar = $this->kullaniciModel->getWebPushUygunKullanicilar();
                    } else {
                        $kullanicilar = $this->kullaniciModel->getBildirimIzinliKullanicilar();
                    }
                } else {
                    if ($kanalInput === 'web' || $kanalInput === 'mobil') {
                        $kullanicilar = $this->kullaniciModel->getSelectedUsersWeb($secilenKullanicilar);
                    } else {
                        $kullanicilar = $this->kullaniciModel->getSelectedUsers($secilenKullanicilar);
                    }
                }

                if (empty($kullanicilar)) {
                    $_SESSION['alert_message'] = ['text' => 'Gönderim iptal: Alıcı bulunamadı (izin yok veya seçim yapılmadı).', 'icon' => 'error', 'confirmButtonText' => 'Tamam'];
                    header('Location: /admin/bildirim_gonder');
                    exit();
                }

                // 1) Her alıcı için tek tek DB kaydı oluştur (durum=beklemede)
                $insertedIds = [];
                $failedInserts = 0;
                foreach ($kullanicilar as $kullanici) {
                    $kanalForUser = $this->resolveChannel($kullanici, $kanalInput);
                    $bildirimData['hedef_kullanici_id'] = $kullanici['id'];
                    $bildirimData['gonderim_kanali'] = $kanalForUser;
                    $insertId = $this->create($bildirimData);
                    if ($insertId) {
                        $insertedIds[] = $insertId;
                    } else {
                        $failedInserts++;
                    }
                }

                // 2) Gönderim: Bireysel ise tekil gönderim + deeplink; değilse toplu
                $kanal = $_POST['gonderim_kanali'] ?? 'mobil';
                $gonderimBasarili = false;
                $sonuc = null;

                if (count($kullanicilar) === 1 && !empty($insertedIds)) {
                    // Tek kullanıcı: detay sayfasına deeplink ver
                    $hedefKullanici = $kullanicilar[0];
                    $bildirimId = $insertedIds[0];
                    $host = $_SERVER['HTTP_HOST'] ?? 'magazatakip.com.tr';
                    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'https';
                    $detayUrl = $scheme . '://' . $host . '/kullanici/bildirimler/detay/' . $bildirimId;
                    // Kullanıcının cihazına göre kanal belirle
                    $kanalForUser = $this->resolveChannel($hedefKullanici, $kanal);
                    $sonuc = $this->bildirimService->tekBildirimGonder(
                        $hedefKullanici,
                        $bildirimData['baslik'],
                        $bildirimData['mesaj'],
                        $kanalForUser,
                        $detayUrl,
                        $bildirimId
                    );

                    if (is_array($sonuc)) {
                        $gonderimBasarili = !empty($sonuc['success']);
                    } elseif ($sonuc === true) {
                        $gonderimBasarili = true;
                    }

                    // Durumu güncelle
                    $stmt = $this->db->prepare("UPDATE bildirimler SET durum = ?, gonderim_kanali = ? WHERE id = ?");
                    $stmt->execute([$gonderimBasarili ? 'gonderildi' : 'basarisiz', $kanalForUser, $bildirimId]);
                } else {
                    // Toplu gönderim (url genel sayfa olabilir)
                    $sonuc = $this->bildirimService->topluBildirimGonder(
                        $kullanicilar,
                        $bildirimData['baslik'],
                        $bildirimData['mesaj'],
                        $kanal,
                        $bildirimData['url']
                    );

                    if (is_array($sonuc)) {
                        $gonderimBasarili = !empty($sonuc['success']);
                    } elseif ($sonuc === true) {
                        $gonderimBasarili = true;
                    }

                    // 3) Sonuca göre tüm eklenen kayıtların durumunu güncelle
                    if (!empty($insertedIds)) {
                        $placeholders = implode(',', array_fill(0, count($insertedIds), '?'));
                        if ($gonderimBasarili) {
                            $stmt = $this->db->prepare("UPDATE bildirimler SET durum = 'gonderildi' WHERE id IN ($placeholders)");
                            $stmt->execute($insertedIds);
                        } else {
                            $stmt = $this->db->prepare("UPDATE bildirimler SET durum = 'basarisiz' WHERE id IN ($placeholders)");
                            $stmt->execute($insertedIds);
                        }
                    }
                }

                // 4) Özet mesaj (insan okunur Türkçe)
                $insertedCount = count($insertedIds);
                $statusText = $gonderimBasarili ? 'başarılı' : 'başarısız';
                $extra = '';
                if (is_array($sonuc)) {
                    if (isset($sonuc['recipients'])) { $extra .= ' | Alıcı sayısı: ' . (int)$sonuc['recipients']; }
                    if (isset($sonuc['http'])) { $extra .= ' | HTTP: ' . (int)$sonuc['http']; }
                }
                $humanSummary = "Toplam {$insertedCount} alıcı için kayıt oluşturuldu. Başarısız kayıt: {$failedInserts}. Gönderim durumu: {$statusText}.{$extra}";

                $_SESSION['alert_message'] = [
                    'text' => $humanSummary,
                    'icon' => (($gonderimBasarili && $failedInserts === 0) ? 'success' : ($gonderimBasarili ? 'warning' : 'error')),
                    'confirmButtonText' => 'Tamam'
                ];

                header('Location: /admin/bildirim_gonder');
                exit();
            } catch (\Exception $e) {
                error_log("Bildirim gönderim hatası: " . $e->getMessage());
                $_SESSION['alert_message'] = ['text' => 'Bildirim gönderimi sırasında bir hata oluştu. Hata: ' . $e->getMessage(), 'icon' => 'error', 'confirmButtonText' => 'Tamam'];
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
            
            if ($stmt->execute($data)) {
                return (int)$this->db->lastInsertId();
            }
            return false;
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

    /**
     * Kullanıcının cihaz/iletişim bilgilerine göre kanal belirle
     */
    private function resolveChannel(array $kullanici, ?string $requested): string
    {
        $req = strtolower((string)($requested ?? ''));
        if (in_array($req, ['web','mobil','email','sms'], true)) {
            return $req;
        }
        $os = strtolower((string)($kullanici['isletim_sistemi'] ?? ''));
        $hasToken = !empty($kullanici['cihaz_token']);
        if ($hasToken) {
            if (strpos($os, 'android') !== false || strpos($os, 'ios') !== false) {
                return 'mobil';
            }
            return 'web';
        }
        if (!empty($kullanici['email'])) { return 'email'; }
        if (!empty($kullanici['telefon'])) { return 'sms'; }
        return 'web';
    }

}
