<?php
// Veritabanı bağlantı bilgilerini alın
$config = require 'app/config/database.php';

try {
    // Veritabanı bağlantısını oluşturun
    $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['dbname'];
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Veritabanı bağlantısı başarısız: " . $e->getMessage()]);
    exit();
}

// Gelen veriyi JSON formatında alın
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['kullanici_auth_key'])) {
    session_start();

    if (isset($_SESSION['user_id'])) {
        $kullanici_auth_key = $data['kullanici_auth_key'];
        $kullanici_id = $_SESSION['user_id']; // Oturumdan kullanıcı ID'sini alın
        $cihaz_bilgisi = $_SERVER['HTTP_USER_AGENT'];

        // SQL sorgusunu hazırlayın
        $stmt = $pdo->prepare("INSERT INTO kullanici_tokenleri (kullanici_id, kullanici_auth_key, cihaz_bilgisi)
                               VALUES (:kullanici_id, :kullanici_auth_key, :cihaz_bilgisi)
                               ON DUPLICATE KEY UPDATE kullanici_auth_key = :kullanici_auth_key, cihaz_bilgisi = :cihaz_bilgisi");

        // Parametreleri bağlayın ve sorguyu çalıştırın
        $stmt->bindParam(':kullanici_id', $kullanici_id);
        $stmt->bindParam(':kullanici_auth_key', $kullanici_auth_key);
        $stmt->bindParam(':cihaz_bilgisi', $cihaz_bilgisi);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Token kaydedilemedi"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Kullanıcı oturumu bulunamadı"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "kullanici_auth_key not found"]);
}
