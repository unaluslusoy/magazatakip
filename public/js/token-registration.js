// OneSignal SDK yüklenme kontrolü
function waitForOneSignal(callback, timeout = 10000) {
    const startTime = Date.now();
    
    function checkOneSignal() {
        if (window.OneSignal && typeof window.OneSignal.init === 'function') {
            callback();
        } else if (Date.now() - startTime < timeout) {
            setTimeout(checkOneSignal, 100);
        } else {
            console.warn('OneSignal SDK yüklenemedi, timeout');
        }
    }
    
    checkOneSignal();
}

// OneSignal başlatma fonksiyonu
async function initializeOneSignal() {
    try {
        // OneSignal yapılandırmasını al
        const response = await fetch('/api/onesignal/config');
        if (!response.ok) {
            throw new Error('OneSignal yapılandırması alınamadı');
        }
        
        const config = await response.json();
        console.log('OneSignal config alındı:', config);
        
        if (!config.success || !config.data.app_id) {
            throw new Error('OneSignal App ID bulunamadı');
        }
        
        // OneSignal'ı başlat
        window.OneSignal = window.OneSignal || [];
        OneSignal.push(function() {
            OneSignal.init({
                appId: config.data.app_id,
                allowLocalhostAsSecureOrigin: true,
                notifyButton: {
                    enable: false
                },
                autoRegister: false,
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
                                    timeDelay: 20
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
                    getAndSendToken();
                }
            });
            
            // İzin değişikliklerini dinle
            OneSignal.on('subscriptionChange', function (isSubscribed) {
                console.log('Subscription changed:', isSubscribed);
                if (isSubscribed) {
                    getAndSendToken();
                } else {
                    // İzin iptal edildi, token'ı sil
                    removeTokenFromServer();
                }
            });
        });
        
    } catch (error) {
        console.error('OneSignal başlatma hatası:', error);
    }
}

// OneSignal SDK hazır olduğunda başlat
waitForOneSignal(initializeOneSignal);

async function getAndSendToken() {
    try {
        const userId = await OneSignal.getUserId();
        console.log('OneSignal UserId:', userId);
        
        if (userId) {
            // Token'ı sunucuya gönder
            await sendTokenToServer(userId);
        } else {
            console.log('UserId alınamadı');
        }
    } catch (error) {
        console.error('Token alma hatası:', error);
    }
}

async function sendTokenToServer(token) {
    try {
        console.log('Sending token to server:', token);
        
        // Platformu tespit et
        const platform = detectPlatform();
        
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