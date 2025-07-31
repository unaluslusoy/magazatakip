<?php

namespace App\Models;

use Core\Model;

class Todo extends Model {
    protected $table = 'gorevler';

    public function getAll() {
        return parent::getAll();
    }

    public function get($id) {
        return parent::get($id);
    }

    public function create($data) {
        return parent::create($data);
    }

    public function update($id, $data) {
        return parent::update($id, $data);
    }

    public function delete($id) {
        return parent::delete($id);
    }

    public function assignUser($taskId, $userId) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET kullanici_id = :kullanici_id WHERE id = :id");
        $stmt->bindParam(':kullanici_id', $userId);
        $stmt->bindParam(':id', $taskId);
        return $stmt->execute();
    }

    public function setDates($taskId, $startDate, $endDate) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET baslangic_tarihi = :baslangic_tarihi, bitis_tarihi = :bitis_tarihi WHERE id = :id");
        $stmt->bindParam(':baslangic_tarihi', $startDate);
        $stmt->bindParam(':bitis_tarihi', $endDate);
        $stmt->bindParam(':id', $taskId);
        return $stmt->execute();
    }

    public function setStatus($taskId, $status) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET durum = :durum WHERE id = :id");
        $stmt->bindParam(':durum', $status);
        $stmt->bindParam(':id', $taskId);
        return $stmt->execute();
    }
}
