<?php

require '/app/config/database.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['kullanici_auth_key'])) {
    $kullanici_auth_key = $data['kullanici_auth_key'];
    session_start();
    $kullanici_id = $_SESSION['user_id']; // Oturumdan kullanıcı ID'sini alın

    $stmt = $db->prepare("INSERT INTO kullanici_tokenleri (kullanici_id, kullanici_auth_key, cihaz_bilgisi) VALUES (?, ?, ?)
                           ON DUPLICATE KEY UPDATE kullanici_auth_key = VALUES(kullanici_auth_key), cihaz_bilgisi = VALUES(cihaz_bilgisi)");
    $stmt->execute([$kullanici_id, $kullanici_auth_key, $_SERVER['HTTP_USER_AGENT']]);

    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "kullanici_auth_key not found"]);
}
?>
