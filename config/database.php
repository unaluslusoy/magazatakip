<?php

// Sade ve tek görevli konfig: sadece dizi döndür
return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'dbname' => getenv('DB_NAME') ?: 'magazatakip_pg',
    'username' => getenv('DB_USER') ?: 'magazatakip_pg',
    // Canlı sistemde kesinti olmaması için eski parola değerini fallback olarak koruyoruz
    'password' => getenv('DB_PASS') ?: 'Magaza.123!'
];