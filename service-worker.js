// OneSignal v16 SW script en üstte olmalı
try { importScripts('https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.sw.js'); } catch (e) {}

const CACHE_NAME = 'magaza-takip-cache-v9';
const APP_VERSION = '1.3.5';
const OFFLINE_URL = '/offline.html';

// Version management
const VERSION_STORAGE_KEY = 'app-version';
const UPDATE_CHECK_INTERVAL = 30 * 60 * 1000; // 30 dakika

const CACHE_URLS = [
    '/',
    '/anasayfa',
    '/profil',
    '/offline.html',
    '/network-offline.html',
    '/public/css/style.bundle.css',
    '/public/js/scripts.bundle.js',
    '/public/plugins/global/plugins.bundle.js',
    '/public/images/icons/icon-192x192.png',
    '/public/images/icons/icon-512x512.png',
    '/public/images/icons/icon-192x192-maskable.png',
    '/public/images/icons/icon-512x512-maskable.png',
    '/public/images/apple-touch-icon.png',
    '/public/images/apple-touch-icon-152x152.png',
    '/public/images/apple-touch-icon-144x144.png',
    '/public/images/apple-touch-icon-120x120.png',
    '/favicon.ico',
    '/public/media/logos/default.svg',
    'https://magazatakip.com.tr/public/media/logos/default.svg',
    '/public/media/logos/default-dark.svg',
    '/public/js/pwa-analytics.js',
    '/auth-guard.js',
    '/public/js/network-monitor.js',
    '/public/js/splash-screen.js',
    '/public/js/business-loader.js',
    '/public/js/page-transitions.js',
    '/public/js/pwa-install.js',
    '/public/js/background-sync.js',
    '/public/js/pull-to-refresh.js',
    '/public/js/view-transitions.js',
    '/public/js/modern-pull-to-refresh.js'
];

self.addEventListener('install', function(event) {
    console.log(`🔄 Service Worker v${APP_VERSION} installing...`);
    
    event.waitUntil(
        (async () => {
            try {
                // Önceki versiyon kontrolü
                const previousVersion = await getStoredVersion();
                const isUpdate = previousVersion && previousVersion !== APP_VERSION;
                
                if (isUpdate) {
                    console.log(`📱 Update detected: ${previousVersion} → ${APP_VERSION}`);
                    
                    // Eski cache'leri temizle
                    await clearOldCaches();
                }

                // Yeni cache oluştur
                const cache = await caches.open(CACHE_NAME);
                console.log('📦 Adding files to cache...');
                
                // URL'leri filtrele ve tek tek ekle
                const validUrls = CACHE_URLS.filter(url => {
                    // Chrome extension ve invalid URL'leri filtrele
                    return !url.startsWith('chrome-extension:') && 
                           !url.startsWith('moz-extension:') &&
                           !url.includes('extension://') &&
                           url.startsWith('/') || url.startsWith('http');
                });
                
                console.log(`📦 Caching ${validUrls.length} valid URLs...`);
                
                await Promise.allSettled(
                    validUrls.map(url => {
                        return cache.add(url).catch(err => {
                            console.warn('❌ Failed to cache:', url, err);
                            return null;
                        });
                    })
                );

                // Versiyon bilgisini kaydet
                await storeVersion(APP_VERSION);
                
                console.log(`✅ Service Worker v${APP_VERSION} installation completed`);
                
                // Update notification gönder
                if (isUpdate) {
                    await notifyClients('UPDATE_AVAILABLE', {
                        version: APP_VERSION,
                        previousVersion: previousVersion
                    });
                }
                
                // Yeni service worker'ı hemen aktif et
                // Hemen aktif ol
                await self.skipWaiting();
                await self.clients.claim();
                return true;
                
            } catch (error) {
                console.error('❌ Service Worker install failed:', error);
                throw error;
            }
        })()
    );
});
// NOTE: OneSignal SDK worker import is handled below via importScripts.
self.addEventListener('activate', function(event) {
    console.log('Service Worker activating...');
    event.waitUntil(
        (async () => {
            const cacheNames = await caches.keys();
            await Promise.all(
                cacheNames.filter(name => name !== CACHE_NAME).map(name => {
                    console.log('Deleting old cache:', name);
                    return caches.delete(name);
                })
            );
            await self.clients.claim();
            // Tüm client'lara controller değişimini duyur (update prompt tekrarını engeller)
            await notifyClients('UPDATE_APPLIED', { version: APP_VERSION, cache: CACHE_NAME });
        })()
    );
});

// Network-first for HTML and API; Cache-first for static assets
self.addEventListener('fetch', function(event) {
    const req = event.request;

    // Yazma istekleri: asla cache'e karışma
    if (req.method !== 'GET') {
        return;
    }

    const url = new URL(req.url);

    // OneSignal SDK ve kritik runtime scriptler: her zaman network'den al (cache bypass)
    const isOneSignal = /OneSignalSDK/i.test(url.pathname) || /cdn.onesignal.com/i.test(url.hostname);
    const isRuntimeScript = url.pathname.endsWith('/public/js/token-registration.js');
    if (isOneSignal || isRuntimeScript) {
        event.respondWith(fetch(req, { cache: 'no-store' }).catch(() => fetch(req)));
        return;
    }
    const isSameOrigin = url.origin === self.location.origin;
    const acceptHeader = req.headers.get('accept') || '';
    const isHtml = req.mode === 'navigate' || acceptHeader.includes('text/html');
    const isApi = isSameOrigin && url.pathname.startsWith('/api/');

    if (isApi) {
        // API istekleri: her zaman network, cacheleme yok ve varyantları atla
        event.respondWith((async () => {
            try {
                const res = await fetch(req, { cache: 'no-store', credentials: 'include' });
                return res;
            } catch (e) {
                // Ağ hatası durumunda 503 yerine 200 dön; istemci success=false üzerinden yönetir
                // Ayrıca client'lara offline bilgisini ilet
                try { await notifyClients('API_OFFLINE', { path: req.url }); } catch(_) {}
                return new Response(JSON.stringify({ success: false, message: 'offline', path: req.url }), {
                    headers: { 'Content-Type': 'application/json' },
                    status: 200
                });
            }
        })());
        return;
    }

    if (isHtml) {
        // HTML sayfaları: network-first, başarısızsa offline
        event.respondWith(
            fetch(new Request(req, { cache: 'no-store' }))
                .then(response => response)
                .catch(async () => {
                    const cached = await caches.match(req);
                    return cached || caches.match(OFFLINE_URL);
                })
        );
        return;
    }

    // Statik varlıklar: cache-first (ama no-cache query-string varsa network tercih et)
    event.respondWith(
        caches.match(req, { ignoreVary: true, ignoreSearch: false }).then(function(response) {
            const forceFresh = url.searchParams.has('v') || url.searchParams.has('_t') || url.searchParams.has('_cache');
            if (forceFresh) {
                return fetch(req, { cache: 'no-store' }).then(networkRes => {
                    if (networkRes && networkRes.status === 200) {
                        const resClone = networkRes.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(req, resClone));
                    }
                    return networkRes;
                }).catch(() => response);
            }
            return response || fetch(req).then(function(networkRes) {
                // Uygun yanıtları cache'e koy
                if (networkRes && networkRes.status === 200 && (networkRes.type === 'basic' || networkRes.type === 'opaque')) {
                    const resClone = networkRes.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(req, resClone));
                }
                return networkRes;
            }).catch(() => undefined);
        })
    );
});

// Background Sync için
self.addEventListener('sync', function(event) {
    if (event.tag === 'background-sync') {
        console.log('Background sync triggered');
        event.waitUntil(syncData());
    }
});

// Sync data fonksiyonu
function syncData() {
    return new Promise(function(resolve) {
        // Offline'da toplanan verileri senkronize et
        console.log('Syncing offline data...');
        resolve();
    });
}
// OneSignal SW importu yukarıda yapıldı.

// Update handling
self.addEventListener('message', function(event) {
    const { type } = event.data || {};
    
    try {
        switch (type) {
            case 'SKIP_WAITING':
                console.log('⚡ Skip waiting mesajı alındı');
                self.skipWaiting();
                
                // Response gönder
                if (event.source) {
                    event.source.postMessage({
                        type: 'SKIP_WAITING_RESPONSE',
                        success: true
                    });
                }
                break;
                
            case 'GET_VERSION':
                // Version bilgisi talep edildi
                if (event.source) {
                    event.source.postMessage({
                        type: 'GET_VERSION_RESPONSE',
                        version: APP_VERSION,
                        cache: CACHE_NAME
                    });
                }
                break;
            case 'INVALIDATE_CACHE':
                // Belirtilen URL'leri cache'den düşür
                event.waitUntil((async () => {
                    const cache = await caches.open(CACHE_NAME);
                    const urls = (event.data && event.data.urls) || [];
                    await Promise.all(urls.map(u => cache.delete(u).catch(() => null)));
                })());
                break;
                
            default:
                console.log('Unknown message type:', type);
        }
    } catch (error) {
        console.error('Message handler error:', error);
        
        // Error response gönder
        if (event.source) {
            event.source.postMessage({
                type: `${type}_RESPONSE`,
                success: false,
                error: error.message
            });
        }
    }
});

// Notification click handling  
self.addEventListener('notificationclick', function(event) {
    try {
        const notificationData = event.notification?.data || {};
        const url = notificationData.url || '/kullanici/bildirimler';
        event.notification.close();
        if (url) {
            event.waitUntil(clients.openWindow(url));
        }
    } catch (e) {}
});

// Push notification handling
// OneSignal bildirimlerini kendi SW'ı işler; çakışmayı önlemek için push handler kaldırıldı.

// ==============================================
// VERSION MANAGEMENT & UPDATE HELPER FUNCTIONS
// ==============================================

/**
 * Stored version'ı al
 */
async function getStoredVersion() {
    try {
        const cache = await caches.open('app-metadata');
        const response = await cache.match(VERSION_STORAGE_KEY);
        if (response) {
            const data = await response.json();
            return data.version;
        }
    } catch (error) {
        console.warn('Version storage read error:', error);
    }
    return null;
}

/**
 * Version bilgisini kaydet
 */
async function storeVersion(version) {
    try {
        const cache = await caches.open('app-metadata');
        const data = { version, timestamp: Date.now() };
        const response = new Response(JSON.stringify(data));
        await cache.put(VERSION_STORAGE_KEY, response);
        console.log(`💾 Version stored: ${version}`);
    } catch (error) {
        console.error('Version storage error:', error);
    }
}

/**
 * Eski cache'leri temizle
 */
async function clearOldCaches() {
    try {
        const cacheNames = await caches.keys();
        const oldCaches = cacheNames.filter(name => 
            name.startsWith('magaza-takip-cache-') && name !== CACHE_NAME
        );
        
        await Promise.all(
            oldCaches.map(cacheName => {
                console.log(`🗑️ Deleting old cache: ${cacheName}`);
                return caches.delete(cacheName);
            })
        );
        
        console.log(`✅ Cleaned ${oldCaches.length} old caches`);
    } catch (error) {
        console.error('Cache cleanup error:', error);
    }
}

/**
 * Tüm client'lara mesaj gönder
 */
async function notifyClients(type, data = {}) {
    try {
        const clients = await self.clients.matchAll({
            includeUncontrolled: true,
            type: 'window'
        });
        
        clients.forEach(client => {
            client.postMessage({
                type,
                data: {
                    ...data,
                    timestamp: Date.now()
                }
            });
        });
        
        console.log(`📡 Notified ${clients.length} clients:`, type);
    } catch (error) {
        console.error('Client notification error:', error);
    }
}

/**
 * Update kontrolü yap
 */
async function checkForUpdates() {
    try {
        const response = await fetch('/api/version', {
            cache: 'no-cache'
        });
        
        if (response.ok) {
            const serverData = await response.json();
            const serverVersion = serverData.version;
            
            if (serverVersion && serverVersion !== APP_VERSION) {
                console.log(`🔄 Server update detected: ${APP_VERSION} → ${serverVersion}`);
                
                // Client'lara update mevcut bilgisi gönder
                await notifyClients('UPDATE_DETECTED', {
                    currentVersion: APP_VERSION,
                    availableVersion: serverVersion
                });
                
                return true;
            }
        }
    } catch (error) {
        console.warn('Update check failed:', error);
    }
    
    return false;
}

// Periodic update check
setInterval(checkForUpdates, UPDATE_CHECK_INTERVAL);

console.log(`🚀 Service Worker v${APP_VERSION} ready with update notifications!`);