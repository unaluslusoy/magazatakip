const CACHE_NAME = 'magaza-takip-cache-v4';
const APP_VERSION = '1.3.1';
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
                
                // URL'leri tek tek ekle hata durumunda devam etsin
                await Promise.allSettled(
                    CACHE_URLS.map(url => {
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
                return self.skipWaiting();
                
            } catch (error) {
                console.error('❌ Service Worker install failed:', error);
                throw error;
            }
        })()
    );
});
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('OneSignalSDKWorker.js')
        .then(function(registration) {
            console.log('OneSignal Service Worker registered with scope:', registration.scope);
        }).catch(function(error) {
        console.log('OneSignal Service Worker registration failed:', error);
    });
}
self.addEventListener('activate', function(event) {
    console.log('Service Worker activating...');
    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all([
                // Eski cache'leri temizle
                ...cacheNames.filter(function(cacheName) {
                    return cacheName !== CACHE_NAME;
                }).map(function(cacheName) {
                    console.log('Deleting old cache:', cacheName);
                    return caches.delete(cacheName);
                }),
                // Tüm clientları kontrol et
                self.clients.claim()
            ]);
        })
    );
});

self.addEventListener('fetch', function(event) {
    // Sadece GET isteklerini cache'le
    if (event.request.method !== 'GET') return;
    
    event.respondWith(
        caches.match(event.request).then(function(response) {
            if (response) {
                console.log('Cache hit for:', event.request.url);
                return response;
            }
            
            return fetch(event.request).then(function(response) {
                // Network'ten gelen valid yanıtları cache'le
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }
                
                const responseToCache = response.clone();
                caches.open(CACHE_NAME).then(function(cache) {
                    cache.put(event.request, responseToCache);
                });
                
                return response;
            }).catch(function() {
                // Offline durumdaysa, HTML sayfaları için offline sayfasını döndür
                if (event.request.mode === 'navigate') {
                    return caches.match(OFFLINE_URL);
                }
            });
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
importScripts('https://cdn.onesignal.com/sdks/OneSignalSDKWorker.js');

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
    const notificationData = event.notification.data;
    const url = notificationData.url;

    event.notification.close();

    if (url) {
        clients.openWindow(url);
    }
});

// Push notification handling
self.addEventListener('push', function(event) {
    if (event.data) {
        const data = event.data.json();
        
        const options = {
            body: data.body || 'Yeni bildirim',
            icon: '/public/images/icons/icon-192x192.png',
            badge: '/public/images/icons/icon-96x96.png',
            data: {
                url: data.url || '/'
            },
            actions: [
                {
                    action: 'open',
                    title: 'Aç'
                },
                {
                    action: 'close', 
                    title: 'Kapat'
                }
            ]
        };
        
        event.waitUntil(
            self.registration.showNotification(data.title || 'Mağaza Takip', options)
        );
    }
});

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