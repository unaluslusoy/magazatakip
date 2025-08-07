<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OneSignal Debug - Mağaza Takip</title>
    
    <!-- OneSignal SDK -->
    <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" defer></script>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        .debug-section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .log-area {
            background-color: #000;
            color: #00ff00;
            border-radius: 5px;
            padding: 15px;
            height: 400px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 12px;
        }
        
        .status-success { color: #28a745; }
        .status-error { color: #dc3545; }
        .status-warning { color: #ffc107; }
        .status-info { color: #17a2b8; }
        
        .token-display {
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 10px;
            font-family: monospace;
            word-break: break-all;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="bi bi-bug"></i>
                    OneSignal Token Debug
                </h1>
                
                <!-- OneSignal Durumu -->
                <div class="debug-section">
                    <h4><i class="bi bi-bell"></i> OneSignal Durumu</h4>
                    <div id="onesignal-status">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                    </div>
                </div>
                
                <!-- Token Bilgileri -->
                <div class="debug-section">
                    <h4><i class="bi bi-key"></i> Token Bilgileri</h4>
                    <div id="token-info">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                    </div>
                </div>
                
                <!-- Manuel Token Test -->
                <div class="debug-section">
                    <h4><i class="bi bi-gear"></i> Manuel Token Test</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <button id="get-onesignal-token" class="btn btn-primary mb-2">
                                <i class="bi bi-key"></i>
                                OneSignal Token Al
                            </button>
                            <button id="check-permission" class="btn btn-info mb-2">
                                <i class="bi bi-shield-check"></i>
                                İzin Durumu Kontrol Et
                            </button>
                            <button id="request-permission" class="btn btn-warning mb-2">
                                <i class="bi bi-shield-plus"></i>
                                İzin İste
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button id="save-token-manually" class="btn btn-success mb-2">
                                <i class="bi bi-save"></i>
                                Token'ı Manuel Kaydet
                            </button>
                            <button id="test-notification" class="btn btn-danger mb-2">
                                <i class="bi bi-bell"></i>
                                Test Bildirimi Gönder
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Platform Bilgileri -->
                <div class="debug-section">
                    <h4><i class="bi bi-phone"></i> Platform Bilgileri</h4>
                    <div id="platform-info">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                    </div>
                </div>
                
                <!-- Debug Logları -->
                <div class="debug-section">
                    <h4><i class="bi bi-terminal"></i> Debug Logları</h4>
                    <div id="debug-log" class="log-area">
                        OneSignal Debug başlatılıyor...
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Debug Script -->
    <script>
        class OneSignalDebug {
            constructor() {
                this.currentToken = null;
                this.platform = this.detectPlatform();
                this.init();
            }
            
            async init() {
                this.log('OneSignal Debug başlatılıyor...', 'info');
                
                // Platform bilgilerini göster
                this.showPlatformInfo();
                
                // Event listener'ları ekle
                this.setupEventListeners();
                
                // OneSignal durumunu kontrol et
                await this.checkOneSignalStatus();
                
                this.log('OneSignal Debug başlatıldı', 'success');
            }
            
            setupEventListeners() {
                // OneSignal token al
                document.getElementById('get-onesignal-token').addEventListener('click', async () => {
                    await this.getOneSignalToken();
                });
                
                // İzin durumu kontrol et
                document.getElementById('check-permission').addEventListener('click', async () => {
                    await this.checkPermission();
                });
                
                // İzin iste
                document.getElementById('request-permission').addEventListener('click', async () => {
                    await this.requestPermission();
                });
                
                // Token'ı manuel kaydet
                document.getElementById('save-token-manually').addEventListener('click', async () => {
                    await this.saveTokenManually();
                });
                
                // Test bildirimi
                document.getElementById('test-notification').addEventListener('click', async () => {
                    await this.sendTestNotification();
                });
            }
            
            async checkOneSignalStatus() {
                try {
                    this.log('OneSignal durumu kontrol ediliyor...', 'info');
                    
                    // OneSignal SDK kontrolü
                    if (typeof window.OneSignal === 'undefined') {
                        this.log('OneSignal SDK yüklenmemiş', 'error');
                        document.getElementById('onesignal-status').innerHTML = `
                            <div class="alert alert-danger">
                                <strong>OneSignal SDK Yüklenmemiş</strong><br>
                                SDK yüklenme durumu: ${typeof window.OneSignal}
                            </div>
                        `;
                        return;
                    }
                    
                    this.log('OneSignal SDK mevcut', 'success');
                    
                    // OneSignal config kontrolü
                    const response = await fetch('/api/onesignal/config');
                    const config = await response.json();
                    
                    if (config.success) {
                        this.log('OneSignal config alındı: ' + config.data.app_id, 'success');
                        document.getElementById('onesignal-status').innerHTML = `
                            <div class="alert alert-success">
                                <strong>OneSignal Aktif</strong><br>
                                App ID: ${config.data.app_id}<br>
                                SDK Durumu: Yüklendi
                            </div>
                        `;
                    } else {
                        this.log('OneSignal config hatası: ' + config.message, 'error');
                        document.getElementById('onesignal-status').innerHTML = `
                            <div class="alert alert-danger">
                                <strong>OneSignal Config Hatası</strong><br>
                                Hata: ${config.message}
                            </div>
                        `;
                    }
                    
                } catch (error) {
                    this.log('OneSignal durum kontrolü hatası: ' + error.message, 'error');
                    document.getElementById('onesignal-status').innerHTML = `
                        <div class="alert alert-danger">
                            <strong>OneSignal Durum Hatası</strong><br>
                            Hata: ${error.message}
                        </div>
                    `;
                }
            }
            
            async getOneSignalToken() {
                try {
                    this.log('OneSignal token alınıyor...', 'info');
                    
                    if (typeof window.OneSignal === 'undefined') {
                        this.log('OneSignal SDK yüklenmemiş', 'error');
                        return;
                    }
                    
                    // OneSignal'ı başlat
                    await this.initializeOneSignal();
                    
                    // Token al
                    const userId = await OneSignal.getUserId();
                    
                    if (userId) {
                        this.currentToken = userId;
                        this.log('OneSignal Token alındı: ' + userId, 'success');
                        this.updateTokenInfo();
                    } else {
                        this.log('OneSignal Token alınamadı', 'error');
                    }
                    
                } catch (error) {
                    this.log('Token alma hatası: ' + error.message, 'error');
                }
            }
            
            async initializeOneSignal() {
                try {
                    this.log('OneSignal başlatılıyor...', 'info');
                    
                    const response = await fetch('/api/onesignal/config');
                    const config = await response.json();
                    
                    if (!config.success) {
                        throw new Error('OneSignal config alınamadı');
                    }
                    
                    window.OneSignal = window.OneSignal || [];
                    
                    return new Promise((resolve, reject) => {
                        OneSignal.push(function() {
                            OneSignal.init({
                                appId: config.data.app_id,
                                allowLocalhostAsSecureOrigin: true,
                                notifyButton: { enable: false },
                                autoRegister: true,
                                autoResubscribe: true
                            });
                            
                            this.log('OneSignal başlatıldı', 'success');
                            resolve();
                        });
                    });
                    
                } catch (error) {
                    this.log('OneSignal başlatma hatası: ' + error.message, 'error');
                    throw error;
                }
            }
            
            async checkPermission() {
                try {
                    this.log('İzin durumu kontrol ediliyor...', 'info');
                    
                    if (typeof window.OneSignal !== 'undefined') {
                        const isEnabled = await OneSignal.isPushNotificationsEnabled();
                        this.log('OneSignal izin durumu: ' + (isEnabled ? 'Aktif' : 'Pasif'), isEnabled ? 'success' : 'warning');
                    }
                    
                    if ('Notification' in window) {
                        this.log('Browser Notification izni: ' + Notification.permission, 'info');
                    }
                    
                } catch (error) {
                    this.log('İzin kontrolü hatası: ' + error.message, 'error');
                }
            }
            
            async requestPermission() {
                try {
                    this.log('Bildirim izni isteniyor...', 'info');
                    
                    if (typeof window.OneSignal !== 'undefined') {
                        await OneSignal.registerForPushNotifications();
                        this.log('OneSignal izin istendi', 'success');
                    } else if ('Notification' in window) {
                        const permission = await Notification.requestPermission();
                        this.log('Browser izin sonucu: ' + permission, 'info');
                    }
                    
                } catch (error) {
                    this.log('İzin isteme hatası: ' + error.message, 'error');
                }
            }
            
            async saveTokenManually() {
                try {
                    if (!this.currentToken) {
                        this.log('Önce token alınmalı', 'warning');
                        return;
                    }
                    
                    this.log('Token manuel kaydediliyor...', 'info');
                    
                    const response = await fetch('/api/device/token/save', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            device_token: this.currentToken,
                            platform: this.platform,
                            notification_permission: true
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.log('Token başarıyla kaydedildi', 'success');
                    } else {
                        this.log('Token kaydetme hatası: ' + data.message, 'error');
                    }
                    
                } catch (error) {
                    this.log('Token kaydetme hatası: ' + error.message, 'error');
                }
            }
            
            async sendTestNotification() {
                try {
                    this.log('Test bildirimi gönderiliyor...', 'info');
                    
                    const response = await fetch('/api/notification/test', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            title: 'Debug Test Bildirimi',
                            message: 'Bu bir debug test bildirimidir!',
                            url: window.location.href
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.log('Test bildirimi başarıyla gönderildi', 'success');
                    } else {
                        this.log('Test bildirimi hatası: ' + data.message, 'error');
                    }
                    
                } catch (error) {
                    this.log('Test bildirimi hatası: ' + error.message, 'error');
                }
            }
            
            showPlatformInfo() {
                document.getElementById('platform-info').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Platform:</strong> ${this.platform}<br>
                            <strong>User Agent:</strong> <small>${navigator.userAgent}</small>
                        </div>
                        <div class="col-md-6">
                            <strong>Screen:</strong> ${screen.width}x${screen.height}<br>
                            <strong>Viewport:</strong> ${window.innerWidth}x${window.innerHeight}
                        </div>
                    </div>
                `;
            }
            
            updateTokenInfo() {
                document.getElementById('token-info').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Token Durumu:</strong> 
                            <span class="status-success">Mevcut</span><br>
                            <strong>Platform:</strong> ${this.platform}
                        </div>
                        <div class="col-md-6">
                            <strong>Device Token:</strong>
                            <div class="token-display">${this.currentToken || 'Token yok'}</div>
                        </div>
                    </div>
                `;
            }
            
            detectPlatform() {
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
            
            log(message, type = 'info') {
                const logArea = document.getElementById('debug-log');
                const timestamp = new Date().toLocaleTimeString();
                const className = type === 'error' ? 'status-error' : 
                                type === 'warning' ? 'status-warning' : 
                                type === 'success' ? 'status-success' : 'status-info';
                logArea.innerHTML += `<span class="${className}">[${timestamp}] ${message}</span>\n`;
                logArea.scrollTop = logArea.scrollHeight;
            }
        }
        
        // Sayfa yüklendiğinde başlat
        document.addEventListener('DOMContentLoaded', function() {
            window.oneSignalDebug = new OneSignalDebug();
        });
    </script>
</body>
</html>
