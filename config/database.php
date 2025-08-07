<?php
// Veritabanı sabitleri - sadece bir kez tanımla
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'magazatakip_pg');
if (!defined('DB_USER')) define('DB_USER', 'magazatakip_pg');
if (!defined('DB_PASS')) define('DB_PASS', 'Magaza.123!');

return [
'host' => 'localhost',
'dbname' => 'magazatakip_pg',
'username' => 'magazatakip_pg',
'password' => 'Magaza.123!'
];
class Database {
    private $host = 'localhost';
    private $db_name = 'magazatakip_pg';
    private $username = 'magazatakip_pg';
    private $password = 'Magaza.123!';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Bağlantı hatası: " . $exception->getMessage();
        }
        return $this->conn;
    }
}