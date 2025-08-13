<?php
namespace app\Controllers\Admin;

use app\Models\Istek;
use core\Controller;
use app\Models\Personel;
use app\Models\Magaza;
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

        // Ekler: hem istek_dosyalari hem de dosyalar_json birleştir
        $istekIds = array_map(function($r){ return (int)$r['id']; }, $istekler);
        $attachmentsMap = method_exists($istekModel, 'getAttachmentsByIstekIds') ? $istekModel->getAttachmentsByIstekIds($istekIds) : [];
        foreach ($istekler as &$row) {
            $merged = $attachmentsMap[$row['id']] ?? [];
            if (!empty($row['dosyalar_json'])) {
                $json = json_decode($row['dosyalar_json'], true);
                if (is_array($json)) {
                    foreach ($json as $file) {
                        if (!empty($file['dosya_yolu'])) {
                            $merged[] = [
                                'dosya_yolu' => $file['dosya_yolu'],
                                'dosya_adi' => $file['dosya_adi'] ?? basename($file['dosya_yolu']),
                                'dosya_turu' => $file['dosya_turu'] ?? 'image/*',
                                'boyut' => $file['boyut'] ?? 0
                            ];
                        }
                    }
                }
            }
            $row['attachments'] = $merged;
        }
        unset($row);

        $personelModel = new Personel();
        $personeller = $personelModel->getAll(); // Personelleri alın

        // Mağaza listesini getir (filtre için)
        $magazaModel = new Magaza();
        $magazalar = $magazaModel->getAll();

        $this->view('admin/istek_listesi', [
            'istekler' => $istekler,
            'personeller' => $personeller,
            'magazalar' => $magazalar
        ]);
    }

    public function guncelle($id) {
        $istekModel = new Istek();
        $personelModel = new Personel();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Boş tarihleri NULL'a çevir ve formatı normalize et
            $baslangic = trim($_POST['baslangic_tarihi'] ?? '');
            $bitis = trim($_POST['bitis_tarihi'] ?? '');
            $baslangic = $baslangic === '' ? null : $baslangic;
            $bitis = $bitis === '' ? null : $bitis;

            $personelIdRaw = trim($_POST['personel_id'] ?? '');
            $personelId = $personelIdRaw === '' ? null : $personelIdRaw;
            $data = [
                'durum' => $_POST['durum'] ?? null,
                'personel_id' => $personelId,
                'is_aciklamasi' => $_POST['is_aciklamasi'] ?? null,
                'baslangic_tarihi' => $baslangic,
                'bitis_tarihi' => $bitis
            ];
            $isAjax = (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest')
                || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
            try {
                $ok = $istekModel->update($id, $data);
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => (bool)$ok,
                        'message' => $ok ? 'İstek başarıyla güncellendi.' : 'Güncelleme yapılamadı.'
                    ]);
                    exit();
                } else {
                    $_SESSION['alert_message'] = [
                        'text' => $ok ? 'İstek başarıyla güncellendi.' : 'Güncelleme yapılamadı.',
                        'icon' => $ok ? 'success' : 'error',
                        'confirmButtonText' => 'Tamam'
                    ];
                }
            } catch (\Throwable $e) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Hata: ' . htmlspecialchars($e->getMessage())
                    ]);
                    exit();
                } else {
                    $_SESSION['alert_message'] = [
                        'text' => 'Hata: ' . htmlspecialchars($e->getMessage()),
                        'icon' => 'error',
                        'confirmButtonText' => 'Tamam'
                    ];
                }
            }
            header('Location: /admin/istekler');
            exit();
        } else {
            $istek = $istekModel->get($id);
            // Tek kayıt için ekleri birleştir
            $merged = method_exists($istekModel, 'getDosyalar') ? $istekModel->getDosyalar((int)$id) : [];
            if (!empty($istek['dosyalar_json'])) {
                $json = json_decode($istek['dosyalar_json'], true);
                if (is_array($json)) {
                    foreach ($json as $file) {
                        if (!empty($file['dosya_yolu'])) {
                            $merged[] = [
                                'dosya_yolu' => $file['dosya_yolu'],
                                'dosya_adi' => $file['dosya_adi'] ?? basename($file['dosya_yolu']),
                                'dosya_turu' => $file['dosya_turu'] ?? 'image/*',
                                'boyut' => $file['boyut'] ?? 0
                            ];
                        }
                    }
                }
            }
            $istek['attachments'] = $merged;
            $personeller = $personelModel->getAll();
            $this->view('admin/istek_detay', ['istek' => $istek, 'personeller' => $personeller]);
        }
    }
    public function ekle() {
        $istekModel = new Istek();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Boş tarihleri NULL'a çevir
            $baslangic = trim($_POST['baslangic_tarihi'] ?? '');
            $bitis = trim($_POST['bitis_tarihi'] ?? '');
            $baslangic = $baslangic === '' ? null : $baslangic;
            $bitis = $bitis === '' ? null : $bitis;

            $personelIdRaw = trim($_POST['personel_id'] ?? '');
            $personelId = $personelIdRaw === '' ? null : $personelIdRaw;
            $data = [
                'kullanici_id' => $_POST['kullanici_id'],
                'baslik' => $_POST['baslik'],
                'aciklama' => $_POST['aciklama'],
                'magaza' => $_POST['magaza'],
                'derece' => $_POST['derece'],
                'personel_id' => $personelId,
                'is_aciklamasi' => $_POST['is_aciklamasi'],
                'baslangic_tarihi' => $baslangic,
                'bitis_tarihi' => $bitis
            ];
            $isAjax = (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest')
                || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
            try {
                $ok = $istekModel->create($data);
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => (bool)$ok,
                        'id' => $ok ? (int)$ok : null,
                        'message' => $ok ? 'İstek başarıyla eklendi.' : 'İstek eklenemedi.'
                    ]);
                    exit();
                } else {
                    $_SESSION['alert_message'] = [
                        'text' => $ok ? 'İstek başarıyla eklendi.' : 'İstek eklenemedi.',
                        'icon' => $ok ? 'success' : 'error',
                        'confirmButtonText' => 'Tamam'
                    ];
                }
            } catch (\Throwable $e) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Hata: ' . htmlspecialchars($e->getMessage())
                    ]);
                    exit();
                } else {
                    $_SESSION['alert_message'] = [
                        'text' => 'Hata: ' . htmlspecialchars($e->getMessage()),
                        'icon' => 'error',
                        'confirmButtonText' => 'Tamam'
                    ];
                }
            }
            header('Location: /admin/istekler');
            exit();
        } else {
            $this->view('admin/istek_ekle');
        }
    }
    public function sil($id) {
        $istekModel = new Istek();
        try {
            $ok = $istekModel->delete($id);
            $_SESSION['alert_message'] = [
                'text' => $ok ? 'İstek silindi.' : 'Silme işlemi başarısız.',
                'icon' => $ok ? 'success' : 'error',
                'confirmButtonText' => 'Tamam'
            ];
        } catch (\Throwable $e) {
            $_SESSION['alert_message'] = [
                'text' => 'Hata: ' . htmlspecialchars($e->getMessage()),
                'icon' => 'error',
                'confirmButtonText' => 'Tamam'
            ];
        }
        header('Location: /admin/istekler');
        exit();
    }
}
