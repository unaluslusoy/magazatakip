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
function initializeOneSignal() {
    // OneSignal yapılandırmasını al ve başlat
    fetch('/token/onesignal-config')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(config => {
            console.log('OneSignal config alındı:', config);
            window.OneSignal = window.OneSignal || [];
            OneSignal.push(function() {
            OneSignal.init({
                appId: config.appId,
            });
            console.log('OneSignal initialized with appId:', config.appId);

            // Kullanıcının bildirim izni durumunu kontrol et
            OneSignal.isPushNotificationsEnabled(function(isEnabled) {
                console.log('Push notifications enabled:', isEnabled);
                if (isEnabled) {
                    // Kullanıcı zaten izin vermiş, token'ı al ve kaydet
                    getAndSendToken();
                } else {
                    // Kullanıcıdan izin iste
                    OneSignal.showHttpPrompt();
                    // İzin verildikten sonra token'ı al ve kaydet
                    OneSignal.on('subscriptionChange', function (isSubscribed) {
                        console.log('Subscription changed:', isSubscribed);
                        if (isSubscribed) {
                            getAndSendToken();
                        }
                    });
                }
            });
        });
        })
        .catch(error => {
            console.error('OneSignal yapılandırması alınamadı:', error);
            // Hata mesajını kontrol et ve daha fazla bilgi ver
            if (error.message.includes('Unexpected token <')) {
                console.error('Muhtemelen sunucudan geçersiz JSON dönüyor. Yanıtı kontrol edin.');
            }
        });
}

// OneSignal SDK hazır olduğunda başlat
waitForOneSignal(initializeOneSignal);

function getAndSendToken() {
    OneSignal.getUserId(function(userId) {
        console.log('OneSignal UserId:', userId);
        if (userId) {
            // Token'ı sunucuya gönder
            sendTokenToServer(userId);
        } else {
            console.log('UserId alınamadı');
        }
    });
}

function sendTokenToServer(token) {
    console.log('Sending token to server:', token);
    // Platformu tespit et (basit bir örnek)
    var platform = /Android/i.test(navigator.userAgent) ? 'Android' :
        (/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream) ? 'iOS' : 'Web';

    fetch('/token/kaydet', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'token=' + encodeURIComponent(token) + '&platform=' + encodeURIComponent(platform)
    })
        .then(response => response.json())
        .then(data => {
            console.log('Server response:', data);
            if (data.success) {
                console.log('Token başarıyla kaydedildi.', token);
            } else {
                console.error('Token kaydedilirken hata oluştu:', data.message);
            }
        })
        .catch(error => {
            console.error('Token gönderilirken bir hata oluştu:', error);
        });
}
// OneSignal event handlers - sadece SDK yüklendikten sonra çalıştır
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

// Event handlers'ı SDK yüklendikten sonra kur
waitForOneSignal(setupOneSignalEventHandlers);