<?php

namespace app\Http;

/**
 * HTTP istemcisi: Tamsoft ERP entegrasyonu için temel GET/POST yardımcıları.
 * Not: Şimdilik projedeki mevcut istek akışını bozmamak adına bağımsız tutuldu.
 * İlerleyen aşamada app\Services\TamsoftStockService içindeki HTTP akışı buraya taşınacak.
 */
class TamsoftHttpClient
{
    public function get(string $url, array $headers = [], int $timeoutSec = 30): array
    {
        $ch = curl_init($url);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeoutSec,
            CURLOPT_CONNECTTIMEOUT => 10,
        ];
        if (!empty($headers)) { curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); }
        curl_setopt_array($ch, $opts);
        $body = curl_exec($ch);
        $err = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [
            'http_code' => $code,
            'error' => $err ?: null,
            'raw_body' => is_string($body) ? trim($body) : '',
        ];
    }

    public function postForm(string $url, array $fields, array $headers = [], int $timeoutSec = 30): array
    {
        $ch = curl_init($url);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeoutSec,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($fields),
        ];
        $headers = array_values(array_merge(['Accept: application/json','Content-Type: application/x-www-form-urlencoded'], $headers));
        curl_setopt_array($ch, $opts);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $body = curl_exec($ch);
        $err = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [
            'http_code' => $code,
            'error' => $err ?: null,
            'raw_body' => is_string($body) ? trim($body) : '',
        ];
    }

    public function decodeFlexible(?string $raw): array
    {
        $raw = is_string($raw) ? trim($raw) : '';
        if ($raw === '') { return []; }
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) { return $decoded; }
        if (is_string($decoded)) {
            $decoded2 = json_decode($decoded, true);
            if (is_array($decoded2)) { return $decoded2; }
        }
        if (strlen($raw) > 2 && (($raw[0] === '"' && substr($raw, -1) === '"') || ($raw[0] === '\'' && substr($raw, -1) === '\''))) {
            $inner = substr($raw, 1, -1);
            $decoded3 = json_decode($inner, true);
            if (is_array($decoded3)) { return $decoded3; }
        }
        return [];
    }
}


