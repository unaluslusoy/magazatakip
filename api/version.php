<?php
/**
 * Version API Endpoint
 * PWA update detection için sürüm bilgisi
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// VERSION dosyasından sürüm bilgisini oku
$versionFile = dirname(__DIR__) . '/VERSION';
$version = '1.2.0'; // Fallback version

if (file_exists($versionFile)) {
    $version = trim(file_get_contents($versionFile));
}

// Composer.json'dan da versiyon bilgisi alalım
$composerFile = dirname(__DIR__) . '/composer.json';
$composerVersion = null;

if (file_exists($composerFile)) {
    $composerData = json_decode(file_get_contents($composerFile), true);
    $composerVersion = $composerData['version'] ?? null;
}

// Build zamanı
$buildTime = null;
$changelogFile = dirname(__DIR__) . '/CHANGELOG.md';
if (file_exists($changelogFile)) {
    $buildTime = filemtime($changelogFile);
}

// Git commit hash (varsa)
$gitHash = null;
$gitHeadFile = dirname(__DIR__) . '/.git/HEAD';
if (file_exists($gitHeadFile)) {
    $gitRef = trim(file_get_contents($gitHeadFile));
    if (strpos($gitRef, 'ref:') === 0) {
        $refFile = dirname(__DIR__) . '/.git/' . substr($gitRef, 5);
        if (file_exists($refFile)) {
            $gitHash = substr(trim(file_get_contents($refFile)), 0, 7);
        }
    } else {
        $gitHash = substr($gitRef, 0, 7);
    }
}

// Feature flags
$features = [
    'view_transitions' => true,
    'pull_to_refresh' => true,
    'update_notifications' => true,
    'offline_support' => true,
    'background_sync' => true
];

// Response data
$response = [
    'version' => $version,
    'composer_version' => $composerVersion,
    'build_time' => $buildTime,
    'build_date' => $buildTime ? date('Y-m-d H:i:s', $buildTime) : null,
    'git_hash' => $gitHash,
    'features' => $features,
    'timestamp' => time(),
    'server_time' => date('Y-m-d H:i:s'),
    'environment' => [
        'php_version' => PHP_VERSION,
        'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
    ],
    'cache_bust' => md5($version . $buildTime . $gitHash)
];

// Debug mode için extra bilgiler
if (isset($_GET['debug']) && $_GET['debug'] === '1') {
    $response['debug'] = [
        'version_file_exists' => file_exists($versionFile),
        'version_file_content' => file_exists($versionFile) ? file_get_contents($versionFile) : null,
        'composer_file_exists' => file_exists($composerFile),
        'request_headers' => getallheaders(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ];
}

// JSON response
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>