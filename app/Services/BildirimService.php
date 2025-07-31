<?php


namespace app\Services;

use Twilio\Rest\Client as TwilioClient;
use SendGrid;
use SendGrid\Mail\Mail;

class BildirimService
{
    public function topluBildirimGonder($tip, $kullanicilar, $baslik, $mesaj, $ayarlar)
    {
        $basarili = 0;
        $hatali = 0;

        foreach ($kullanicilar as $kullanici) {
            $sonuc = false;

            switch ($tip) {
                case 'push':
                    if ($kullanici['cihaz_token'] && $kullanici['isletim_sistemi']) {
                        $sonuc = $this->sendOneSignalNotification($ayarlar['onesignal_app_id'], $ayarlar['onesignal_api_key'], $baslik, $mesaj, $kullanici['cihaz_token']);
                    }
                    break;
                case 'sms':
                    if ($kullanici['telefon']) {
                        $sonuc = $this->sendSMS($ayarlar['twilio_sid'], $ayarlar['twilio_token'], $ayarlar['twilio_phone'], $kullanici['telefon'], $mesaj);
                    }
                    break;
                case 'email':
                    if ($kullanici['email']) {
                        $sonuc = $this->sendEmail($ayarlar['sendgrid_api_key'], $ayarlar['sendgrid_from_email'], $kullanici['email'], $baslik, $mesaj);
                    }
                    break;
            }

            if ($sonuc) {
                $basarili++;
            } else {
                $hatali++;
            }
        }

        return ['basarili' => $basarili, 'hatali' => $hatali];
    }

    private function sendOneSignalNotification($app_id, $api_key, $baslik, $mesaj, $cihaz_token)
    {
        // OneSignal gönderme kodu (değişmedi)
    }

    private function sendSMS($twilioSid, $twilioToken, $twilioPhone, $to, $message)
    {
        // SMS gönderme kodu (değişmedi)
    }

    private function sendEmail($sendgridApiKey, $fromEmail, $toEmail, $subject, $content)
    {
        // E-posta gönderme kodu (değişmedi)
    }
}