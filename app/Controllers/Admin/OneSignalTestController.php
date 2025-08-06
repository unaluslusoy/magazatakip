<?php
namespace app\Controllers\Admin;

use core\Controller;
use app\Middleware\AdminMiddleware;
use app\Models\OneSignalAyarlar;
use app\Models\Kullanici;

class OneSignalTestController extends Controller
{
    private $oneSignalAyarlar;
    private $kullaniciModel;

    public function __construct()
    {
        AdminMiddleware::handle();
        
        $this->oneSignalAyarlar = new OneSignalAyarlar();
        $this->kullaniciModel = new Kullanici();
    }

    public function index()
    {
        $ayarlar = $this->oneSignalAyarlar->getAyarlar();
        $kullanicilar = $this->kullaniciModel->getBildirimIzinliKullanicilar();
        
        $data = [
            'title' => 'OneSignal Test',
            'link' => 'OneSignal Test',
            'ayarlar' => $ayarlar,
            'kullanicilar' => $kullanicilar
        ];

        $this->view('admin/onesignal/test', $data);
    }

    public function testGonder()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $kullaniciId = $_POST['kullanici_id'] ?? null;
                $baslik = $_POST['baslik'] ?? 'Test Bildirimi';
                $mesaj = $_POST['mesaj'] ?? 'Bu bir test bildirimidir.';
                
                if (!$kullaniciId) {
                    throw new \Exception('Kullanıcı seçilmedi.');
                }

                $kullanici = $this->kullaniciModel->get($kullaniciId);
                if (!$kullanici) {
                    throw new \Exception('Kullanıcı bulunamadı.');
                }

                $ayarlar = $this->oneSignalAyarlar->getAyarlar();
                
                if (empty($ayarlar['onesignal_app_id']) || empty($ayarlar['onesignal_api_key'])) {
                    throw new \Exception('OneSignal ayarları eksik. Lütfen site ayarlarından kontrol edin.');
                }

                // Test bildirimi gönder
                $sonuc = $this->oneSignalGonder($kullanici, $baslik, $mesaj, $ayarlar);
                
                if ($sonuc['success']) {
                    $_SESSION['message'] = "Test bildirimi başarıyla gönderildi!";
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = "Test bildirimi gönderilemedi: " . $sonuc['error'];
                    $_SESSION['message_type'] = 'error';
                }

            } catch (\Exception $e) {
                $_SESSION['message'] = "Hata: " . $e->getMessage();
                $_SESSION['message_type'] = 'error';
            }

            header('Location: /admin/onesignal-test');
            exit();
        }
    }

    private function oneSignalGonder($kullanici, $baslik, $mesaj, $ayarlar)
    {
        try {
            if (empty($kullanici['cihaz_token'])) {
                return ['success' => false, 'error' => 'Kullanıcının cihaz token\'ı bulunamadı.'];
            }

            $data = [
                'app_id' => $ayarlar['onesignal_app_id'],
                'include_player_ids' => [$kullanici['cihaz_token']],
                'headings' => ['tr' => $baslik],
                'contents' => ['tr' => $mesaj],
                'data' => [
                    'kullanici_id' => $kullanici['id'],
                    'test' => true
                ]
            ];

            $url = 'https://onesignal.com/api/v1/notifications';
            
            $headers = [
                'Content-Type: application/json; charset=utf-8',
                'Authorization: Basic ' . $ayarlar['onesignal_api_key']
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return ['success' => false, 'error' => 'cURL Hatası: ' . $error];
            }

            if ($httpCode !== 200) {
                return ['success' => false, 'error' => 'HTTP Kodu: ' . $httpCode . ' - Yanıt: ' . $response];
            }

            $result = json_decode($response, true);
            
            if (isset($result['success']) && $result['success']) {
                return ['success' => true, 'id' => $result['id'] ?? 'Bilinmiyor'];
            } else {
                return ['success' => false, 'error' => json_encode($result)];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
} 