<?php
namespace core;
use core\Database;
class Model {
    protected $db;
    protected $table;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function get($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create($data) {
        // Dizi değerlerini güvenli bir şekilde işle
        foreach ($data as $key => &$value) {
            // JSON alanları için özel işlem
            if ($key === 'dosyalar_json' || $key === 'json_data') {
                // Dizi ise JSON'a çevir
                if (is_array($value)) {
                    $value = !empty($value) ? json_encode($value) : null;
                }
                // String ise kontrol et
                elseif (is_string($value)) {
                    $value = !empty(trim($value)) ? $value : null;
                }
            }
            // Diğer alanlar için genel kontrol
            elseif (is_array($value)) {
                // Dizi boş değilse JSON'a çevir, boşsa null yap
                $value = !empty($value) ? json_encode($value) : null;
            } elseif ($value === false) {
                // false değerini null'a çevir
                $value = null;
            }
        }
        // Referansı bırak
        unset($value);

        // Boş olmayan alanları filtrele
        $data = array_filter($data, function($value) {
            return $value !== null;
        });

        $fields = implode(", ", array_keys($data));
        $values = ":" . implode(", :", array_keys($data));
        $stmt = $this->db->prepare("INSERT INTO {$this->table} ({$fields}) VALUES ({$values})");
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $ok = $stmt->execute();
        return $ok ? (int)$this->db->lastInsertId() : false;
    }

    public function update($id, $data) {
        $set = "";
        foreach ($data as $key => $value) {
            $set .= "{$key} = :{$key}, ";
        }
        $set = rtrim($set, ", ");
        $stmt = $this->db->prepare("UPDATE {$this->table} SET {$set} WHERE id = :id");
        $stmt->bindParam(':id', $id);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getDb(): \PDO {
        return $this->db;
    }
}
