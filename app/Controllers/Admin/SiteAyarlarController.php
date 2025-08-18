<?php

namespace app\Controllers\Admin;

use core\Controller;
use app\Models\OneSignalAyarlar;
use app\Models\MailAyarlar;
use app\Services\MailService;
use app\Models\SiteAyarlar;
use app\Middleware\AdminMiddleware;

class SiteAyarlarController extends Controller
{
    private $oneSignalAyarlarModel;
    private $siteAyarlarModel;
    private $mailAyarlarModel;
    private $appLogConfigPath;
    private $rateLimitConfigPath;

    public function __construct()
    {
        // 🔒 GÜVENLIK: Admin erişim kontrolü
        AdminMiddleware::handle();
        $this->oneSignalAyarlarModel = new OneSignalAyarlar();
        $this->siteAyarlarModel = new SiteAyarlar();
        $this->mailAyarlarModel = new MailAyarlar();
        $this->appLogConfigPath = __DIR__ . '/../../../config/app_logging.php';
        $this->rateLimitConfigPath = __DIR__ . '/../../../config/rate_limit.php';
    }

    public function index()
    {
        $oneSignalAyarlar = $this->oneSignalAyarlarModel->getAyarlar();
        $mailAyarlar = $this->mailAyarlarModel->getAyarlar();
        $siteAyarlar = $this->siteAyarlarModel->getAyarlar();
        $logConfig = file_exists($this->appLogConfigPath) ? (require $this->appLogConfigPath) : ['enabled'=>true,'router'=>false,'slow'=>true];
        $rateLimit = file_exists($this->rateLimitConfigPath) ? (require $this->rateLimitConfigPath) : [
            'enabled'=>true,
            'window_seconds'=>60,
            'default'=>['max_requests'=>60],
            'overrides'=>['api/auth/login'=>['max_requests'=>10]]
        ];
        
        $message = $_SESSION['message'] ?? null;
        $messageType = $_SESSION['message_type'] ?? null;

        // Mesajı gösterdikten sonra session'dan kaldır
        unset($_SESSION['message'], $_SESSION['message_type']);

        $this->view('admin/site_ayarlar/index', [
            'oneSignalAyarlar' => $oneSignalAyarlar,
            'siteAyarlar' => $siteAyarlar,
            'mailAyarlar' => $mailAyarlar,
            'logAyarlar' => $logConfig,
            'rateLimit' => $rateLimit,
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
                $_SESSION['message_type'] = 'danger';
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
                $_SESSION['message_type'] = 'danger';
            }

            header('Location: /admin/site-ayarlar#genel');
            exit();
        }
    }

    public function logAyarKaydet()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $enabled = isset($_POST['log_enabled']);
            $router = isset($_POST['log_router']);
            $slow = isset($_POST['log_slow']);

            $content = "<?php\n\nreturn [\n    'enabled' => " . ($enabled ? 'true' : 'false') . ",\n    'router' => " . ($router ? 'true' : 'false') . ",\n    'slow' => " . ($slow ? 'true' : 'false') . ",\n];\n";

            try {
                file_put_contents($this->appLogConfigPath, $content);
                $_SESSION['message'] = 'Log ayarları güncellendi.';
                $_SESSION['message_type'] = 'success';
            } catch (\Throwable $e) {
                $_SESSION['message'] = 'Log ayarları kaydedilemedi: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }

            header('Location: /admin/site-ayarlar#genel');
            exit();
        }
    }

    public function rateLimitKaydet()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $enabled = isset($_POST['rl_enabled']);
            $window = isset($_POST['rl_window']) ? max(1, (int)$_POST['rl_window']) : 60;
            $defaultMax = isset($_POST['rl_default_max']) ? max(1, (int)$_POST['rl_default_max']) : 60;
            $loginMax = isset($_POST['rl_login_max']) ? max(1, (int)$_POST['rl_login_max']) : 10;

            $content = "<?php\n\nreturn [\n    'enabled' => " . ($enabled ? 'true' : 'false') . ",\n    'window_seconds' => " . $window . ",\n    'default' => [ 'max_requests' => " . $defaultMax . " ],\n    'overrides' => [ 'api/auth/login' => [ 'max_requests' => " . $loginMax . " ] ],\n];\n";

            try {
                file_put_contents($this->rateLimitConfigPath, $content);
                $_SESSION['message'] = 'Rate limit ayarları güncellendi.';
                $_SESSION['message_type'] = 'success';
            } catch (\Throwable $e) {
                $_SESSION['message'] = 'Rate limit ayarları kaydedilemedi: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }

            header('Location: /admin/site-ayarlar#genel');
            exit();
        }
    }

    public function mailKaydet()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log('MailKaydet POST alındı: ' . json_encode(array_keys($_POST)));
            $ayarlar = [
                'smtp_driver' => $_POST['smtp_driver'] ?? 'smtp',
                'smtp_host' => $_POST['smtp_host'] ?? '',
                'smtp_port' => isset($_POST['smtp_port']) ? (int)$_POST['smtp_port'] : null,
                'smtp_encryption' => $_POST['smtp_encryption'] ?? '',
                'smtp_username' => $_POST['smtp_username'] ?? '',
                // Parola boş gönderildiyse mevcut değeri koru
                'smtp_password' => ($_POST['smtp_password'] ?? '') !== '' ? $_POST['smtp_password'] : ($this->mailAyarlarModel->getAyarlar()['smtp_password'] ?? ''),
                'from_email' => $_POST['from_email'] ?? '',
                'from_name' => $_POST['from_name'] ?? '',
                'reply_to_email' => $_POST['reply_to_email'] ?? ''
            ];

            $result = $this->mailAyarlarModel->ayarlariGuncelle($ayarlar);
            error_log('MailKaydet DB sonucu: ' . var_export($result, true));

            if ($result) {
                $_SESSION['message'] = 'Mail ayarları başarıyla güncellendi.';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Mail ayarları güncellenirken bir hata oluştu.';
                $_SESSION['message_type'] = 'danger';
            }

            header('Location: /admin/site-ayarlar#mail');
            exit();
        }
    }

    public function mailTestGonder()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $to = $_POST['test_email'] ?? '';
            if (empty($to)) {
                $_SESSION['message'] = 'Test e-posta adresi gerekli.';
                $_SESSION['message_type'] = 'danger';
                header('Location: /admin/site-ayarlar#mail');
                exit();
            }

            try {
                $service = new MailService();
                $ok = $service->send($to, $to, 'MagazaTakip Test Mail', '<p>Bu bir test iletisidir.</p>');
                if ($ok) {
                    $_SESSION['message'] = 'Test e-postası gönderildi: ' . htmlspecialchars($to);
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = 'Test e-postası gönderilemedi. Lütfen SMTP bilgilerini kontrol edin.';
                    $_SESSION['message_type'] = 'danger';
                }
            } catch (\Throwable $t) {
                $_SESSION['message'] = 'Test e-postası hata: ' . $t->getMessage();
                $_SESSION['message_type'] = 'danger';
            }

            header('Location: /admin/site-ayarlar#mail');
            exit();
        }
    }
} 