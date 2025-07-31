<?php


function customErrorHandler($errno, $errstr, $errfile, $errline)
{
    $logFile = __DIR__ . '/logs/error_log.txt';
    $date = date('Y-m-d H:i:s');
    $errorMessage = "[$date] Error: $errstr in $errfile on line $errline\n";
    file_put_contents($logFile, $errorMessage, FILE_APPEND);

    // Eğer hata çok ciddi değilse, PHP'nin varsayılan hata işleyicisine devam edelim
    return false;
}

function customExceptionHandler($exception)
{
    $logFile = __DIR__ . '/logs/error_log.txt';
    $date = date('Y-m-d H:i:s');
    $errorMessage = "[$date] Uncaught exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine() . "\n";
    file_put_contents($logFile, $errorMessage, FILE_APPEND);
}

// Hata işleyiciyi ayarla
set_error_handler('customErrorHandler');

// İstisna işleyiciyi ayarla
set_exception_handler('customExceptionHandler');

// Shutdown fonksiyonu ile kapanış sırasında fatalleri yakala
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        customErrorHandler($error['type'], $error['message'], $error['file'], $error['line']);
    }
});

// Hata raporlamayı etkinleştir
error_reporting(E_ALL);
ini_set('display_errors', 1);
