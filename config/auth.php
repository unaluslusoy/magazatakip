<?php

/**
 * Authentication Configuration
 * Tüm kimlik doğrulama ayarları burada merkezi olarak yönetilir
 */

return [
    // Session ayarları
    'session' => [
        'timeout' => 1800,              // 30 dakika (saniye)
        'activity_key' => 'last_activity',
        'user_id_key' => 'user_id',
        'admin_key' => 'is_admin',
        'name_key' => 'user_name',
        'email_key' => 'user_email',
        'role_key' => 'user_role',
        'login_time_key' => 'login_time'
    ],
    
    // Remember Me ayarları
    'remember' => [
        'short_duration' => 1800,           // Beni hatırla KAPALI: 30 dakika
        'long_duration' => 86400 * 365,     // Beni hatırla AÇIK: 1 yıl
        'cookie_name' => 'remember_me',
        'token_length' => 16,               // byte
        'secure' => true,                   // HTTPS gerekli
        'httponly' => true                  // XSS koruması
    ],
    
    // Client-side güvenlik
    'client' => [
        'heartbeat_interval' => 30000,  // 30 saniye (ms)
        'warning_time' => 300000,       // 5 dakika önce uyar (ms)
        'session_timeout' => 1800000,   // 30 dakika (ms)
        'auto_logout_delay' => 30000    // Warning sonrası çıkış (ms)
    ],
    
    // Route koruması
    'routes' => [
        'public' => [
            '/auth/giris',
            '/auth/kayit',
            '/'
        ],
        'admin_prefix' => '/admin',
        'user_dashboard' => '/anasayfa',
        'admin_dashboard' => '/admin',
        'login_page' => '/auth/giris'
    ],
    
    // Güvenlik ayarları
    'security' => [
        'password_min_length' => 6,
        'max_login_attempts' => 5,      // İleride kullanılabilir
        'lockout_duration' => 900,      // 15 dakika (saniye)
        'log_activities' => true
    ],
    
    // API endpoints
    'api' => [
        'heartbeat' => '/api/heartbeat',
        'check_session' => '/api/check-session',
        'extend_session' => '/api/extend-session',
        'logout' => '/api/logout'
    ]
];