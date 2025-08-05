<?php

namespace core;

/**
 * Cache Manager - Redis ve File Cache desteği
 * Performans optimizasyonu için cache yönetimi
 */
class CacheManager {
    
    private static $instance = null;
    private $config;
    private $redis = null;
    private $useRedis = false;
    
    private function __construct() {
        $this->config = $this->getConfig();
        $this->initializeRedis();
    }
    
    /**
     * Singleton pattern
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Cache configuration
     */
    private function getConfig() {
        return [
            'redis' => [
                'host' => '127.0.0.1',
                'port' => 6379,
                'timeout' => 2.0,
                'password' => null,
                'database' => 0
            ],
            'file_cache' => [
                'directory' => __DIR__ . '/../cache/',
                'extension' => '.cache'
            ],
            'default_ttl' => 3600, // 1 saat
            'session_cache_ttl' => 1800, // 30 dakika
        ];
    }
    
    /**
     * Redis bağlantısını başlat
     */
    private function initializeRedis() {
        if (!extension_loaded('redis')) {
            error_log('Redis extension not loaded, using file cache');
            return;
        }
        
        try {
            $this->redis = new \Redis();
            $connected = $this->redis->connect(
                $this->config['redis']['host'],
                $this->config['redis']['port'],
                $this->config['redis']['timeout']
            );
            
            if ($connected) {
                if ($this->config['redis']['password']) {
                    $this->redis->auth($this->config['redis']['password']);
                }
                
                $this->redis->select($this->config['redis']['database']);
                $this->useRedis = true;
                
                error_log('✅ Redis bağlantısı başarılı');
            }
        } catch (Exception $e) {
            error_log('❌ Redis bağlantı hatası: ' . $e->getMessage());
            $this->useRedis = false;
        }
    }
    
    /**
     * Cache'e veri kaydetme
     */
    public function set($key, $value, $ttl = null) {
        $ttl = $ttl ?? $this->config['default_ttl'];
        $serializedValue = serialize($value);
        
        if ($this->useRedis) {
            return $this->redis->setex($key, $ttl, $serializedValue);
        } else {
            return $this->setFileCache($key, $serializedValue, $ttl);
        }
    }
    
    /**
     * Cache'den veri alma
     */
    public function get($key) {
        if ($this->useRedis) {
            $value = $this->redis->get($key);
            return $value !== false ? unserialize($value) : null;
        } else {
            return $this->getFileCache($key);
        }
    }
    
    /**
     * Cache'den veri silme
     */
    public function delete($key) {
        if ($this->useRedis) {
            return $this->redis->del($key) > 0;
        } else {
            return $this->deleteFileCache($key);
        }
    }
    
    /**
     * Tüm cache'i temizleme
     */
    public function flush() {
        if ($this->useRedis) {
            return $this->redis->flushDB();
        } else {
            return $this->flushFileCache();
        }
    }
    
    /**
     * Cache'de key var mı kontrolü
     */
    public function exists($key) {
        if ($this->useRedis) {
            return $this->redis->exists($key) > 0;
        } else {
            return $this->fileExists($key);
        }
    }
    
    /**
     * Multiple get
     */
    public function getMultiple($keys) {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }
        return $result;
    }
    
    /**
     * Multiple set
     */
    public function setMultiple($items, $ttl = null) {
        $success = true;
        foreach ($items as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        return $success;
    }
    
    /**
     * Cache istatistikleri
     */
    public function getStats() {
        if ($this->useRedis) {
            return [
                'type' => 'Redis',
                'connected' => $this->redis->ping() === '+PONG',
                'info' => $this->redis->info('memory'),
                'keys_count' => $this->redis->dbSize()
            ];
        } else {
            $cacheDir = $this->config['file_cache']['directory'];
            $files = glob($cacheDir . '*' . $this->config['file_cache']['extension']);
            
            return [
                'type' => 'File Cache',
                'cache_directory' => $cacheDir,
                'cache_files_count' => count($files),
                'total_size' => $this->getDirectorySize($cacheDir)
            ];
        }
    }
    
    // FILE CACHE METHODS
    
    /**
     * File cache kaydetme
     */
    private function setFileCache($key, $value, $ttl) {
        $this->ensureCacheDirectory();
        $filename = $this->getFileCachePath($key);
        
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        
        return file_put_contents($filename, serialize($data), LOCK_EX) !== false;
    }
    
    /**
     * File cache'den okuma
     */
    private function getFileCache($key) {
        $filename = $this->getFileCachePath($key);
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $data = unserialize(file_get_contents($filename));
        
        if ($data['expires'] < time()) {
            unlink($filename);
            return null;
        }
        
        return unserialize($data['value']);
    }
    
    /**
     * File cache silme
     */
    private function deleteFileCache($key) {
        $filename = $this->getFileCachePath($key);
        return file_exists($filename) ? unlink($filename) : true;
    }
    
    /**
     * Tüm file cache temizleme
     */
    private function flushFileCache() {
        $cacheDir = $this->config['file_cache']['directory'];
        $files = glob($cacheDir . '*' . $this->config['file_cache']['extension']);
        
        $success = true;
        foreach ($files as $file) {
            if (!unlink($file)) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    /**
     * File cache path oluşturma
     */
    private function getFileCachePath($key) {
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
        return $this->config['file_cache']['directory'] . $safeKey . $this->config['file_cache']['extension'];
    }
    
    /**
     * Cache directory'nin var olduğundan emin ol
     */
    private function ensureCacheDirectory() {
        $dir = $this->config['file_cache']['directory'];
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    
    /**
     * File exists kontrolü
     */
    private function fileExists($key) {
        $filename = $this->getFileCachePath($key);
        
        if (!file_exists($filename)) {
            return false;
        }
        
        $data = unserialize(file_get_contents($filename));
        
        if ($data['expires'] < time()) {
            unlink($filename);
            return false;
        }
        
        return true;
    }
    
    /**
     * Directory size hesaplama
     */
    private function getDirectorySize($directory) {
        $size = 0;
        foreach (glob($directory . '*') as $file) {
            $size += filesize($file);
        }
        return $size;
    }
    
    // HELPER METHODS
    
    /**
     * Auth cache için özel key
     */
    public function getAuthCacheKey($userId) {
        return "auth_user_{$userId}";
    }
    
    /**
     * Session cache için özel key  
     */
    public function getSessionCacheKey($sessionId) {
        return "session_{$sessionId}";
    }
    
    /**
     * User data cache
     */
    public function cacheUserData($userId, $userData, $ttl = null) {
        $key = $this->getAuthCacheKey($userId);
        $ttl = $ttl ?? $this->config['session_cache_ttl'];
        return $this->set($key, $userData, $ttl);
    }
    
    /**
     * User data cache'den alma
     */
    public function getCachedUserData($userId) {
        $key = $this->getAuthCacheKey($userId);
        return $this->get($key);
    }
    
    /**
     * User cache temizleme
     */
    public function clearUserCache($userId) {
        $key = $this->getAuthCacheKey($userId);
        return $this->delete($key);
    }
    
    /**
     * Cache durumu kontrolü
     */
    public function isRedisAvailable() {
        return $this->useRedis;
    }
    
    /**
     * Cache temizlik (expired files)
     */
    public function cleanup() {
        if (!$this->useRedis) {
            $cacheDir = $this->config['file_cache']['directory'];
            $files = glob($cacheDir . '*' . $this->config['file_cache']['extension']);
            
            $cleaned = 0;
            foreach ($files as $file) {
                $data = unserialize(file_get_contents($file));
                if ($data['expires'] < time()) {
                    unlink($file);
                    $cleaned++;
                }
            }
            
            return $cleaned;
        }
        
        return 0; // Redis automatically handles expiration
    }
}
?>


