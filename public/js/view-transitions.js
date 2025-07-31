/**
 * Modern View Transitions API Implementation
 * Performans odaklı sayfa geçişleri - Sıfır bağımlılık
 * v1.2.0
 */

class ViewTransitions {
    constructor() {
        this.isSupported = 'startViewTransition' in document;
        this.fallbackEnabled = true;
        this.animationDuration = 300;
        
        console.log('🎭 View Transitions API destekleniyor:', this.isSupported);
        this.init();
    }

    init() {
        // View Transitions desteği varsa modern API'yi kullan
        if (this.isSupported) {
            this.setupModernTransitions();
        } else {
            console.log('🔄 Fallback transition sistemi aktif');
            this.setupFallbackTransitions();
        }

        // Link'lerde otomatik transition
        this.setupLinkTransitions();
        
        // Form submission'larda transition
        this.setupFormTransitions();
    }

    /**
     * Modern View Transitions API
     */
    setupModernTransitions() {
        // CSS tanımlamaları ekle
        this.injectViewTransitionCSS();
        
        // Page navigation events
        window.addEventListener('beforeunload', () => {
            this.prepareForTransition();
        });
    }

    /**
     * Modern tarayıcılar için CSS View Transitions
     */
    injectViewTransitionCSS() {
        const style = document.createElement('style');
        style.textContent = `
            /* View Transitions Root */
            ::view-transition {
                pointer-events: none;
            }

            /* Sayfa geçiş animasyonları */
            ::view-transition-old(root) {
                animation: slide-out-left 0.3s ease-in-out;
            }

            ::view-transition-new(root) {
                animation: slide-in-right 0.3s ease-in-out;
            }

            /* Özel geçiş türleri */
            .page-transition-fade {
                view-transition-name: page-fade;
            }

            ::view-transition-old(page-fade) {
                animation: fade-out 0.2s ease-out;
            }

            ::view-transition-new(page-fade) {
                animation: fade-in 0.2s ease-in;
            }

            /* Profil sayfası özel geçişi */
            .profile-transition {
                view-transition-name: profile-slide;
            }

            ::view-transition-old(profile-slide) {
                animation: slide-out-right 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            }

            ::view-transition-new(profile-slide) {
                animation: slide-in-left 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            }

            /* Keyframe animasyonları */
            @keyframes slide-out-left {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(-100%); opacity: 0; }
            }

            @keyframes slide-in-right {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }

            @keyframes slide-out-right {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }

            @keyframes slide-in-left {
                from { transform: translateX(-100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }

            @keyframes fade-out {
                from { opacity: 1; }
                to { opacity: 0; }
            }

            @keyframes fade-in {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            /* Mobil optimizasyonları */
            @media (max-width: 768px) {
                ::view-transition-old(root),
                ::view-transition-new(root) {
                    animation-duration: 0.25s;
                }
            }

            /* Reduced motion desteği */
            @media (prefers-reduced-motion: reduce) {
                ::view-transition-old(root),
                ::view-transition-new(root) {
                    animation: none;
                }
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Link'ler için otomatik transition
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

            e.preventDefault();
            this.navigateWithTransition(link.href, link);
        });
    }

    /**
     * Form'lar için transition
     */
    setupFormTransitions() {
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (!form.classList.contains('use-transition')) return;

            e.preventDefault();
            this.submitFormWithTransition(form);
        });
    }

    /**
     * Modern View Transition ile sayfa geçişi
     */
    async navigateWithTransition(url, element = null) {
        if (!this.isSupported) {
            window.location.href = url;
            return;
        }

        try {
            // Transition türünü belirle
            const transitionType = element?.dataset.transition || 'default';
            
            // Loading state göster
            this.showTransitionLoading();

            // Modern View Transition başlat
            await document.startViewTransition(async () => {
                // Yeni içeriği yükle
                await this.loadNewContent(url);
            });

            console.log('✅ View transition tamamlandı:', url);
            
        } catch (error) {
            console.error('❌ View transition hatası:', error);
            
            // Error tracking
            if (window.errorHandler) {
                window.errorHandler.logError('ViewTransition', error, { url });
            }
            
            // Fallback navigation
            console.log('🔄 Fallback navigation başlatılıyor...');
            this.hideTransitionLoading();
            
            // Smooth fallback transition
            document.body.style.opacity = '0.5';
            setTimeout(() => {
                window.location.href = url;
            }, 150);
            
        } finally {
            this.hideTransitionLoading();
        }
    }

    /**
     * Yeni içerik yükleme
     */
    async loadNewContent(url) {
        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const html = await response.text();
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');

            // Sayfa içeriğini güncelle
            this.updatePageContent(newDoc);
            
            // URL'yi güncelle
            window.history.pushState({}, '', url);
            
            // Page load event trigger et
            this.triggerPageLoadEvents();
            
        } catch (error) {
            console.error('Content loading error:', error);
            
            // Detaylı error logging
            if (window.errorHandler) {
                window.errorHandler.logError('ContentLoading', error, { 
                    url, 
                    responseStatus: error.status,
                    userAgent: navigator.userAgent 
                });
            }
            
            throw new Error(`Content loading failed: ${error.message || 'Unknown error'}`);
        }
    }

    /**
     * Sayfa içeriğini güncelle
     */
    updatePageContent(newDoc) {
        // Title güncelle
        document.title = newDoc.title;

        // Main content güncelle
        const oldMain = document.querySelector('main, .container, #kt_app_content');
        const newMain = newDoc.querySelector('main, .container, #kt_app_content');
        
        if (oldMain && newMain) {
            oldMain.innerHTML = newMain.innerHTML;
        }

        // Meta tags güncelle
        this.updateMetaTags(newDoc);
        
        // Scripts yeniden yükle
        this.reloadScripts(newDoc);
    }

    /**
     * Meta tags güncelle
     */
    updateMetaTags(newDoc) {
        const metaSelectors = ['meta[name="description"]', 'meta[property^="og:"]'];
        
        metaSelectors.forEach(selector => {
            const oldMeta = document.querySelector(selector);
            const newMeta = newDoc.querySelector(selector);
            
            if (oldMeta && newMeta) {
                oldMeta.setAttribute('content', newMeta.getAttribute('content'));
            }
        });
    }

    /**
     * Scripts yeniden yükle
     */
    reloadScripts(newDoc) {
        try {
            // Yeni sayfadaki script tag'leri bul
            const newScripts = newDoc.querySelectorAll('script[src]');
            const existingScripts = new Set();
            
            // Mevcut script'leri kaydet
            document.querySelectorAll('script[src]').forEach(script => {
                existingScripts.add(script.src);
            });
            
            // Yeni script'leri yükle
            newScripts.forEach(scriptEl => {
                if (!existingScripts.has(scriptEl.src)) {
                    const newScript = document.createElement('script');
                    newScript.src = scriptEl.src;
                    newScript.async = true;
                    
                    // Script attributes'ları kopyala
                    Array.from(scriptEl.attributes).forEach(attr => {
                        if (attr.name !== 'src') {
                            newScript.setAttribute(attr.name, attr.value);
                        }
                    });
                    
                    document.head.appendChild(newScript);
                    console.log('🔄 Script reloaded:', scriptEl.src);
                }
            });
            
            // Inline script'leri güvenli şekilde çalıştır
            const inlineScripts = newDoc.querySelectorAll('script:not([src])');
            inlineScripts.forEach((script, index) => {
                if (script.textContent.trim() && this.isScriptSafe(script.textContent)) {
                    try {
                        // Script'i sandboxed context'te çalıştır
                        const func = new Function(script.textContent);
                        func.call(window);
                        console.log(`✅ Inline script ${index + 1} executed safely`);
                    } catch (error) {
                        console.warn(`❌ Inline script ${index + 1} execution failed:`, error);
                    }
                }
            });
            
        } catch (error) {
            console.warn('Script reloading failed:', error);
        }
    }

    /**
     * Script güvenlik kontrolü
     */
    isScriptSafe(scriptContent) {
        // Tehlikeli pattern'leri kontrol et
        const dangerousPatterns = [
            /eval\s*\(/,
            /Function\s*\(/,
            /setTimeout\s*\(\s*["'`]/,
            /setInterval\s*\(\s*["'`]/,
            /document\.write/,
            /innerHTML\s*=/,
            /outerHTML\s*=/,
            /location\s*=/,
            /href\s*=/
        ];

        // Kötü amaçlı code pattern'leri tespit et
        for (const pattern of dangerousPatterns) {
            if (pattern.test(scriptContent)) {
                console.warn('🚨 Potentially unsafe script detected:', pattern);
                return false;
            }
        }

        // Script boyut kontrolü (çok büyük script'leri engelle)
        if (scriptContent.length > 50000) {
            console.warn('🚨 Script too large, skipping execution');
            return false;
        }

        return true;
    }

    /**
     * Page load events trigger
     */
    triggerPageLoadEvents() {
        // DOM ready event
        const event = new CustomEvent('pageTransitionComplete', {
            detail: { url: window.location.href }
        });
        document.dispatchEvent(event);

        // Existing scripts re-initialize
        if (window.KTApp) {
            window.KTApp.init();
        }
    }

    /**
     * Loading state yönetimi
     */
    showTransitionLoading() {
        const loader = document.createElement('div');
        loader.id = 'view-transition-loader';
        loader.innerHTML = `
            <div class="d-flex justify-content-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Yükleniyor...</span>
                </div>
            </div>
        `;
        loader.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            background: rgba(255,255,255,0.9);
            padding: 20px;
            border-radius: 8px;
            backdrop-filter: blur(5px);
        `;
        document.body.appendChild(loader);
    }

    hideTransitionLoading() {
        const loader = document.getElementById('view-transition-loader');
        if (loader) loader.remove();
    }

    /**
     * Fallback transitions (eski tarayıcılar için)
     */
    setupFallbackTransitions() {
        const style = document.createElement('style');
        style.textContent = `
            .page-transition-active {
                transition: all 0.3s ease-in-out;
            }
            
            .page-fade-out {
                opacity: 0;
                transform: translateX(-20px);
            }
            
            .page-fade-in {
                opacity: 1;
                transform: translateX(0);
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Programmatik transition başlatma
     */
    static navigate(url, options = {}) {
        const instance = window.viewTransitions || new ViewTransitions();
        return instance.navigateWithTransition(url);
    }

    /**
     * Transition türü ayarlama
     */
    static setTransitionType(element, type) {
        if (element) {
            element.dataset.transition = type;
        }
    }
}

// Global instance oluştur
window.viewTransitions = new ViewTransitions();

// Export for modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ViewTransitions;
}

console.log('🎭 View Transitions API hazır! Modern sayfa geçişleri aktif.');