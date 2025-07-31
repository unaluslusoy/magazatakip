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
        // Yeni alan desteği ile genişletilmiş create metodu
        $fields = [
            'calisan_no', 'kullanici_id', 'magaza_id', 'durum', 'ad', 'soyad', 
            'dogum_tarihi', 'dogum_yeri', 'uyruk', 'tc_kimlik_no', 'cinsiyet', 
            'kan_grubu', 'medeni_durum', 'cocuk_sayisi', 'ehliyet_sinifi',
            'eposta', 'telefon', 'cep_telefonu', 'ev_adresi', 'acil_durum_kisi_adi', 
            'acil_durum_kisi_telefon', 'ise_baslama_tarihi', 'is_cikis_tarihi',
            'pozisyon', 'departman', 'raporlama_yoneticisi', 'calisma_sekli',
            'calisma_saatleri', 'vardiya_sistemi', 'calisma_sozlesmesi_tarihi', 
            'sozlesme_suresi', 'ucret', 'maas_tipi', 'iban_no', 'sgk_no', 
            'vergi_no', 'puantaj_sistemi', 'izin_gunleri', 'egitim_bilgileri', 
            'dil_bilgisi', 'ozel_yetenekler', 'foto', 'notlar', 'kullanici_adi', 
            'sifre', 'guvenlik_sorulari'
        ];
        
        // Sadece gönderilen alanları kullan
        $validFields = array_intersect(array_keys($data), $fields);
        $fieldPlaceholders = ':' . implode(', :', $validFields);
        $fieldNames = implode(', ', $validFields);
        
        $sql = "INSERT INTO {$this->table} ({$fieldNames}) VALUES ({$fieldPlaceholders})";
        $stmt = $this->db->prepare($sql);
        
        // Sadece geçerli alanları execute et
        $validData = array_intersect_key($data, array_flip($validFields));
        return $stmt->execute($validData);
    }

    public function PersonelCreate($data) {
        $sql = "INSERT INTO $this->table (ad, soyad, eposta, telefon, pozisyon) VALUES (:ad, :soyad, :eposta, :telefon, :pozisyon)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        // Yeni alan desteği ile genişletilmiş update metodu
        $fields = [
            'calisan_no', 'kullanici_id', 'magaza_id', 'durum', 'ad', 'soyad', 
            'dogum_tarihi', 'dogum_yeri', 'uyruk', 'tc_kimlik_no', 'cinsiyet', 
            'kan_grubu', 'medeni_durum', 'cocuk_sayisi', 'ehliyet_sinifi',
            'eposta', 'telefon', 'cep_telefonu', 'ev_adresi', 'acil_durum_kisi_adi', 
            'acil_durum_kisi_telefon', 'ise_baslama_tarihi', 'is_cikis_tarihi',
            'pozisyon', 'departman', 'raporlama_yoneticisi', 'calisma_sekli',
            'calisma_saatleri', 'vardiya_sistemi', 'calisma_sozlesmesi_tarihi', 
            'sozlesme_suresi', 'ucret', 'maas_tipi', 'iban_no', 'sgk_no', 
            'vergi_no', 'puantaj_sistemi', 'izin_gunleri', 'egitim_bilgileri', 
            'dil_bilgisi', 'ozel_yetenekler', 'foto', 'notlar', 'kullanici_adi', 
            'sifre', 'guvenlik_sorulari'
        ];
        
        // Sadece gönderilen ve geçerli alanları kullan
        $validFields = array_intersect(array_keys($data), $fields);
        
        if (empty($validFields)) {
            return false; // Güncellenecek alan yok
        }
        
        // SET kısmını oluştur
        $setParts = [];
        foreach ($validFields as $field) {
            $setParts[] = "{$field} = :{$field}";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        // ID'yi ekle
        $validData = array_intersect_key($data, array_flip($validFields));
        $validData['id'] = $id;
        
        return $stmt->execute($validData);
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

    public function getByEmail($eposta) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE eposta = :eposta");
        $stmt->execute(['eposta' => $eposta]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getByKullaniciId($kullanici_id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE kullanici_id = :kullanici_id");
        $stmt->execute(['kullanici_id' => $kullanici_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
