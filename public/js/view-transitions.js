/**
 * Splash Loading Animations
 * Basit ve etkili sayfa geçiş sistemi
 * v1.3.2
 */

class SplashTransitions {
    constructor() {
        this.isLoading = false;
        this.loadingElement = null;
        
        this.init();
        console.log('🎬 Splash Loading Animations aktif!');
    }

    init() {
        this.createLoadingElement();
        this.setupLinkTransitions();
        this.setupFormTransitions();
    }

    /**
     * Loading splash element oluştur
     */
    createLoadingElement() {
        this.loadingElement = document.createElement('div');
        this.loadingElement.id = 'splash-loading';
        this.loadingElement.innerHTML = `
            <div class="splash-overlay">
                <div class="splash-content">
                    <div class="splash-logo">
                        <img src="/public/media/logos/default.svg" alt="Loading" class="splash-logo-img">
                    </div>
                    <div class="splash-spinner">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                    </div>
                    <div class="splash-text">
                        <span class="loading-text">Sayfa yükleniyor...</span>
                    </div>
                </div>
            </div>
        `;

        this.injectSplashCSS();
        document.body.appendChild(this.loadingElement);
        this.hideLoading(); // Başlangıçta gizle
    }

    /**
     * Splash loading CSS'i inject et
     */
    injectSplashCSS() {
        const style = document.createElement('style');
        style.textContent = `
            #splash-loading {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 10000;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease-in-out;
                pointer-events: none;
            }

            #splash-loading.show {
                opacity: 1;
                visibility: visible;
                pointer-events: all;
            }

            .splash-overlay {
                width: 100%;
                height: 100%;
                background: linear-gradient(135deg, 
                    rgba(255, 255, 255, 0.95) 0%, 
                    rgba(248, 249, 250, 0.98) 100%);
                backdrop-filter: blur(10px);
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .splash-content {
                text-align: center;
                animation: splashFadeIn 0.5s ease-out;
            }

            .splash-logo {
                margin-bottom: 24px;
            }

            .splash-logo-img {
                width: 80px;
                height: 80px;
                animation: logoFloat 2s ease-in-out infinite;
                filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
            }

            .splash-spinner {
                margin-bottom: 16px;
            }

            .splash-spinner .spinner-border {
                width: 3rem;
                height: 3rem;
                border-width: 0.3em;
            }

            .splash-text {
                font-size: 16px;
                font-weight: 500;
                color: #374151;
                animation: textPulse 1.5s ease-in-out infinite;
            }

            /* Animasyonlar */
            @keyframes splashFadeIn {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes logoFloat {
                0%, 100% {
                    transform: translateY(0);
                }
                50% {
                    transform: translateY(-10px);
                }
            }

            @keyframes textPulse {
                0%, 100% {
                    opacity: 0.7;
                }
                50% {
                    opacity: 1;
                }
            }

            /* Dark mode */
            @media (prefers-color-scheme: dark) {
                .splash-overlay {
                    background: linear-gradient(135deg, 
                        rgba(17, 24, 39, 0.95) 0%, 
                        rgba(31, 41, 55, 0.98) 100%);
                }
                
                .splash-text {
                    color: #e5e7eb;
                }
            }

            /* Mobile optimizasyon */
            @media (max-width: 768px) {
                .splash-logo-img {
                    width: 60px;
                    height: 60px;
                }
                
                .splash-text {
                    font-size: 14px;
                }
                
                .splash-spinner .spinner-border {
                    width: 2.5rem;
                    height: 2.5rem;
                }
            }

            /* Reduced motion */
            @media (prefers-reduced-motion: reduce) {
                #splash-loading,
                .splash-content,
                .splash-logo-img,
                .splash-text {
                    animation: none;
                    transition: none;
                }
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Link'ler için otomatik loading
     */
    setupLinkTransitions() {
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a[href]');
            if (!link || link.target === '_blank') return;

            // Aynı origin kontrolü
            try {
                const url = new URL(link.href, window.location.origin);
                if (url.origin !== window.location.origin) return;
            } catch {
                return;
            }

            // Aynı sayfa ise skip
            if (link.href === window.location.href) return;

            console.log('🔗 Link clicked, starting navigation to:', link.href);
            e.preventDefault();
            this.navigateWithSplash(link.href);
        });
    }

    /**
     * Form'lar için loading
     */
    setupFormTransitions() {
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (!form.classList.contains('use-splash')) return;

            console.log('📝 Form submitted with splash loading');
            this.showLoading('Form gönderiliyor...');
            
            // Form normal şekilde submit olsun, loading otomatik gizlensin
            setTimeout(() => {
                if (this.isLoading) {
                    this.hideLoading();
                }
            }, 5000); // 5 saniye timeout
        });
    }

    /**
     * Splash ile sayfa geçişi
     */
    async navigateWithSplash(url) {
        try {
            console.log('🎬 Starting splash navigation to:', url);
            
            // Loading göster
            this.showLoading();
            
            // İçerik yükle
            await this.loadContent(url);
            
            console.log('✅ Navigation completed successfully');
            
        } catch (error) {
            console.error('❌ Navigation error:', error);
            
            // Hata durumunda normal navigation
            window.location.href = url;
        }
    }

    /**
     * İçerik yükleme
     */
    async loadContent(url) {
        try {
            console.log('📦 Fetching content from:', url);
            
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const html = await response.text();
            console.log('📥 Content received, length:', html.length);
            
            // Parse HTML
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');
            
            // Content güncelle
            this.updateContent(newDoc);
            
            // URL güncelle
            window.history.pushState({}, '', url);
            console.log('🔗 URL updated to:', url);
            
            // Loading gizle
            this.hideLoading();
            
            // Başarı feedback'i
            this.showSuccessFeedback();
            
        } catch (error) {
            console.error('Content loading failed:', error);
            throw error;
        }
    }

    /**
     * Sayfa içeriğini güncelle
     */
    updateContent(newDoc) {
        console.log('🔄 Updating page content...');
        
        // Title güncelle
        document.title = newDoc.title;
        
        // Content container güncelle
        const oldContent = document.querySelector('#kt_app_content_container');
        const newContent = newDoc.querySelector('#kt_app_content_container');
        
        if (oldContent && newContent) {
            oldContent.innerHTML = newContent.innerHTML;
            console.log('✅ Content updated successfully');
        } else {
            // Fallback: app-content güncelle
            const fallbackOld = document.querySelector('#kt_app_content');
            const fallbackNew = newDoc.querySelector('#kt_app_content');
            
            if (fallbackOld && fallbackNew) {
                fallbackOld.innerHTML = fallbackNew.innerHTML;
                console.log('✅ Content updated via fallback');
            } else {
                throw new Error('Content containers not found');
            }
        }
        
        // Page load event trigger
        this.triggerPageEvents();
    }

    /**
     * Loading göster
     */
    showLoading(text = 'Sayfa yükleniyor...') {
        if (this.isLoading) return;
        
        this.isLoading = true;
        
        // Loading text güncelle
        const textElement = this.loadingElement.querySelector('.loading-text');
        if (textElement) {
            textElement.textContent = text;
        }
        
        // Göster
        this.loadingElement.classList.add('show');
        document.body.style.overflow = 'hidden'; // Scroll engelle
        
        console.log('🎬 Splash loading shown:', text);
    }

    /**
     * Loading gizle
     */
    hideLoading() {
        if (!this.isLoading) return;
        
        this.isLoading = false;
        this.loadingElement.classList.remove('show');
        document.body.style.overflow = ''; // Scroll restore
        
        console.log('🎬 Splash loading hidden');
    }

    /**
     * Başarı feedback'i
     */
    showSuccessFeedback() {
        // Kısa süre için success state göster
        const originalText = this.loadingElement.querySelector('.loading-text').textContent;
        const textElement = this.loadingElement.querySelector('.loading-text');
        
        if (textElement) {
            textElement.textContent = '✅ Yüklendi!';
            textElement.style.color = '#10b981';
            
            setTimeout(() => {
                textElement.textContent = originalText;
                textElement.style.color = '';
            }, 500);
        }
    }

    /**
     * Page events trigger
     */
    triggerPageEvents() {
        // Custom event dispatch
        const event = new CustomEvent('pageLoaded', {
            detail: { url: window.location.href }
        });
        document.dispatchEvent(event);

        // KTApp re-init
        if (window.KTApp) {
            window.KTApp.init();
        }
        
        console.log('📡 Page events triggered');
    }

    /**
     * Manual navigation
     */
    static navigate(url, text = 'Sayfa yükleniyor...') {
        const instance = window.splashTransitions;
        if (instance) {
            instance.navigateWithSplash(url, text);
        } else {
            window.location.href = url;
        }
    }

    /**
     * Manual loading control
     */
    static showLoading(text) {
        const instance = window.splashTransitions;
        if (instance) {
            instance.showLoading(text);
        }
    }

    static hideLoading() {
        const instance = window.splashTransitions;
        if (instance) {
            instance.hideLoading();
        }
    }
}

// Global instance oluştur
window.splashTransitions = new SplashTransitions();

// Export for modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SplashTransitions;
}

console.log('🎬 Splash Loading Animations hazır! Basit ve etkili sayfa geçişleri aktif.');
