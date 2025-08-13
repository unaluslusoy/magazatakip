<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Token Debug - Mağaza Takip</title>
    
    <!-- OneSignal SDK -->
    <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js?v=<?php echo time(); ?>" defer></script>
    
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
            height: 300px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 12px;
        }
        
        .status-success { color: #28a745; }
        .status-error { color: #dc3545; }
        .status-warning { color: #ffc107; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="bi bi-bug"></i>
                    Token Debug Sayfası
                </h1>
                
                <!-- Kullanıcı Bilgileri -->
                <div class="debug-section">
                    <h4><i class="bi bi-person"></i> Kullanıcı Bilgileri</h4>
                    <div id="user-info">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                    </div>
                </div>
                
                <!-- OneSignal Durumu -->
                <div class="debug-section">
                    <h4><i class="bi bi-bell"></i> OneSignal Durumu</h4>
                    <div id="onesignal-status">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                    </div>
                </div>
                
                <!-- Cihaz Bilgileri -->
                <div class="debug-section">
                    <h4><i class="bi bi-phone"></i> Cihaz Bilgileri</h4>
                    <div id="device-info">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                    </div>
                </div>
                
                <!-- Manuel Token Kaydetme -->
                <div class="debug-section">
                    <h4><i class="bi bi-gear"></i> Manuel Token Kaydetme</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="manual-token" class="form-label">Device Token:</label>
                                <input type="text" class="form-control" id="manual-token" placeholder="OneSignal device token">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="manual-platform" class="form-label">Platform:</label>
                                <select class="form-select" id="manual-platform">
                                    <option value="Web">Web</option>
                                    <option value="Android">Android</option>
                                    <option value="iOS">iOS</option>
                                    <option value="Desktop">Desktop</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button id="save-manual-token" class="btn btn-primary">
                        <i class="bi bi-save"></i>
                        Token'ı Kaydet
                    </button>
                </div>
                
                <!-- Test Bildirimi -->
                <div class="debug-section">
                    <h4><i class="bi bi-send"></i> Test Bildirimi</h4>
                    <button id="send-test-notification" class="btn btn-success">
                        <i class="bi bi-bell"></i>
                        Test Bildirimi Gönder
                    </button>
                </div>
                
                <!-- Debug Logları -->
                <div class="debug-section">
                    <h4><i class="bi bi-terminal"></i> Debug Logları</h4>
                    <div id="debug-log" class="log-area">
                        Debug başlatılıyor...
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
    
    <!-- Debug Script -->
    <script>
        class TokenDebug {
            constructor() {
                this.apiService = window.apiService;
                this.deviceTokenService = window.deviceTokenService;
                this.oneSignalAyarlarApiService = window.oneSignalAyarlarApiService;
                this.testBildirimApiService = window.testBildirimApiService;
                this.init();
            }
            
            async init() {
                this.log('Token Debug başlatılıyor...');
                
                // Event listener'ları ekle
                this.setupEventListeners();
                
                // Bilgileri yükle
                await this.loadUserInfo();
                await this.loadOneSignalStatus();
                await this.loadDeviceInfo();
                
                this.log('Token Debug başlatıldı');
            }
            
            setupEventListeners() {
                // Manuel token kaydetme
                document.getElementById('save-manual-token').addEventListener('click', async () => {
                    await this.saveManualToken();
                });
                
                // Test bildirimi
                document.getElementById('send-test-notification').addEventListener('click', async () => {
                    await this.sendTestNotification();
                });
            }
            
            async loadUserInfo() {
                try {
                    this.log('Kullanıcı bilgileri yükleniyor...');
                    
                    const response = await this.apiService.get('/api/user/profile');
                    
                    if (response.success) {
                        const user = response.data;
                        document.getElementById('user-info').innerHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>ID:</strong> ${user.id}<br>
                                    <strong>Ad:</strong> ${user.ad}<br>
                                    <strong>Email:</strong> ${user.email}
                                </div>
                                <div class="col-md-6">
                                    <strong>Mağaza ID:</strong> ${user.magaza_id || 'Yok'}<br>
                                    <strong>Yönetici:</strong> ${user.yonetici ? 'Evet' : 'Hayır'}<br>
                                    <strong>Durum:</strong> <span class="status-success">Aktif</span>
                                </div>
                            </div>
                        `;
                        this.log('Kullanıcı bilgileri yüklendi');
                    } else {
                        throw new Error(response.message);
                    }
                } catch (error) {
                    this.log('Kullanıcı bilgileri hatası: ' + error.message, 'error');
                    document.getElementById('user-info').innerHTML = `
                        <div class="alert alert-danger">
                            Kullanıcı bilgileri yüklenemedi: ${error.message}
                        </div>
                    `;
                }
            }
            
            async loadOneSignalStatus() {
                try {
                    this.log('OneSignal durumu kontrol ediliyor...');
                    
                    const response = await this.oneSignalAyarlarApiService.getStatus();
                    
                    if (response.success) {
                        const status = response.data;
                        document.getElementById('onesignal-status').innerHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Yapılandırılmış:</strong> 
                                    <span class="${status.configured ? 'status-success' : 'status-error'}">
                                        ${status.configured ? 'Evet' : 'Hayır'}
                                    </span><br>
                                    <strong>Geçerli:</strong> 
                                    <span class="${status.valid ? 'status-success' : 'status-error'}">
                                        ${status.valid ? 'Evet' : 'Hayır'}
                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <strong>App ID:</strong> 
                                    <span class="${status.app_id_available ? 'status-success' : 'status-error'}">
                                        ${status.app_id_available ? 'Mevcut' : 'Eksik'}
                                    </span><br>
                                    <strong>API Key:</strong> 
                                    <span class="${status.api_key_available ? 'status-success' : 'status-error'}">
                                        ${status.api_key_available ? 'Mevcut' : 'Eksik'}
                                    </span>
                                </div>
                            </div>
                        `;
                        this.log('OneSignal durumu kontrol edildi');
                    } else {
                        throw new Error(response.message);
                    }
                } catch (error) {
                    this.log('OneSignal durumu hatası: ' + error.message, 'error');
                    document.getElementById('onesignal-status').innerHTML = `
                        <div class="alert alert-danger">
                            OneSignal durumu kontrol edilemedi: ${error.message}
                        </div>
                    `;
                }
            }
            
            async loadDeviceInfo() {
                try {
                    this.log('Cihaz bilgileri yükleniyor...');
                    
                    const response = await this.deviceTokenService.getDeviceInfo();
                    
                    document.getElementById('device-info').innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Platform:</strong> ${response.platform || 'Bilinmiyor'}<br>
                                <strong>Bildirim İzni:</strong> 
                                <span class="${response.notification_permission ? 'status-success' : 'status-warning'}">
                                    ${response.notification_permission ? 'Aktif' : 'Pasif'}
                                </span>
                            </div>
                            <div class="col-md-6">
                                <strong>Token Durumu:</strong> 
                                <span class="${response.has_token ? 'status-success' : 'status-error'}">
                                    ${response.has_token ? 'Mevcut' : 'Yok'}
                                </span><br>
                                <strong>Device Token:</strong> 
                                <small class="text-muted">${response.device_token || 'Token yok'}</small>
                            </div>
                        </div>
                    `;
                    this.log('Cihaz bilgileri yüklendi');
                } catch (error) {
                    this.log('Cihaz bilgileri hatası: ' + error.message, 'error');
                    document.getElementById('device-info').innerHTML = `
                        <div class="alert alert-danger">
                            Cihaz bilgileri yüklenemedi: ${error.message}
                        </div>
                    `;
                }
            }
            
            async saveManualToken() {
                try {
                    const token = document.getElementById('manual-token').value.trim();
                    const platform = document.getElementById('manual-platform').value;
                    
                    if (!token) {
                        alert('Lütfen device token girin');
                        return;
                    }
                    
                    this.log('Manuel token kaydediliyor...');
                    
                    const response = await this.deviceTokenService.saveDeviceToken(token, platform, true);
                    
                    this.log('Token başarıyla kaydedildi: ' + token);
                    alert('Token başarıyla kaydedildi!');
                    
                    // Cihaz bilgilerini yenile
                    await this.loadDeviceInfo();
                    
                } catch (error) {
                    this.log('Token kaydetme hatası: ' + error.message, 'error');
                    alert('Token kaydetme hatası: ' + error.message);
                }
            }
            
            async sendTestNotification() {
                try {
                    this.log('Test bildirimi gönderiliyor...');
                    
                    const response = await this.testBildirimApiService.sendTestNotification(
                        'Debug Test Bildirimi',
                        'Bu bir debug test bildirimidir. Token sistemi çalışıyor!',
                        window.location.href
                    );
                    
                    if (response.success) {
                        this.log('Test bildirimi başarıyla gönderildi');
                        alert('Test bildirimi başarıyla gönderildi!');
                    } else {
                        this.log('Test bildirimi gönderilemedi: ' + response.message, 'error');
                        alert('Test bildirimi gönderilemedi: ' + response.message);
                    }
                } catch (error) {
                    this.log('Test bildirimi hatası: ' + error.message, 'error');
                    alert('Test bildirimi hatası: ' + error.message);
                }
            }
            
            log(message, type = 'info') {
                const logArea = document.getElementById('debug-log');
                const timestamp = new Date().toLocaleTimeString();
                const className = type === 'error' ? 'status-error' : type === 'warning' ? 'status-warning' : 'status-success';
                logArea.innerHTML += `<span class="${className}">[${timestamp}] ${message}</span>\n`;
                logArea.scrollTop = logArea.scrollHeight;
            }
        }
        
        // Sayfa yüklendiğinde başlat
        document.addEventListener('DOMContentLoaded', function() {
            window.tokenDebug = new TokenDebug();
        });
    </script>
</body>
</html>
