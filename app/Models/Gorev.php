<?php

namespace app\Models;

use core\Model;

class Gorev extends Model {
    protected $table = 'gorevler';
    public function getPendingCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE durum = 'bekliyor'");
        return $stmt->fetch(\PDO::FETCH_ASSOC)['count'];
    }
    public function getPending() {
        $stmt = $this->db->query("SELECT * FROM {$this->table} WHERE durum = 'bekliyor'");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function getCountByStatus($status) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE durum = :durum");
        $stmt->execute(['durum' => $status]);
        return $stmt->fetch(\PDO::FETCH_ASSOC)['count'];
    }
}
