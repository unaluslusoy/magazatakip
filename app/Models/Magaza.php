<?php
namespace app\Models;
use core\Model;

class Magaza extends Model {
    protected $table = 'magazalar';
    public function getTotalCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM {$this->table}");
        return $stmt->fetch(\PDO::FETCH_ASSOC)['count'];
    }
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
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (ad, adres, telefon, email) VALUES (:ad, :adres, :telefon, :email)");
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
    

    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
