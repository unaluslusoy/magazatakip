/**
 * App Update Manager
 * PWA sürüm güncellemelerini yöneten sistem
 * v1.2.0
 */

class AppUpdateManager {
    constructor() {
        this.currentVersion = '1.2.0';
        this.updateAvailable = false;
        this.updateData = null;
        
        this.init();
    }

    init() {
        this.registerServiceWorker();
        this.setupUpdateListeners();
        this.createUpdateNotificationUI();
        
        console.log('📱 App Update Manager initialized');
    }

    /**
     * Service Worker registration ve update detection
     */
    async registerServiceWorker() {
        if (!('serviceWorker' in navigator)) {
            console.warn('Service Worker desteklenmiyor');
            return;
        }

        try {
            const registration = await navigator.serviceWorker.register('/service-worker.js');
            
            console.log('✅ Service Worker registered:', registration.scope);

            // Update event listeners
            registration.addEventListener('updatefound', () => {
                console.log('🔄 Service Worker update found');
                this.handleServiceWorkerUpdate(registration);
            });

            // Immediate update check
            await registration.update();
            
        } catch (error) {
            console.error('❌ Service Worker registration failed:', error);
        }
    }

    /**
     * Service Worker update handling
     */
    handleServiceWorkerUpdate(registration) {
        const newWorker = registration.installing;
        
        newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                console.log('🎉 New Service Worker installed, waiting for activation');
                this.showUpdateAvailableNotification();
            }
        });
    }

    /**
     * Service Worker messages listener
     */
    setupUpdateListeners() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.addEventListener('message', (event) => {
                const { type, data } = event.data;
                
                switch (type) {
                    case 'UPDATE_AVAILABLE':
                        console.log('📱 Update available:', data);
                        this.handleUpdateAvailable(data);
                        break;
                        
                    case 'UPDATE_DETECTED':
                        console.log('🔍 Server update detected:', data);
                        this.handleServerUpdateDetected(data);
                        break;
                        
                    case 'UPDATE_APPLIED':
                        console.log('✅ Update applied successfully');
                        this.handleUpdateApplied();
                        break;
                }
            });
        }

        // Page visibility change - check for updates when app becomes visible
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.checkForUpdates();
            }
        });

        // Periodic update check
        setInterval(() => this.checkForUpdates(), 5 * 60 * 1000); // 5 dakika
    }

    /**
     * Update notification UI oluştur
     */
    createUpdateNotificationUI() {
        // CSS inject et
        const style = document.createElement('style');
        style.textContent = `
            .app-update-notification {
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                background: linear-gradient(135deg, #1976d2, #1565c0);
                color: white;
                padding: 16px 24px;
                border-radius: 16px;
                box-shadow: 0 8px 32px rgba(25, 118, 210, 0.3);
                z-index: 10000;
                display: none;
                max-width: 400px;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
                animation: slideDown 0.5s ease-out;
            }

            .app-update-notification.show {
                display: block;
            }

            .update-content {
                display: flex;
                align-items: center;
                gap: 16px;
            }

            .update-icon {
                font-size: 32px;
                animation: bounce 2s infinite;
            }

            .update-text {
                flex: 1;
            }

            .update-title {
                font-weight: 600;
                font-size: 16px;
                margin-bottom: 4px;
            }

            .update-description {
                font-size: 14px;
                opacity: 0.9;
                margin-bottom: 12px;
            }

            .update-actions {
                display: flex;
                gap: 8px;
            }

            .update-btn {
                padding: 8px 16px;
                border: none;
                border-radius: 8px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .update-btn-primary {
                background: rgba(255, 255, 255, 0.2);
                color: white;
                border: 1px solid rgba(255, 255, 255, 0.3);
            }

            .update-btn-primary:hover {
                background: rgba(255, 255, 255, 0.3);
                transform: translateY(-1px);
            }

            .update-btn-secondary {
                background: transparent;
                color: rgba(255, 255, 255, 0.8);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }

            .update-btn-secondary:hover {
                background: rgba(255, 255, 255, 0.1);
            }

            @keyframes slideDown {
                from {
                    transform: translateX(-50%) translateY(-100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(-50%) translateY(0);
                    opacity: 1;
                }
            }

            @keyframes bounce {
                0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
                40% { transform: translateY(-10px); }
                60% { transform: translateY(-5px); }
            }

            /* Mobile responsive */
            @media (max-width: 480px) {
                .app-update-notification {
                    left: 10px;
                    right: 10px;
                    transform: none;
                    max-width: none;
                }
            }

            /* Dark mode */
            @media (prefers-color-scheme: dark) {
                .app-update-notification {
                    background: linear-gradient(135deg, #0d47a1, #1565c0);
                }
            }
        `;
        document.head.appendChild(style);

        // Notification element oluştur
        this.notificationElement = document.createElement('div');
        this.notificationElement.className = 'app-update-notification';
        this.notificationElement.innerHTML = `
            <div class="update-content">
                <div class="update-icon">🚀</div>
                <div class="update-text">
                    <div class="update-title">Yeni Sürüm Mevcut!</div>
                    <div class="update-description">Uygulamanın yeni sürümü indirildi. Güncellemek için yeniden başlatın.</div>
                    <div class="update-actions">
                        <button class="update-btn update-btn-primary" onclick="window.appUpdateManager.applyUpdate()">
                            Şimdi Güncelle
                        </button>
                        <button class="update-btn update-btn-secondary" onclick="window.appUpdateManager.dismissUpdate()">
                            Daha Sonra
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(this.notificationElement);
    }

    /**
     * Update available notification göster
     */
    handleUpdateAvailable(data) {
        this.updateAvailable = true;
        this.updateData = data;
        
        // Version bilgisini güncelle
        if (data.version) {
            const description = this.notificationElement.querySelector('.update-description');
            description.textContent = `v${this.currentVersion} → v${data.version} güncellemesi hazır!`;
        }
        
        this.showUpdateNotification();
        
        // Haptic feedback
        if ('vibrate' in navigator) {
            navigator.vibrate([100, 50, 100]);
        }
        
        // Analytics
        this.trackUpdateEvent('update_available', data);
    }

    /**
     * Server'dan update tespit edildiğinde
     */
    handleServerUpdateDetected(data) {
        console.log('🔄 Server update detected, reloading service worker...');
        
        // Service Worker'ı yenile
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(registrations => {
                registrations.forEach(registration => registration.update());
            });
        }
    }

    /**
     * Update notification göster
     */
    showUpdateNotification() {
        this.notificationElement.classList.add('show');
        
        // 10 saniye sonra otomatik gizle
        setTimeout(() => {
            if (this.notificationElement.classList.contains('show')) {
                this.dismissUpdate();
            }
        }, 10000);
    }

    /**
     * Update'i uygula
     */
    async applyUpdate() {
        try {
            console.log('⚡ Applying update...');
            
            this.showUpdateProgress();
            
            // Service Worker'a skip waiting mesajı gönder
            await this.sendMessageToServiceWorker({
                type: 'SKIP_WAITING'
            });
            
            // Cache'leri temizle
            if ('caches' in window) {
                const cacheNames = await caches.keys();
                await Promise.all(
                    cacheNames
                        .filter(name => name.startsWith('magaza-takip-cache-'))
                        .map(name => caches.delete(name))
                );
            }
            
            // LocalStorage'ı temizle (session hariç)
            Object.keys(localStorage).forEach(key => {
                if (!key.startsWith('session_') && !key.startsWith('remember_')) {
                    localStorage.removeItem(key);
                }
            });
            
            // Analytics
            this.trackUpdateEvent('update_applied', this.updateData);
            
            // Sayfayı yenile
            setTimeout(() => {
                window.location.reload();
            }, 1000);
            
        } catch (error) {
            console.error('❌ Update application failed:', error);
            this.hideUpdateNotification();
        }
    }

    /**
     * Update progress göster
     */
    showUpdateProgress() {
        const content = this.notificationElement.querySelector('.update-content');
        content.innerHTML = `
            <div class="update-icon">⏳</div>
            <div class="update-text">
                <div class="update-title">Güncelleniyor...</div>
                <div class="update-description">Uygulama yeniden başlatılıyor...</div>
            </div>
        `;
    }

    /**
     * Update notification'ı dismiss et
     */
    dismissUpdate() {
        this.notificationElement.classList.remove('show');
        
        // Analytics
        this.trackUpdateEvent('update_dismissed', this.updateData);
        
        // 1 saat sonra tekrar göster
        setTimeout(() => {
            if (this.updateAvailable) {
                this.showUpdateNotification();
            }
        }, 60 * 60 * 1000);
    }

    /**
     * Update notification'ı gizle
     */
    hideUpdateNotification() {
        this.notificationElement.classList.remove('show');
    }

    /**
     * Manual update check
     */
    async checkForUpdates() {
        try {
            if ('serviceWorker' in navigator) {
                const registration = await navigator.serviceWorker.getRegistration();
                if (registration) {
                    await registration.update();
                }
            }
        } catch (error) {
            console.warn('Manual update check failed:', error);
        }
    }

    /**
     * Update applied handler
     */
    handleUpdateApplied() {
        // Success notification göster
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #4caf50;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            z-index: 10001;
            font-weight: 500;
        `;
        toast.textContent = '✅ Uygulama başarıyla güncellendi!';
        document.body.appendChild(toast);
        
        setTimeout(() => toast.remove(), 3000);
    }

    /**
     * Analytics tracking
     */
    trackUpdateEvent(event, data) {
        if (window.gtag) {
            window.gtag('event', 'app_update', {
                event_category: 'PWA',
                event_label: event,
                custom_map: { custom_version: data?.version || this.currentVersion }
            });
        }
        
        console.log(`📊 Update event tracked: ${event}`, data);
    }

    /**
     * Public API - Manual update trigger
     */
    static triggerUpdate() {
        if (window.appUpdateManager) {
            window.appUpdateManager.checkForUpdates();
        }
    }

    /**
     * Current version getter
     */
    getCurrentVersion() {
        return this.currentVersion;
    }

    /**
     * Güvenli Service Worker messaging
     */
    async sendMessageToServiceWorker(message, timeout = 5000) {
        return new Promise((resolve, reject) => {
            // Service Worker desteği kontrolü
            if (!('serviceWorker' in navigator)) {
                console.warn('Service Worker desteklenmiyor');
                resolve(false);
                return;
            }

            // Controller kontrolü
            if (!navigator.serviceWorker.controller) {
                console.warn('Service Worker controller mevcut değil');
                resolve(false);
                return;
            }

            try {
                // Timeout mekanizması
                const timeoutId = setTimeout(() => {
                    console.warn('Service Worker mesaj timeout');
                    resolve(false);
                }, timeout);

                // Response listener
                const handleMessage = (event) => {
                    if (event.data && event.data.type === `${message.type}_RESPONSE`) {
                        clearTimeout(timeoutId);
                        navigator.serviceWorker.removeEventListener('message', handleMessage);
                        resolve(true);
                    }
                };

                // Event listener ekle
                navigator.serviceWorker.addEventListener('message', handleMessage);

                // Mesajı gönder
                navigator.serviceWorker.controller.postMessage(message);
                
                console.log('📡 Service Worker mesajı gönderildi:', message.type);

            } catch (error) {
                console.error('Service Worker mesaj hatası:', error);
                resolve(false);
            }
        });
    }
}

// Auto initialize
document.addEventListener('DOMContentLoaded', () => {
    window.appUpdateManager = new AppUpdateManager();
});

// Export for modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AppUpdateManager;
}

console.log('📱 App Update Manager loaded!');