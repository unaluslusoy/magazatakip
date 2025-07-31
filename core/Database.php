<?php

namespace core;

use PDO;
use PDOException;

class Database {
    private static ?self $instance = null;
    private PDO $conn;

    private function __construct() {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? 'magazatakip_pg';
        $username = $_ENV['DB_USER'] ?? 'magazatakip_pg';
        $password = $_ENV['DB_PASS'] ?? 'Magaza.123!';
        $port = $_ENV['DB_PORT'] ?? 3306;
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true, // Kalıcı bağlantı
        ];

        try {
            $this->conn = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // Hata günlüğe kaydedilir
            error_log('Veritabanı Bağlantı Hatası: ' . $e->getMessage());
            throw new PDOException('Veritabanı bağlantısı kurulamadı.', 500);
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->conn;
    }

    // Bağlantıyı kapatmak için ek bir metot
    public function closeConnection(): void {
        $this->conn = null;
    }
}
