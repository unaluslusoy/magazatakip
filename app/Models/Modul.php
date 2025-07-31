<?php

namespace App\Models;

use Core\Model;

class Modul extends Model {
    protected $table = 'moduller';

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

    public function aktifPasif($id, $durum) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET aktif = :durum WHERE id = :id");
        $stmt->bindParam(':durum', $durum);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
