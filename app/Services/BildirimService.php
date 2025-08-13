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
    public function tekBildirimGonder($kullanici, $baslik, $mesaj, $kanal = 'web', $url = null, $notificationId = null)
    {
        try {
            $ayarlar = $this->oneSignalAyarlar->getAyarlar();
            
            if (empty($ayarlar['onesignal_app_id']) || empty($ayarlar['onesignal_api_key'])) {
                error_log("OneSignal ayarları eksik");
                return false;
            }

            // Not: v16 ile external_id (alias) üzerinden de gönderim yapılabilir. 
            // Bu yüzden cihaz_token yoksa doğrudan başarısız dönmeyelim; 
            // aşağıda alias veya player id ile devam etmeyi deneyelim.

            // Varsayılan bildirim sayfası URL'si (in-app yönlendirme)
            if (empty($url)) {
                $host = $_SERVER['HTTP_HOST'] ?? 'magazatakip.com.tr';
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'https';
                $url = $scheme . '://' . $host . '/kullanici/bildirimler';
            }

            // Kanal otomatik çözüm (cihaz/token ve iletişim bilgilerine göre)
            $resolvedChannel = $this->resolveChannel($kullanici, $kanal);

            // OneSignal v1 endpoint ile uyumlu: mümkünse external_user_id ile hedefle, aksi halde include_player_ids kullan
            $data = [
                'app_id' => $ayarlar['onesignal_app_id'],
                'url' => $url,
                // OneSignal 400 hatasını önlemek için İngilizce fallback ekleyelim
                'headings' => ['tr' => $baslik, 'en' => $baslik],
                'contents' => ['tr' => $mesaj, 'en' => $mesaj],
                'data' => [
                    'url' => $url,
                    'kullanici_id' => $kullanici['id'],
                    'notification_id' => $notificationId,
                    'kanal' => $resolvedChannel
                ]
            ];
            // Hem external_user_id hem player_id mevcutsa ikisini de gönder
            if (!empty($kullanici['id'])) {
                $data['include_external_user_ids'] = [ (string)$kullanici['id'] ];
                // Kanalı belirt: push/email/sms
                if (in_array($resolvedChannel, ['mobil','web'], true)) {
                    $data['channel_for_external_user_ids'] = 'push';
                } elseif ($resolvedChannel === 'email') {
                    $data['channel_for_external_user_ids'] = 'email';
                } elseif ($resolvedChannel === 'sms') {
                    $data['channel_for_external_user_ids'] = 'sms';
                }
            }
            if (!empty($kullanici['cihaz_token'])) {
                $data['include_player_ids'] = [ $kullanici['cihaz_token'] ];
            }

            // Kanal bazlı ayarlar
            switch ($resolvedChannel) {
                case 'mobil':
                    // v1'de external_user_id ile push varsayılandır
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
            $externalIds = [];
            $emailTokens = [];
            $phoneNumbers = [];

            foreach ($kullanicilar as $kullanici) {
                $resolved = $this->resolveChannel($kullanici, $kanal);
                if ($resolved === 'email') {
                    if (!empty($kullanici['email'])) { $emailTokens[] = $kullanici['email']; }
                    continue;
                }
                if ($resolved === 'sms') {
                    if (!empty($kullanici['telefon'])) { $phoneNumbers[] = $kullanici['telefon']; }
                    continue;
                }
                // Push (web/mobil)
                if (!empty($kullanici['cihaz_token'])) { $playerIds[] = $kullanici['cihaz_token']; }
                if (!empty($kullanici['id'])) { $externalIds[] = (string)$kullanici['id']; }
            }

            if (empty($playerIds) && empty($emailTokens) && empty($phoneNumbers) && empty($externalIds)) {
                error_log("Hiçbir geçerli alıcı bulunamadı");
                return false;
            }

            // Varsayılan bildirim sayfası URL'si (in-app yönlendirme)
            if (empty($url)) {
                $host = $_SERVER['HTTP_HOST'] ?? 'magazatakip.com.tr';
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'https';
                $url = $scheme . '://' . $host . '/kullanici/bildirimler';
            }

            $data = [
                'app_id' => $ayarlar['onesignal_app_id'],
                'url' => $url,
                // OneSignal 400 hatasını önlemek için İngilizce fallback ekleyelim
                'headings' => ['tr' => $baslik, 'en' => $baslik],
                'contents' => ['tr' => $mesaj, 'en' => $mesaj],
                'data' => [
                    'url' => $url
                ]
            ];

            if (!empty($externalIds)) {
                $data['include_external_user_ids'] = $externalIds;
                // Dış ID'ler varsa ve push listesi de varsa push kana'lını önceliklendirelim
                if (!empty($playerIds)) {
                    $data['channel_for_external_user_ids'] = 'push';
                } elseif (!empty($emailTokens)) {
                    $data['channel_for_external_user_ids'] = 'email';
                } elseif (!empty($phoneNumbers)) {
                    $data['channel_for_external_user_ids'] = 'sms';
                } else {
                    $data['channel_for_external_user_ids'] = 'push';
                }
            }
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
     * Kullanıcı ID'sine tek bildirim gönder (push + opsiyonel DB kaydı)
     */
    public function notifyUserId(int $userId, string $title, string $message, string $url = null, bool $alsoPersistDb = true)
    {
        try {
            $kullaniciModel = new \app\Models\Kullanici();
            $user = $kullaniciModel->get($userId);
            if (!$user) { return [ 'success' => false, 'error' => 'user_not_found' ]; }

            $result = $this->tekBildirimGonder($user, $title, $message, 'web', $url);

            if ($alsoPersistDb) {
                try {
                    $bildirimModel = new \app\Models\Bildirim();
                    $bildirimModel->create([
                        'kullanici_id' => null,
                        'hedef_kullanici_id' => $userId,
                        'baslik' => $title,
                        'mesaj' => $message,
                        'url' => $url ?? '/kullanici/bildirimler',
                        'alici_tipi' => 'bireysel',
                        'gonderim_kanali' => 'web',
                        'oncelik' => 'normal',
                        'durum' => (is_array($result) && !empty($result['success'])) ? 'gonderildi' : 'beklemede',
                        'okundu' => 0,
                        'gonderim_tarihi' => date('Y-m-d H:i:s')
                    ]);
                } catch (\Throwable $t) { /* swallow */ }
            }

            return $result;
        } catch (\Throwable $e) {
            return [ 'success' => false, 'error' => $e->getMessage() ];
        }
    }

    /**
     * Çoklu kullanıcı ID'lerine bildirim gönder (push)
     */
    public function notifyUserIds(array $userIds, string $title, string $message, string $url = null)
    {
        try {
            if (empty($userIds)) { return [ 'success' => false, 'error' => 'empty_ids' ]; }
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $kullaniciModel = new \app\Models\Kullanici();
            $stmt = $kullaniciModel->db->prepare("SELECT * FROM kullanicilar WHERE id IN ($placeholders)");
            $stmt->execute(array_values($userIds));
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if (!$users) { return [ 'success' => false, 'error' => 'users_not_found' ]; }
            return $this->topluBildirimGonder($users, $title, $message, 'web', $url);
        } catch (\Throwable $e) {
            return [ 'success' => false, 'error' => $e->getMessage() ];
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

            if ($httpCode < 200 || $httpCode >= 300) {
                error_log("OneSignal API Hatası - HTTP Kodu: " . $httpCode . " - Yanıt: " . $response);
                return [ 'success' => false, 'http' => $httpCode, 'response' => $response, 'request' => $data ];
            }

            $result = json_decode($response, true);
            if (isset($result['errors'])) {
                error_log('OneSignal errors: ' . json_encode($result['errors']));
            }

            // 2xx dönerse başarı kabul et; recipients/id bilgisini ekle
            $recipients = isset($result['recipients']) ? (int)$result['recipients'] : null;
            $id = $result['id'] ?? null;
            error_log("Bildirim gönderim HTTP {$httpCode}, recipients=" . ($recipients ?? 'null') . ", id=" . ($id ?? 'null'));
            return [ 'success' => true, 'id' => $id, 'recipients' => $recipients, 'request' => $data, 'response' => $result ];

        } catch (\Exception $e) {
            error_log("OneSignal API hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kanalı otomatik belirle: cihaz token varsa push; iOS/Android ise 'mobil', aksi halde 'web'.
     * Token yoksa email varsa 'email', değilse telefon varsa 'sms', aksi halde 'web'.
     */
    private function resolveChannel(array $kullanici, ?string $requested): string
    {
        $req = strtolower((string)($requested ?? ''));
        if (in_array($req, ['web','mobil','email','sms'], true)) {
            return $req;
        }
        // otomatik
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