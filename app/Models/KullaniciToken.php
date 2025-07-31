<?php


namespace app\Models;

use core\Model;

class KullaniciToken extends Model
{
    protected $table = 'kullanici_tokenleri';

    public function getByUserId($kullanici_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE kullanici_id = :kullanici_id");
        $stmt->execute(['kullanici_id' => $kullanici_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}

