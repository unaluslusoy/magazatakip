<?php

namespace app\Models;

use core\Model;

class OneSignalAyarlar extends Model
{
    protected $table = 'onesignal_ayarlar';

    public function getAyarlar()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM {$this->table} LIMIT 1");
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Veritabanından OneSignal ayarları getirilirken hata oluştu: " . $e->getMessage());
            return false;
        }
    }

    public function ayarlariGuncelle($ayarlar)
    {
        try {
            // Önce mevcut kaydı kontrol edelim
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table}");
            $stmt->execute();
            $count = $stmt->fetchColumn();

            if ($count == 0) {
                // Kayıt yoksa INSERT yapalım
                $sql = "INSERT INTO {$this->table} (onesignal_app_id, onesignal_api_key, twilio_sid, twilio_token, twilio_phone, sendgrid_api_key, sendgrid_from_email) 
                        VALUES (:onesignal_app_id, :onesignal_api_key, :twilio_sid, :twilio_token, :twilio_phone, :sendgrid_api_key, :sendgrid_from_email)";
            } else {
                // Kayıt varsa UPDATE yapalım
                $sql = "UPDATE {$this->table} SET 
                        onesignal_app_id = :onesignal_app_id,
                        onesignal_api_key = :onesignal_api_key,
                        twilio_sid = :twilio_sid,
                        twilio_token = :twilio_token,
                        twilio_phone = :twilio_phone,
                        sendgrid_api_key = :sendgrid_api_key,
                        sendgrid_from_email = :sendgrid_from_email
                        WHERE id = 1";
            }

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($ayarlar);

            if (!$result) {
                // Hata loglaması yapalım
                error_log("OneSignal ayarları güncellenirken hata oluştu: " . print_r($stmt->errorInfo(), true));
            }

            return $result;
        } catch (\PDOException $e) {
            error_log("OneSignal ayarları güncellenirken veritabanı hatası oluştu: " . $e->getMessage());
            return false;
        }
    }

    public function getOneSignalAppId()
    {
        try {
            $stmt = $this->db->prepare("SELECT onesignal_app_id FROM {$this->table} LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result['onesignal_app_id'] ?? null;
        } catch (\PDOException $e) {
            error_log("Veritabanından OneSignal App ID getirilirken hata oluştu: " . $e->getMessage());
            return null;
        }
    }
}
