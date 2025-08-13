<?php

namespace app\Models;

use core\Model;

class MailAyarlar extends Model
{
    protected $table = 'mail_ayarlar';

    public function getAyarlar()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY id DESC LIMIT 1");
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Mail ayarları alınırken hata: ' . $e->getMessage());
            return false;
        }
    }

    public function ayarlariGuncelle(array $ayarlar)
    {
        try {
            $existing = $this->getAyarlar();

            // Varsayılanları uygula
            $ayarlar = array_merge([
                'smtp_driver' => 'smtp',
                'smtp_host' => null,
                'smtp_port' => null,
                'smtp_encryption' => null,
                'smtp_username' => null,
                'smtp_password' => null,
                'from_email' => null,
                'from_name' => null,
                'reply_to_email' => null,
            ], $ayarlar);

            if ($existing && isset($existing['id'])) {
                return $this->update((int)$existing['id'], $ayarlar);
            }

            return $this->create($ayarlar) !== false;
        } catch (\PDOException $e) {
            error_log('Mail ayarları güncellenirken hata: ' . $e->getMessage());
            return false;
        }
    }
}


