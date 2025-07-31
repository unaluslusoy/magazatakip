// Business-focused Loader for Mağaza Takip App
class BusinessLoader {
    constructor(options = {}) {
        this.options = {
            // Business themed settings
            logoUrl: 'https://magazatakip.com.tr/public/media/logos/default.svg',
            primaryColor: '#1976d2',
            accentColor: '#1565c0',
            backgroundColor: '#ffffff',
            
            // Loading settings
            showProgress: true,
            showBusinessStats: true,
            showNetworkStatus: true,
            
            // Timing
            animationDuration: 600,
            minDisplayTime: 800,
            maxDisplayTime: 5000,
            
            // Business messages
            loadingMessages: [
                'Mağaza verileri yükleniyor...',
                'İş emirleri kontrol ediliyor...',
                'Envanter bilgileri güncelleniyor...',
                'Dashboard hazırlanıyor...',
                'Son işlemler getiriliyor...'
            ],
            
            ...options
        };
        
        this.state = {
            isLoading: false,
            startTime: null,
            progress: 0,
            currentMessageIndex: 0,
            businessStats: {
                totalOrders: 0,
                activeJobs: 0,
                completedToday: 0
            }
        };
        
        this.elements = {};
        this.timers = {};
        
        this.init();
    }
    
    init() {
        this.createLoader();
        this.setupEventListeners();
        
        // PWA analytics integration
        if (window.pwaAnalytics) {
            this.analytics = window.pwaAnalytics;
        }
        
        console.log('✅ Business Loader initialized');
    }
    
    createLoader() {
        // Remove existing loader
        const existingLoader = document.getElementById('business-loader');
        if (existingLoader) {
            existingLoader.remove();
        }
        
        const loader = document.createElement('div');
        loader.id = 'business-loader';
        loader.className = 'business-loader';
        
        loader.innerHTML = `
            <div class="business-backdrop"></div>
            <div class="business-content">
                <!-- Logo Section -->
                <div class="business-logo-section">
                    <div class="logo-container">
                        <img src="${this.options.logoUrl}" alt="Mağaza Takip" class="business-logo">
                        <div class="logo-ring"></div>
                        <div class="logo-glow"></div>
                    </div>
                </div>
                
                <!-- App Title -->
                <div class="business-title">
                    <h2>Mağaza Takip</h2>
                    <p>İş Yönetim Sistemi</p>
                </div>
                
                <!-- Progress Section -->
                ${this.options.showProgress ? `
                    <div class="business-progress">
                        <div class="progress-track">
                            <div class="progress-fill"></div>
                            <div class="progress-indicator"></div>
                        </div>
                        <div class="progress-percentage">0%</div>
                    </div>
                ` : ''}
                
                <!-- Loading Message -->
                <div class="business-message">
                    <span class="message-text">${this.options.loadingMessages[0]}</span>
                    <div class="message-dots">
                        <span></span><span></span><span></span>
                    </div>
                </div>
                
                <!-- Business Stats -->
                ${this.options.showBusinessStats ? `
                    <div class="business-stats">
                        <div class="stat-item">
                            <i class="ki-outline ki-document"></i>
                            <span class="stat-value" id="stat-orders">--</span>
                            <span class="stat-label">İş Emri</span>
                        </div>
                        <div class="stat-item">
                            <i class="ki-outline ki-time"></i>
                            <span class="stat-value" id="stat-active">--</span>
                            <span class="stat-label">Aktif</span>
                        </div>
                        <div class="stat-item">
                            <i class="ki-outline ki-check"></i>
                            <span class="stat-value" id="stat-completed">--</span>
                            <span class="stat-label">Tamamlanan</span>
                        </div>
                    </div>
                ` : ''}
                
                <!-- Network Status -->
                ${this.options.showNetworkStatus ? `
                    <div class="business-network">
                        <div class="network-status online">
                            <div class="network-dot"></div>
                            <span class="network-text">Bağlantı Aktif</span>
                        </div>
                    </div>
                ` : ''}
                
                <!-- Security Badge -->
                <div class="business-security">
                    <i class="ki-outline ki-security-check"></i>
                    <span>Güvenli Bağlantı</span>
                </div>
            </div>
        `;
        
        // Store element references
        this.elements = {
            loader,
            backdrop: loader.querySelector('.business-backdrop'),
            content: loader.querySelector('.business-content'),
            progressFill: loader.querySelector('.progress-fill'),
            progressIndicator: loader.querySelector('.progress-indicator'),
            progressPercentage: loader.querySelector('.progress-percentage'),
            messageText: loader.querySelector('.message-text'),
            networkStatus: loader.querySelector('.network-status'),
            statOrders: loader.querySelector('#stat-orders'),
            statActive: loader.querySelector('#stat-active'),
            statCompleted: loader.querySelector('#stat-completed')
        };
        
        // Inject styles
        this.injectStyles();
        
        // Add to document
        document.body.appendChild(loader);
    }
    
    injectStyles() {
        if (document.getElementById('business-loader-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'business-loader-styles';
        style.textContent = `
            .business-loader {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 9999;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease, visibility 0.3s ease;
            }
            
            .business-loader.active {
                opacity: 1;
                visibility: visible;
            }
            
            .business-backdrop {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(135deg, 
                    ${this.options.backgroundColor} 0%, 
                    ${this.lightenColor(this.options.backgroundColor, 5)} 50%, 
                    ${this.options.backgroundColor} 100%);
                background-size: 400% 400%;
                animation: businessGradient 3s ease-in-out infinite;
            }
            
            @keyframes businessGradient {
                0%, 100% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
            }
            
            .business-content {
                position: relative;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                height: 100%;
                padding: 2rem;
                animation: businessContentSlide 0.8s ease-out;
            }
            
            @keyframes businessContentSlide {
                0% {
                    opacity: 0;
                    transform: translateY(30px);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            /* Logo Section */
            .business-logo-section {
                margin-bottom: 2rem;
                position: relative;
            }
            
            .logo-container {
                position: relative;
                display: inline-block;
                animation: logoEntrance 1s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            }
            
            @keyframes logoEntrance {
                0% {
                    opacity: 0;
                    transform: scale(0.3) rotate(-90deg);
                }
                70% {
                    transform: scale(1.1) rotate(5deg);
                }
                100% {
                    opacity: 1;
                    transform: scale(1) rotate(0deg);
                }
            }
            
            .business-logo {
                width: 100px;
                height: 100px;
                object-fit: contain;
                filter: drop-shadow(0 8px 16px rgba(0,0,0,0.1));
                position: relative;
                z-index: 2;
            }
            
            .logo-ring {
                position: absolute;
                top: 50%;
                left: 50%;
                width: 120px;
                height: 120px;
                border: 3px solid ${this.options.primaryColor};
                border-radius: 50%;
                border-top-color: transparent;
                border-right-color: transparent;
                transform: translate(-50%, -50%);
                animation: logoRotate 2s linear infinite;
                z-index: 1;
            }
            
            @keyframes logoRotate {
                0% { transform: translate(-50%, -50%) rotate(0deg); }
                100% { transform: translate(-50%, -50%) rotate(360deg); }
            }
            
            .logo-glow {
                position: absolute;
                top: 50%;
                left: 50%;
                width: 140px;
                height: 140px;
                background: radial-gradient(circle, ${this.options.primaryColor}20 0%, transparent 70%);
                border-radius: 50%;
                transform: translate(-50%, -50%);
                animation: logoGlow 2s ease-in-out infinite alternate;
                z-index: 0;
            }
            
            @keyframes logoGlow {
                0% { transform: translate(-50%, -50%) scale(1); opacity: 0.5; }
                100% { transform: translate(-50%, -50%) scale(1.2); opacity: 0.8; }
            }
            
            /* Title Section */
            .business-title {
                text-align: center;
                margin-bottom: 2rem;
                animation: titleFadeIn 0.8s ease-out 0.3s both;
            }
            
            .business-title h2 {
                font-size: 2rem;
                font-weight: 700;
                color: ${this.options.primaryColor};
                margin: 0 0 0.5rem 0;
                letter-spacing: -0.5px;
            }
            
            .business-title p {
                font-size: 1rem;
                color: #6c757d;
                margin: 0;
                font-weight: 400;
            }
            
            @keyframes titleFadeIn {
                0% {
                    opacity: 0;
                    transform: translateY(15px);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            /* Progress Section */
            .business-progress {
                width: 250px;
                margin-bottom: 1.5rem;
                animation: progressSlideIn 0.8s ease-out 0.5s both;
            }
            
            .progress-track {
                position: relative;
                width: 100%;
                height: 6px;
                background: rgba(25, 118, 210, 0.1);
                border-radius: 3px;
                overflow: hidden;
                margin-bottom: 0.5rem;
            }
            
            .progress-fill {
                height: 100%;
                background: linear-gradient(90deg, ${this.options.primaryColor} 0%, ${this.options.accentColor} 100%);
                border-radius: 3px;
                width: 0%;
                transition: width 0.3s ease;
                position: relative;
                overflow: hidden;
            }
            
            .progress-fill::after {
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
            
            .progress-indicator {
                position: absolute;
                top: -2px;
                right: 0;
                width: 10px;
                height: 10px;
                background: ${this.options.primaryColor};
                border-radius: 50%;
                transform: translateX(50%);
                box-shadow: 0 0 8px rgba(25, 118, 210, 0.5);
                animation: indicatorPulse 1s ease-in-out infinite alternate;
            }
            
            @keyframes indicatorPulse {
                0% { transform: translateX(50%) scale(1); }
                100% { transform: translateX(50%) scale(1.2); }
            }
            
            .progress-percentage {
                text-align: center;
                font-size: 0.875rem;
                font-weight: 600;
                color: ${this.options.primaryColor};
            }
            
            @keyframes progressSlideIn {
                0% {
                    opacity: 0;
                    transform: translateY(20px);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            /* Loading Message */
            .business-message {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                margin-bottom: 2rem;
                animation: messageSlideIn 0.8s ease-out 0.7s both;
            }
            
            .message-text {
                font-size: 0.9rem;
                color: #495057;
                font-weight: 500;
                transition: all 0.3s ease;
            }
            
            .message-dots {
                display: flex;
                gap: 3px;
            }
            
            .message-dots span {
                width: 4px;
                height: 4px;
                background: ${this.options.primaryColor};
                border-radius: 50%;
                animation: dotBounce 1.4s ease-in-out infinite both;
            }
            
            .message-dots span:nth-child(1) { animation-delay: -0.32s; }
            .message-dots span:nth-child(2) { animation-delay: -0.16s; }
            .message-dots span:nth-child(3) { animation-delay: 0s; }
            
            @keyframes dotBounce {
                0%, 80%, 100% {
                    transform: scale(0.8);
                    opacity: 0.5;
                }
                40% {
                    transform: scale(1.2);
                    opacity: 1;
                }
            }
            
            @keyframes messageSlideIn {
                0% {
                    opacity: 0;
                    transform: translateY(15px);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            /* Business Stats */
            .business-stats {
                display: flex;
                gap: 2rem;
                margin-bottom: 1.5rem;
                animation: statsSlideIn 0.8s ease-out 0.9s both;
            }
            
            .stat-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                min-width: 80px;
            }
            
            .stat-item i {
                font-size: 1.5rem;
                color: ${this.options.primaryColor};
                margin-bottom: 0.5rem;
            }
            
            .stat-value {
                font-size: 1.25rem;
                font-weight: 700;
                color: ${this.options.primaryColor};
                line-height: 1;
                margin-bottom: 0.25rem;
                animation: statCountUp 2s ease-out;
            }
            
            .stat-label {
                font-size: 0.75rem;
                color: #6c757d;
                font-weight: 500;
            }
            
            @keyframes statCountUp {
                0% { transform: scale(0.8); opacity: 0; }
                100% { transform: scale(1); opacity: 1; }
            }
            
            @keyframes statsSlideIn {
                0% {
                    opacity: 0;
                    transform: translateY(20px);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            /* Network Status */
            .business-network {
                margin-bottom: 1rem;
                animation: networkSlideIn 0.8s ease-out 1.1s both;
            }
            
            .network-status {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                border-radius: 20px;
                font-size: 0.75rem;
                font-weight: 500;
                transition: all 0.3s ease;
            }
            
            .network-status.online {
                background: rgba(40, 167, 69, 0.1);
                color: #28a745;
                border: 1px solid rgba(40, 167, 69, 0.2);
            }
            
            .network-status.offline {
                background: rgba(220, 53, 69, 0.1);
                color: #dc3545;
                border: 1px solid rgba(220, 53, 69, 0.2);
            }
            
            .network-dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: currentColor;
                animation: networkPulse 2s ease-in-out infinite;
            }
            
            @keyframes networkPulse {
                0%, 100% { opacity: 1; transform: scale(1); }
                50% { opacity: 0.6; transform: scale(0.8); }
            }
            
            @keyframes networkSlideIn {
                0% {
                    opacity: 0;
                    transform: translateY(10px);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            /* Security Badge */
            .business-security {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.75rem;
                color: #6c757d;
                animation: securityFadeIn 0.8s ease-out 1.3s both;
            }
            
            .business-security i {
                color: #28a745;
            }
            
            @keyframes securityFadeIn {
                0% { opacity: 0; }
                100% { opacity: 1; }
            }
            
            /* Mobile optimizations */
            @media (max-width: 768px) {
                .business-content {
                    padding: 1.5rem;
                }
                
                .business-logo {
                    width: 80px;
                    height: 80px;
                }
                
                .logo-ring,
                .logo-glow {
                    width: 100px;
                    height: 100px;
                }
                
                .business-title h2 {
                    font-size: 1.75rem;
                }
                
                .business-progress {
                    width: 200px;
                }
                
                .business-stats {
                    gap: 1.5rem;
                }
                
                .stat-item {
                    min-width: 60px;
                }
                
                .stat-item i {
                    font-size: 1.25rem;
                }
                
                .stat-value {
                    font-size: 1.1rem;
                }
            }
            
            /* Reduced motion */
            @media (prefers-reduced-motion: reduce) {
                .business-loader *,
                .business-loader *::before,
                .business-loader *::after {
                    animation-duration: 0.1s !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.1s !important;
                }
            }
            
            /* Dark mode support */
            @media (prefers-color-scheme: dark) {
                .business-backdrop {
                    background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 50%, #1a1a1a 100%);
                }
                
                .business-title h2 {
                    color: #ffffff;
                }
                
                .business-title p {
                    color: #adb5bd;
                }
                
                .message-text {
                    color: #dee2e6;
                }
                
                .stat-label {
                    color: #adb5bd;
                }
                
                .business-security {
                    color: #adb5bd;
                }
            }
        `;
        
        document.head.appendChild(style);
    }
    
    setupEventListeners() {
        // Network status monitoring
        window.addEventListener('online', () => {
            this.updateNetworkStatus(true);
        });
        
        window.addEventListener('offline', () => {
            this.updateNetworkStatus(false);
        });
        
        // Performance monitoring
        if ('PerformanceObserver' in window) {
            this.setupPerformanceMonitoring();
        }
    }
    
    show(customMessage = null) {
        if (this.state.isLoading) return;
        
        this.state.isLoading = true;
        this.state.startTime = Date.now();
        this.state.progress = 0;
        this.state.currentMessageIndex = 0;
        
        const loader = this.elements.loader;
        if (!loader) return;
        
        // Update message if provided
        if (customMessage && this.elements.messageText) {
            this.elements.messageText.textContent = customMessage;
        }
        
        // Show loader
        loader.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Start animations
        this.startProgressAnimation();
        this.startMessageRotation();
        this.updateBusinessStats();
        this.updateNetworkStatus(navigator.onLine);
        
        // Auto hide
        setTimeout(() => {
            this.hide();
        }, this.options.minDisplayTime);
        
        // Track analytics
        if (this.analytics) {
            this.analytics.trackFeatureUsage('business-loader', 'show');
        }
        
        console.log('🏢 Business loader shown');
    }
    
    hide() {
        if (!this.state.isLoading) return;
        
        const loader = this.elements.loader;
        if (!loader) return;
        
        const loadTime = Date.now() - this.state.startTime;
        
        // Clear timers
        Object.values(this.timers).forEach(timer => {
            if (timer) clearTimeout(timer);
        });
        
        // Hide loader
        loader.classList.remove('active');
        
        setTimeout(() => {
            document.body.style.overflow = '';
            this.state.isLoading = false;
            
            console.log(`✅ Business loaded in ${loadTime}ms`);
            
            if (this.analytics) {
                this.analytics.trackPerformance('business-load-time', loadTime);
            }
        }, this.options.animationDuration);
    }
    
    setProgress(percent) {
        if (!this.state.isLoading) return;
        
        this.state.progress = Math.min(100, Math.max(0, percent));
        
        if (this.elements.progressFill) {
            this.elements.progressFill.style.width = `${this.state.progress}%`;
        }
        
        if (this.elements.progressPercentage) {
            this.elements.progressPercentage.textContent = `${Math.round(this.state.progress)}%`;
        }
        
        if (this.elements.progressIndicator) {
            this.elements.progressIndicator.style.right = `${100 - this.state.progress}%`;
        }
    }
    
    startProgressAnimation() {
        let progress = 0;
        const increment = () => {
            if (!this.state.isLoading) return;
            
            const random = Math.random() * 12;
            const slowdown = progress > 70 ? 0.3 : 1;
            progress += random * slowdown;
            
            this.setProgress(Math.min(progress, 90));
            
            if (progress < 90) {
                this.timers.progress = setTimeout(increment, 150 + Math.random() * 200);
            }
        };
        
        increment();
    }
    
    startMessageRotation() {
        if (this.options.loadingMessages.length <= 1) return;
        
        const rotateMessage = () => {
            if (!this.state.isLoading) return;
            
            this.state.currentMessageIndex = (this.state.currentMessageIndex + 1) % this.options.loadingMessages.length;
            
            if (this.elements.messageText) {
                this.elements.messageText.textContent = this.options.loadingMessages[this.state.currentMessageIndex];
            }
            
            this.timers.message = setTimeout(rotateMessage, 1800);
        };
        
        this.timers.message = setTimeout(rotateMessage, 1800);
    }
    
    updateBusinessStats() {
        // Simulate business stats
        const stats = {
            orders: Math.floor(Math.random() * 50) + 20,
            active: Math.floor(Math.random() * 15) + 5,
            completed: Math.floor(Math.random() * 10) + 2
        };
        
        if (this.elements.statOrders) {
            this.animateCounter(this.elements.statOrders, stats.orders);
        }
        if (this.elements.statActive) {
            this.animateCounter(this.elements.statActive, stats.active);
        }
        if (this.elements.statCompleted) {
            this.animateCounter(this.elements.statCompleted, stats.completed);
        }
    }
    
    animateCounter(element, target) {
        let current = 0;
        const increment = target / 20;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.round(current);
        }, 50);
    }
    
    updateNetworkStatus(isOnline) {
        const status = this.elements.networkStatus;
        if (!status) return;
        
        status.classList.toggle('online', isOnline);
        status.classList.toggle('offline', !isOnline);
        
        const text = status.querySelector('.network-text');
        if (text) {
            text.textContent = isOnline ? 'Bağlantı Aktif' : 'Çevrimdışı Mod';
        }
    }
    
    setupPerformanceMonitoring() {
        try {
            const observer = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                entries.forEach(entry => {
                    if (entry.entryType === 'navigation') {
                        const loadTime = entry.loadEventEnd - entry.fetchStart;
                        if (this.analytics && loadTime > 0) {
                            this.analytics.trackPerformance('business-navigation-timing', loadTime);
                        }
                    }
                });
            });
            
            observer.observe({ entryTypes: ['navigation'] });
        } catch (error) {
            console.warn('Performance monitoring setup failed:', error);
        }
    }
    
    // Utility function to lighten color
    lightenColor(color, percent) {
        if (color.startsWith('#')) {
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
    showWithMessage(message) {
        this.show(message);
    }
    
    complete() {
        this.setProgress(100);
        setTimeout(() => this.hide(), 300);
    }
    
    destroy() {
        Object.values(this.timers).forEach(timer => {
            if (timer) clearTimeout(timer);
        });
        
        if (this.elements.loader) {
            this.elements.loader.remove();
        }
        
        document.body.style.overflow = '';
        
        console.log('Business loader destroyed');
    }
}

// Auto-initialize business loader
let businessLoader = null;

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        businessLoader = new BusinessLoader();
        
        // Make globally available
        window.businessLoader = businessLoader;
        
        // Replace old page loader
        if (window.pageLoader) {
            window.pageLoader.destroy();
        }
        window.pageLoader = businessLoader;
        
        console.log('✅ Business Loader system ready');
        
    }, 300);
});

// Show on page load
window.addEventListener('load', function() {
    if (businessLoader && document.readyState === 'complete') {
        businessLoader.complete();
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BusinessLoader;
}