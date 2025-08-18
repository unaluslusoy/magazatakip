<?php

namespace app\Http;

/**
 * HTTP istemcisi: Tamsoft ERP entegrasyonu için temel GET/POST yardımcıları.
 * Not: Şimdilik projedeki mevcut istek akışını bozmamak adına bağımsız tutuldu.
 * İlerleyen aşamada app\Services\TamsoftStockService içindeki HTTP akışı buraya taşınacak.
 */
class TamsoftHttpClient
{
\tpublic function get(string $url, array $headers = [], int $timeoutSec = 30): array
\t{
\t\t$ch = curl_init($url);
\t\t$opts = [
\t\t\tCURLOPT_RETURNTRANSFER => true,
\t\t\tCURLOPT_TIMEOUT => $timeoutSec,
\t\t\tCURLOPT_CONNECTTIMEOUT => 10,
\t\t];
\t\tif (!empty($headers)) { curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); }
\t\tcurl_setopt_array($ch, $opts);
\t\t$body = curl_exec($ch);
\t\t$err = curl_error($ch);
\t\t$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
\t\tcurl_close($ch);
\t\treturn [
\t\t\t'http_code' => $code,
\t\t\t'error' => $err ?: null,
\t\t\t'raw_body' => is_string($body) ? trim($body) : '',
\t\t];
\t}

\tpublic function postForm(string $url, array $fields, array $headers = [], int $timeoutSec = 30): array
\t{
\t\t$ch = curl_init($url);
\t\t$opts = [
\t\t\tCURLOPT_RETURNTRANSFER => true,
\t\t\tCURLOPT_TIMEOUT => $timeoutSec,
\t\t\tCURLOPT_CONNECTTIMEOUT => 10,
\t\t\tCURLOPT_POST => true,
\t\t\tCURLOPT_POSTFIELDS => http_build_query($fields),
\t\t];
\t\t$headers = array_values(array_merge(['Accept: application/json','Content-Type: application/x-www-form-urlencoded'], $headers));
\t\tcurl_setopt_array($ch, $opts);
\t\tcurl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
\t\t$body = curl_exec($ch);
\t\t$err = curl_error($ch);
\t\t$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
\t\tcurl_close($ch);
\t\treturn [
\t\t\t'http_code' => $code,
\t\t\t'error' => $err ?: null,
\t\t\t'raw_body' => is_string($body) ? trim($body) : '',
\t\t];
\t}

\tpublic function decodeFlexible(?string $raw): array
\t{
\t\t$raw = is_string($raw) ? trim($raw) : '';
\t\tif ($raw === '') { return []; }
\t\t$decoded = json_decode($raw, true);
\t\tif (is_array($decoded)) { return $decoded; }
\t\tif (is_string($decoded)) {
\t\t\t$decoded2 = json_decode($decoded, true);
\t\t\tif (is_array($decoded2)) { return $decoded2; }
\t\t}
\t\tif (strlen($raw) > 2 && (($raw[0] === '"' && substr($raw, -1) === '"') || ($raw[0] === '\'' && substr($raw, -1) === '\''))) {
\t\t\t$inner = substr($raw, 1, -1);
\t\t\t$decoded3 = json_decode($inner, true);
\t\t\tif (is_array($decoded3)) { return $decoded3; }
\t\t}
\t\treturn [];
\t}
}


