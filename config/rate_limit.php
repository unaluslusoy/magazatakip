<?php

return [
    'enabled' => true,
    // Pencere süresi (saniye)
    'window_seconds' => 60,
    // Varsayılan limit
    'default' => [
        'max_requests' => 60,
    ],
    // Yol bazlı override (path trimlenmiş biçimde, örn: api/auth/login)
    'overrides' => [
        'api/auth/login' => [ 'max_requests' => 10 ],
    ],
];





