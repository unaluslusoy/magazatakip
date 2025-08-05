<?php

require_once '../core/AuthManager.php';

use core\AuthManager;

// API response helper
function apiResponse($data, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit();
}

// CORS ve method kontrolü
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

$authManager = AuthManager::getInstance();
$requestUri = $_SERVER['REQUEST_URI'];

// Endpoint routing
switch (true) {
    case strpos($requestUri, '/api/heartbeat') !== false:
        handleHeartbeat($authManager);
        break;
        
    case strpos($requestUri, '/api/check-session') !== false:
        handleCheckSession($authManager);
        break;
        
    case strpos($requestUri, '/api/extend-session') !== false:
        handleExtendSession($authManager);
        break;
        
    case strpos($requestUri, '/api/logout') !== false:
        handleLogout($authManager);
        break;
        
    default:
        apiResponse(['error' => 'Endpoint not found'], 404);
}

/**
 * Heartbeat endpoint - client aktivitesini bildirme
 */
function handleHeartbeat($authManager) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        apiResponse(['error' => 'Method not allowed'], 405);
    }
    
    $authResult = $authManager->authenticate();
    
    if (!$authResult['authenticated']) {
        apiResponse([
            'status' => 'session_expired',
            'message' => 'Oturum süresi dolmuş'
        ], 401);
    }
    
    // Aktiviteyi güncelle
    $authManager->updateActivity();
    
    $input = json_decode(file_get_contents('php://input'), true);
    $timestamp = $input['timestamp'] ?? time() * 1000;
    $page = $input['page'] ?? 'unknown';
    
    // Optional: Log the heartbeat
    error_log("Heartbeat - User: " . $authResult['user']['id'] . ", Page: $page, Time: $timestamp");
    
    apiResponse([
        'status' => 'ok',
        'server_time' => time() * 1000,
        'session_info' => $authManager->getSessionInfo()
    ]);
}

/**
 * Session durumu kontrolü
 */
function handleCheckSession($authManager) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        apiResponse(['error' => 'Method not allowed'], 405);
    }
    
    $authResult = $authManager->authenticate();
    $sessionInfo = $authManager->getSessionInfo();
    
    apiResponse([
        'valid' => $authResult['authenticated'],
        'user' => $authResult['user'],
        'session_info' => $sessionInfo
    ]);
}

/**
 * Session uzatma
 */
function handleExtendSession($authManager) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        apiResponse(['error' => 'Method not allowed'], 405);
    }
    
    $authResult = $authManager->authenticate();
    
    if (!$authResult['authenticated']) {
        apiResponse(['error' => 'Not authenticated'], 401);
    }
    
    // Aktiviteyi güncelle (session'ı uzatır)
    $authManager->updateActivity();
    
    apiResponse([
        'status' => 'extended',
        'session_info' => $authManager->getSessionInfo()
    ]);
}

/**
 * Logout endpoint
 */
function handleLogout($authManager) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        apiResponse(['error' => 'Method not allowed'], 405);
    }
    
    $authManager->logout();
    
    apiResponse([
        'status' => 'logged_out',
        'message' => 'Başarıyla çıkış yapıldı'
    ]);
}