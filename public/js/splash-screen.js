// PWA Splash Screen Custom Implementation
class SplashScreen {
    constructor(options = {}) {
        this.options = {
            logoUrl: 'https://magazatakip.com.tr/public/media/logos/default.svg',
            appName: 'Mağaza Takip',
            backgroundColor: '#ffffff',
            textColor: '#2c3e50',
            duration: 2500,
            fadeOutDuration: 800,
            showProgress: true,
            showAppName: true,
            enableAutoHide: true,
            ...options
        };
        
        this.state = {
            isShowing: false,
            isFirstLoad: false
        };
        
        this.init();
    }
    
    init() {
        // Check if this is first app load
        this.checkFirstLoad();
        
        // Create splash screen elements
        this.createSplashElements();
        
        // Show splash screen if conditions met
        if (this.shouldShowSplash()) {
            this.show();
        }
        
        console.log('✅ Splash Screen initialized');
    }
    
    checkFirstLoad() {
        // Check if PWA was launched from home screen
        const isPWA = window.matchMedia('(display-mode: standalone)').matches || 
                     window.navigator.standalone || 
                     document.referrer.includes('android-app://');
        
        // Check if this is first visit today
        const lastSplash = localStorage.getItem('pwa-last-splash');
        const today = new Date().toDateString();
        const isFirstToday = !lastSplash || lastSplash !== today;
        
        this.state.isFirstLoad = isPWA && isFirstToday;
        
        if (this.state.isFirstLoad) {
            localStorage.setItem('pwa-last-splash', today);
        }
    }
    
    shouldShowSplash() {
        // Show splash on first PWA load or when manually triggered
        return this.state.isFirstLoad || 
               window.location.hash.includes('splash') ||
               sessionStorage.getItem('force-splash') === 'true';
    }
    
    createSplashElements() {
        // Remove existing splash if any
        const existingSplash = document.getElementById('pwa-splash-screen');
        if (existingSplash) {
            existingSplash.remove();
        }
        
        // Create splash screen container
        const splash = document.createElement('div');
        splash.id = 'pwa-splash-screen';
        splash.className = 'pwa-splash-screen';
        
        splash.innerHTML = `
            <div class="splash-background"></div>
            <div class="splash-content">
                <div class="splash-logo-container">
                    <div class="splash-logo-wrapper">
                        <img src="${this.options.logoUrl}" alt="${this.options.appName}" class="splash-logo">
                        <div class="splash-logo-glow"></div>
                        <div class="splash-logo-pulse"></div>
                    </div>
                </div>
                
                ${this.options.showAppName ? `
                    <div class="splash-app-info">
                        <h1 class="splash-app-name">${this.options.appName}</h1>
                        <p class="splash-app-tagline">Modern İş Yönetimi</p>
                    </div>
                ` : ''}
                
                ${this.options.showProgress ? `
                    <div class="splash-progress">
                        <div class="splash-progress-bar">
                            <div class="splash-progress-fill"></div>
                        </div>
                        <div class="splash-loading-text">Yükleniyor...</div>
                    </div>
                ` : ''}
                
                <div class="splash-powered-by">
                    <span>Powered by PWA Technology</span>
                </div>
            </div>
        `;
        
        // Add splash screen styles
        this.injectSplashStyles();
        
        // Insert splash screen
        document.body.appendChild(splash);
        this.splashElement = splash;
    }
    
    injectSplashStyles() {
        if (document.getElementById('splash-screen-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'splash-screen-styles';
        style.textContent = `
            .pwa-splash-screen {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 10000;
                display: flex;
                align-items: center;
                justify-content: center;
                opacity: 0;
                visibility: hidden;
                transition: opacity ${this.options.fadeOutDuration}ms ease, visibility ${this.options.fadeOutDuration}ms ease;
            }
            
            .pwa-splash-screen.active {
                opacity: 1;
                visibility: visible;
            }
            
            .splash-background {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(135deg, 
                    ${this.options.backgroundColor} 0%, 
                    ${this.lightenColor(this.options.backgroundColor, 10)} 50%, 
                    ${this.options.backgroundColor} 100%);
                background-size: 400% 400%;
                animation: gradientShift 4s ease-in-out infinite;
            }
            
            @keyframes gradientShift {
                0%, 100% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
            }
            
            .splash-content {
                position: relative;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-align: center;
                padding: 2rem;
                animation: splashContentFadeIn 1s ease-out;
            }
            
            @keyframes splashContentFadeIn {
                0% {
                    opacity: 0;
                    transform: translateY(30px) scale(0.9);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }
            
            .splash-logo-container {
                margin-bottom: 2rem;
                position: relative;
            }
            
            .splash-logo-wrapper {
                position: relative;
                display: inline-block;
                animation: logoAppear 1.2s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            }
            
            @keyframes logoAppear {
                0% {
                    opacity: 0;
                    transform: scale(0) rotate(-180deg);
                }
                70% {
                    transform: scale(1.1) rotate(0deg);
                }
                100% {
                    opacity: 1;
                    transform: scale(1) rotate(0deg);
                }
            }
            
            .splash-logo {
                width: 120px;
                height: 120px;
                object-fit: contain;
                filter: drop-shadow(0 8px 16px rgba(0, 0, 0, 0.1));
                position: relative;
                z-index: 2;
            }
            
            .splash-logo-glow {
                position: absolute;
                top: 50%;
                left: 50%;
                width: 140px;
                height: 140px;
                background: radial-gradient(circle, ${this.options.backgroundColor}40 0%, transparent 70%);
                border-radius: 50%;
                transform: translate(-50%, -50%);
                animation: logoGlow 2s ease-in-out infinite alternate;
                z-index: 1;
            }
            
            @keyframes logoGlow {
                0% { transform: translate(-50%, -50%) scale(1); opacity: 0.5; }
                100% { transform: translate(-50%, -50%) scale(1.1); opacity: 0.8; }
            }
            
            .splash-logo-pulse {
                position: absolute;
                top: 50%;
                left: 50%;
                width: 120px;
                height: 120px;
                border: 3px solid ${this.options.textColor}40;
                border-radius: 50%;
                transform: translate(-50%, -50%);
                animation: logoPulse 2s ease-in-out infinite;
                z-index: 1;
            }
            
            @keyframes logoPulse {
                0%, 100% {
                    transform: translate(-50%, -50%) scale(1);
                    opacity: 0.8;
                }
                50% {
                    transform: translate(-50%, -50%) scale(1.3);
                    opacity: 0.3;
                }
            }
            
            .splash-app-info {
                margin-bottom: 2rem;
                animation: appInfoSlideUp 1s ease-out 0.3s both;
            }
            
            @keyframes appInfoSlideUp {
                0% {
                    opacity: 0;
                    transform: translateY(20px);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .splash-app-name {
                font-size: 2rem;
                font-weight: 700;
                color: ${this.options.textColor};
                margin: 0 0 0.5rem 0;
                letter-spacing: -0.5px;
            }
            
            .splash-app-tagline {
                font-size: 1rem;
                color: ${this.options.textColor}80;
                margin: 0;
                font-weight: 400;
            }
            
            .splash-progress {
                margin-bottom: 2rem;
                width: 200px;
                animation: progressAppear 1s ease-out 0.6s both;
            }
            
            @keyframes progressAppear {
                0% {
                    opacity: 0;
                    transform: translateY(15px);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .splash-progress-bar {
                width: 100%;
                height: 4px;
                background: ${this.options.textColor}20;
                border-radius: 2px;
                overflow: hidden;
                margin-bottom: 1rem;
            }
            
            .splash-progress-fill {
                height: 100%;
                background: linear-gradient(90deg, ${this.options.textColor} 0%, ${this.lightenColor(this.options.textColor, 20)} 100%);
                border-radius: 2px;
                width: 0%;
                transition: width 0.3s ease;
                position: relative;
                overflow: hidden;
            }
            
            .splash-progress-fill::after {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.4) 50%, transparent 100%);
                animation: progressShimmer 1.5s ease-in-out infinite;
            }
            
            @keyframes progressShimmer {
                0% { transform: translateX(-100%); }
                100% { transform: translateX(100%); }
            }
            
            .splash-loading-text {
                font-size: 0.875rem;
                color: ${this.options.textColor}70;
                text-align: center;
            }
            
            .splash-powered-by {
                position: absolute;
                bottom: 2rem;
                left: 50%;
                transform: translateX(-50%);
                font-size: 0.75rem;
                color: ${this.options.textColor}50;
                animation: poweredByFadeIn 1s ease-out 1s both;
            }
            
            @keyframes poweredByFadeIn {
                0% { opacity: 0; }
                100% { opacity: 1; }
            }
            
            /* Mobile optimizations */
            @media (max-width: 768px) {
                .splash-logo {
                    width: 100px;
                    height: 100px;
                }
                
                .splash-logo-glow,
                .splash-logo-pulse {
                    width: 120px;
                    height: 120px;
                }
                
                .splash-app-name {
                    font-size: 1.75rem;
                }
                
                .splash-app-tagline {
                    font-size: 0.875rem;
                }
                
                .splash-progress {
                    width: 180px;
                }
            }
            
            /* Dark mode support */
            @media (prefers-color-scheme: dark) {
                .splash-background {
                    background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 50%, #1a1a1a 100%);
                }
            }
            
            /* Reduced motion */
            @media (prefers-reduced-motion: reduce) {
                .pwa-splash-screen *,
                .pwa-splash-screen *::before,
                .pwa-splash-screen *::after {
                    animation-duration: 0.1s !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.1s !important;
                }
            }
        `;
        
        document.head.appendChild(style);
    }
    
    show() {
        if (this.state.isShowing || !this.splashElement) return;
        
        this.state.isShowing = true;
        
        // Disable body scroll
        document.body.style.overflow = 'hidden';
        
        // Show splash screen
        this.splashElement.classList.add('active');
        
        // Start progress animation
        if (this.options.showProgress) {
            this.animateProgress();
        }
        
        // Auto hide after duration
        if (this.options.enableAutoHide) {
            setTimeout(() => {
                this.hide();
            }, this.options.duration);
        }
        
        // Track analytics
        if (window.pwaAnalytics) {
            window.pwaAnalytics.trackFeatureUsage('splash-screen', 'shown');
        }
        
        console.log('🎯 Splash screen shown');
    }
    
    hide() {
        if (!this.state.isShowing || !this.splashElement) return;
        
        this.state.isShowing = false;
        
        // Fade out
        this.splashElement.classList.remove('active');
        
        // Remove after animation
        setTimeout(() => {
            if (this.splashElement && this.splashElement.parentNode) {
                this.splashElement.parentNode.removeChild(this.splashElement);
            }
            
            // Re-enable body scroll
            document.body.style.overflow = '';
            
            // Start page loader if available
            if (window.pageLoader && !window.pageLoader.state.isLoading) {
                window.pageLoader.show('Uygulama hazırlanıyor...');
                setTimeout(() => {
                    window.pageLoader.complete();
                }, 1500);
            }
            
        }, this.options.fadeOutDuration);
        
        // Track analytics
        if (window.pwaAnalytics) {
            window.pwaAnalytics.trackFeatureUsage('splash-screen', 'hidden');
        }
        
        console.log('✅ Splash screen hidden');
    }
    
    animateProgress() {
        const progressFill = this.splashElement.querySelector('.splash-progress-fill');
        if (!progressFill) return;
        
        let progress = 0;
        const interval = setInterval(() => {
            if (!this.state.isShowing) {
                clearInterval(interval);
                return;
            }
            
            progress += Math.random() * 25;
            progress = Math.min(progress, 95);
            
            progressFill.style.width = `${progress}%`;
            
            if (progress >= 95) {
                clearInterval(interval);
                // Complete progress
                setTimeout(() => {
                    progressFill.style.width = '100%';
                }, 200);
            }
        }, 200);
    }
    
    // Utility function to lighten color
    lightenColor(color, percent) {
        if (color.startsWith('#')) {
            // Simple hex color lightening
            const num = parseInt(color.replace("#", ""), 16);
            const amt = Math.round(2.55 * percent);
            const R = (num >> 16) + amt;
            const G = (num >> 8 & 0x00FF) + amt;
            const B = (num & 0x0000FF) + amt;
            return "#" + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
                (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
                (B < 255 ? B < 1 ? 0 : B : 255))
                .toString(16).slice(1);
        }
        return color;
    }
    
    // Public API
    forceShow() {
        sessionStorage.setItem('force-splash', 'true');
        this.show();
    }
    
    forceHide() {
        this.hide();
    }
    
    destroy() {
        if (this.splashElement && this.splashElement.parentNode) {
            this.splashElement.parentNode.removeChild(this.splashElement);
        }
        
        const style = document.getElementById('splash-screen-styles');
        if (style) {
            style.remove();
        }
        
        document.body.style.overflow = '';
        
        console.log('Splash screen destroyed');
    }
}

// Auto-initialize splash screen
let splashScreen = null;

// Initialize immediately for fast display
document.addEventListener('DOMContentLoaded', function() {
    splashScreen = new SplashScreen({
        logoUrl: 'https://magazatakip.com.tr/public/media/logos/default.svg',
        appName: 'Mağaza Takip',
        backgroundColor: '#ffffff',
        textColor: '#2c3e50',
        duration: 2500
    });
    
    // Make globally available
    window.splashScreen = splashScreen;
    
    console.log('✅ Splash Screen system ready');
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SplashScreen;
}