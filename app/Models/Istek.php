<?php

namespace app\Models;
use core\Model;

class Istek extends Model {
    protected $table = 'istekler';

    public function getAll() {
        $stmt = $this->db->query("SELECT istekler.*, personel.ad AS personel_adi, personel.soyad AS personel_soyad
                              FROM {$this->table} 
                              LEFT JOIN personel ON istekler.personel_id = personel.id
                              ORDER BY istekler.id DESC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function get($id) {
        $stmt = $this->db->prepare("SELECT istekler.*, personeller.ad AS personel_adi, personeller.soyad AS personel_soyad 
                                FROM {$this->table} 
                                LEFT JOIN personeller ON istekler.personel_id = personeller.id 
                                WHERE istekler.id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (kullanici_id, baslik, aciklama, magaza, derece, tarih, durum, personel_id, is_aciklamasi, baslangic_tarihi, bitis_tarihi) 
                                    VALUES (:kullanici_id, :baslik, :aciklama, :magaza, :derece, NOW(), 'yeni', :personel_id, :is_aciklamasi, :baslangic_tarihi, :bitis_tarihi)");
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $setPart = [];
        foreach ($data as $key => $value) {
            $setPart[] = "{$key} = :{$key}";
        }
        $setPart = implode(', ', $setPart);
        $data['id'] = $id;

        $stmt = $this->db->prepare("UPDATE {$this->table} SET {$setPart} WHERE id = :id");
        return $stmt->execute($data);
    }
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    public function getIsEmriCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE durum = 'yeni'");
        return $stmt->fetch(\PDO::FETCH_ASSOC)['count'];
    }
}
