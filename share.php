<?php
// Share Target API Handler
header('Content-Type: application/json');

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    $title = $_POST['title'] ?? '';
    $text = $_POST['text'] ?? '';
    $url = $_POST['url'] ?? '';
    
    // Dosya upload handling
    $uploadedFiles = [];
    if (isset($_FILES['file'])) {
        $files = $_FILES['file'];
        
        // Multiple file handling
        if (is_array($files['name'])) {
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $uploadedFiles[] = handleFileUpload([
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'size' => $files['size'][$i]
                    ]);
                }
            }
        } else {
            // Single file
            if ($files['error'] === UPLOAD_ERR_OK) {
                $uploadedFiles[] = handleFileUpload($files);
            }
        }
    }
    
    // Log shared content
    $shareData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'title' => $title,
        'text' => $text,
        'url' => $url,
        'files' => $uploadedFiles,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];
    
    // Save to log file (production'da database'e kaydetmek daha iyi)
    $logFile = __DIR__ . '/logs/share_target.log';
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    
    file_put_contents($logFile, json_encode($shareData) . "\n", FILE_APPEND | LOCK_EX);
    
    // Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'İçerik başarıyla paylaşıldı',
        'data' => [
            'title' => $title,
            'text' => $text,
            'url' => $url,
            'files_count' => count($uploadedFiles)
        ],
        'redirect_url' => '/anasayfa?shared=1'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}

function handleFileUpload($file) {
    $uploadDir = __DIR__ . '/uploads/shared/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = date('Y-m-d_H-i-s') . '_' . uniqid() . '.' . $fileExtension;
    $uploadPath = $uploadDir . $fileName;
    
    // Validate file type
    $allowedTypes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Dosya türü desteklenmiyor: ' . $file['type']);
    }
    
    // Validate file size (max 10MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        throw new Exception('Dosya boyutu çok büyük (max 10MB)');
    }
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return [
            'original_name' => $file['name'],
            'saved_name' => $fileName,
            'type' => $file['type'],
            'size' => $file['size'],
            'path' => '/uploads/shared/' . $fileName
        ];
    } else {
        throw new Exception('Dosya yükleme hatası');
    }
}
?>