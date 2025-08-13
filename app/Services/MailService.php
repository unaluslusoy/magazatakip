<?php

namespace app\Services;

use app\Models\MailAyarlar;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

class MailService
{
    private MailAyarlar $mailAyarlarModel;

    public function __construct()
    {
        $this->mailAyarlarModel = new MailAyarlar();
    }

    public function send(string $toEmail, string $toName, string $subject, string $htmlBody, ?string $textBody = null): bool
    {
        $ayarlar = $this->mailAyarlarModel->getAyarlar();
        if (!$ayarlar) {
            error_log('MailService: Ayarlar bulunamadı');
            return false;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->SMTPAuth = true;
            $mail->Host = $ayarlar['smtp_host'] ?? 'smtp.yandex.com';
            $mail->Port = isset($ayarlar['smtp_port']) ? (int)$ayarlar['smtp_port'] : 465;

            $enc = $ayarlar['smtp_encryption'] ?? 'ssl';
            if ($enc === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($enc === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = false;
                $mail->SMTPAutoTLS = false;
            }

            $mail->Username = $ayarlar['smtp_username'] ?? '';
            $mail->Password = $ayarlar['smtp_password'] ?? '';

            $fromEmail = $ayarlar['from_email'] ?? $mail->Username;
            $fromName = $ayarlar['from_name'] ?? 'MagazaTakip';
            $replyTo = $ayarlar['reply_to_email'] ?? null;

            $mail->setFrom($fromEmail, $fromName);
            if (!empty($replyTo)) {
                $mail->addReplyTo($replyTo, $fromName);
            }

            $mail->addAddress($toEmail, $toName ?: $toEmail);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody ?: strip_tags($htmlBody);

            $sent = $mail->send();
            return (bool)$sent;
        } catch (MailException $e) {
            error_log('MailService send error: ' . $e->getMessage());
            return false;
        } catch (\Throwable $t) {
            error_log('MailService send throwable: ' . $t->getMessage());
            return false;
        }
    }
}


