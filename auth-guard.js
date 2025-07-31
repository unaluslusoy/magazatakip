// Global Authentication Guard
class AuthGuard {
    constructor() {
        this.checkInterval = 30000; // 30 saniye
        this.warningTime = 300000; // 5 dakika önce uyar
        this.sessionTimeout = 1800000; // 30 dakika
        this.lastActivity = Date.now();
        
        this.init();
    }
    
    init() {
        this.setupActivityTracking();
        this.setupPeriodicCheck();
        this.setupPageVisibilityCheck();
        
        console.log('🛡️ Auth Guard initialized');
    }
    
    setupActivityTracking() {
        // Kullanıcı aktivitelerini takip et
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        
        events.forEach(event => {
            document.addEventListener(event, () => {
                this.updateActivity();
            }, { passive: true });
        });
    }
    
    setupPeriodicCheck() {
        setInterval(() => {
            this.checkSession();
        }, this.checkInterval);
    }
    
    setupPageVisibilityCheck() {
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                // Sayfa aktif olduğunda session kontrolü yap
                this.checkSession();
            }
        });
    }
    
    updateActivity() {
        this.lastActivity = Date.now();
        
        // Server'a aktivite bildir (AJAX)
        this.sendHeartbeat();
    }
    
    async sendHeartbeat() {
        try {
            const response = await fetch('/api/heartbeat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    timestamp: Date.now(),
                    page: window.location.pathname
                })
            });
            
            if (!response.ok) {
                throw new Error('Heartbeat failed');
            }
            
            const data = await response.json();
            
            if (data.status === 'session_expired') {
                this.handleSessionExpired();
            }
            
        } catch (error) {
            console.warn('Heartbeat failed:', error);
            // Network hatası durumunda session kontrolü yap
            this.checkLocalSession();
        }
    }
    
    checkSession() {
        const now = Date.now();
        const timeSinceActivity = now - this.lastActivity;
        
        // Session timeout kontrolü
        if (timeSinceActivity > this.sessionTimeout) {
            this.handleSessionExpired();
            return;
        }
        
        // Warning göster
        if (timeSinceActivity > (this.sessionTimeout - this.warningTime)) {
            this.showSessionWarning();
        }
        
        // Server session kontrolü
        this.checkServerSession();
    }
    
    checkLocalSession() {
        // Local session kontrolü (PHP session yok ise)
        if (!this.hasValidSession()) {
            this.handleSessionExpired();
        }
    }
    
    hasValidSession() {
        // Basic session kontrolü - gerçek implementasyon projeye göre değişir
        return document.cookie.includes('PHPSESSID') || 
               localStorage.getItem('user_token') || 
               sessionStorage.getItem('user_id');
    }
    
    async checkServerSession() {
        try {
            const response = await fetch('/api/check-session', {
                method: 'GET',
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error('Session check failed');
            }
            
            const data = await response.json();
            
            if (!data.valid) {
                this.handleSessionExpired();
            }
            
        } catch (error) {
            console.warn('Server session check failed:', error);
        }
    }
    
    showSessionWarning() {
        // Sadece bir kez göster
        if (document.getElementById('session-warning')) return;
        
        const warning = document.createElement('div');
        warning.id = 'session-warning';
        warning.className = 'session-warning-modal';
        warning.innerHTML = `
            <div class="modal-backdrop"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h5><i class="ki-outline ki-time"></i> Oturum Uyarısı</h5>
                </div>
                <div class="modal-body">
                    <p>Oturumunuz yakında sona erecek.</p>
                    <p>Devam etmek istiyorsanız bir aktivite gerçekleştirin.</p>
                </div>
                <div class="modal-actions">
                    <button onclick="authGuard.extendSession()" class="btn btn-primary">
                        Oturumu Uzat
                    </button>
                    <button onclick="authGuard.logout()" class="btn btn-secondary">
                        Çıkış Yap
                    </button>
                </div>
            </div>
        `;
        
        // Styles
        const style = document.createElement('style');
        style.textContent = `
            .session-warning-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 10000;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .session-warning-modal .modal-backdrop {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                backdrop-filter: blur(4px);
            }
            
            .session-warning-modal .modal-content {
                position: relative;
                background: white;
                border-radius: 15px;
                padding: 0;
                max-width: 400px;
                width: 90%;
                box-shadow: 0 20px 40px rgba(0,0,0,0.3);
                animation: modalSlideIn 0.3s ease-out;
            }
            
            @keyframes modalSlideIn {
                0% {
                    opacity: 0;
                    transform: scale(0.9) translateY(-20px);
                }
                100% {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }
            
            .session-warning-modal .modal-header {
                padding: 1.5rem;
                border-bottom: 1px solid #e9ecef;
                border-radius: 15px 15px 0 0;
                background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
                color: white;
            }
            
            .session-warning-modal .modal-header h5 {
                margin: 0;
                font-size: 1.1rem;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .session-warning-modal .modal-body {
                padding: 1.5rem;
                text-align: center;
            }
            
            .session-warning-modal .modal-body p {
                margin: 0 0 1rem 0;
                color: #495057;
            }
            
            .session-warning-modal .modal-actions {
                padding: 1rem 1.5rem 1.5rem;
                display: flex;
                gap: 1rem;
                justify-content: center;
            }
            
            .session-warning-modal .btn {
                padding: 0.5rem 1.5rem;
                border: none;
                border-radius: 8px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            
            .session-warning-modal .btn-primary {
                background: #007bff;
                color: white;
            }
            
            .session-warning-modal .btn-primary:hover {
                background: #0056b3;
                transform: translateY(-1px);
            }
            
            .session-warning-modal .btn-secondary {
                background: #6c757d;
                color: white;
            }
            
            .session-warning-modal .btn-secondary:hover {
                background: #545b62;
            }
        `;
        
        document.head.appendChild(style);
        document.body.appendChild(warning);
        
        // 30 saniye sonra otomatik çıkış
        setTimeout(() => {
            if (document.getElementById('session-warning')) {
                this.handleSessionExpired();
            }
        }, 30000);
    }
    
    extendSession() {
        // Warning'i kaldır
        const warning = document.getElementById('session-warning');
        if (warning) {
            warning.remove();
        }
        
        // Aktiviteyi güncelle
        this.updateActivity();
        
        // Server'a uzatma isteği gönder
        this.sendSessionExtend();
    }
    
    async sendSessionExtend() {
        try {
            await fetch('/api/extend-session', {
                method: 'POST',
                credentials: 'same-origin'
            });
        } catch (error) {
            console.warn('Session extend failed:', error);
        }
    }
    
    handleSessionExpired() {
        // Loader göster
        if (window.businessLoader) {
            window.businessLoader.showWithMessage('Oturum süresi doldu, yönlendiriliyorsunuz...');
        }
        
        // Session temizle
        this.clearSession();
        
        // Login sayfasına yönlendir
        setTimeout(() => {
            window.location.href = '/auth/giris';
        }, 1500);
    }
    
    logout() {
        // Loader göster
        if (window.businessLoader) {
            window.businessLoader.showWithMessage('Çıkış yapılıyor...');
        }
        
        // Logout API çağrısı
        this.sendLogout();
        
        // Session temizle
        this.clearSession();
        
        // Login sayfasına yönlendir
        setTimeout(() => {
            window.location.href = '/auth/giris';
        }, 1000);
    }
    
    async sendLogout() {
        try {
            await fetch('/api/logout', {
                method: 'POST',
                credentials: 'same-origin'
            });
        } catch (error) {
            console.warn('Logout API failed:', error);
        }
    }
    
    clearSession() {
        // Local storage temizle
        localStorage.clear();
        sessionStorage.clear();
        
        // Cookies temizle (mümkün olduğunca)
        document.cookie.split(";").forEach(function(c) { 
            document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/"); 
        });
    }
    
    // Public API
    forceCheck() {
        this.checkSession();
    }
    
    isActive() {
        const timeSinceActivity = Date.now() - this.lastActivity;
        return timeSinceActivity < this.sessionTimeout;
    }
}

// Auto-initialize auth guard (sadece authenticated sayfalarda)
let authGuard = null;

document.addEventListener('DOMContentLoaded', function() {
    // Sadece login sayfası değilse auth guard başlat
    if (!window.location.pathname.includes('/auth/') && 
        !window.location.pathname.includes('/login')) {
        
        setTimeout(() => {
            authGuard = new AuthGuard();
            window.authGuard = authGuard;
            
            console.log('✅ Auth Guard system ready');
        }, 1000);
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AuthGuard;
}