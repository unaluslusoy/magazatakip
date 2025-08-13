<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OneSignal Entegrasyonu - Mağaza Takip</title>
    
    <!-- OneSignal SDK -->
    <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js?v=<?php echo time(); ?>" defer></script>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        .notification-status {
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .status-enabled {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .status-disabled {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .device-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        
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
                    <i class="bi bi-bell"></i>
                    Push Notification Entegrasyonu
                </h1>
                
                <!-- Bildirim Durumu -->
                <div id="notification-status" class="notification-status">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                        <p class="mt-2">Bildirim durumu kontrol ediliyor...</p>
                    </div>
                </div>
                
                <!-- Cihaz Bilgileri -->
                <div id="device-info" class="device-info" style="display: none;">
                    <h4><i class="bi bi-phone"></i> Cihaz Bilgileri</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Platform:</strong>
                            <span id="platform-info">-</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Bildirim İzni:</strong>
                            <span id="permission-info">-</span>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <strong>Device Token:</strong>
                            <div id="token-display" class="token-display">-</div>
                        </div>
                    </div>
                </div>
                
                <!-- Kontrol Butonları -->
                <div class="text-center mt-4">
                    <button id="enable-notifications" class="btn btn-success btn-lg me-3" style="display: none;">
                        <i class="bi bi-bell"></i>
                        Bildirimleri Etkinleştir
                    </button>
                    
                    <button id="disable-notifications" class="btn btn-danger btn-lg me-3" style="display: none;">
                        <i class="bi bi-bell-slash"></i>
                        Bildirimleri Devre Dışı Bırak
                    </button>
                    
                    <button id="refresh-status" class="btn btn-primary btn-lg">
                        <i class="bi bi-arrow-clockwise"></i>
                        Durumu Yenile
                    </button>
                </div>
                
                <!-- Test Bildirimi -->
                <div class="text-center mt-4">
                    <button id="test-notification" class="btn btn-warning btn-lg" style="display: none;">
                        <i class="bi bi-bell"></i>
                        Test Bildirimi Gönder
                    </button>
                </div>
                
                <!-- Log Alanı -->
                <div class="mt-4">
                    <h5><i class="bi bi-terminal"></i> Sistem Logları</h5>
                    <div id="log-area" style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; height: 200px; overflow-y: auto; font-family: monospace; font-size: 12px;">
                        Sistem başlatılıyor...
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- API Service -->
    <script src="/app/Views/kullanici/api-service.js"></script>
    
    <!-- Device Token Service -->
    <script src="/app/Views/kullanici/device-token-service.js"></script>
    
    <!-- OneSignal Integration Script -->
    <script>
        class OneSignalIntegration {
            constructor() {
                this.deviceTokenService = window.deviceTokenService;
                this.apiService = window.apiService;
                this.oneSignalAyarlarApiService = window.oneSignalAyarlarApiService;
                this.testBildirimApiService = window.testBildirimApiService;
                this.init();
            }
            
            async init() {
                this.log('OneSignal entegrasyonu başlatılıyor...');
                
                // OneSignal durumunu kontrol et
                await this.checkOneSignalStatus();
                
                // Event listener'ları ekle
                this.setupEventListeners();
                
                // Cihaz durumunu kontrol et
                await this.checkNotificationStatus();
                
                this.log('OneSignal entegrasyonu başlatıldı');
            }
            
            async checkOneSignalStatus() {
                try {
                    this.log('OneSignal durumu kontrol ediliyor...');
                    
                    const response = await this.oneSignalAyarlarApiService.getStatus();
                    
                    if (response.success) {
                        const status = response.data;
                        this.log(`OneSignal Durumu: Yapılandırılmış=${status.configured}, Geçerli=${status.valid}`);
                        
                        if (!status.configured) {
                            this.showOneSignalNotConfigured();
                            return false;
                        }
                        
                        if (!status.valid) {
                            this.showOneSignalInvalid();
                            return false;
                        }
                        
                        this.log('OneSignal yapılandırması geçerli');
                        return true;
                    } else {
                        this.log('OneSignal durumu kontrol edilemedi: ' + response.message);
                        this.showOneSignalError();
                        return false;
                    }
                } catch (error) {
                    this.log('OneSignal durum kontrolü hatası: ' + error.message);
                    this.showOneSignalError();
                    return false;
                }
            }
            
            showOneSignalNotConfigured() {
                const statusDiv = document.getElementById('notification-status');
                statusDiv.className = 'notification-status status-disabled';
                statusDiv.innerHTML = `
                    <div class="text-center">
                        <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                        <h4 class="mt-2">OneSignal Yapılandırılmamış</h4>
                        <p>OneSignal ayarları yapılandırılmamış. Lütfen yönetici ile iletişime geçin.</p>
                    </div>
                `;
            }
            
            showOneSignalInvalid() {
                const statusDiv = document.getElementById('notification-status');
                statusDiv.className = 'notification-status status-disabled';
                statusDiv.innerHTML = `
                    <div class="text-center">
                        <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                        <h4 class="mt-2">OneSignal Ayarları Geçersiz</h4>
                        <p>OneSignal App ID veya API Key eksik. Lütfen yönetici ile iletişime geçin.</p>
                    </div>
                `;
            }
            
            showOneSignalError() {
                const statusDiv = document.getElementById('notification-status');
                statusDiv.className = 'notification-status status-disabled';
                statusDiv.innerHTML = `
                    <div class="text-center">
                        <i class="bi bi-x-circle" style="font-size: 2rem;"></i>
                        <h4 class="mt-2">OneSignal Bağlantı Hatası</h4>
                        <p>OneSignal ayarları kontrol edilemedi.</p>
                    </div>
                `;
            }
            
            setupEventListeners() {
                // Bildirimleri etkinleştir
                document.getElementById('enable-notifications').addEventListener('click', async () => {
                    await this.enableNotifications();
                });
                
                // Bildirimleri devre dışı bırak
                document.getElementById('disable-notifications').addEventListener('click', async () => {
                    await this.disableNotifications();
                });
                
                // Durumu yenile
                document.getElementById('refresh-status').addEventListener('click', async () => {
                    await this.checkNotificationStatus();
                });
                
                // Test bildirimi
                document.getElementById('test-notification').addEventListener('click', async () => {
                    await this.sendTestNotification();
                });
            }
            
            async checkNotificationStatus() {
                try {
                    this.log('Bildirim durumu kontrol ediliyor...');
                    
                    const deviceInfo = await this.deviceTokenService.getDeviceInfo();
                    this.updateStatusDisplay(deviceInfo);
                    
                    this.log('Bildirim durumu güncellendi');
                } catch (error) {
                    this.log('Hata: ' + error.message);
                    this.showErrorStatus();
                }
            }
            
            updateStatusDisplay(deviceInfo) {
                const statusDiv = document.getElementById('notification-status');
                const deviceInfoDiv = document.getElementById('device-info');
                const enableBtn = document.getElementById('enable-notifications');
                const disableBtn = document.getElementById('disable-notifications');
                const testBtn = document.getElementById('test-notification');
                
                // Cihaz bilgilerini güncelle
                document.getElementById('platform-info').textContent = deviceInfo.platform || 'Bilinmiyor';
                document.getElementById('permission-info').textContent = deviceInfo.notification_permission ? 'Aktif' : 'Pasif';
                document.getElementById('token-display').textContent = deviceInfo.device_token || 'Token yok';
                
                if (deviceInfo.has_token && deviceInfo.notification_permission) {
                    // Bildirimler aktif
                    statusDiv.className = 'notification-status status-enabled';
                    statusDiv.innerHTML = `
                        <div class="text-center">
                            <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                            <h4 class="mt-2">Bildirimler Aktif</h4>
                            <p>Push notification'lar başarıyla etkinleştirildi.</p>
                        </div>
                    `;
                    
                    enableBtn.style.display = 'none';
                    disableBtn.style.display = 'inline-block';
                    testBtn.style.display = 'inline-block';
                } else {
                    // Bildirimler pasif
                    statusDiv.className = 'notification-status status-disabled';
                    statusDiv.innerHTML = `
                        <div class="text-center">
                            <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                            <h4 class="mt-2">Bildirimler Pasif</h4>
                            <p>Push notification'ları almak için etkinleştirin.</p>
                        </div>
                    `;
                    
                    enableBtn.style.display = 'inline-block';
                    disableBtn.style.display = 'none';
                    testBtn.style.display = 'none';
                }
                
                deviceInfoDiv.style.display = 'block';
            }
            
            showErrorStatus() {
                const statusDiv = document.getElementById('notification-status');
                statusDiv.className = 'notification-status status-disabled';
                statusDiv.innerHTML = `
                    <div class="text-center">
                        <i class="bi bi-x-circle" style="font-size: 2rem;"></i>
                        <h4 class="mt-2">Bağlantı Hatası</h4>
                        <p>Bildirim durumu kontrol edilemedi.</p>
                    </div>
                `;
            }
            
            async enableNotifications() {
                try {
                    this.log('Bildirimler etkinleştiriliyor...');
                    
                    const granted = await this.deviceTokenService.requestNotificationPermission();
                    
                    if (granted) {
                        this.log('Bildirim izni verildi');
                        await this.checkNotificationStatus();
                    } else {
                        this.log('Bildirim izni reddedildi');
                        alert('Bildirim izni reddedildi. Tarayıcı ayarlarından izin verebilirsiniz.');
                    }
                } catch (error) {
                    this.log('Hata: ' + error.message);
                }
            }
            
            async disableNotifications() {
                try {
                    this.log('Bildirimler devre dışı bırakılıyor...');
                    
                    await this.deviceTokenService.removeDeviceToken();
                    await this.checkNotificationStatus();
                    
                    this.log('Bildirimler devre dışı bırakıldı');
                } catch (error) {
                    this.log('Hata: ' + error.message);
                }
            }
            
            async sendTestNotification() {
                try {
                    this.log('Test bildirimi gönderiliyor...');
                    
                    const response = await this.testBildirimApiService.sendTestNotification(
                        'Test Bildirimi',
                        'Bu bir test bildirimidir. Sistem çalışıyor!',
                        window.location.href
                    );
                    
                    if (response.success) {
                        this.log('Test bildirimi başarıyla gönderildi');
                        alert('Test bildirimi başarıyla gönderildi!');
                    } else {
                        this.log('Test bildirimi gönderilemedi: ' + response.message);
                        alert('Test bildirimi gönderilemedi: ' + response.message);
                    }
                } catch (error) {
                    this.log('Hata: ' + error.message);
                    alert('Test bildirimi hatası: ' + error.message);
                }
            }
            
            log(message) {
                const logArea = document.getElementById('log-area');
                const timestamp = new Date().toLocaleTimeString();
                logArea.innerHTML += `[${timestamp}] ${message}\n`;
                logArea.scrollTop = logArea.scrollHeight;
            }
        }
        
        // Sayfa yüklendiğinde başlat
        document.addEventListener('DOMContentLoaded', function() {
            window.oneSignalIntegration = new OneSignalIntegration();
        });
    </script>
</body>
</html>
