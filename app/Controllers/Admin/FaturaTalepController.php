<?php
namespace app\Controllers\Admin;

use core\Controller;
use app\Middleware\AdminMiddleware;
use app\Models\AdminFaturaTalep;
use app\Models\Magaza;
use app\Middleware\AuthMiddleware;
use app\Services\WhatsAppService;

class FaturaTalepController extends Controller
{
    private $faturaTalepModel;
    private $magazaModel;
    private $whatsAppService;
    public function __construct()
    {
        // 🔒 GÜVENLIK: Admin erişim kontrolü
        AdminMiddleware::handle();
        $this->faturaTalepModel = new AdminFaturaTalep();
        $this->magazaModel = new Magaza();
        $this->whatsAppService = new WhatsAppService();
    }

    public function listesi()
    {
        $faturaTalepleri = $this->faturaTalepModel->getAll();
        $data = [
            'faturaTalepleri' => $faturaTalepleri,
            'noDataMessage' => empty($faturaTalepleri) ? 'Eklenmiş fatura talebi bulunmamaktadır.' : null,
        ];
        $this->view('/admin/fatura_talep/listesi', $data);
    }
    public function getFaturaDetails($id)
    {
        $fatura = $this->faturaTalepModel->getById($id);
        if ($fatura) {
            echo json_encode(['success' => true, 'fatura' => $fatura]);
        } else {
            echo json_encode(['success' => false]);
        }
    }


    public function sendWhatsapp()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $musteriTelefon = $data['musteri_telefon'] ?? '';
            $faturaPdf = $data['fatura_pdf_path'] ?? '';
            $musteriAd = $data['musteri_ad'] ?? '';

            if ($musteriTelefon && $faturaPdf) {
                $whatsappService = new WhatsAppService();
                $message = "Merhaba $musteriAd,\nFaturanızı ekte bulabilirsiniz.";
                $pdfUrl = "https://magazatakip.com.tr/public/uploads/" . $faturaPdf;

                $result = $whatsappService->sendMessage($musteriTelefon, $message, $pdfUrl);

                echo json_encode(['success' => $result]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Eksik bilgi.']);
            }
        }
    }
    public function duzenle($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'magaza_id' => $_POST['magaza_id'] ?? null,
                'magaza_ad' => $this->getMagazaAdById($_POST['magaza_id'] ?? null),
                'musteri_ad' => $_POST['musteri_ad'] ?? null,
                'musteri_adres' => $_POST['musteri_adres'] ?? null,
                'musteri_vergi_no' => $_POST['musteri_vergi_no'] ?? null,
                'musteri_vergi_dairesi' => $_POST['musteri_vergi_dairesi'] ?? null,
                'musteri_telefon' => $_POST['musteri_telefon'] ?? null,
                'musteri_email' => $_POST['musteri_email'] ?? null,
                'aciklama' => $_POST['aciklama'] ?? null,
                'durum' => $_POST['durum'] ?? 'Yeni',
                'fatura_pdf_path' => null
            ];

            // Mevcut fatura talebini al
            $faturaTalep = $this->faturaTalepModel->getById($id);

            // Dosya yükleme işlemi
            if (isset($_FILES['fatura_pdf']) && $_FILES['fatura_pdf']['error'] == 0) {
                $uploadResult = $this->uploadFile($_FILES['fatura_pdf'], $data['musteri_ad'], $id);
                if ($uploadResult) {
                    // Eski dosya varsa sil
                    if (!empty($faturaTalep['fatura_pdf_path']) && file_exists(__DIR__ . '/../../../public/uploads/' . $faturaTalep['fatura_pdf_path'])) {
                        unlink(__DIR__ . '/../../../public/uploads/' . $faturaTalep['fatura_pdf_path']);
                    }
                    $data['fatura_pdf_path'] = $uploadResult;
                } else {
                    $_SESSION['message'] = "Dosya yüklenirken bir hata oluştu.";
                    $_SESSION['message_type'] = 'error';
                    header('Location: /admin/fatura_talep/duzenle/' . $id);
                    exit();
                }
            } else {
                // Yeni dosya yüklenmediyse, eski dosya yolunu koru
                $data['fatura_pdf_path'] = $faturaTalep['fatura_pdf_path'] ?? null;
            }

            if (!$data['magaza_id'] || !$data['magaza_ad'] || !$data['musteri_ad'] || !$data['musteri_adres'] || !$data['musteri_vergi_no'] || !$data['musteri_vergi_dairesi']) {
                $_SESSION['message'] = "Lütfen tüm alanları doldurunuz.";
                $_SESSION['message_type'] = 'error';
                header('Location: /admin/fatura_talep/duzenle/' . $id);
                exit();
            }

            $this->faturaTalepModel->update($id, $data);
            $_SESSION['message'] = "Fatura talebi başarıyla güncellendi.";
            $_SESSION['message_type'] = 'success';
            header('Location: /admin/fatura_talep/listesi');
            exit();
        } else {
            $faturaTalep = $this->faturaTalepModel->getById($id);
            $magazaModel = new Magaza();
            $magazalar = $magazaModel->getAll();
            $this->view('/admin/fatura_talep/duzenle', ['faturaTalep' => $faturaTalep, 'magazalar' => $magazalar]);
        }
    }

    private function uploadFile($file, $musteriAd, $id)
    {
        $uploadDir = __DIR__ . '/../../../public/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = $this->sanitizeFileName($musteriAd . '_' . $id . '_' . $file['name']);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            return $fileName;
        }
        return null;
    }

    private function sanitizeFileName($fileName)
    {
        // Türkçe karakterleri ve boşlukları temizle
        $fileName = strtr($fileName, 'ĞÜŞİÖÇğüşiöç ', 'GUSIOCgusioc_');
        $fileName = preg_replace('/[^a-zA-Z0-9-_\.]/', '', $fileName);
        return $fileName;
    }
    public function sil($id)
    {
        $faturaTalep = $this->faturaTalepModel->getById($id);
        if ($faturaTalep) {
            $pdfPath = $faturaTalep['fatura_pdf_path'];
            if ($this->faturaTalepModel->delete($id)) {
                if ($pdfPath && file_exists(__DIR__ . '/../../../public/uploads/' . $pdfPath)) {
                    unlink(__DIR__ . '/../../../public/uploads/' . $pdfPath);
                }
                $_SESSION['message'] = "Fatura talebi ve ilgili dosya başarıyla silindi.";
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = "Fatura talebi silinirken bir hata oluştu.";
                $_SESSION['message_type'] = 'error';
            }
        } else {
            $_SESSION['message'] = "Fatura talebi bulunamadı.";
            $_SESSION['message_type'] = 'error';
        }

        header('Location: /admin/fatura_talep/listesi');
        exit();
    }



    private function getMagazaAdById($magazaId)
    {
        $magaza = $this->magazaModel->getById($magazaId);
        return $magaza ? $magaza['ad'] : null;
    }


}
