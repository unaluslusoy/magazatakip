<?php
namespace app\Models;
use core\Model;

class Personel extends Model {

    protected $table = 'personel';

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
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (ad, soyad, dogum_tarihi, tc_kimlik_no, cinsiyet, eposta, telefon, cep_telefonu, ev_adresi, ise_baslama_tarihi, pozisyon, departman, raporlama_yoneticisi, calisma_sekli, calisma_sozlesmesi_tarihi, sozlesme_suresi, ucret, sgk_no, puantaj_sistemi, izin_gunleri, egitim_bilgileri, dil_bilgisi, ozel_yetenekler, foto, notlar, kullanici_adi, sifre, guvenlik_sorulari) VALUES (:ad, :soyad, :dogum_tarihi, :tc_kimlik_no, :cinsiyet, :eposta, :telefon, :cep_telefonu, :ev_adresi, :ise_baslama_tarihi, :pozisyon, :departman, :raporlama_yoneticisi, :calisma_sekli, :calisma_sozlesmesi_tarihi, :sozlesme_suresi, :ucret, :sgk_no, :puantaj_sistemi, :izin_gunleri, :egitim_bilgileri, :dil_bilgisi, :ozel_yetenekler, :foto, :notlar, :kullanici_adi, :sifre, :guvenlik_sorulari)");
        $stmt->execute($data);
    }

    public function PersonelCreate($data) {
        $sql = "INSERT INTO $this->table (ad, soyad, eposta, telefon, pozisyon) VALUES (:ad, :soyad, :eposta, :telefon, :pozisyon)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET ad = :ad, soyad = :soyad, dogum_tarihi = :dogum_tarihi, tc_kimlik_no = :tc_kimlik_no, cinsiyet = :cinsiyet, eposta = :eposta, telefon = :telefon, cep_telefonu = :cep_telefonu, ev_adresi = :ev_adresi, ise_baslama_tarihi = :ise_baslama_tarihi, pozisyon = :pozisyon, departman = :departman, raporlama_yoneticisi = :raporlama_yoneticisi, calisma_sekli = :calisma_sekli, calisma_sozlesmesi_tarihi = :calisma_sozlesmesi_tarihi, sozlesme_suresi = :sozlesme_suresi, ucret = :ucret, sgk_no = :sgk_no, puantaj_sistemi = :puantaj_sistemi, izin_gunleri = :izin_gunleri, egitim_bilgileri = :egitim_bilgileri, dil_bilgisi = :dil_bilgisi, ozel_yetenekler = :ozel_yetenekler, foto = :foto, notlar = :notlar, kullanici_adi = :kullanici_adi, sifre = :sifre, guvenlik_sorulari = :guvenlik_sorulari WHERE id = :id");
        $data['id'] = $id;
        $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function getTotalCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM {$this->table}");
        return $stmt->fetch(\PDO::FETCH_ASSOC)['count'];
    }

    public function assignToMagaza($personelId, $magazaId) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET magaza_id = :magaza_id WHERE id = :id");
        $stmt->bindParam(':magaza_id', $magazaId);
        $stmt->bindParam(':id', $personelId);
        return $stmt->execute();
    }
  
    public function exists($eposta) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE eposta = :eposta");
        $stmt->execute(['eposta' => $eposta]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
}
