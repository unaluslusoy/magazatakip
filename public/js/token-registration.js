// OneSignal SDK yüklenme kontrolü
function waitForOneSignal(callback, timeout = 15000) {
    const startTime = Date.now();

    function checkOneSignal() {
        try {
            const pageScriptLoaded = !!document.querySelector('script[src*="OneSignalSDK.page.js"]');
            const sdkReady = !!window.OneSignalDeferred || (typeof window.OneSignal === 'object' || typeof window.OneSignal === 'function');
            console.log('OneSignal kontrol ediliyor...', { pageScriptLoaded, sdkReady });

            if (pageScriptLoaded || sdkReady) {
                console.log('OneSignal SDK bulundu/sıraya hazır, başlatılıyor...');
                callback();
                return;
            }
        } catch (e) {}

        if (Date.now() - startTime < timeout) {
            setTimeout(checkOneSignal, 500);
        } else {
            console.error('OneSignal SDK yüklenemedi, timeout');
            // Fallback: Manuel izin isteme
            requestNotificationPermissionManually();
        }
    }

    checkOneSignal();
}

// Basit toast/alert gösterimi (hem admin hem kullanıcı)
function ensureToastContainer() {
    let container = document.getElementById('global-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'global-toast-container';
        container.style.position = 'fixed';
        container.style.top = '16px';
        container.style.right = '16px';
        container.style.zIndex = '10000';
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.gap = '8px';
        document.body.appendChild(container);
    }
    return container;
}

function showToast(message, variant = 'success', durationMs = 4000) {
    try {
        const container = ensureToastContainer();
        const toast = document.createElement('div');
        toast.className = `alert alert-${variant} shadow-sm mb-0 py-2 px-3`;
        toast.style.minWidth = '260px';
        toast.style.maxWidth = '420px';
        toast.style.wordBreak = 'break-word';
        toast.innerHTML = `${message}`;
        container.appendChild(toast);
        setTimeout(() => { if (toast && toast.parentNode) toast.remove(); }, durationMs);
    } catch (e) {
        // no-op
    }
}

// TR telefon normalizasyonu (E.164: +90XXXXXXXXXX)
function normalizePhoneTR(raw) {
    try {
        if (!raw) return null;
        let s = String(raw).trim();
        // + ile başlıyorsa kabullen
        if (s.startsWith('+')) return s.replace(/\s+/g, '');
        // Sayı dışını temizle
        s = s.replace(/[^0-9]/g, '');
        if (s.startsWith('90') && s.length === 12) {
            return '+' + s;
        }
        if (s.startsWith('0')) {
            s = s.substring(1);
        }
        if (s.length === 10) {
            return '+90' + s;
        }
        // Fallback
        return s.length > 0 ? '+' + s : null;
    } catch (e) { return null; }
}

// İzin banner'ı (ilk yüklemede göstermek için)
function showPermissionBanner() {
    if (document.getElementById('notif-permission-banner')) return;
    const bar = document.createElement('div');
    bar.id = 'notif-permission-banner';
    bar.className = 'position-fixed top-0 start-50 translate-middle-x bg-primary text-white px-4 py-2 rounded-bottom shadow';
    bar.style.zIndex = 9999;
    bar.innerHTML = '<strong>Bildirim izni gerekli</strong> — Anında uyarılar için <button class="btn btn-light btn-sm ms-2" id="enable-notifs-btn">İzin Ver</button>';
    document.body.appendChild(bar);
    const btn = document.getElementById('enable-notifs-btn');
    if (btn) {
        btn.onclick = async () => {
            try {
                if (window.OneSignal && OneSignal?.Notifications?.requestPermission) {
                    await OneSignal.Notifications.requestPermission();
                } else if ('Notification' in window && Notification.permission === 'default') {
                    await Notification.requestPermission();
                }
                showToast('Bildirim izni güncellendi', 'info');
                try { postTokenIfNeeded(); } catch (e) {}
            } catch (e) {
                showToast('Bildirim izni verilemedi', 'danger');
            } finally {
                try { bar.remove(); } catch (e) {}
            }
        };
    }
}

// OneSignal User/Subscription ID hazır olana kadar bekle (PWA'da gerekli)
async function waitForOneSignalId(timeoutMs = 15000) {
    const start = Date.now();
    while (Date.now() - start < timeoutMs) {
        try {
            let id = null;
            // v16: include_player_ids için PushSubscription.id gerekir; önce bunu dene
            if (window.OneSignal && OneSignal.User && OneSignal.User.PushSubscription) {
                id = OneSignal.User.PushSubscription.id || null;
            }
            // Gerekirse User.getId() (alias) dönebilir, ama sunucuya player id olarak göndermemek daha doğru
            if (!id && window.OneSignal && OneSignal.User && typeof OneSignal.User.getId === 'function') {
                // Not: Bunu gönderirsek sunucu alias'ı kullanmalı (external_id). Biz kaydetmeyelim.
                const aliasId = await OneSignal.User.getId();
                if (aliasId) {
                    console.log('OneSignal alias (User.getId) hazır ancak subscription id yok');
                }
            }
            if (id) {
                console.log('OneSignal onesignalId hazır:', id);
                return id;
            }
        } catch (e) {
            // beklemeye devam
        }
        await new Promise(r => setTimeout(r, 500));
    }
    throw new Error('OneSignal onesignalId zaman aşımı');
}

// ID hazırsa token'ı gerektikçe gönder
async function postTokenIfNeeded() {
    try {
        const id = await waitForOneSignalId(15000);
        const lastPosted = localStorage.getItem('onesignal_last_id');
        if (id && id !== lastPosted) {
            console.log('OneSignal ID değişti/yeni, sunucuya gönderiliyor...');
            await sendTokenToServer(id);
            localStorage.setItem('onesignal_last_id', id);
        } else {
            console.log('OneSignal ID zaten kayıtlı görünüyor, gönderim atlandı');
        }
    } catch (e) {
        console.warn('postTokenIfNeeded bekleme hatası:', e.message);
    }
}

// Manuel bildirim izni isteme
function requestNotificationPermissionManually() {
    console.log('Manuel bildirim izni isteniyor...');
    
    if ('Notification' in window) {
        if (Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                console.log('Bildirim izni:', permission);
                if (permission === 'granted') {
                    console.log('Bildirim izni verildi, OneSignal yeniden deneniyor...');
                    // OneSignal'ı tekrar dene
                    setTimeout(() => {
                        if (window.OneSignal) {
                            initializeOneSignal();
                        }
                    }, 1000);
                }
            });
        } else if (Notification.permission === 'granted') {
            console.log('Bildirim izni zaten verilmiş');
        } else {
            console.log('Bildirim izni reddedilmiş');
        }
    }
}

// OneSignal v16 başlatma fonksiyonu
async function initializeOneSignal() {
    try {
        console.log('OneSignal başlatılıyor...');
        
        // OneSignal yapılandırmasını al
        const response = await fetch('/api/onesignal/config');
        console.log('OneSignal config response:', response);
        
        if (!response.ok) {
            throw new Error(`OneSignal yapılandırması alınamadı: ${response.status}`);
        }
        
        // Boş veya hatalı JSON yanıtlarını güvenle işle
        let configRaw = await response.text();
        if (!configRaw || !configRaw.trim()) {
            throw new Error('OneSignal config boş döndü');
        }
        let config;
        try {
            config = JSON.parse(configRaw);
        } catch (e) {
            console.error('OneSignal config parse hatası, metin:', configRaw);
            throw e;
        }
        console.log('OneSignal config alındı:', config);
        
        if (!config.success || !config.data || !config.data.app_id) {
            throw new Error('OneSignal App ID bulunamadı');
        }
        
        // OneSignal v16 init
        window.OneSignalDeferred = window.OneSignalDeferred || [];
        OneSignalDeferred.push(async function(OneSignal) {
            await OneSignal.init({
                appId: config.data.app_id,
                allowLocalhostAsSecureOrigin: true,
            });

            console.log('OneSignal v16 initialized with appId:', config.data.app_id);

            // Event listener'ları ekle
            setupOneSignalEventHandlers(OneSignal);

            // V16 önerilen: login ile kullanıcıyı OneSignal kimliğiyle ilişkilendir
            try {
                if (window.CURRENT_USER_ID) {
                    await OneSignal.login(String(window.CURRENT_USER_ID));
                    console.log('OneSignal login yapıldı kullanıcı:', window.CURRENT_USER_ID);

                    // Profilden email/telefon ve diğer tag bilgilerini al, uygun kanalları abone et
                    try {
                        const profileResp = await fetch('/api/user/profile', { credentials: 'include' });
                        if (profileResp.ok) {
                            const profileJson = await profileResp.json();
                            const user = profileJson?.data || {};
                            const email = user.email || null;
                            const phone = normalizePhoneTR(user.telefon || user.phone || null);
                            const magazaId = user.magaza_id || null;
                            const rol = (user.yonetici ? 'admin' : 'user');

                            // v16 tercih: addEmail/addSms; yoksa setEmail/setSMSNumber fallback
                            if (email) {
                                if (OneSignal?.User?.addEmail) {
                                    try { await OneSignal.User.addEmail(String(email)); console.log('OneSignal addEmail:', email); } catch(e) { console.warn('OneSignal addEmail hatası', e); }
                                } else if (OneSignal?.User?.setEmail) {
                                    try { await OneSignal.User.setEmail(String(email)); console.log('OneSignal setEmail:', email); } catch(e) { console.warn('OneSignal setEmail hatası', e); }
                                }
                            }
                            if (phone) {
                                if (OneSignal?.User?.addSms) {
                                    try { await OneSignal.User.addSms(String(phone)); console.log('OneSignal addSms:', phone); } catch(e) { console.warn('OneSignal addSms hatası', e); }
                                } else if (OneSignal?.User?.setSMSNumber) {
                                    try { await OneSignal.User.setSMSNumber(String(phone)); console.log('OneSignal setSMSNumber:', phone); } catch(e) { console.warn('OneSignal setSMSNumber hatası', e); }
                                }
                            }

                            // Kullanışlı tag'ler
                            try {
                                if (OneSignal?.User?.addTag) {
                                    await OneSignal.User.addTag('rol', rol);
                                    if (magazaId) { await OneSignal.User.addTag('magaza_id', String(magazaId)); }
                                } else if (OneSignal?.User?.addTags) {
                                    const tags = { rol };
                                    if (magazaId) tags.magaza_id = String(magazaId);
                                    await OneSignal.User.addTags(tags);
                                }
                            } catch (e) { console.warn('OneSignal tag set hatası', e); }

                            // Dil bilgisi (ISO 639-1) ve saat dilimi
                            try { if (OneSignal?.User?.setLanguage) await OneSignal.User.setLanguage('tr'); } catch (e) {}
                        }
                    } catch (e) { console.warn('Profil bilgisi alınamadı', e); }
                }
            } catch (e) { console.warn('OneSignal.login hatası', e); }

            const permission = await OneSignal.Notifications.permission;
            if (permission === 'granted') {
                setTimeout(() => { getAndSendToken(); }, 1000);
                postTokenIfNeeded();
            } else {
                try { await OneSignal.Notifications.requestPermission(); } catch(e) {}
                postTokenIfNeeded();
            }

            OneSignal.User.PushSubscription.addEventListener('change', (e) => {
                console.log('PushSubscription change:', e);
                if (e.current && e.current.optedIn) {
                    setTimeout(() => { getAndSendToken(); }, 1000);
                    postTokenIfNeeded();
                } else {
                    removeTokenFromServer();
                }
            });
        });
        
    } catch (error) {
        console.error('OneSignal başlatma hatası:', error);
        // Fallback: Manuel izin isteme
        requestNotificationPermissionManually();
    }
}

// OneSignal SDK hazır olduğunda başlat
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM yüklendi, OneSignal bekleniyor...');
    waitForOneSignal(initializeOneSignal);
    // İlk yüklemede izin banner'ı
    try {
        if ('Notification' in window && Notification.permission === 'default') {
            setTimeout(showPermissionBanner, 800);
        }
    } catch (e) {}
    // SW üzerinden version/check ve update prompt örneği
    if (navigator.serviceWorker) {
        navigator.serviceWorker.addEventListener('message', (event) => {
            const { type } = event.data || {};
            if (type === 'UPDATE_DETECTED') {
                try {
                    const { currentVersion, availableVersion } = event.data?.data || {};
                    // Aynı versiyon için birden fazla kez göstermeyi engelle
                    const lastSuppressed = localStorage.getItem('pwa_update_suppressed');
                    if (lastSuppressed && lastSuppressed === String(availableVersion)) {
                        return;
                    }

                    const bar = document.createElement('div');
                    bar.className = 'position-fixed top-0 start-50 translate-middle-x bg-primary text-white px-4 py-2 rounded-bottom shadow';
                    bar.style.zIndex = 9999;
                    bar.innerHTML = '<strong>Güncelleme mevcut</strong> — En yeni sürümü görmek için <button class="btn btn-light btn-sm ms-2" id="sw-reload-btn">Yenile</button>';
                    document.body.appendChild(bar);

                    const applyUpdate = async () => {
                        try {
                            if ('serviceWorker' in navigator) {
                                const reg = await navigator.serviceWorker.getRegistration();
                                if (reg && reg.waiting) {
                                    // Doğru hedef: waiting worker'a mesaj
                                    reg.waiting.postMessage({ type: 'SKIP_WAITING' });
                                } else if (navigator.serviceWorker.controller) {
                                    navigator.serviceWorker.controller.postMessage({ type: 'SKIP_WAITING' });
                                }
                                let reloaded = false;
                                navigator.serviceWorker.addEventListener('controllerchange', () => {
                                    if (!reloaded) {
                                        reloaded = true;
                                        if (availableVersion) localStorage.setItem('pwa_update_suppressed', String(availableVersion));
                                        window.location.reload();
                                    }
                                });
                                setTimeout(() => {
                                    if (!reloaded) {
                                        if (availableVersion) localStorage.setItem('pwa_update_suppressed', String(availableVersion));
                                        window.location.reload();
                                    }
                                }, 1500);
                            } else {
                                window.location.reload();
                            }
                        } catch (e) {
                            window.location.reload();
                        }
                    };

                    document.getElementById('sw-reload-btn').onclick = applyUpdate;

                    // Otomatik uygulamayı da deneyin (UX'i hızlandırır)
                    applyUpdate();
                } catch (e) {}
            }
            if (type === 'UPDATE_APPLIED') {
                // SW yeni sürüm aktif olduysa barı kapat
                const existing = document.getElementById('sw-reload-btn');
                if (existing) {
                    try { existing.closest('div').remove(); } catch (e) {}
                }
            }
            if (type === 'PUSH_NOTIFICATION') {
                try {
                    const { title, body, url } = event.data.data || {};
                    // Küçük kayan bildirim çubuğu
                    const bar = document.createElement('div');
                    bar.className = 'position-fixed bottom-0 start-50 translate-middle-x bg-dark text-white px-4 py-3 rounded-top shadow';
                    bar.style.zIndex = 9999;
                    bar.style.maxWidth = '90vw';
                    bar.innerHTML = `
                        <strong>${title || 'Bildirim'}</strong>
                        <div class="small opacity-75">${body || ''}</div>
                        ${url ? '<div class="mt-2"><button class="btn btn-sm btn-light" id="notif-open-btn">Aç</button></div>' : ''}
                    `;
                    document.body.appendChild(bar);
                    if (url) {
                        document.getElementById('notif-open-btn').onclick = () => window.location.href = url;
                    }
                    setTimeout(() => { if (bar && bar.parentNode) bar.remove(); }, 8000);
                } catch (e) {}
            }
        });
    }
    // Sekme tekrar görünür olduğunda token'ı tazele
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            try { getAndSendToken(); } catch (e) { console.warn('Token refresh (visibility) error', e); }
        }
    });
    // Online olduğunda token'ı tazele
    window.addEventListener('online', () => {
        try { getAndSendToken(); } catch (e) { console.warn('Token refresh (online) error', e); }
    });
    // OneSignal v16 foreground in-app bar desteği (SDK pushlarını SW işler)
    if (window.OneSignal && OneSignal.Notifications && OneSignal.Notifications.addEventListener) {
        OneSignal.Notifications.addEventListener('foregroundWillDisplay', (event) => {
            try {
                // Varsayılan davranışta foreground'da sistem bildirimi görünmez; kendi UI'mizi göstereceğiz
                if (event && typeof event.preventDefault === 'function') {
                    event.preventDefault();
                }
                const url = event?.notification?.data?.url;
                const title = event?.notification?.title || 'Bildirim';
                const body = event?.notification?.body || '';
                const bar = document.createElement('div');
                bar.className = 'position-fixed bottom-0 start-50 translate-middle-x bg-dark text-white px-4 py-3 rounded-top shadow';
                bar.style.zIndex = 9999;
                bar.style.maxWidth = '90vw';
                bar.innerHTML = `<strong>${title}</strong><div class="small opacity-75">${body}</div>${url ? '<div class="mt-2"><button class="btn btn-sm btn-light" id="notif-open-btn">Aç</button></div>' : ''}`;
                document.body.appendChild(bar);
                if (url) {
                    document.getElementById('notif-open-btn').onclick = () => window.location.href = url;
                }
                setTimeout(() => { if (bar && bar.parentNode) bar.remove(); }, 8000);
            } catch (e) {}
        });
    }
});

async function getAndSendToken() {
    try {
        console.log('Token alınıyor...');
        // v16: Önce PushSubscription.id (player/subscription id) dene
        let userId = null;
        try { userId = OneSignal?.User?.PushSubscription?.id || null; } catch(e) {}
        if (!userId) {
            try {
                const aliasId = (typeof OneSignal.User.getId === 'function') ? await OneSignal.User.getId() : null;
                if (aliasId) {
                    console.log('Alias mevcut ancak subscription id yok; alias ile login eşlemesi yapıldı, subscription bekleniyor');
                }
            } catch (e) {}
        }
        console.log('OneSignal SubscriptionId:', userId);
        
        if (userId) {
            // Token'ı sunucuya gönder
            await sendTokenToServer(userId);
        } else {
            console.log('UserId alınamadı, tekrar deneniyor...');
            // Tekrar dene
            setTimeout(() => {
                getAndSendToken();
            }, 2000);
        }
    } catch (error) {
        console.error('Token alma hatası:', error);
    }
}

async function sendTokenToServer(token) {
    try {
        console.log('Token sunucuya gönderiliyor:', token);
        
        // Platformu tespit et
        const platform = detectPlatform();
        console.log('Platform tespit edildi:', platform);
        
        const response = await fetch('/api/device/token/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                device_token: token,
                platform: platform,
                notification_permission: true
            })
        });
        
        const data = await response.json();
        console.log('Server response:', data);
        
        if (data.success) {
            console.log('Token başarıyla kaydedildi:', token);
        } else {
            console.error('Token kaydedilirken hata oluştu:', data.message);
        }
    } catch (error) {
        console.error('Token gönderilirken bir hata oluştu:', error);
    }
}

async function removeTokenFromServer() {
    try {
        const response = await fetch('/api/device/token/remove', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include'
        });
        
        const data = await response.json();
        console.log('Token silme response:', data);
        
        if (data.success) {
            console.log('Token başarıyla silindi');
        } else {
            console.error('Token silinirken hata oluştu:', data.message);
        }
    } catch (error) {
        console.error('Token silme hatası:', error);
    }
}

function detectPlatform() {
    const userAgent = navigator.userAgent.toLowerCase();
    console.log('User Agent:', navigator.userAgent);
    
    if (/android/.test(userAgent)) {
        return 'Android';
    } else if (/iphone|ipad|ipod/.test(userAgent)) {
        return 'iOS';
    } else if (/windows/.test(userAgent)) {
        return 'Desktop';
    } else if (/macintosh|mac os x/.test(userAgent)) {
        return 'Desktop';
    } else if (/linux/.test(userAgent)) {
        return 'Desktop';
    } else {
        return 'Web';
    }
}

// OneSignal event handlers
function setupOneSignalEventHandlers(OneSignal) {
    console.log('OneSignal v16 event handlers kuruluyor...');
    if (!OneSignal || !OneSignal.Notifications || typeof OneSignal.Notifications.addEventListener !== 'function') {
        return;
    }
    // Bildirim tıklama
    OneSignal.Notifications.addEventListener('click', (event) => {
        try {
            console.log('OneSignal click:', event);
            const url = event?.notification?.data?.url;
            if (url) window.location.href = url;
        } catch (e) {}
    });
    // Ön planda gösterim öncesi (isteğe bağlı)
    OneSignal.Notifications.addEventListener('foregroundWillDisplay', (event) => {
        try {
            console.log('OneSignal foregroundWillDisplay:', event);
            // event.preventDefault(); // Gerekirse müdahale edin
        } catch (e) {}
    });
}