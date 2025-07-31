<?php

namespace app\Models;

use core\Model;

class GeriBildirim extends Model {
    protected $table = 'geri_bildirimler';

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function get($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (baslik, icerik, kategori, durum, olusturma_tarihi) VALUES (:baslik, :icerik, :kategori, :durum, :olusturma_tarihi)");
        $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function updateStatus($id, $durum) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET durum = :durum WHERE id = :id");
        $stmt->execute(['durum' => $durum, 'id' => $id]);
    }
}
