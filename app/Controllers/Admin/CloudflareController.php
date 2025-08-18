<?php

namespace app\Controllers\Admin;

use core\Controller;
use app\Middleware\AdminMiddleware;

class CloudflareController extends Controller
{
    private $configPath;

    public function __construct()
    {
        AdminMiddleware::handle();
        $this->configPath = __DIR__ . '/../../../config/cloudflare.php';
    }

    public function index()
    {
        $config = file_exists($this->configPath) ? (require $this->configPath) : [
            'enabled' => true, 'real_ip_header' => 'CF-Connecting-IP', 'show_recommendations' => true
        ];
        // Flash messages
        $message = $_SESSION['message'] ?? null;
        $messageType = $_SESSION['message_type'] ?? null;
        unset($_SESSION['message'], $_SESSION['message_type']);
        // API testleri (best-effort)
        $api = [ 'token_set' => !empty($config['api_token']), 'zone_id_set' => !empty($config['zone_id']) ];
        $zoneDetails = null; $devMode = null; $dns = null; $zones = null;
        if (!empty($config['api_token'])) {
            try {
                require_once __DIR__ . '/../../Services/CloudflareApi.php';
                $cf = new \app\Services\CloudflareApi($config['api_token']);
                if (!empty($config['account_id']) && empty($config['zone_id'])) {
                    $zones = $cf->listZones($config['account_id']);
                }
                if (!empty($config['zone_id'])) {
                    $zoneDetails = $cf->zoneDetails($config['zone_id']);
                    $devMode = $cf->getDevelopmentMode($config['zone_id']);
                    $dns = $cf->listDnsRecords($config['zone_id']);
                }
            } catch (\Throwable $e) { /* sessiz */ }
        }
        $this->view('admin/cloudflare/index', [
            'config' => $config,
            'api' => $api,
            'zones' => $zones,
            'zoneDetails' => $zoneDetails,
            'devMode' => $devMode,
            'dns' => $dns,
            'message' => $message,
            'messageType' => $messageType,
            'server' => [
                'cf_connecting_ip' => $_SERVER['HTTP_CF_CONNECTING_IP'] ?? null,
                'true_client_ip' => $_SERVER['HTTP_TRUE_CLIENT_IP'] ?? null,
                'x_forwarded_for' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
                'cf_visitor' => $_SERVER['HTTP_CF_VISITOR'] ?? null,
                'proto' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? (($_SERVER['HTTPS'] ?? '') !== 'off' ? 'https' : 'http'),
                'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]
        ]);
    }

    public function save()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') { header('Location: /admin/cloudflare'); return; }
        $enabled = isset($_POST['enabled']);
        $real = trim((string)($_POST['real_ip_header'] ?? ''));
        $show = isset($_POST['show_recommendations']);
        $apiToken = trim((string)($_POST['api_token'] ?? ''));
        $accountId = trim((string)($_POST['account_id'] ?? ''));
        $zoneId = trim((string)($_POST['zone_id'] ?? ''));

        $content = "<?php\n\nreturn [\n    'enabled' => " . ($enabled ? 'true' : 'false') . ",\n    'real_ip_header' => '" . addslashes($real ?: 'CF-Connecting-IP') . "',\n    'show_recommendations' => " . ($show ? 'true' : 'false') . ",\n    'api_token' => '" . addslashes($apiToken) . "',\n    'account_id' => '" . addslashes($accountId) . "',\n    'zone_id' => '" . addslashes($zoneId) . "',\n];\n";
        try {
            file_put_contents($this->configPath, $content);
            $_SESSION['message'] = 'Cloudflare ayarları güncellendi.';
            $_SESSION['message_type'] = 'success';
        } catch (\Throwable $e) {
            $_SESSION['message'] = 'Ayarlar kaydedilemedi: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }
        header('Location: /admin/cloudflare');
    }

    public function devModeToggle()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') { header('Location: /admin/cloudflare'); return; }
        $cfg = file_exists($this->configPath) ? (require $this->configPath) : [];
        $on = (isset($_POST['on']) && $_POST['on'] === '1');
        try {
            require_once __DIR__ . '/../../Services/CloudflareApi.php';
            $cf = new \app\Services\CloudflareApi((string)$cfg['api_token']);
            $res = $cf->setDevelopmentMode((string)$cfg['zone_id'], $on);
            $_SESSION['message'] = !empty($res['success']) ? 'Development Mode güncellendi.' : ('Hata: ' . json_encode($res));
            $_SESSION['message_type'] = !empty($res['success']) ? 'success' : 'danger';
        } catch (\Throwable $e) {
            $_SESSION['message'] = 'Hata: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }
        header('Location: /admin/cloudflare');
    }

    public function purge()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') { header('Location: /admin/cloudflare'); return; }
        $cfg = file_exists($this->configPath) ? (require $this->configPath) : [];
        $files = isset($_POST['files']) ? array_filter(array_map('trim', explode("\n", $_POST['files']))) : [];
        try {
            require_once __DIR__ . '/../../Services/CloudflareApi.php';
            $cf = new \app\Services\CloudflareApi((string)$cfg['api_token']);
            $res = empty($files) ? $cf->purgeAllCache((string)$cfg['zone_id']) : $cf->purgeFiles((string)$cfg['zone_id'], $files);
            $_SESSION['message'] = !empty($res['success']) ? 'Cache purge işlemi tetiklendi.' : ('Hata: ' . json_encode($res));
            $_SESSION['message_type'] = !empty($res['success']) ? 'success' : 'danger';
        } catch (\Throwable $e) {
            $_SESSION['message'] = 'Hata: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }
        header('Location: /admin/cloudflare');
    }

    public function dnsCreate()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') { header('Location: /admin/cloudflare'); return; }
        $cfg = file_exists($this->configPath) ? (require $this->configPath) : [];
        $data = [
            'type' => trim((string)($_POST['type'] ?? 'A')),
            'name' => trim((string)($_POST['name'] ?? '')),
            'content' => trim((string)($_POST['content'] ?? '')),
            'ttl' => (int)($_POST['ttl'] ?? 1),
            'proxied' => isset($_POST['proxied'])
        ];
        try {
            require_once __DIR__ . '/../../Services/CloudflareApi.php';
            $cf = new \app\Services\CloudflareApi((string)$cfg['api_token']);
            $res = $cf->createDnsRecord((string)$cfg['zone_id'], $data);
            $_SESSION['message'] = !empty($res['success']) ? 'DNS kaydı oluşturuldu.' : ('Hata: ' . json_encode($res));
            $_SESSION['message_type'] = !empty($res['success']) ? 'success' : 'danger';
        } catch (\Throwable $e) {
            $_SESSION['message'] = 'Hata: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }
        header('Location: /admin/cloudflare');
    }

    public function dnsDelete()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') { header('Location: /admin/cloudflare'); return; }
        $cfg = file_exists($this->configPath) ? (require $this->configPath) : [];
        $recordId = trim((string)($_POST['record_id'] ?? ''));
        try {
            require_once __DIR__ . '/../../Services/CloudflareApi.php';
            $cf = new \app\Services\CloudflareApi((string)$cfg['api_token']);
            $res = $cf->deleteDnsRecord((string)$cfg['zone_id'], $recordId);
            $_SESSION['message'] = !empty($res['success']) ? 'DNS kaydı silindi.' : ('Hata: ' . json_encode($res));
            $_SESSION['message_type'] = !empty($res['success']) ? 'success' : 'danger';
        } catch (\Throwable $e) {
            $_SESSION['message'] = 'Hata: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }
        header('Location: /admin/cloudflare');
    }

    public function sslSet()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') { header('Location: /admin/cloudflare'); return; }
        $cfg = file_exists($this->configPath) ? (require $this->configPath) : [];
        $mode = trim((string)($_POST['mode'] ?? 'full'));
        try {
            require_once __DIR__ . '/../../Services/CloudflareApi.php';
            $cf = new \app\Services\CloudflareApi((string)$cfg['api_token']);
            $res = $cf->setSslMode((string)$cfg['zone_id'], $mode);
            $_SESSION['message'] = !empty($res['success']) ? 'SSL modu güncellendi.' : ('Hata: ' . json_encode($res));
            $_SESSION['message_type'] = !empty($res['success']) ? 'success' : 'danger';
        } catch (\Throwable $e) {
            $_SESSION['message'] = 'Hata: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }
        header('Location: /admin/cloudflare');
    }
}


