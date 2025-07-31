<?php
// models/KullaniciCihaz.php

namespace app\Models;

use core\Model;

class KullaniciCihaz extends Model {
    protected $table = 'kullanici_cihazlari';

    public function saveToken($kullanici_id, $cihaz_adi, $token) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (kullanici_id, cihaz_adi, token) VALUES (:kullanici_id, :cihaz_adi, :token)");
        $stmt->execute(['kullanici_id' => $kullanici_id, 'cihaz_adi' => $cihaz_adi, 'token' => $token]);
    }

    public function getTokensByUserId($kullanici_id) {
        $stmt = $this->db->prepare("SELECT token FROM {$this->table} WHERE kullanici_id = :kullanici_id");
        $stmt->execute(['kullanici_id' => $kullanici_id]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}
