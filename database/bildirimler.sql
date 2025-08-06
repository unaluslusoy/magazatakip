-- Bildirimler Tablosu - Güncellenmiş Yapı
DROP TABLE IF EXISTS `bildirimler`;
CREATE TABLE `bildirimler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kullanici_id` int(11) DEFAULT NULL COMMENT 'Bildirimi gönderen kullanıcı',
  `hedef_kullanici_id` int(11) DEFAULT NULL COMMENT 'Bildirimi alan kullanıcı',
  `baslik` varchar(255) NOT NULL COMMENT 'Bildirim başlığı',
  `mesaj` text NOT NULL COMMENT 'Bildirim mesajı',
  `url` varchar(255) DEFAULT NULL COMMENT 'Yönlendirme URL\'si',
  `icon` varchar(255) DEFAULT NULL COMMENT 'Bildirim ikonu URL\'si',
  `alici_tipi` enum('tum','bireysel','grup') DEFAULT 'tum' COMMENT 'Alıcı tipi',
  `gonderim_kanali` enum('web','mobil','email') DEFAULT 'web' COMMENT 'Gönderim kanalı',
  `oncelik` enum('dusuk','normal','yuksek') DEFAULT 'normal' COMMENT 'Bildirim önceliği',
  `etiketler` varchar(255) DEFAULT NULL COMMENT 'Bildirim etiketleri',
  `ekstra_veri` json DEFAULT NULL COMMENT 'Ekstra veriler (JSON)',
  `durum` enum('gonderildi','beklemede','basarisiz') DEFAULT 'beklemede' COMMENT 'Bildirim durumu',
  `okundu` tinyint(1) DEFAULT 0 COMMENT 'Okundu mu?',
  `gonderim_tarihi` datetime DEFAULT NULL COMMENT 'Gönderim tarihi',
  `okunma_tarihi` datetime DEFAULT NULL COMMENT 'Okunma tarihi',
  PRIMARY KEY (`id`),
  KEY `idx_kullanici_id` (`kullanici_id`),
  KEY `idx_hedef_kullanici_id` (`hedef_kullanici_id`),
  KEY `idx_durum` (`durum`),
  KEY `idx_gonderim_tarihi` (`gonderim_tarihi`),
  KEY `idx_alici_tipi` (`alici_tipi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Örnek bildirim verileri
INSERT INTO `bildirimler` (`kullanici_id`, `hedef_kullanici_id`, `baslik`, `mesaj`, `url`, `alici_tipi`, `gonderim_kanali`, `oncelik`, `durum`, `gonderim_tarihi`) VALUES
(1, NULL, 'Hoş Geldiniz!', 'Mağaza takip sistemine hoş geldiniz. Sistem artık kullanıma hazır.', '/anasayfa', 'tum', 'web', 'normal', 'gonderildi', NOW()),
(1, NULL, 'Sistem Güncellemesi', 'Sistem güncellemesi tamamlandı. Yeni özellikler kullanıma hazır.', '/admin', 'tum', 'web', 'normal', 'gonderildi', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, NULL, 'Bakım Bildirimi', 'Sistem bakımı 23:00-02:00 saatleri arasında yapılacaktır.', '/admin', 'tum', 'web', 'yuksek', 'beklemede', NOW()); 