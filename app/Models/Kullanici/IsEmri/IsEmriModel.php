<?php

namespace app\Models\Kullanici\IsEmri;

use core\Model;

class IsEmriModel extends Model {
    protected $table = 'istekler'; // Tablo adınızı burada belirtin

    public function create($data) {
        // Dosyalar JSON sütununu çıkar
        $dosyalarJson = $data['dosyalar_json'] ?? null;
        unset($data['dosyalar_json']);

        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        
        $istek_id = $this->db->lastInsertId();
        
        return $istek_id;
    }

    public function update($id, $data) {
        // Güvenlik ve veri doğrulama
        $allowedFields = ['baslik', 'aciklama', 'magaza_id', 'derece', 'dosyalar_json'];
        
        // Sadece izin verilen alanları filtrele
        $filteredData = array_intersect_key($data, array_flip($allowedFields));
        
        // Boş değerleri filtrele ve JSON alanını güvenli bir şekilde işle
        $filteredData = array_filter($filteredData, function($value, $key) {
            // dosyalar_json için özel işlem
            if ($key === 'dosyalar_json') {
                // Eğer dizi ise JSON'a çevir
                if (is_array($value)) {
                    return !empty($value);
                }
                // String ise kontrol et
                return is_string($value) && !empty($value);
            }
            
            // Diğer alanlar için normal kontrol
            return $value !== null && $value !== '';
        }, ARRAY_FILTER_USE_BOTH);
        
        // dosyalar_json için JSON kontrolü
        if (isset($filteredData['dosyalar_json'])) {
            // Dizi ise JSON'a çevir
            if (is_array($filteredData['dosyalar_json'])) {
                // Geçerli dosya bilgilerini filtrele
                $validFiles = array_filter($filteredData['dosyalar_json'], function($file) {
                    return !empty($file['dosya_adi']) && !empty($file['dosya_yolu']);
                });
                
                $filteredData['dosyalar_json'] = !empty($validFiles) ? json_encode($validFiles) : null;
            }
            // String ise kontrol et
            elseif (is_string($filteredData['dosyalar_json'])) {
                // JSON formatını kontrol et
                $decodedValue = json_decode($filteredData['dosyalar_json'], true);
                $filteredData['dosyalar_json'] = ($decodedValue !== null && json_last_error() === JSON_ERROR_NONE) 
                    ? $filteredData['dosyalar_json'] 
                    : null;
            }
            else {
                $filteredData['dosyalar_json'] = null;
            }
        }
        
        // Boş verileri temizle
        $filteredData = array_filter($filteredData, function($value) {
            return $value !== null && $value !== '';
        });
        
        // Güncelleme için yeterli veri yoksa işlemi durdur
        if (empty($filteredData)) {
            return 0;
        }
        
        // Güncelleme sorgusu
        $setClause = implode(', ', array_map(function($key) {
            return "$key = :$key";
        }, array_keys($filteredData)));
        
        $sql = "UPDATE {$this->table} SET $setClause WHERE id = :id";
        
        // Parametrelere ID'yi ekle
        $filteredData['id'] = $id;
        
        // Sorguyu hazırla ve çalıştır
        $stmt = $this->db->prepare($sql);
        $stmt->execute($filteredData);
        
        return $stmt->rowCount(); // Etkilenen satır sayısını döndür
    }

    private function saveDosyaBilgileri($istek_id, $dosyalar) {
        if (empty($dosyalar)) return;

        $dosyaEklemeSql = "INSERT INTO istek_dosyalari (istek_id, dosya_yolu, dosya_adi, dosya_turu, boyut) 
                           VALUES (:istek_id, :dosya_yolu, :dosya_adi, :dosya_turu, :boyut)";
        
        $stmt = $this->db->prepare($dosyaEklemeSql);
        
        foreach ($dosyalar as $dosya) {
            // Geçerli dosya bilgilerini kontrol et
            if (!empty($dosya['dosya_yolu']) && !empty($dosya['dosya_adi'])) {
                $stmt->execute([
                    ':istek_id' => $istek_id,
                    ':dosya_yolu' => $dosya['dosya_yolu'],
                    ':dosya_adi' => $dosya['dosya_adi'],
                    ':dosya_turu' => $dosya['dosya_turu'] ?? 'application/octet-stream',
                    ':boyut' => $dosya['boyut'] ?? 0
                ]);
            }
        }
    }

    public function getAllByMagaza($magaza_id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE magaza_id = :magaza_id ORDER BY id DESC");
        $stmt->bindParam(':magaza_id', $magaza_id);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getByIdAndMagaza($id, $magaza_id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id AND magaza_id = :magaza_id");
        $stmt->execute(['id' => $id, 'magaza_id' => $magaza_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        // Önce ilişkili dosyaları sil
        $this->deleteDosyalar($id);
        
        // İş emrini sil
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        // Silinen satır sayısını döndür
        return $stmt->rowCount();
    }

    private function deleteDosyalar($istek_id) {
        $sql = "DELETE FROM istek_dosyalari WHERE istek_id = :istek_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['istek_id' => $istek_id]);
        
        return $stmt->rowCount();
    }

    public function getCountByStatus($status, $magaza_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE durum = :status AND magaza_id = :magaza_id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':magaza_id', $magaza_id);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'];
    }

    /**
     * Bekleyen iş emirlerinin sayısını getir
     */
    public function getPendingCount($magaza_id) {
        return $this->getCountByStatus('Yeni', $magaza_id);
    }

    /**
     * Devam eden iş emirlerinin sayısını getir
     */
    public function getInProgressCount($magaza_id) {
        return $this->getCountByStatus('Devam Ediyor', $magaza_id);
    }

    /**
     * Tamamlanan iş emirlerinin sayısını getir
     */
    public function getCompletedCount($magaza_id) {
        return $this->getCountByStatus('Tamamlandı', $magaza_id);
    }

    /**
     * Toplam iş emirlerinin sayısını getir
     */
    public function getTotalCount($magaza_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE magaza_id = :magaza_id");
        $stmt->bindParam(':magaza_id', $magaza_id);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'];
    }
}
?>
