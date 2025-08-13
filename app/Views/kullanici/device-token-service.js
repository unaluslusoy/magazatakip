/**
 * Cihaz Token Yönetim Servisi
 * OneSignal ve diğer push notification servisleri için cihaz token yönetimi
 */

class DeviceTokenService {
    constructor() {
        this.apiService = window.apiService;
        this.oneSignalAppId = null;
        this.init();
    }
    
    async init() {
        try {
            // OneSignal yapılandırmasını al
            await this.loadOneSignalConfig();
            
            // OneSignal'ı başlat
            await this.initializeOneSignal();
            
            // Mevcut cihaz bilgilerini kontrol et
            await this.checkDeviceInfo();
            
            console.log('DeviceTokenService başlatıldı');
        } catch (error) {
            console.error('DeviceTokenService başlatma hatası:', error);
        }
    }
    
    /**
     * OneSignal yapılandırmasını veritabanından al
     */
    async loadOneSignalConfig() {
        try {
            const response = await this.apiService.get('/api/onesignal/config');
            
            if (response.success && response.data.app_id) {
                this.oneSignalAppId = response.data.app_id;
                console.log('OneSignal App ID yüklendi:', this.oneSignalAppId);
            } else {
                console.warn('OneSignal App ID bulunamadı veya yapılandırılmamış');
                throw new Error('OneSignal yapılandırması bulunamadı');
            }
        } catch (error) {
            console.error('OneSignal yapılandırması yükleme hatası:', error);
            throw error;
        }
    }
    
    /**
     * OneSignal'ı başlat
     */
    async initializeOneSignal() {
        if (typeof OneSignal === 'undefined') {
            console.warn('OneSignal SDK yüklenmemiş');
            return;
        }
        
        if (!this.oneSignalAppId) {
            console.error('OneSignal App ID mevcut değil');
            return;
        }
        
        try {
            // OneSignal'ı başlat
            await OneSignal.init({
                appId: this.oneSignalAppId,
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
            
            // OneSignal event listener'larını ekle
            this.setupOneSignalListeners();
            
            console.log('OneSignal başarıyla başlatıldı');

            try {
                if (window.CURRENT_USER_ID) {
                    await OneSignal.login(String(window.CURRENT_USER_ID));
                    console.log('OneSignal login yapıldı kullanıcı:', window.CURRENT_USER_ID);
                }
            } catch (e) { console.warn('OneSignal.login hatası', e); }
            
        } catch (error) {
            console.error('OneSignal başlatma hatası:', error);
        }
    }
    
    /**
     * OneSignal event listener'larını ayarla
     */
    setupOneSignalListeners() {
        // v16: abonelik değişimi
        if (OneSignal?.User?.PushSubscription?.addEventListener) {
            OneSignal.User.PushSubscription.addEventListener('change', async (e) => {
                const isSubscribed = !!(e?.current?.optedIn);
                console.log('Bildirim aboneliği değişti:', isSubscribed);
                if (isSubscribed) {
                    await this.handleSubscriptionGranted();
                } else {
                    await this.handleSubscriptionRevoked();
                }
            });
        }
        // v16: bildirim olayları
        if (OneSignal?.Notifications?.addEventListener) {
            OneSignal.Notifications.addEventListener('click', (event) => {
                console.log('Bildirim tıklandı:', event);
                this.handleNotificationClick(event);
            });
            OneSignal.Notifications.addEventListener('foregroundWillDisplay', (event) => {
                console.log('Bildirim alındı (foreground):', event);
                this.handleNotificationReceived(event);
            });
        }
    }
    
    /**
     * Bildirim izni verildiğinde
     */
    async handleSubscriptionGranted() {
        try {
            // v16: Önce User.getId, yoksa PushSubscription.id
            let deviceToken = null;
            try { deviceToken = (typeof OneSignal.User.getId === 'function') ? await OneSignal.User.getId() : null; } catch(e) {}
            if (!deviceToken) {
                try { deviceToken = OneSignal?.User?.PushSubscription?.id || null; } catch(e) {}
            }
            const platform = this.detectPlatform();
            
            if (deviceToken) {
                await this.saveDeviceToken(deviceToken, platform, true);
                console.log('Cihaz token kaydedildi:', deviceToken);
            }
        } catch (error) {
            console.error('Token kaydetme hatası:', error);
        }
    }
    
    /**
     * Bildirim izni iptal edildiğinde
     */
    async handleSubscriptionRevoked() {
        try {
            await this.removeDeviceToken();
            console.log('Cihaz token silindi');
        } catch (error) {
            console.error('Token silme hatası:', error);
        }
    }
    
    /**
     * Bildirim tıklandığında
     */
    handleNotificationClick(event) {
        // Bildirim tıklandığında yapılacak işlemler
        const dataUrl = event?.notification?.data?.url;
        const extraUrl = event?.notification?.additionalData?.url;
        const targetUrl = dataUrl || extraUrl || '/kullanici/bildirimler';

        if (targetUrl) {
            window.location.href = targetUrl;
        }

        const nid = event?.notification?.data?.notification_id || event?.notification?.additionalData?.notification_id;
        if (nid) {
            this.markNotificationAsRead(nid);
        }
    }
    
    /**
     * Bildirim alındığında
     */
    handleNotificationReceived(event) {
        // Bildirim alındığında yapılacak işlemler
        console.log('Yeni bildirim alındı:', event);
        
        // Bildirim sayısını güncelle
        this.updateNotificationCount();
    }
    
    /**
     * Cihaz token'ını kaydet
     */
    async saveDeviceToken(deviceToken, platform, notificationPermission = true) {
        try {
            const response = await this.apiService.post('/api/device/token/save', {
                device_token: deviceToken,
                platform: platform,
                notification_permission: notificationPermission
            });
            
            if (response.success) {
                console.log('Cihaz token başarıyla kaydedildi');
                return response.data;
            } else {
                throw new Error(response.message);
            }
        } catch (error) {
            console.error('Token kaydetme hatası:', error);
            throw error;
        }
    }
    
    /**
     * Cihaz token'ını sil
     */
    async removeDeviceToken() {
        try {
            const response = await this.apiService.delete('/api/device/token/remove');
            
            if (response.success) {
                console.log('Cihaz token başarıyla silindi');
                return true;
            } else {
                throw new Error(response.message);
            }
        } catch (error) {
            console.error('Token silme hatası:', error);
            throw error;
        }
    }
    
    /**
     * Cihaz bilgilerini getir
     */
    async getDeviceInfo() {
        try {
            const response = await this.apiService.get('/api/device/info');
            
            if (response.success) {
                return response.data;
            } else {
                throw new Error(response.message);
            }
        } catch (error) {
            console.error('Cihaz bilgisi getirme hatası:', error);
            throw error;
        }
    }
    
    /**
     * Bildirim iznini güncelle
     */
    async updateNotificationPermission(permission) {
        try {
            const response = await this.apiService.put('/api/device/notification-permission', {
                permission: permission
            });
            
            if (response.success) {
                console.log('Bildirim izni güncellendi:', permission);
                return response.data;
            } else {
                throw new Error(response.message);
            }
        } catch (error) {
            console.error('İzin güncelleme hatası:', error);
            throw error;
        }
    }
    
    /**
     * Mevcut cihaz bilgilerini kontrol et
     */
    async checkDeviceInfo() {
        try {
            const deviceInfo = await this.getDeviceInfo();
            console.log('Mevcut cihaz bilgileri:', deviceInfo);
            
            // Eğer token yoksa ve OneSignal mevcutsa, token al
            if (!deviceInfo.has_token && typeof OneSignal !== 'undefined') {
                // v16’da doğrudan permission/opt-in kontrolü
                let optedIn = false;
                try { optedIn = !!(OneSignal?.User?.PushSubscription?.optedIn); } catch(e) {}
                if (optedIn) await this.handleSubscriptionGranted();
            }
        } catch (error) {
            console.error('Cihaz bilgisi kontrol hatası:', error);
        }
    }
    
    /**
     * Platform tespit et
     */
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
    
    /**
     * Bildirim sayısını güncelle
     */
    async updateNotificationCount() {
        try {
            const response = await this.apiService.get('/api/bildirim/okunmamis-sayi');
            
            if (response.success) {
                const count = response.data.unread_count;
                
                // Bildirim sayısını UI'da güncelle
                this.updateNotificationBadge(count);
            }
        } catch (error) {
            console.error('Bildirim sayısı güncelleme hatası:', error);
        }
    }
    
    /**
     * Bildirim rozetini güncelle
     */
    updateNotificationBadge(count) {
        // Bildirim rozetini güncelle
        const badge = document.getElementById('notification-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'inline';
            } else {
                badge.style.display = 'none';
            }
        }
        
        // Sayfa başlığını güncelle
        if (count > 0) {
            document.title = `(${count}) ${document.title.replace(/^\(\d+\)\s*/, '')}`;
        } else {
            document.title = document.title.replace(/^\(\d+\)\s*/, '');
        }
    }
    
    /**
     * Bildirimi okundu olarak işaretle
     */
    async markNotificationAsRead(notificationId) {
        try {
            const response = await this.apiService.put(`/api/bildirim/okundu/${notificationId}`);
            
            if (response.success) {
                console.log('Bildirim okundu olarak işaretlendi');
                // Bildirim sayısını güncelle
                await this.updateNotificationCount();
            }
        } catch (error) {
            console.error('Bildirim işaretleme hatası:', error);
        }
    }
    
    /**
     * Manuel bildirim izni iste
     */
    async requestNotificationPermission() {
        try {
            if (typeof OneSignal !== 'undefined' && OneSignal?.Notifications?.requestPermission) {
                await OneSignal.Notifications.requestPermission();
                return true;
            } else if ('Notification' in window) {
                const permission = await Notification.requestPermission();
                return permission === 'granted';
            }
            return false;
        } catch (error) {
            console.error('Bildirim izni isteme hatası:', error);
            return false;
        }
    }
}

// Global instance oluştur
window.deviceTokenService = new DeviceTokenService();
