<?php

/**
 * Performance Configuration
 * Sistem performans ayarları
 */

return [
    // Cache ayarları
    'cache' => [
        'enabled' => true,
        'default_ttl' => 3600,
        'user_cache_ttl' => 300,
        'session_cache_ttl' => 1800,
        'auth_cache_ttl' => 600
    ],
    
    // Redis ayarları
    'redis' => [
        'enabled' => extension_loaded('redis'),
        'host' => '127.0.0.1',
        'port' => 6379,
        'timeout' => 2.0,
        'database' => 0,
        'prefix' => 'magazatakip:'
    ],
    
    // Database optimization
    'database' => [
        'connection_pool' => true,
        'query_cache' => true,
        'slow_query_log' => true,
        'slow_query_time' => 2.0
    ],
    
    // File system optimizations
    'filesystem' => [
        'gzip_compression' => true,
        'image_optimization' => true,
        'css_minification' => true,
        'js_minification' => true
    ],
    
    // Session optimization
    'session' => [
        'use_redis' => extension_loaded('redis'),
        'gc_probability' => 1,
        'gc_divisor' => 100,
        'gc_maxlifetime' => 1800
    ],
    
    // Logging
    'logging' => [
        'enabled' => true,
        'level' => 'INFO',
        'max_file_size' => '10MB',
        'retention_days' => 30
    ],
    
    // Memory limits
    'memory' => [
        'limit' => '256M',
        'cache_limit' => '64M',
        'upload_limit' => '32M'
    ]
];