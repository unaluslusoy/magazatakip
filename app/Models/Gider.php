<?php
namespace app\Models;

use core\Database;

class Gider {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll($magaza_id = null) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT g.*, m.ad as magaza_adi 
                FROM giderler g 
                LEFT JOIN magazalar m ON g.magaza_id = m.id";
        
        if ($magaza_id) {
            $sql .= " WHERE g.magaza_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$magaza_id]);
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
        }
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function get($id) {
        $conn = $this->db->getConnection();
        $sql = "SELECT g.*, m.ad as magaza_adi 
                FROM giderler g 
                LEFT JOIN magazalar m ON g.magaza_id = m.id 
                WHERE g.id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $conn = $this->db->getConnection();
        $sql = "INSERT INTO giderler (magaza_id, baslik, miktar, aciklama, tarih, kategori, gorsel, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $params = [
            $data['magaza_id'],
            $data['baslik'],
            $data['miktar'],
            $data['aciklama'],
            $data['tarih'],
            $data['kategori'],
            $data['gorsel'] ?? null
        ];
        return $stmt->execute($params);
    }
    
    public function update($id, $data) {
        $conn = $this->db->getConnection();
        
        // Görsel alanı varsa güncelle, yoksa mevcut değeri koru
        if (isset($data['gorsel'])) {
            $sql = "UPDATE giderler SET 
                    baslik = ?, miktar = ?, aciklama = ?, 
                    tarih = ?, kategori = ?, gorsel = ?, updated_at = NOW() 
                    WHERE id = ?";
            $params = [
                $data['baslik'],
                $data['miktar'],
                $data['aciklama'],
                $data['tarih'],
                $data['kategori'],
                $data['gorsel'],
                $id
            ];
        } else {
            $sql = "UPDATE giderler SET 
                    baslik = ?, miktar = ?, aciklama = ?, 
                    tarih = ?, kategori = ?, updated_at = NOW() 
                    WHERE id = ?";
            $params = [
                $data['baslik'],
                $data['miktar'],
                $data['aciklama'],
                $data['tarih'],
                $data['kategori'],
                $id
            ];
        }
        
        $stmt = $conn->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($id) {
        $conn = $this->db->getConnection();
        $sql = "DELETE FROM giderler WHERE id = ?";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function getMonthlyTotal($magaza_id = null, $year = null, $month = null) {
        $conn = $this->db->getConnection();
        if (!$year) $year = date('Y');
        if (!$month) $month = date('m');
        $sql = "SELECT SQL_NO_CACHE SUM(miktar) as toplam FROM giderler WHERE YEAR(tarih) = ? AND MONTH(tarih) = ?";
        $params = [$year, $month];
        if ($magaza_id) {
            $sql .= " AND magaza_id = ?";
            $params[] = $magaza_id;
        }
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row['toplam'] ?? 0;
    }
    
    public function getYearlyTotal($magaza_id = null, $year = null) {
        $conn = $this->db->getConnection();
        if (!$year) $year = date('Y');
        $sql = "SELECT SQL_NO_CACHE SUM(miktar) as toplam FROM giderler WHERE YEAR(tarih) = ?";
        $params = [$year];
        if ($magaza_id) {
            $sql .= " AND magaza_id = ?";
            $params[] = $magaza_id;
        }
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row['toplam'] ?? 0;
    }
    
    public function getTodayTotal($magaza_id = null) {
        $conn = $this->db->getConnection();
        $sql = "SELECT SQL_NO_CACHE SUM(miktar) as toplam FROM giderler WHERE DATE(tarih) = CURDATE()";
        $params = [];
        if ($magaza_id) {
            $sql .= " AND magaza_id = ?";
            $params[] = $magaza_id;
        }
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row['toplam'] ?? 0;
    }
    
    public function getTotal($magaza_id = null) {
        $conn = $this->db->getConnection();
        $sql = "SELECT SQL_NO_CACHE SUM(miktar) as toplam FROM giderler";
        $params = [];
        if ($magaza_id) {
            $sql .= " WHERE magaza_id = ?";
            $params[] = $magaza_id;
        }
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row['toplam'] ?? 0;
    }
} 