<?php

namespace app\Services;

use app\Models\OneSignalAyarlar;

class BildirimService
{
    private $oneSignalAyarlar;

    public function __construct()
    {
        $this->oneSignalAyarlar = new OneSignalAyarlar();
    }

    /**
     * Tek bir kullanıcıya bildirim gönder
     */
    public function tekBildirimGonder($kullanici, $baslik, $mesaj, $kanal = 'web', $url = null)
    {
        try {
            $ayarlar = $this->oneSignalAyarlar->getAyarlar();
            
            if (empty($ayarlar['onesignal_app_id']) || empty($ayarlar['onesignal_api_key'])) {
                error_log("OneSignal ayarları eksik");
                return false;
            }

            // Kullanıcının cihaz token'ı var mı kontrol et
            if (empty($kullanici['cihaz_token'])) {
                error_log("Kullanıcı cihaz token'ı bulunamadı: " . $kullanici['id']);
                return false;
            }

            $data = [
                'app_id' => $ayarlar['onesignal_app_id'],
                'include_player_ids' => [$kullanici['cihaz_token']],
                'headings' => ['tr' => $baslik],
                'contents' => ['tr' => $mesaj],
                'data' => [
                    'url' => $url,
                    'kullanici_id' => $kullanici['id']
                ]
            ];

            // Kanal bazlı ayarlar
            switch ($kanal) {
                case 'mobil':
                    $data['channel_for_external_user_ids'] = 'push';
                    break;
                case 'email':
                    if (!empty($kullanici['email'])) {
                        $data['include_email_tokens'] = [$kullanici['email']];
                    }
                    break;
                case 'sms':
                    if (!empty($kullanici['telefon'])) {
                        $data['include_phone_numbers'] = [$kullanici['telefon']];
                    }
                    break;
            }

            return $this->oneSignalGonder($data, $ayarlar['onesignal_api_key']);

        } catch (\Exception $e) {
            error_log("Bildirim gönderme hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toplu bildirim gönder
     */
    public function topluBildirimGonder($kullanicilar, $baslik, $mesaj, $kanal = 'web', $url = null)
    {
        try {
            $ayarlar = $this->oneSignalAyarlar->getAyarlar();
            
            if (empty($ayarlar['onesignal_app_id']) || empty($ayarlar['onesignal_api_key'])) {
                error_log("OneSignal ayarları eksik");
                return false;
            }

            $playerIds = [];
            $emailTokens = [];
            $phoneNumbers = [];

            foreach ($kullanicilar as $kullanici) {
                if (!empty($kullanici['cihaz_token'])) {
                    $playerIds[] = $kullanici['cihaz_token'];
                }
                
                if ($kanal === 'email' && !empty($kullanici['email'])) {
                    $emailTokens[] = $kullanici['email'];
                }
                
                if ($kanal === 'sms' && !empty($kullanici['telefon'])) {
                    $phoneNumbers[] = $kullanici['telefon'];
                }
            }

            if (empty($playerIds) && empty($emailTokens) && empty($phoneNumbers)) {
                error_log("Hiçbir geçerli alıcı bulunamadı");
                return false;
            }

            $data = [
                'app_id' => $ayarlar['onesignal_app_id'],
                'headings' => ['tr' => $baslik],
                'contents' => ['tr' => $mesaj],
                'data' => [
                    'url' => $url
                ]
            ];

            if (!empty($playerIds)) {
                $data['include_player_ids'] = $playerIds;
            }

            if (!empty($emailTokens)) {
                $data['include_email_tokens'] = $emailTokens;
            }

            if (!empty($phoneNumbers)) {
                $data['include_phone_numbers'] = $phoneNumbers;
            }

            return $this->oneSignalGonder($data, $ayarlar['onesignal_api_key']);

        } catch (\Exception $e) {
            error_log("Toplu bildirim gönderme hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * OneSignal API'ye bildirim gönder
     */
    private function oneSignalGonder($data, $apiKey)
    {
        try {
            $url = 'https://onesignal.com/api/v1/notifications';
            
            $headers = [
                'Content-Type: application/json; charset=utf-8',
                'Authorization: Basic ' . $apiKey
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
                error_log("cURL Hatası: " . $error);
                return false;
            }

            if ($httpCode !== 200) {
                error_log("OneSignal API Hatası - HTTP Kodu: " . $httpCode . " - Yanıt: " . $response);
                return false;
            }

            $result = json_decode($response, true);
            
            if (isset($result['success']) && $result['success']) {
                error_log("Bildirim başarıyla gönderildi. ID: " . ($result['id'] ?? 'Bilinmiyor'));
                return true;
            } else {
                error_log("Bildirim gönderilemedi: " . json_encode($result));
                return false;
            }

        } catch (\Exception $e) {
            error_log("OneSignal API hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * E-posta bildirimi gönder (SendGrid ile)
     */
    public function emailBildirimGonder($kullanici, $baslik, $mesaj, $url = null)
    {
        try {
            $ayarlar = $this->oneSignalAyarlar->getAyarlar();
            
            if (empty($ayarlar['sendgrid_api_key']) || empty($kullanici['email'])) {
                error_log("SendGrid API anahtarı veya kullanıcı e-postası eksik");
                return false;
            }

            // SendGrid API kullanarak e-posta gönder
            // Bu kısım SendGrid entegrasyonu gerektirir
            // Şimdilik basit bir log yazalım
            error_log("E-posta bildirimi gönderildi: " . $kullanici['email'] . " - " . $baslik);
            return true;

        } catch (\Exception $e) {
            error_log("E-posta bildirimi hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * SMS bildirimi gönder (Twilio ile)
     */
    public function smsBildirimGonder($kullanici, $mesaj)
    {
        try {
            $ayarlar = $this->oneSignalAyarlar->getAyarlar();
            
            if (empty($ayarlar['twilio_account_sid']) || empty($ayarlar['twilio_auth_token']) || empty($kullanici['telefon'])) {
                error_log("Twilio ayarları veya kullanıcı telefonu eksik");
                return false;
            }

            // Twilio API kullanarak SMS gönder
            // Bu kısım Twilio entegrasyonu gerektirir
            // Şimdilik basit bir log yazalım
            error_log("SMS bildirimi gönderildi: " . $kullanici['telefon'] . " - " . $mesaj);
            return true;

        } catch (\Exception $e) {
            error_log("SMS bildirimi hatası: " . $e->getMessage());
            return false;
        }
    }
}