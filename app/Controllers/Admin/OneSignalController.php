<?php

namespace app\Controllers\Admin;
use core\Controller;
use app\Models\OneSignalAyarlar;
use app\Middleware\AuthMiddleware;

class OneSignalController extends Controller
{
    private $ayarlarModel;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->ayarlarModel = new OneSignalAyarlar();
    }

    public function ayarlar()
    {
        $ayarlar = $this->ayarlarModel->getAyarlar();
        $message = $_SESSION['message'] ?? null;
        $messageType = $_SESSION['message_type'] ?? null;

        // Mesajı gösterdikten sonra session'dan kaldır
        unset($_SESSION['message'], $_SESSION['message_type']);

        $this->view('admin/onesignal/ayarlar', [
            'ayarlar' => $ayarlar,
            'message' => $message,
            'messageType' => $messageType
        ]);
    }

    public function kaydet()
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

            $result = $this->ayarlarModel->ayarlariGuncelle($ayarlar);

            if ($result) {
                $_SESSION['message'] = 'Ayarlar başarıyla güncellendi.';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Ayarlar güncellenirken bir hata oluştu. Lütfen tekrar deneyin.';
                $_SESSION['message_type'] = 'error';
            }

            header('Location: /admin/onesignal/ayarlar');
            exit();
        }
    }
}