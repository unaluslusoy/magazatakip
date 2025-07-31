const CACHE_NAME = 'magaza-takip-cache-v2';
const OFFLINE_URL = '/offline.html';

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
    '/public/js/pull-to-refresh.js'
];

self.addEventListener('install', function(event) {
    console.log('Service Worker installing...');
    event.waitUntil(
        caches.open(CACHE_NAME).then(function(cache) {
            console.log('Adding files to cache...');
            // URL'leri tek tek ekle hata durumunda devam etsin
            return Promise.allSettled(
                CACHE_URLS.map(url => {
                    return cache.add(url).catch(err => {
                        console.warn('Failed to cache:', url, err);
                        return null;
                    });
                })
            );
        }).then(function() {
            console.log('Service Worker installation completed');
            // Yeni service worker'ı hemen aktif et
            return self.skipWaiting();
        }).catch(function(error) {
            console.error('Service Worker install failed:', error);
        })
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
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
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