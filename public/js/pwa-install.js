// PWA Install ve Update Manager
class PWAManager {
    constructor() {
        this.deferredPrompt = null;
        this.isInstalled = false;
        this.hasUpdate = false;
        
        this.init();
    }
    
    init() {
        this.checkInstallationStatus();
        this.setupInstallPrompt();
        this.setupUpdateChecker();
        this.setupBeforeInstallPrompt();
    }
    
    checkInstallationStatus() {
        // PWA yüklü mü kontrol et
        if (window.matchMedia('(display-mode: standalone)').matches || 
            window.navigator.standalone || 
            document.referrer.includes('android-app://')) {
            this.isInstalled = true;
            this.hideInstallButton();
        }
    }
    
    setupBeforeInstallPrompt() {
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('Before install prompt triggered');
            // Varsayılan tarayıcı prompt'unu engelle
            e.preventDefault();
            this.deferredPrompt = e;
            this.showInstallButton();
            
            // Analytics tracking
            if (window.pwaAnalytics) {
                pwaAnalytics.trackInstallPromptShown();
            }
        });
    }
    
    setupInstallPrompt() {
        // Özel install butonu oluştur
        this.createInstallButton();
        
        // Install button click handler
        document.addEventListener('click', (e) => {
            if (e.target && e.target.classList && e.target.classList.contains('pwa-install-btn')) {
                this.showInstallPrompt();
            }
        });
    }
    
    createInstallButton() {
        // Eğer zaten varsa oluşturma
        if (document.querySelector('.pwa-install-btn')) return;
        
        // DOM'un hazır olduğundan emin ol
        if (!document.body) {
            setTimeout(() => this.createInstallButton(), 100);
            return;
        }
        
        const installBtn = document.createElement('button');
        installBtn.className = 'pwa-install-btn btn btn-primary position-fixed';
        installBtn.style.cssText = `
            bottom: 20px; 
            right: 20px; 
            z-index: 1050;
            border-radius: 50px;
            padding: 12px 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            display: none;
            animation: pulse 2s infinite;
        `;
        installBtn.innerHTML = `
            <i class="ki-outline ki-cloud-download fs-2 me-2"></i>
            <span>Uygulamayı Yükle</span>
        `;
        
        document.body.appendChild(installBtn);
        
        // CSS animation ekle
        const style = document.createElement('style');
        style.textContent = `
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.05); }
                100% { transform: scale(1); }
            }
            .pwa-install-btn:hover {
                transform: translateY(-2px) !important;
                box-shadow: 0 6px 25px rgba(0,0,0,0.4) !important;
            }
        `;
        document.head.appendChild(style);
    }
    
    showInstallButton() {
        const btn = document.querySelector('.pwa-install-btn');
        if (btn && !this.isInstalled) {
            btn.style.display = 'flex';
            btn.style.alignItems = 'center';
            
            // 5 saniye sonra otomatik gizle
            setTimeout(() => {
                if (btn.style.display !== 'none') {
                    this.hideInstallButton();
                }
            }, 30000);
        }
    }
    
    hideInstallButton() {
        const btn = document.querySelector('.pwa-install-btn');
        if (btn) {
            btn.style.display = 'none';
        }
    }
    
    async showInstallPrompt() {
        if (!this.deferredPrompt) {
            this.showInstallInstructions();
            return;
        }
        
        try {
            // Install prompt'unu göster
            const result = await this.deferredPrompt.prompt();
            console.log('Install prompt result:', result);
            
            if (result.outcome === 'accepted') {
                this.trackInstallEvent('accepted');
                this.hideInstallButton();
            } else {
                this.trackInstallEvent('dismissed');
            }
            
            this.deferredPrompt = null;
            
        } catch (error) {
            console.error('Install prompt error:', error);
            this.showInstallInstructions();
        }
    }
    
    showInstallInstructions() {
        // Tarayıcıya özel kurulum talimatları göster
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        const isAndroid = /Android/.test(navigator.userAgent);
        
        let instructions = '';
        
        if (isIOS) {
            instructions = `
                <div class="alert alert-info">
                    <h5><i class="ki-outline ki-information fs-2 me-2"></i>iOS'ta Yükleme</h5>
                    <ol>
                        <li>Safari tarayıcısının alt kısmındaki <strong>Paylaş</strong> butonuna tıklayın</li>
                        <li><strong>"Ana Ekrana Ekle"</strong> seçeneğini bulun</li>
                        <li><strong>"Ekle"</strong> butonuna tıklayın</li>
                    </ol>
                </div>
            `;
        } else if (isAndroid) {
            instructions = `
                <div class="alert alert-info">
                    <h5><i class="ki-outline ki-information fs-2 me-2"></i>Android'de Yükleme</h5>
                    <ol>
                        <li>Chrome tarayıcısının menü butonuna (⋮) tıklayın</li>
                        <li><strong>"Ana ekrana ekle"</strong> veya <strong>"Uygulama yükle"</strong> seçeneğini bulun</li>
                        <li><strong>"Yükle"</strong> butonuna tıklayın</li>
                    </ol>
                </div>
            `;
        } else {
            instructions = `
                <div class="alert alert-info">
                    <h5><i class="ki-outline ki-information fs-2 me-2"></i>Masaüstünde Yükleme</h5>
                    <p>Tarayıcınızın adres çubuğundaki <strong>yükleme</strong> simgesine tıklayın veya tarayıcı menüsünden <strong>"Uygulama yükle"</strong> seçeneğini bulun.</p>
                </div>
            `;
        }
        
        this.showModal('PWA Yükleme', instructions);
    }
    
    setupUpdateChecker() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.addEventListener('message', (event) => {
                if (event.data.type === 'UPDATE_AVAILABLE') {
                    this.showUpdateNotification();
                }
            });
            
            // Service worker güncellemelerini kontrol et
            navigator.serviceWorker.ready.then((registration) => {
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            this.showUpdateNotification();
                        }
                    });
                });
            });
        }
    }
    
    showUpdateNotification() {
        // DOM hazır olduğundan emin ol
        if (!document.body) {
            setTimeout(() => this.showUpdateNotification(), 100);
            return;
        }
        
        const updateToast = document.createElement('div');
        updateToast.className = 'toast position-fixed top-0 end-0 m-3';
        updateToast.style.zIndex = '1060';
        updateToast.innerHTML = `
            <div class="toast-header bg-primary text-white">
                <i class="ki-outline ki-cloud-download fs-3 me-2"></i>
                <strong class="me-auto">Güncelleme Hazır</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                Yeni bir uygulama sürümü mevcut. Şimdi güncellemek ister misiniz?
                <div class="mt-2">
                    <button class="btn btn-primary btn-sm me-2" onclick="pwaManager.reloadForUpdate()">
                        Güncelle
                    </button>
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="toast">
                        Sonra
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(updateToast);
        
        // Bootstrap toast'ını başlat
        if (window.bootstrap && bootstrap.Toast) {
            const toast = new bootstrap.Toast(updateToast, { delay: 10000 });
            toast.show();
        }
    }
    
    reloadForUpdate() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.ready.then((registration) => {
                if (registration.waiting) {
                    registration.waiting.postMessage({ type: 'SKIP_WAITING' });
                }
            });
        }
        window.location.reload();
    }
    
    showModal(title, content) {
        // DOM hazır olduğundan emin ol
        if (!document.body) {
            setTimeout(() => this.showModal(title, content), 100);
            return;
        }
        
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ${content}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        if (window.bootstrap && bootstrap.Modal) {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            
            modal.addEventListener('hidden.bs.modal', () => {
                modal.remove();
            });
        }
    }
    
    trackInstallEvent(outcome) {
        // Analytics tracking
        if (window.gtag) {
            gtag('event', 'pwa_install', {
                'event_category': 'PWA',
                'event_label': outcome
            });
        }
        
        console.log('PWA Install:', outcome);
    }
}

// PWA Manager'ı başlat
let pwaManager;
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        pwaManager = new PWAManager();
    });
} else {
    pwaManager = new PWAManager();
}