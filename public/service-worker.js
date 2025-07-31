self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open('magaza-takip-cache-v1').then(function(cache) {
            return cache.addAll([
                '/',
                '/public/css/styles.css',
                '/public/js/scripts.js',
                '/public/images/icons/icon-192x192.png',
                '/public/images/icons/icon-512x512.png'
            ]);
        })
    );
    console.log('Service Worker installing.');
});

