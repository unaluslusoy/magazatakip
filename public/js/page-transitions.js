(function(){
  const overlay = document.getElementById('pageLoaderOverlay');
  if (!overlay) return;
  let count = 0; const show=()=>overlay.classList.remove('d-none'); const hide=()=>overlay.classList.add('d-none');
  const inc=()=>{ if(++count>0) show(); }; const dec=()=>{ count=Math.max(0,count-1); if(count===0) hide(); };
  window.addEventListener('beforeunload',()=>{ show(); });
  document.addEventListener('click',e=>{ const a=e.target.closest('a'); if(!a) return; const href=a.getAttribute('href'); const target=a.getAttribute('target'); if(!href||href.startsWith('#')||href.startsWith('javascript:')||target==='_blank'||a.hasAttribute('data-no-loader')||a.closest('[data-no-loader]')) return; inc(); setTimeout(dec,15000); }, true);
  document.addEventListener('submit',e=>{ const f=e.target; if (!f || f.hasAttribute('data-no-loader')|| f.closest('[data-no-loader]')) return; inc(); setTimeout(dec,15000); }, true);
})();
// Advanced Page Transitions for Mobile PWA
class PageTransitions {
    constructor(options = {}) {
        this.options = {
            // Transition settings
            duration: 300,
            easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
            
            // Transition types
            defaultTransition: 'slide',
            mobileTransition: 'fade-slide',
            
            // Mobile specific
            enableSwipeBack: true,
            swipeThreshold: 50,
            
            // Performance
            enableGPUAcceleration: true,
            prefetchPages: true,
            
            // Customization
            transitions: {
                'slide': 'slideTransition',
                'fade': 'fadeTransition',
                'fade-slide': 'fadeSlideTransition',
                'scale': 'scaleTransition',
                'flip': 'flipTransition'
            },
            
            // Page specific transitions
            pageTransitions: {
                '/anasayfa': 'fade-slide',
                '/profil': 'slide',
                '/isemri': 'scale'
            },
            
            ...options
        };
        
        this.state = {
            isTransitioning: false,
            currentPage: window.location.pathname,
            direction: 'forward',
            transitionHistory: []
        };
        
        this.elements = {
            pageContainer: null,
            currentPage: null,
            nextPage: null
        };
        
        this.init();
    }
    
    init() {
        this.createTransitionContainer();
        this.setupEventListeners();
        this.initSwipeGestures();
        
        // PWA integration
        if (window.pwaAnalytics) {
            this.analytics = window.pwaAnalytics;
        }
        
        console.log('✅ Page Transitions initialized');
    }
    
    createTransitionContainer() {
        // Create or find page container
        let container = document.getElementById('page-transition-container');
        if (!container) {
            // Find existing page container or create new
            const appPage = document.getElementById('kt_app_page');
            if (appPage) {
                container = appPage;
                container.id = 'page-transition-container';
            } else {
                container = document.createElement('div');
                container.id = 'page-transition-container';
                container.className = 'page-transition-container';
                
                // Wrap existing content
                const body = document.body;
                while (body.firstChild) {
                    container.appendChild(body.firstChild);
                }
                body.appendChild(container);
            }
        }
        
        container.classList.add('transition-enabled');
        this.elements.pageContainer = container;
        
        // Add transition styles if not present
        this.injectTransitionStyles();
    }
    
    injectTransitionStyles() {
        if (document.getElementById('page-transition-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'page-transition-styles';
        style.textContent = `
            .page-transition-container {
                position: relative;
                overflow: hidden;
                width: 100%;
                min-height: 100vh;
            }
            
            .page-transition-container.transitioning {
                overflow: hidden;
            }
            
            .page-slide-enter {
                transform: translateX(100%);
                opacity: 0;
            }
            
            .page-slide-enter-active {
                transform: translateX(0);
                opacity: 1;
                transition: transform ${this.options.duration}ms ${this.options.easing},
                           opacity ${this.options.duration}ms ${this.options.easing};
            }
            
            .page-slide-exit {
                transform: translateX(0);
                opacity: 1;
            }
            
            .page-slide-exit-active {
                transform: translateX(-100%);
                opacity: 0;
                transition: transform ${this.options.duration}ms ${this.options.easing},
                           opacity ${this.options.duration}ms ${this.options.easing};
            }
            
            .page-fade-enter {
                opacity: 0;
                transform: scale(0.98);
            }
            
            .page-fade-enter-active {
                opacity: 1;
                transform: scale(1);
                transition: opacity ${this.options.duration}ms ${this.options.easing},
                           transform ${this.options.duration}ms ${this.options.easing};
            }
            
            .page-fade-exit {
                opacity: 1;
                transform: scale(1);
            }
            
            .page-fade-exit-active {
                opacity: 0;
                transform: scale(1.02);
                transition: opacity ${this.options.duration}ms ${this.options.easing},
                           transform ${this.options.duration}ms ${this.options.easing};
            }
            
            .page-scale-enter {
                opacity: 0;
                transform: scale(0.8);
            }
            
            .page-scale-enter-active {
                opacity: 1;
                transform: scale(1);
                transition: opacity ${this.options.duration}ms ${this.options.easing},
                           transform ${this.options.duration}ms ${this.options.easing};
            }
            
            .page-scale-exit {
                opacity: 1;
                transform: scale(1);
            }
            
            .page-scale-exit-active {
                opacity: 0;
                transform: scale(1.2);
                transition: opacity ${this.options.duration}ms ${this.options.easing},
                           transform ${this.options.duration}ms ${this.options.easing};
            }
            
            /* Mobile optimizations */
            @media (max-width: 768px) {
                .page-slide-enter {
                    transform: translateX(100%);
                }
                
                .page-slide-exit-active {
                    transform: translateX(-30%);
                }
                
                .page-fade-slide-enter {
                    opacity: 0;
                    transform: translateX(20px) scale(0.98);
                }
                
                .page-fade-slide-enter-active {
                    opacity: 1;
                    transform: translateX(0) scale(1);
                    transition: all ${this.options.duration}ms ${this.options.easing};
                }
                
                .page-fade-slide-exit {
                    opacity: 1;
                    transform: translateX(0) scale(1);
                }
                
                .page-fade-slide-exit-active {
                    opacity: 0;
                    transform: translateX(-20px) scale(0.98);
                    transition: all ${this.options.duration}ms ${this.options.easing};
                }
            }
            
            /* GPU acceleration */
            .page-transition-container.transition-enabled * {
                ${this.options.enableGPUAcceleration ? 'transform: translateZ(0); will-change: transform, opacity;' : ''}
            }
            
            /* Reduced motion */
            @media (prefers-reduced-motion: reduce) {
                .page-slide-enter-active,
                .page-slide-exit-active,
                .page-fade-enter-active,
                .page-fade-exit-active,
                .page-scale-enter-active,
                .page-scale-exit-active,
                .page-fade-slide-enter-active,
                .page-fade-slide-exit-active {
                    transition: none !important;
                }
                
                .page-slide-enter,
                .page-fade-enter,
                .page-scale-enter,
                .page-fade-slide-enter {
                    opacity: 1 !important;
                    transform: none !important;
                }
            }
        `;
        
        document.head.appendChild(style);
    }
    
    setupEventListeners() {
        // Intercept link clicks
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (this.shouldInterceptLink(link)) {
                e.preventDefault();
                this.navigateToPage(link.href, 'forward');
            }
        });
        
        // Browser navigation
        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.transitionId) {
                this.navigateToPage(window.location.href, 'back', true);
            }
        });
        
        // Mobile viewport changes
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                this.recalculateTransitions();
            }, 100);
        });
        
        // Page load completion
        window.addEventListener('load', () => {
            if (window.pageLoader) {
                setTimeout(() => {
                    this.enableTransitions();
                }, 500);
            }
        });
    }
    
    shouldInterceptLink(link) {
        if (!link) return false;
        
        const href = link.getAttribute('href');
        if (!href) return false;
        
        // Skip external links
        if (href.startsWith('http') && !href.includes(window.location.host)) {
            return false;
        }
        
        // Skip anchors and special protocols
        if (href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('tel:') || href.startsWith('mailto:')) {
            return false;
        }
        
        // Skip links with target="_blank"
        if (link.hasAttribute('target') && link.getAttribute('target') !== '_self') {
            return false;
        }
        
        // Skip if transitions disabled
        if (this.state.isTransitioning || link.hasAttribute('data-no-transition')) {
            return false;
        }
        
        return true;
    }
    
    async navigateToPage(url, direction = 'forward', isPopState = false) {
        if (this.state.isTransitioning) return;
        
        this.state.isTransitioning = true;
        this.state.direction = direction;
        
        // Show loader
        if (window.pageLoader) {
            window.pageLoader.show(`Sayfa ${direction === 'forward' ? 'yükleniyor' : 'geri dönülüyor'}...`);
        }
        
        // Determine transition type
        const transitionType = this.getTransitionType(url);
        
        // Track analytics
        if (this.analytics) {
            this.analytics.trackFeatureUsage('page-transitions', transitionType);
        }
        
        try {
            // Prefetch if enabled
            if (this.options.prefetchPages && direction === 'forward') {
                await this.prefetchPage(url);
            }
            
            // Perform transition
            await this.performTransition(url, transitionType, direction);
            
            // Update browser history
            if (!isPopState) {
                const transitionId = Date.now().toString();
                history.pushState({ transitionId }, '', url);
            }
            
            // Update state
            this.state.transitionHistory.push({
                url,
                direction,
                timestamp: Date.now(),
                transitionType
            });
            
            this.state.currentPage = new URL(url).pathname;
            
        } catch (error) {
            console.error('Page transition failed:', error);
            
            // Fallback to regular navigation
            window.location.href = url;
            
            if (this.analytics) {
                this.analytics.trackError(error, 'page-transition');
            }
        } finally {
            this.state.isTransitioning = false;
            
            if (window.pageLoader) {
                setTimeout(() => {
                    window.pageLoader.complete();
                }, 100);
            }
        }
    }
    
    getTransitionType(url) {
        const pathname = new URL(url).pathname;
        
        // Check page-specific transitions
        if (this.options.pageTransitions[pathname]) {
            return this.options.pageTransitions[pathname];
        }
        
        // Mobile vs desktop
        if (window.innerWidth <= 768) {
            return this.options.mobileTransition;
        }
        
        return this.options.defaultTransition;
    }
    
    async performTransition(url, transitionType, direction) {
        const container = this.elements.pageContainer;
        
        // Add transitioning class
        container.classList.add('transitioning');
        
        // Apply transition based on type
        switch (transitionType) {
            case 'slide':
                await this.slideTransition(url, direction);
                break;
            case 'fade':
                await this.fadeTransition(url, direction);
                break;
            case 'fade-slide':
                await this.fadeSlideTransition(url, direction);
                break;
            case 'scale':
                await this.scaleTransition(url, direction);
                break;
            default:
                await this.fadeSlideTransition(url, direction);
        }
        
        // Remove transitioning class
        setTimeout(() => {
            container.classList.remove('transitioning');
        }, this.options.duration + 50);
    }
    
    async slideTransition(url, direction) {
        return new Promise((resolve) => {
            const container = this.elements.pageContainer;
            const currentContent = container.innerHTML;
            
            // Create transition wrapper
            const wrapper = document.createElement('div');
            wrapper.style.cssText = `
                position: relative;
                width: 100%;
                height: 100%;
                overflow: hidden;
            `;
            
            // Current page
            const currentPage = document.createElement('div');
            currentPage.innerHTML = currentContent;
            currentPage.className = direction === 'forward' ? 'page-slide-exit' : 'page-slide-enter';
            
            wrapper.appendChild(currentPage);
            container.innerHTML = '';
            container.appendChild(wrapper);
            
            // Trigger exit animation
            setTimeout(() => {
                currentPage.className = direction === 'forward' ? 'page-slide-exit-active' : 'page-slide-enter-active';
                
                // Load new page
                setTimeout(() => {
                    window.location.href = url;
                    resolve();
                }, this.options.duration);
                
            }, 10);
        });
    }
    
    async fadeTransition(url, direction) {
        return new Promise((resolve) => {
            const container = this.elements.pageContainer;
            
            container.style.transition = `opacity ${this.options.duration}ms ${this.options.easing}`;
            container.style.opacity = '0';
            
            setTimeout(() => {
                window.location.href = url;
                resolve();
            }, this.options.duration / 2);
        });
    }
    
    async fadeSlideTransition(url, direction) {
        return new Promise((resolve) => {
            const container = this.elements.pageContainer;
            
            // Mobile-optimized fade + slide
            container.style.transition = `all ${this.options.duration}ms ${this.options.easing}`;
            container.style.opacity = '0';
            container.style.transform = direction === 'forward' ? 'translateX(-20px) scale(0.98)' : 'translateX(20px) scale(0.98)';
            
            setTimeout(() => {
                window.location.href = url;
                resolve();
            }, this.options.duration / 2);
        });
    }
    
    async scaleTransition(url, direction) {
        return new Promise((resolve) => {
            const container = this.elements.pageContainer;
            
            container.style.transition = `all ${this.options.duration}ms ${this.options.easing}`;
            container.style.opacity = '0';
            container.style.transform = 'scale(0.9)';
            
            setTimeout(() => {
                window.location.href = url;
                resolve();
            }, this.options.duration / 2);
        });
    }
    
    async prefetchPage(url) {
        try {
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = url;
            document.head.appendChild(link);
            
            // Also prefetch DNS if external
            if (!url.includes(window.location.host)) {
                const dnsLink = document.createElement('link');
                dnsLink.rel = 'dns-prefetch';
                dnsLink.href = new URL(url).origin;
                document.head.appendChild(dnsLink);
            }
            
        } catch (error) {
            console.warn('Prefetch failed:', error);
        }
    }
    
    initSwipeGestures() {
        if (!this.options.enableSwipeBack || !('ontouchstart' in window)) return;
        
        let startX = 0;
        let isSwipeDetected = false;
        
        document.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1 && e.touches[0].clientX < 30) {
                startX = e.touches[0].clientX;
                isSwipeDetected = true;
            }
        }, { passive: true });
        
        document.addEventListener('touchmove', (e) => {
            if (!isSwipeDetected) return;
            
            const currentX = e.touches[0].clientX;
            const diffX = currentX - startX;
            
            if (diffX > this.options.swipeThreshold) {
                isSwipeDetected = false;
                this.handleSwipeBack();
            }
        }, { passive: true });
        
        document.addEventListener('touchend', () => {
            isSwipeDetected = false;
        }, { passive: true });
    }
    
    handleSwipeBack() {
        if (window.history.length > 1) {
            window.history.back();
        }
    }
    
    enableTransitions() {
        this.elements.pageContainer.classList.add('transitions-ready');
    }
    
    disableTransitions() {
        this.elements.pageContainer.classList.remove('transitions-ready');
    }
    
    recalculateTransitions() {
        // Recalculate transition parameters after orientation change
        this.injectTransitionStyles();
    }
    
    // Public API
    setTransitionType(pathname, transitionType) {
        this.options.pageTransitions[pathname] = transitionType;
    }
    
    getTransitionHistory() {
        return this.state.transitionHistory;
    }
    
    clearTransitionHistory() {
        this.state.transitionHistory = [];
    }
    
    destroy() {
        // Remove event listeners and clean up
        this.elements.pageContainer.classList.remove('transition-enabled', 'transitions-ready', 'transitioning');
        
        const style = document.getElementById('page-transition-styles');
        if (style) {
            style.remove();
        }
        
        console.log('Page Transitions destroyed');
    }
}

// Auto-initialize
let pageTransitions = null;

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        pageTransitions = new PageTransitions({
            duration: window.innerWidth <= 768 ? 250 : 300,
            enableSwipeBack: true,
            prefetchPages: true,
            pageTransitions: {
                '/anasayfa': 'fade-slide',
                '/profil': 'slide',
                '/isemri': 'scale',
                '/fatura': 'fade'
            }
        });
        
        window.pageTransitions = pageTransitions;
        
        console.log('✅ Page Transitions ready');
        
    }, 800);
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PageTransitions;
}