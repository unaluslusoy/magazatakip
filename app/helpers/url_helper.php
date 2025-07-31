<?php

if (!function_exists('site_url')) {
    function site_url($uri = '')
    {
        $base_url = 'https://magazatakip.com.tr/';
        return $base_url . ltrim($uri, '/');
    }
}