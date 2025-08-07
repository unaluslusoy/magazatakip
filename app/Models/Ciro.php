<?php
namespace app\Models;

use core\Database;

class Ciro {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll($magaza_id = null) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT c.*, m.ad as magaza_adi 
                FROM cirolar c 
                LEFT JOIN magazalar m ON c.magaza_id = m.id";
        
        if ($magaza_id) {
            $sql .= " WHERE c.magaza_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $magaza_id);
        } else {
            $stmt = $conn->prepare($sql);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function get($id) {
        $conn = $this->db->getConnection();
        $sql = "SELECT c.*, m.ad as magaza_adi 
                FROM cirolar c 
                LEFT JOIN magazalar m ON c.magaza_id = m.id 
                WHERE c.id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    public function create($data) {
        $conn = $this->db->getConnection();
        $sql = "INSERT INTO cirolar (magaza_id, magaza_ad, ekleme_tarihi, gun, nakit, kredi_karti, carliston, getir_carsi, trendyolgo, multinet, sodexo, edenred, setcard, tokenflex, iwallet, metropol, toplam, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssdddddddddddd", 
            $data['magaza_id'],
            $data['magaza_ad'],
            $data['ekleme_tarihi'],
            $data['gun'],
            $data['nakit'],
            $data['kredi_karti'],
            $data['carliston'],
            $data['getir_carsi'],
            $data['trendyolgo'],
            $data['multinet'],
            $data['sodexo'],
            $data['edenred'],
            $data['setcard'],
            $data['tokenflex'],
            $data['iwallet'],
            $data['metropol'],
            $data['toplam']
        );
        
        return $stmt->execute();
    }
    
    public function update($id, $data) {
        $conn = $this->db->getConnection();
        $sql = "UPDATE cirolar SET 
                magaza_id = ?, magaza_ad = ?, ekleme_tarihi = ?, gun = ?, 
                nakit = ?, kredi_karti = ?, carliston = ?, getir_carsi = ?, 
                trendyolgo = ?, multinet = ?, sodexo = ?, edenred = ?, 
                setcard = ?, tokenflex = ?, iwallet = ?, metropol = ?, 
                toplam = ?, updated_at = NOW() 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $params = [
            $data['magaza_id'],
            $data['magaza_ad'],
            $data['ekleme_tarihi'],
            $data['gun'],
            $data['nakit'],
            $data['kredi_karti'],
            $data['carliston'],
            $data['getir_carsi'],
            $data['trendyolgo'],
            $data['multinet'],
            $data['sodexo'],
            $data['edenred'],
            $data['setcard'],
            $data['tokenflex'],
            $data['iwallet'],
            $data['metropol'],
            $data['toplam'],
            $id
        ];
        return $stmt->execute($params);
    }
    
    public function delete($id) {
        $conn = $this->db->getConnection();
        $sql = "DELETE FROM cirolar WHERE id = ?";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function getMonthlyTotal($magaza_id = null, $year = null, $month = null) {
        $conn = $this->db->getConnection();
        if (!$year) $year = date('Y');
        if (!$month) $month = date('m');
        $sql = "SELECT SQL_NO_CACHE SUM(toplam) as toplam FROM cirolar WHERE YEAR(gun) = ? AND MONTH(gun) = ?";
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
        $sql = "SELECT SQL_NO_CACHE SUM(toplam) as toplam FROM cirolar WHERE YEAR(gun) = ?";
        $params = [$year];
        if ($magaza_id) {
            $sql .= " AND magaza_id = ?";
            $params[] = $magaza_id;
        }
        $stmt = $conn->prepare($sql);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row['toplam'] ?? 0;
    }
} 