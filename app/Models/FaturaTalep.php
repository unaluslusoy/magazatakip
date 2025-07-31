<?php
namespace app\Models;


use core\Model;
use PDO;
use PDOException;


class FaturaTalep extends Model {
    protected $table = 'fatura_talepleri';

    public function create($data)
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO {$this->table} 
            (magaza_id, magaza_ad, kullanici_id, kullanici_ad, musteri_ad, musteri_adres, musteri_vergi_no, musteri_vergi_dairesi, aciklama, musteri_telefon, musteri_email, durum)
            VALUES (:magaza_id, :magaza_ad, :kullanici_id, :kullanici_ad, :musteri_ad, :musteri_adres, :musteri_vergi_no, :musteri_vergi_dairesi, :aciklama, :musteri_telefon, :musteri_email, 'Yeni')";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($data);

            if (!$result) {
                throw new PDOException("Veritabanına kayıt eklenirken bir hata oluştu.");
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Veritabanı hatası: " . $e->getMessage());
            return false;
        }
    }

    public function getAllByMagaza($magaza_id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE magaza_id = :magaza_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['magaza_id' => $magaza_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $data)
    {
        try {
            $sql = "UPDATE {$this->table} SET
            magaza_id = :magaza_id,
            magaza_ad = :magaza_ad,
            musteri_ad = :musteri_ad,
            musteri_adres = :musteri_adres,
            musteri_vergi_no = :musteri_vergi_no,
            musteri_vergi_dairesi = :musteri_vergi_dairesi,
            aciklama = :aciklama,
            guncellenme_tarihi = NOW()
            WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $data['id'] = $id;
            return $stmt->execute($data);
        } catch (\PDOException $e) {
            error_log("Fatura talebi güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }



    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
    }
}
