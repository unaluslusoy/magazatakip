<?php

namespace app\Models;

use core\Model;

class OneSignalAyarlar extends Model
{
    protected $table = 'onesignal_ayarlar';

    /**
     * OneSignal ayarlarını getir
     */
    public function getAyarlar()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY id DESC LIMIT 1");
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("OneSignal ayarları alınırken hata: " . $e->getMessage());
            return false;
        }
    }

    /**
     * OneSignal App ID'yi getir
     */
    public function getAppId()
    {
        try {
            $stmt = $this->db->query("SELECT onesignal_app_id FROM {$this->table} ORDER BY id DESC LIMIT 1");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ? $result['onesignal_app_id'] : null;
        } catch (\PDOException $e) {
            error_log("OneSignal App ID alınırken hata: " . $e->getMessage());
            return null;
        }
    }

    /**
     * OneSignal API Key'i getir
     */
    public function getApiKey()
    {
        try {
            $stmt = $this->db->query("SELECT onesignal_api_key FROM {$this->table} ORDER BY id DESC LIMIT 1");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ? $result['onesignal_api_key'] : null;
        } catch (\PDOException $e) {
            error_log("OneSignal API Key alınırken hata: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Ayarları güncelle
     */
    public function updateAyarlar($data)
    {
        try {
            $existing = $this->getAyarlar();
            
            if ($existing) {
                // Mevcut ayarları güncelle
                return $this->update($existing['id'], $data);
            } else {
                // Yeni ayar oluştur
                return $this->create($data);
            }
        } catch (\PDOException $e) {
            error_log("OneSignal ayarları güncellenirken hata: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ayarların mevcut olup olmadığını kontrol et
     */
    public function ayarlarMevcut()
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM {$this->table}");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (\PDOException $e) {
            error_log("OneSignal ayarları kontrol edilirken hata: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Geçerli OneSignal ayarlarını kontrol et
     */
    public function ayarlarGecerli()
    {
        $ayarlar = $this->getAyarlar();
        
        if (!$ayarlar) {
            return false;
        }
        
        // App ID ve API Key'in mevcut olup olmadığını kontrol et
        return !empty($ayarlar['onesignal_app_id']) && !empty($ayarlar['onesignal_api_key']);
    }
}
