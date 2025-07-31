<?php

namespace app\Models;

use core\Model;
use PDO;

class AdminFaturaTalep extends Model
{
    protected $table = 'fatura_talepleri';

    public function getAll()
    {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (magaza_id, magaza_ad, kullanici_id, kullanici_ad, musteri_ad, musteri_adres, musteri_vergi_no, musteri_vergi_dairesi, musteri_telefon, musteri_email, aciklama, durum, fatura_pdf_path) VALUES (:magaza_id, :magaza_ad, :kullanici_id, :kullanici_ad, :musteri_ad, :musteri_adres, :musteri_vergi_no, :musteri_vergi_dairesi, :musteri_telefon, :musteri_email, :aciklama, :durum, :fatura_pdf_path)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET 
            magaza_id = :magaza_id, 
            magaza_ad = :magaza_ad, 
            musteri_ad = :musteri_ad, 
            musteri_adres = :musteri_adres, 
            musteri_vergi_no = :musteri_vergi_no, 
            musteri_vergi_dairesi = :musteri_vergi_dairesi, 
            musteri_telefon = :musteri_telefon, 
            musteri_email = :musteri_email, 
            aciklama = :aciklama, 
            durum = :durum, 
            fatura_pdf_path = :fatura_pdf_path 
            WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'magaza_id' => $data['magaza_id'],
            'magaza_ad' => $data['magaza_ad'],
            'musteri_ad' => $data['musteri_ad'],
            'musteri_adres' => $data['musteri_adres'],
            'musteri_vergi_no' => $data['musteri_vergi_no'],
            'musteri_vergi_dairesi' => $data['musteri_vergi_dairesi'],
            'musteri_telefon' => $data['musteri_telefon'],
            'musteri_email' => $data['musteri_email'],
            'aciklama' => $data['aciklama'],
            'durum' => $data['durum'],
            'fatura_pdf_path' => $data['fatura_pdf_path'],
            'id' => $id
        ]);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
