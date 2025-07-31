<?php

namespace app\Services;

class UrlService
{
    private static $baseUrl = 'https://magazatakip.com.tr/'; // 127.0.0.1/magazatakip/

    public static function site_url($uri = '')
    {
        return self::$baseUrl . ltrim($uri, '/');
    }
}