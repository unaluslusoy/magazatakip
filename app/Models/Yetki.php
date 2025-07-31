<?php

namespace App\Models;

use Core\Model;

class Yetki extends Model {
    protected $table = 'yetkiler';

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
}
