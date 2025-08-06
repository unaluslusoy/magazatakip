-- Site Ayarları Tablosu
CREATE TABLE IF NOT EXISTS `site_ayarlar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_adi` varchar(255) DEFAULT NULL,
  `site_aciklama` text DEFAULT NULL,
  `site_keywords` text DEFAULT NULL,
  `site_logo` varchar(500) DEFAULT NULL,
  `site_favicon` varchar(500) DEFAULT NULL,
  `iletisim_email` varchar(255) DEFAULT NULL,
  `iletisim_telefon` varchar(50) DEFAULT NULL,
  `iletisim_adres` text DEFAULT NULL,
  `sosyal_medya_facebook` varchar(500) DEFAULT NULL,
  `sosyal_medya_twitter` varchar(500) DEFAULT NULL,
  `sosyal_medya_instagram` varchar(500) DEFAULT NULL,
  `sosyal_medya_linkedin` varchar(500) DEFAULT NULL,
  `bakim_modu` tinyint(1) DEFAULT 0,
  `bakim_mesaji` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan site ayarları
INSERT INTO `site_ayarlar` (`id`, `site_adi`, `site_aciklama`, `site_keywords`, `site_logo`, `site_favicon`, `iletisim_email`, `iletisim_telefon`, `iletisim_adres`, `sosyal_medya_facebook`, `sosyal_medya_twitter`, `sosyal_medya_instagram`, `sosyal_medya_linkedin`, `bakim_modu`, `bakim_mesaji`) VALUES
(1, 'Mağaza Takip Sistemi', 'Mağaza envanter yönetiminden personel görev takibine kadar geniş bir yelpazede hizmet sunar.', 'mağaza, takip, yönetim, envanter, personel', '/public/media/logos/default.svg', '/public/media/logos/favicon.ico', 'info@magazatakip.com.tr', '+90 850 532 0756', 'İstanbul, Türkiye', '', '', '', '', 0, 'Sistem bakımda. Lütfen daha sonra tekrar deneyiniz.'); 