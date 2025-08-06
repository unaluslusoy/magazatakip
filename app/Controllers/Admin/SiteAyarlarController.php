<?php

namespace app\Controllers\Admin;

use core\Controller;
use app\Models\OneSignalAyarlar;
use app\Models\SiteAyarlar;
use app\Middleware\AdminMiddleware;

class SiteAyarlarController extends Controller
{
    private $oneSignalAyarlarModel;
    private $siteAyarlarModel;

    public function __construct()
    {
        // 🔒 GÜVENLIK: Admin erişim kontrolü
        AdminMiddleware::handle();
        $this->oneSignalAyarlarModel = new OneSignalAyarlar();
        $this->siteAyarlarModel = new SiteAyarlar();
    }

    public function index()
    {
        $oneSignalAyarlar = $this->oneSignalAyarlarModel->getAyarlar();
        $siteAyarlar = $this->siteAyarlarModel->getAyarlar();
        
        $message = $_SESSION['message'] ?? null;
        $messageType = $_SESSION['message_type'] ?? null;

        // Mesajı gösterdikten sonra session'dan kaldır
        unset($_SESSION['message'], $_SESSION['message_type']);

        $this->view('admin/site_ayarlar/index', [
            'oneSignalAyarlar' => $oneSignalAyarlar,
            'siteAyarlar' => $siteAyarlar,
            'message' => $message,
            'messageType' => $messageType
        ]);
    }

    public function oneSignalKaydet()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ayarlar = [
                'onesignal_app_id' => $_POST['onesignal_app_id'] ?? '',
                'onesignal_api_key' => $_POST['onesignal_api_key'] ?? '',
                'twilio_sid' => $_POST['twilio_sid'] ?? '',
                'twilio_token' => $_POST['twilio_token'] ?? '',
                'twilio_phone' => $_POST['twilio_phone'] ?? '',
                'sendgrid_api_key' => $_POST['sendgrid_api_key'] ?? '',
                'sendgrid_from_email' => $_POST['sendgrid_from_email'] ?? ''
            ];

            $result = $this->oneSignalAyarlarModel->ayarlariGuncelle($ayarlar);

            if ($result) {
                $_SESSION['message'] = 'OneSignal ayarları başarıyla güncellendi.';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'OneSignal ayarları güncellenirken bir hata oluştu.';
                $_SESSION['message_type'] = 'error';
            }

            header('Location: /admin/site-ayarlar#onesignal');
            exit();
        }
    }

    public function siteAyarlarKaydet()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ayarlar = [
                'site_adi' => $_POST['site_adi'] ?? '',
                'site_aciklama' => $_POST['site_aciklama'] ?? '',
                'site_keywords' => $_POST['site_keywords'] ?? '',
                'site_logo' => $_POST['site_logo'] ?? '',
                'site_favicon' => $_POST['site_favicon'] ?? '',
                'iletisim_email' => $_POST['iletisim_email'] ?? '',
                'iletisim_telefon' => $_POST['iletisim_telefon'] ?? '',
                'iletisim_adres' => $_POST['iletisim_adres'] ?? '',
                'sosyal_medya_facebook' => $_POST['sosyal_medya_facebook'] ?? '',
                'sosyal_medya_twitter' => $_POST['sosyal_medya_twitter'] ?? '',
                'sosyal_medya_instagram' => $_POST['sosyal_medya_instagram'] ?? '',
                'sosyal_medya_linkedin' => $_POST['sosyal_medya_linkedin'] ?? '',
                'bakim_modu' => isset($_POST['bakim_modu']) ? 1 : 0,
                'bakim_mesaji' => $_POST['bakim_mesaji'] ?? ''
            ];

            $result = $this->siteAyarlarModel->ayarlariGuncelle($ayarlar);

            if ($result) {
                $_SESSION['message'] = 'Site ayarları başarıyla güncellendi.';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Site ayarları güncellenirken bir hata oluştu.';
                $_SESSION['message_type'] = 'error';
            }

            header('Location: /admin/site-ayarlar#genel');
            exit();
        }
    }
} 