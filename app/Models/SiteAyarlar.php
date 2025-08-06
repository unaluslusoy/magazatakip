<?php

namespace app\Models;

use core\Model;

class SiteAyarlar extends Model
{
    protected $table = 'site_ayarlar';

    public function getAyarlar()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM {$this->table} LIMIT 1");
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Veritabanından site ayarları getirilirken hata oluştu: " . $e->getMessage());
            return false;
        }
    }

    public function ayarlariGuncelle($ayarlar)
    {
        try {
            // Önce mevcut kaydı kontrol edelim
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table}");
            $stmt->execute();
            $count = $stmt->fetchColumn();

            if ($count == 0) {
                // Kayıt yoksa INSERT yapalım
                $sql = "INSERT INTO {$this->table} (
                    site_adi, site_aciklama, site_keywords, site_logo, site_favicon,
                    iletisim_email, iletisim_telefon, iletisim_adres,
                    sosyal_medya_facebook, sosyal_medya_twitter, sosyal_medya_instagram, sosyal_medya_linkedin,
                    bakim_modu, bakim_mesaji
                ) VALUES (
                    :site_adi, :site_aciklama, :site_keywords, :site_logo, :site_favicon,
                    :iletisim_email, :iletisim_telefon, :iletisim_adres,
                    :sosyal_medya_facebook, :sosyal_medya_twitter, :sosyal_medya_instagram, :sosyal_medya_linkedin,
                    :bakim_modu, :bakim_mesaji
                )";
            } else {
                // Kayıt varsa UPDATE yapalım
                $sql = "UPDATE {$this->table} SET 
                        site_adi = :site_adi,
                        site_aciklama = :site_aciklama,
                        site_keywords = :site_keywords,
                        site_logo = :site_logo,
                        site_favicon = :site_favicon,
                        iletisim_email = :iletisim_email,
                        iletisim_telefon = :iletisim_telefon,
                        iletisim_adres = :iletisim_adres,
                        sosyal_medya_facebook = :sosyal_medya_facebook,
                        sosyal_medya_twitter = :sosyal_medya_twitter,
                        sosyal_medya_instagram = :sosyal_medya_instagram,
                        sosyal_medya_linkedin = :sosyal_medya_linkedin,
                        bakim_modu = :bakim_modu,
                        bakim_mesaji = :bakim_mesaji
                        WHERE id = 1";
            }

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($ayarlar);

            if ($result) {
                error_log("Site ayarları başarıyla güncellendi");
                return true;
            } else {
                error_log("Site ayarları güncellenirken hata oluştu");
                return false;
            }
        } catch (\PDOException $e) {
            error_log("Site ayarları güncellenirken veritabanı hatası: " . $e->getMessage());
            return false;
        }
    }
} 