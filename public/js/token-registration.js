// OneSignal SDK yüklenme kontrolü
function waitForOneSignal(callback, timeout = 15000) {
    const startTime = Date.now();
    
    function checkOneSignal() {
        console.log('OneSignal kontrol ediliyor...', typeof window.OneSignal);
        
        if (window.OneSignal && typeof window.OneSignal.init === 'function') {
            console.log('OneSignal SDK bulundu, başlatılıyor...');
            callback();
        } else if (Date.now() - startTime < timeout) {
            setTimeout(checkOneSignal, 500);
        } else {
            console.error('OneSignal SDK yüklenemedi, timeout');
            // Fallback: Manuel izin isteme
            requestNotificationPermissionManually();
        }
    }
    
    checkOneSignal();
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

// OneSignal başlatma fonksiyonu
async function initializeOneSignal() {
    try {
        console.log('OneSignal başlatılıyor...');
        
        // OneSignal yapılandırmasını al
        const response = await fetch('/api/onesignal/config');
        console.log('OneSignal config response:', response);
        
        if (!response.ok) {
            throw new Error(`OneSignal yapılandırması alınamadı: ${response.status}`);
        }
        
        const config = await response.json();
        console.log('OneSignal config alındı:', config);
        
        if (!config.success || !config.data.app_id) {
            throw new Error('OneSignal App ID bulunamadı');
        }
        
        // OneSignal'ı başlat
        window.OneSignal = window.OneSignal || [];
        
        OneSignal.push(function() {
            console.log('OneSignal.push çalıştı');
            
            OneSignal.init({
                appId: config.data.app_id,
                allowLocalhostAsSecureOrigin: true,
                notifyButton: {
                    enable: false
                },
                autoRegister: true, // Otomatik kayıt aktif
                autoResubscribe: true,
                persistNotification: false,
                promptOptions: {
                    slidedown: {
                        prompts: [
                            {
                                type: "push",
                                autoPrompt: true,
                                text: {
                                    actionMessage: "Bildirimleri almak için izin verin",
                                    acceptButton: "İzin Ver",
                                    cancelButton: "İptal"
                                },
                                delay: {
                                    pageViews: 1,
                                    timeDelay: 10 // Daha hızlı
                                }
                            }
                        ]
                    }
                }
            });
            
            console.log('OneSignal initialized with appId:', config.data.app_id);
            
            // Event listener'ları ekle
            setupOneSignalEventHandlers();
            
            // Kullanıcının bildirim izni durumunu kontrol et
            OneSignal.isPushNotificationsEnabled(function(isEnabled) {
                console.log('Push notifications enabled:', isEnabled);
                if (isEnabled) {
                    // Kullanıcı zaten izin vermiş, token'ı al ve kaydet
                    setTimeout(() => {
                        getAndSendToken();
                    }, 1000);
                } else {
                    console.log('Bildirim izni verilmemiş, izin isteniyor...');
                    // İzin iste
                    OneSignal.registerForPushNotifications();
                }
            });
            
            // İzin değişikliklerini dinle
            OneSignal.on('subscriptionChange', function (isSubscribed) {
                console.log('Subscription changed:', isSubscribed);
                if (isSubscribed) {
                    setTimeout(() => {
                        getAndSendToken();
                    }, 1000);
                } else {
                    // İzin iptal edildi, token'ı sil
                    removeTokenFromServer();
                }
            });
            
            // Bildirim izni verildiğinde
            OneSignal.on('notificationPermissionChange', function(permissionChange) {
                console.log('Notification permission changed:', permissionChange);
                if (permissionChange.current === 'granted') {
                    setTimeout(() => {
                        getAndSendToken();
                    }, 1000);
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
});

async function getAndSendToken() {
    try {
        console.log('Token alınıyor...');
        
        const userId = await OneSignal.getUserId();
        console.log('OneSignal UserId:', userId);
        
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
            }
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
function setupOneSignalEventHandlers() {
    console.log('OneSignal event handlers kuruluyor...');
    
    if (window.OneSignal && typeof window.OneSignal.push === 'function') {
        OneSignal.push(function() {
            OneSignal.on('notificationDisplay', function(event) {
                console.log('OneSignal notification displayed:', event);
            });

            OneSignal.on('notificationDismiss', function(event) {
                console.log('OneSignal notification dismissed:', event);
            });

            OneSignal.on('notificationClick', function(event) {
                console.log('OneSignal notification clicked:', event);

                // URL'yi kontrol et ve yönlendir
                if (event.data && event.data.url) {
                    window.location.href = event.data.url;
                }
            });
        });
    }
}