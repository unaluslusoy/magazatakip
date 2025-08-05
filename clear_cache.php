<?php
// Web cache temizleyici
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache temizlendi\n";
}

if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    echo "APC cache temizlendi\n";
}

// Manuel cache temizlik
$cacheFiles = glob('cache/*');
foreach ($cacheFiles as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}

echo "Cache temizleme tamamlandı\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
?>