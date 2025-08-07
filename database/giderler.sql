-- Giderler Tablosu
DROP TABLE IF EXISTS `giderler`;
CREATE TABLE `giderler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `magaza_id` int(11) NOT NULL COMMENT 'Mağaza ID',
  `baslik` varchar(255) NOT NULL COMMENT 'Gider başlığı',
  `miktar` decimal(10,2) NOT NULL COMMENT 'Gider miktarı',
  `aciklama` text DEFAULT NULL COMMENT 'Gider açıklaması',
  `tarih` date NOT NULL COMMENT 'Gider tarihi',
  `kategori` varchar(100) DEFAULT 'Genel' COMMENT 'Gider kategorisi',
  `gorsel` varchar(500) DEFAULT NULL COMMENT 'Görsel dosya yolu',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Oluşturulma tarihi',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Güncellenme tarihi',
  PRIMARY KEY (`id`),
  KEY `idx_magaza_id` (`magaza_id`),
  KEY `idx_tarih` (`tarih`),
  KEY `idx_kategori` (`kategori`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_giderler_magaza` FOREIGN KEY (`magaza_id`) REFERENCES `magazalar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Örnek gider verileri
INSERT INTO `giderler` (`magaza_id`, `baslik`, `miktar`, `aciklama`, `tarih`, `kategori`) VALUES
(1, 'Elektrik Faturası', 1250.50, 'Ocak ayı elektrik faturası', '2024-01-15', 'Elektrik'),
(1, 'Su Faturası', 450.75, 'Ocak ayı su faturası', '2024-01-20', 'Su'),
(1, 'Temizlik Malzemeleri', 350.00, 'Aylık temizlik malzemeleri', '2024-01-25', 'Temizlik'),
(1, 'Personel Maaşları', 15000.00, 'Ocak ayı personel maaşları', '2024-01-31', 'Personel'),
(1, 'Kira Ödemesi', 8000.00, 'Ocak ayı kira ödemesi', '2024-01-01', 'Kira');
