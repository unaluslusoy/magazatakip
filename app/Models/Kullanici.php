<?php
namespace app\Models;
use core\Model;

class Kullanici extends Model {
    protected $table = 'kullanicilar';
    protected $bildirimTable = 'bildirimler';

    public function getByToken($token) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE token = :token");
        $stmt->execute(['token' => $token]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function setToken($userId, $token) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET token = :token WHERE id = :id");
        $stmt->execute(['token' => $token, 'id' => $userId]);
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT kullanicilar.*, magazalar.ad AS magaza_isim FROM {$this->table} LEFT JOIN magazalar ON kullanicilar.magaza_id = magazalar.id");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function get($id) {
        // Debug: ID'nin türünü kontrol et
        if (is_array($id)) {
            error_log("HATA: get() metoduna array ID geldi: " . print_r($id, true));
            return false;
        }
        
        // ID'yi integer'a dönüştür
        $id = intval($id);
        if ($id <= 0) {
            error_log("HATA: Geçersiz ID: " . $id);
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("SELECT kullanicilar.*, magazalar.ad AS magaza_isim FROM {$this->table} LEFT JOIN magazalar ON kullanicilar.magaza_id = magazalar.id WHERE kullanicilar.id = :id");
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            error_log("Kullanıcı sorgu sonucu ID $id: " . ($result ? 'BULUNDU' : 'BULUNAMADI'));
            return $result;
        } catch (Exception $e) {
            error_log("Veritabanı hatası get($id): " . $e->getMessage());
            return false;
        }
    }

    public function create($data): bool
    {
        return parent::create($data);
    }

    /**
     * Email'e göre kullanıcı getir
     */
    public function getByEmail($email) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email");
            $stmt->execute(['email' => $email]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Veritabanı hatası getByEmail($email): " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data): bool
    {
        return parent::update($id, $data);
    }

    public function delete($id): bool
    {
        return parent::delete($id);
    }

    public function getTotalCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM {$this->table}");
        return $stmt->fetch(\PDO::FETCH_ASSOC)['count'];
    }
    public function getAllUsers(): false|array
    {
        $stmt = $this->db->prepare("SELECT id, ad FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getBildirimIzinliKullanicilar(): false|array
    {
        $stmt = $this->db->query("SELECT id, email, telefon, cihaz_token, isletim_sistemi FROM {$this->table} WHERE bildirim_izni = 1 AND (cihaz_token IS NOT NULL OR email IS NOT NULL OR telefon IS NOT NULL)");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getSelectedUsers($userIds): false|array
    {
        if (empty($userIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $query = "SELECT id, email, telefon, cihaz_token, isletim_sistemi 
              FROM {$this->table} 
              WHERE id IN ($placeholders) AND bildirim_izni = 1";

        $stmt = $this->db->prepare($query);
        $stmt->execute($userIds);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function updateToken($userId, $token, $platform)
    {
        $query = "UPDATE {$this->table} SET cihaz_token = :token, isletim_sistemi = :platform, bildirim_izni = 1 WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':token' => $token,
            ':platform' => $platform,
            ':id' => $userId
        ]);
    }

    public function getKullaniciByToken($token)
    {
        $sql = "SELECT * FROM {$this->table} WHERE cihaz_token = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }


    public function getBildirimler(): false|array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->bildirimTable} ORDER BY gonderim_tarihi DESC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
