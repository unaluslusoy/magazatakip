<?php require_once __DIR__ . '/../layouts/layout/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/layout/navbar.php'; ?>

<div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="container-xxl py-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ayarlar</h3>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <h5 class="mb-3">Bildirim İzinleri</h5>
                            <p class="text-muted">Web push bildirimlerini açıp kapatabilirsiniz.</p>
                            <div class="d-flex gap-2 flex-wrap">
                                <button id="enable-notifications" class="btn btn-primary">
                                    <i class="ki-outline ki-notification-on me-2"></i> Bildirimleri Aç
                                </button>
                                <button id="disable-notifications" class="btn btn-light-danger">
                                    <i class="ki-outline ki-notification-off me-2"></i> Bildirimleri Kapat
                                </button>
                                <button id="refresh-status" class="btn btn-light">
                                    <i class="ki-outline ki-refresh me-2"></i> Durumu Yenile
                                </button>
                            </div>
                            <div class="mt-3">
                                <span class="text-muted">Durum: </span>
                                <span id="notification-status" class="fw-bold">Bilinmiyor</span>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <h5 class="mb-3">PWA & Önbellek</h5>
                            <p class="text-muted">Yeni içerikler için servis çalışanı ve cache kontrolü.</p>
                            <div class="d-flex gap-2 flex-wrap">
                                <button id="check-updates" class="btn btn-outline-primary">
                                    <i class="ki-outline ki-search-list me-2"></i> Güncellemeleri Kontrol Et
                                </button>
                                <button id="force-reload" class="btn btn-outline-secondary">
                                    <i class="ki-outline ki-refresh me-2"></i> Zorla Yeniden Yükle
                                </button>
                                <button id="clear-caches" class="btn btn-outline-danger">
                                    <i class="ki-outline ki-trash me-2"></i> Tüm Cache'i Temizle
                                </button>
                            </div>
                            <div class="mt-3">
                                <span class="text-muted">Uygulama Sürümü: </span>
                                <span id="app-version" class="fw-bold">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const statusEl = document.getElementById('notification-status');
    const versionEl = document.getElementById('app-version');

    async function refreshPermissionStatus() {
        try {
            if (window.OneSignal && OneSignal.Notifications) {
                const perm = await OneSignal.Notifications.permission;
                statusEl.textContent = perm === 'granted' ? 'Açık' : (perm === 'denied' ? 'Engelli' : 'Sorulmadı');
            } else {
                statusEl.textContent = Notification && Notification.permission ? Notification.permission : 'Bilinmiyor';
            }
        } catch (e) {
            statusEl.textContent = 'Bilinmiyor';
        }
    }

    document.getElementById('enable-notifications').addEventListener('click', async () => {
        try {
            if (window.OneSignal && OneSignal.Notifications) {
                await OneSignal.Notifications.requestPermission();
            } else if (window.Notification && Notification.requestPermission) {
                await Notification.requestPermission();
            }
            await refreshPermissionStatus();
        } catch (e) { console.log(e); }
    });

    document.getElementById('disable-notifications').addEventListener('click', async () => {
        try {
            if (window.OneSignal && OneSignal.Notifications) {
                await OneSignal.User.PushSubscription.optOut();
            }
            await refreshPermissionStatus();
        } catch (e) { console.log(e); }
    });

    document.getElementById('refresh-status').addEventListener('click', refreshPermissionStatus);

    // PWA & cache
    document.getElementById('check-updates').addEventListener('click', async () => {
        if (navigator.serviceWorker && navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage({ type: 'GET_VERSION' });
        }
        // Trigger fetch of assets with cache-busting
        const assets = ['/public/js/scripts.bundle.js', '/public/css/style.bundle.css'];
        await Promise.allSettled(assets.map(u => fetch(u + '?v=' + Date.now(), { cache: 'no-store' })));
        alert('Güncellemeler kontrol edildi.');
    });

    document.getElementById('force-reload').addEventListener('click', () => {
        const url = new URL(window.location.href);
        url.searchParams.set('_cache', Date.now().toString());
        window.location.href = url.toString();
    });

    document.getElementById('clear-caches').addEventListener('click', async () => {
        if ('caches' in window) {
            const names = await caches.keys();
            await Promise.all(names.map(n => caches.delete(n)));
        }
        if (navigator.serviceWorker) {
            navigator.serviceWorker.getRegistrations().then(regs => regs.forEach(r => r.update()));
        }
        alert('Tüm cache temizlendi.');
    });

    // SW version listener
    if (navigator.serviceWorker) {
        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data && event.data.type === 'GET_VERSION_RESPONSE') {
                versionEl.textContent = `${event.data.version} (${event.data.cache})`;
            }
        });
        // İlk sorgu
        setTimeout(() => {
            if (navigator.serviceWorker.controller) {
                navigator.serviceWorker.controller.postMessage({ type: 'GET_VERSION' });
            }
        }, 500);
    }

    // İlk durum
    refreshPermissionStatus();
});
</script>

<?php require_once __DIR__ . '/../layouts/layout/footer.php'; ?>


