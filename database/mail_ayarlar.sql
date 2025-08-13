-- Mail Ayarları Tablosu (Yandex SMTP uyumlu)
CREATE TABLE IF NOT EXISTS `mail_ayarlar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `smtp_driver` varchar(50) DEFAULT 'smtp',
  `smtp_host` varchar(255) DEFAULT NULL,
  `smtp_port` int(11) DEFAULT NULL,
  `smtp_encryption` varchar(10) DEFAULT NULL, -- ssl | tls | null
  `smtp_username` varchar(255) DEFAULT NULL,
  `smtp_password` varchar(255) DEFAULT NULL,
  `from_email` varchar(255) DEFAULT NULL,
  `from_name` varchar(255) DEFAULT NULL,
  `reply_to_email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Yandex için örnek başlangıç kayıtları (gerekirse düzenlenir)
INSERT INTO `mail_ayarlar` (`smtp_driver`, `smtp_host`, `smtp_port`, `smtp_encryption`, `smtp_username`, `smtp_password`, `from_email`, `from_name`, `reply_to_email`)
VALUES ('smtp', 'smtp.yandex.com', 465, 'ssl', 'noreply@magazatakip.com.tr', '', 'noreply@magazatakip.com.tr', 'MagazaTakip', 'destek@magazatakip.com.tr');


