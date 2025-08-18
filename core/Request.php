<?php

namespace core;

class Request
{
    public static function getClientIp(): string
    {
        // Cloudflare gerçek IP
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return trim((string)$_SERVER['HTTP_CF_CONNECTING_IP']);
        }
        // Akamai/Proxy
        if (!empty($_SERVER['HTTP_TRUE_CLIENT_IP'])) {
            return trim((string)$_SERVER['HTTP_TRUE_CLIENT_IP']);
        }
        // X-Forwarded-For
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
            if (!empty($parts)) { return $parts[0]; }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') { return true; }
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') { return true; }
        if (!empty($_SERVER['HTTP_CF_VISITOR'])) {
            $v = $_SERVER['HTTP_CF_VISITOR'];
            // {"scheme":"https"}
            if (strpos($v, 'https') !== false) { return true; }
        }
        return false;
    }
}




