<?php

namespace app\Services;

class CloudflareApi
{
    private string $apiBase = 'https://api.cloudflare.com/client/v4';
    private string $apiToken;

    public function __construct(string $apiToken)
    {
        $this->apiToken = $apiToken;
    }

    private function request(string $method, string $path, ?array $data = null): array
    {
        $url = rtrim($this->apiBase, '/') . '/' . ltrim($path, '/');
        $ch = curl_init();
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiToken,
        ];
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['success' => false, 'error' => 'cURL: ' . $err, 'http' => $http];
        }
        $json = json_decode((string)$resp, true);
        if ($json === null) {
            return ['success' => false, 'error' => 'Invalid JSON response', 'http' => $http, 'raw' => $resp];
        }
        return $json + ['http' => $http];
    }

    public function listZones(string $accountId, int $page = 1, int $perPage = 50): array
    {
        $q = http_build_query(['account.id' => $accountId, 'page' => $page, 'per_page' => $perPage]);
        return $this->request('GET', 'zones?' . $q);
    }

    public function zoneDetails(string $zoneId): array
    {
        return $this->request('GET', 'zones/' . $zoneId);
    }

    public function getDevelopmentMode(string $zoneId): array
    {
        return $this->request('GET', 'zones/' . $zoneId . '/settings/development_mode');
    }

    public function setDevelopmentMode(string $zoneId, bool $on): array
    {
        // CF expects value: 'on' or 'off'
        return $this->request('PATCH', 'zones/' . $zoneId . '/settings/development_mode', [
            'value' => $on ? 'on' : 'off'
        ]);
    }

    public function purgeAllCache(string $zoneId): array
    {
        return $this->request('POST', 'zones/' . $zoneId . '/purge_cache', [
            'purge_everything' => true
        ]);
    }

    public function purgeFiles(string $zoneId, array $files): array
    {
        return $this->request('POST', 'zones/' . $zoneId . '/purge_cache', [
            'files' => array_values($files)
        ]);
    }

    public function listDnsRecords(string $zoneId, int $page = 1, int $perPage = 100): array
    {
        $q = http_build_query(['page' => $page, 'per_page' => $perPage]);
        return $this->request('GET', 'zones/' . $zoneId . '/dns_records?' . $q);
    }

    public function createDnsRecord(string $zoneId, array $data): array
    {
        // data: type, name, content, ttl (integer or 1 for auto), proxied (bool)
        return $this->request('POST', 'zones/' . $zoneId . '/dns_records', $data);
    }

    public function updateDnsRecord(string $zoneId, string $recordId, array $data): array
    {
        return $this->request('PUT', 'zones/' . $zoneId . '/dns_records/' . $recordId, $data);
    }

    public function deleteDnsRecord(string $zoneId, string $recordId): array
    {
        return $this->request('DELETE', 'zones/' . $zoneId . '/dns_records/' . $recordId);
    }

    public function getSslMode(string $zoneId): array
    {
        return $this->request('GET', 'zones/' . $zoneId . '/settings/ssl');
    }

    public function setSslMode(string $zoneId, string $mode): array
    {
        // mode: off, flexible, full, strict
        return $this->request('PATCH', 'zones/' . $zoneId . '/settings/ssl', ['value' => $mode]);
    }

    public function getZoneSetting(string $zoneId, string $setting): array
    {
        return $this->request('GET', 'zones/' . $zoneId . '/settings/' . $setting);
    }

    public function setZoneSetting(string $zoneId, string $setting, $value): array
    {
        return $this->request('PATCH', 'zones/' . $zoneId . '/settings/' . $setting, ['value' => $value]);
    }

    public function listIpAccessRules(string $accountId, int $page = 1, int $perPage = 100): array
    {
        $q = http_build_query(['page' => $page, 'per_page' => $perPage]);
        return $this->request('GET', 'accounts/' . $accountId . '/firewall/access_rules/rules?' . $q);
    }

    public function addIpAccessRule(string $accountId, string $mode, string $target, string $value, string $notes = ''): array
    {
        // mode: whitelist (allow), block, challenge; target: ip or ip_range; value: IP string
        return $this->request('POST', 'accounts/' . $accountId . '/firewall/access_rules/rules', [
            'mode' => $mode,
            'configuration' => [ 'target' => $target, 'value' => $value ],
            'notes' => $notes,
        ]);
    }

    public function deleteIpAccessRule(string $accountId, string $ruleId): array
    {
        return $this->request('DELETE', 'accounts/' . $accountId . '/firewall/access_rules/rules/' . $ruleId);
    }
}


