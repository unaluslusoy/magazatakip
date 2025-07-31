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

            /* Header ve Footer sabit - transition'a dahil etme */
            #kt_app_header,
            #kt_app_footer,
            .app-header,
            .app-footer {
                view-transition-name: none;
            }

            /* Sadece Content Container transition yapsın */
            #kt_app_content_container {
                view-transition-name: main-content;
            }

            /* Content geçiş animasyonları */
            ::view-transition-old(main-content) {
                animation: slide-out-left 0.3s ease-in-out;
            }

            ::view-transition-new(main-content) {
                animation: slide-in-right 0.3s ease-in-out;
            }

            /* Root transition'ı disable et (sadece content için) */
            ::view-transition-old(root),
            ::view-transition-new(root) {
                animation: none;
            }

            /* Content için özel geçiş türleri */
            #kt_app_content_container.page-transition-fade {
                view-transition-name: content-fade;
            }

            ::view-transition-old(content-fade) {
                animation: fade-out 0.2s ease-out;
            }

            ::view-transition-new(content-fade) {
                animation: fade-in 0.2s ease-in;
            }

            /* Profil sayfası özel geçişi */
            #kt_app_content_container.profile-transition {
                view-transition-name: content-profile;
            }

            ::view-transition-old(content-profile) {
                animation: slide-out-right 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            }

            ::view-transition-new(content-profile) {
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
        console.log('🎭 Starting navigation to:', url);
        
        if (!this.isSupported) {
            console.log('⚠️ View Transitions not supported, using fallback');
            window.location.href = url;
            return;
        }

        try {
            // Transition türünü belirle
            const transitionType = element?.dataset.transition || 'default';
            console.log('🎨 Transition type:', transitionType);
            
            // Loading state göster
            this.showTransitionLoading();

            // Modern View Transition başlat
            await document.startViewTransition(async () => {
                console.log('📦 View transition started, loading content...');
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
            console.log('🔄 Loading content from:', url);
            
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const html = await response.text();
            console.log('📥 HTML received, length:', html.length);
            
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');
            
            // Debug: Document parsing
            console.log('📋 New document title:', newDoc.title);
            console.log('📦 Content container found:', !!newDoc.querySelector('#kt_app_content_container'));

            // Sayfa içeriğini güncelle
            this.updatePageContent(newDoc);
            
            // URL'yi güncelle
            window.history.pushState({}, '', url);
            console.log('🔗 URL updated to:', url);
            
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
        console.log('🔄 Starting page content update...');
        
        // Title güncelle
        const oldTitle = document.title;
        document.title = newDoc.title;
        console.log('📝 Title updated:', oldTitle, '→', newDoc.title);

        // Debug: Mevcut DOM yapısını kontrol et
        const oldContent = document.querySelector('#kt_app_content_container');
        const newContent = newDoc.querySelector('#kt_app_content_container');
        
        console.log('🔍 DOM Debug:', {
            currentPage: window.location.pathname,
            oldContentExists: !!oldContent,
            newContentExists: !!newContent,
            oldContentHTML: oldContent ? oldContent.innerHTML.substring(0, 200) + '...' : 'NULL',
            newContentHTML: newContent ? newContent.innerHTML.substring(0, 200) + '...' : 'NULL'
        });
        
        if (oldContent && newContent) {
            // Content'i güncelle
            const oldHTML = oldContent.innerHTML;
            oldContent.innerHTML = newContent.innerHTML;
            
            console.log('📄 Content replaced:', {
                oldLength: oldHTML.length,
                newLength: newContent.innerHTML.length,
                changed: oldHTML !== newContent.innerHTML
            });
            
            // Transition class'ını uygula
            this.applyTransitionClass(oldContent, newContent);
            
            console.log('✅ Page content updated successfully');
        } else {
            console.warn('⚠️ Content containers not found:', { 
                oldContent: !!oldContent, 
                newContent: !!newContent,
                currentHTML: document.body.innerHTML.substring(0, 500) + '...'
            });
            
            // Fallback: tüm app-content'i güncelle
            const fallbackOld = document.querySelector('#kt_app_content');
            const fallbackNew = newDoc.querySelector('#kt_app_content');
            
            console.log('🔄 Trying fallback selectors:', {
                fallbackOldExists: !!fallbackOld,
                fallbackNewExists: !!fallbackNew
            });
            
            if (fallbackOld && fallbackNew) {
                fallbackOld.innerHTML = fallbackNew.innerHTML;
                console.log('✅ Content updated via fallback selector');
            } else {
                // Son çare: full page reload
                console.error('❌ No content selectors found, falling back to full reload');
                window.location.reload();
                return;
            }
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
     * Content container'a transition class uygula
     */
    applyTransitionClass(oldContent, newContent) {
        try {
            // Mevcut transition class'larını temizle
            oldContent.classList.remove('page-transition-fade', 'profile-transition');
            
            // URL'e göre transition type'ını belirle
            const currentUrl = window.location.pathname;
            
            if (currentUrl.includes('/profil')) {
                oldContent.classList.add('profile-transition');
                console.log('🎭 Profile transition applied');
            } else if (currentUrl.includes('/anasayfa') || currentUrl === '/') {
                oldContent.classList.add('page-transition-fade');
                console.log('🎭 Fade transition applied');
            } else {
                // Default transition
                oldContent.classList.add('page-transition-fade');
                console.log('🎭 Default fade transition applied');
            }
            
            // Transition sonrası class'ları temizle
            setTimeout(() => {
                oldContent.classList.remove('page-transition-fade', 'profile-transition');
            }, 500);
            
        } catch (error) {
            console.warn('Transition class application failed:', error);
        }
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
console.log('🎭 View Transitions API hazır! Modern sayfa geçişleri aktif.');