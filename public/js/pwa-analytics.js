// PWA Analytics & Performance Tracking
class PWAAnalytics {
    constructor() {
        this.startTime = performance.now();
        this.metrics = {};
        
        this.init();
    }
    
    init() {
        this.trackInstallationState();
        this.trackPerformanceMetrics();
        this.trackUserEngagement();
        this.setupVisibilityTracking();
    }
    
    trackInstallationState() {
        // PWA installation durumunu track et
        const isInstalled = window.matchMedia('(display-mode: standalone)').matches || 
                           window.navigator.standalone || 
                           document.referrer.includes('android-app://');
        
        this.metrics.isInstalled = isInstalled;
        this.metrics.displayMode = this.getDisplayMode();
        
        // Platform detection
        this.metrics.platform = this.detectPlatform();
        
        this.sendAnalytics('pwa_state', {
            installed: isInstalled,
            display_mode: this.metrics.displayMode,
            platform: this.metrics.platform
        });
    }
    
    getDisplayMode() {
        if (window.matchMedia('(display-mode: standalone)').matches) {
            return 'standalone';
        }
        if (window.matchMedia('(display-mode: minimal-ui)').matches) {
            return 'minimal-ui';
        }
        if (window.matchMedia('(display-mode: fullscreen)').matches) {
            return 'fullscreen';
        }
        return 'browser';
    }
    
    detectPlatform() {
        const userAgent = navigator.userAgent.toLowerCase();
        
        if (/android/.test(userAgent)) return 'android';
        if (/iphone|ipad|ipod/.test(userAgent)) return 'ios';
        if (/windows/.test(userAgent)) return 'windows';
        if (/macintosh/.test(userAgent)) return 'macos';
        if (/linux/.test(userAgent)) return 'linux';
        
        return 'unknown';
    }
    
    trackPerformanceMetrics() {
        // Core Web Vitals ve PWA özel metrikleri
        if ('PerformanceObserver' in window) {
            // Largest Contentful Paint (LCP)
            new PerformanceObserver((list) => {
                const entries = list.getEntries();
                const lcpEntry = entries[entries.length - 1];
                this.metrics.lcp = lcpEntry.renderTime || lcpEntry.loadTime;
                
                this.sendAnalytics('core_web_vitals', {
                    metric: 'lcp',
                    value: this.metrics.lcp,
                    rating: this.getLCPRating(this.metrics.lcp)
                });
            }).observe({ entryTypes: ['largest-contentful-paint'] });
            
            // First Input Delay (FID)
            new PerformanceObserver((list) => {
                const entries = list.getEntries();
                entries.forEach((entry) => {
                    this.metrics.fid = entry.processingStart - entry.startTime;
                    
                    this.sendAnalytics('core_web_vitals', {
                        metric: 'fid',
                        value: this.metrics.fid,
                        rating: this.getFIDRating(this.metrics.fid)
                    });
                });
            }).observe({ entryTypes: ['first-input'] });
            
            // Cumulative Layout Shift (CLS)
            let clsValue = 0;
            new PerformanceObserver((list) => {
                const entries = list.getEntries();
                entries.forEach((entry) => {
                    if (!entry.hadRecentInput) {
                        clsValue += entry.value;
                    }
                });
                this.metrics.cls = clsValue;
            }).observe({ entryTypes: ['layout-shift'] });
        }
        
        // Service Worker performance
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.ready.then((registration) => {
                this.metrics.swActivationTime = performance.now() - this.startTime;
                
                this.sendAnalytics('sw_performance', {
                    activation_time: this.metrics.swActivationTime,
                    sw_state: registration.active ? 'active' : 'inactive'
                });
            });
        }
        
        // Cache hit rate tracking
        this.trackCachePerformance();
    }
    
    trackCachePerformance() {
        const originalFetch = window.fetch;
        let totalRequests = 0;
        let cacheHits = 0;
        
        window.fetch = function(...args) {
            totalRequests++;
            
            return originalFetch.apply(this, args).then((response) => {
                // Response cache durumunu kontrol et
                if (response.headers.get('x-cache') === 'HIT' || 
                    response.type === 'cached') {
                    cacheHits++;
                }
                
                // Her 10 request'te bir cache rate'i rapor et
                if (totalRequests % 10 === 0) {
                    const cacheHitRate = (cacheHits / totalRequests) * 100;
                    
                    pwaAnalytics.sendAnalytics('cache_performance', {
                        cache_hit_rate: cacheHitRate,
                        total_requests: totalRequests,
                        cache_hits: cacheHits
                    });
                }
                
                return response;
            });
        };
    }
    
    trackUserEngagement() {
        // Session duration tracking
        this.sessionStart = Date.now();
        
        // Page visibility changes
        let visibilityStart = Date.now();
        
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                const visibleTime = Date.now() - visibilityStart;
                this.metrics.totalVisibleTime = (this.metrics.totalVisibleTime || 0) + visibleTime;
            } else {
                visibilityStart = Date.now();
            }
        });
        
        // Before unload - session summary
        window.addEventListener('beforeunload', () => {
            const sessionDuration = Date.now() - this.sessionStart;
            
            this.sendAnalytics('session_end', {
                session_duration: sessionDuration,
                total_visible_time: this.metrics.totalVisibleTime || 0,
                engagement_rate: ((this.metrics.totalVisibleTime || 0) / sessionDuration) * 100,
                is_installed: this.metrics.isInstalled,
                platform: this.metrics.platform
            });
        });
        
        // Interaction tracking
        let interactionCount = 0;
        ['click', 'keydown', 'touchstart'].forEach(eventType => {
            document.addEventListener(eventType, () => {
                interactionCount++;
                
                // Her 50 interaction'da bir rapor et
                if (interactionCount % 50 === 0) {
                    this.sendAnalytics('user_engagement', {
                        interaction_count: interactionCount,
                        session_time: Date.now() - this.sessionStart
                    });
                }
            }, { passive: true });
        });
    }
    
    setupVisibilityTracking() {
        // PWA açılma kaynaklarını track et
        const referrer = document.referrer;
        const isFromHomeScreen = window.matchMedia('(display-mode: standalone)').matches;
        
        this.sendAnalytics('app_launch', {
            source: isFromHomeScreen ? 'home_screen' : 'browser',
            referrer: referrer,
            timestamp: Date.now()
        });
    }
    
    // Feature usage tracking
    trackFeatureUsage(feature, action, data = {}) {
        this.sendAnalytics('feature_usage', {
            feature,
            action,
            ...data,
            timestamp: Date.now(),
            is_installed: this.metrics.isInstalled
        });
    }
    
    // Error tracking
    trackError(error, context = '') {
        this.sendAnalytics('error', {
            message: error.message || error,
            stack: error.stack || '',
            context,
            timestamp: Date.now(),
            url: window.location.href,
            user_agent: navigator.userAgent
        });
    }
    
    // Rating helpers
    getLCPRating(value) {
        if (value <= 2500) return 'good';
        if (value <= 4000) return 'needs-improvement';
        return 'poor';
    }
    
    getFIDRating(value) {
        if (value <= 100) return 'good';
        if (value <= 300) return 'needs-improvement';
        return 'poor';
    }
    
    getCLSRating(value) {
        if (value <= 0.1) return 'good';
        if (value <= 0.25) return 'needs-improvement';
        return 'poor';
    }
    
    // Analytics data gönderme
    sendAnalytics(event, data) {
        // Google Analytics 4
        if (window.gtag) {
            gtag('event', event, {
                custom_parameter_1: JSON.stringify(data),
                ...data
            });
        }
        
        // Kendi analytics endpoint'imize gönder
        if (navigator.sendBeacon) {
            const analyticsData = {
                event,
                data,
                timestamp: Date.now(),
                url: window.location.href,
                user_agent: navigator.userAgent
            };
            
            try {
                navigator.sendBeacon('/analytics.php', JSON.stringify(analyticsData));
            } catch (error) {
                // Fallback: fetch ile gönder
                fetch('/analytics.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(analyticsData)
                }).catch(err => console.warn('Analytics failed:', err));
            }
        }
        
        // Debug mode
        if (localStorage.getItem('pwa_debug') === 'true') {
            console.log('PWA Analytics:', event, data);
        }
    }
    
    // Public API
    trackInstallPromptShown() {
        this.trackFeatureUsage('install_prompt', 'shown');
    }
    
    trackInstallPromptAccepted() {
        this.trackFeatureUsage('install_prompt', 'accepted');
    }
    
    trackInstallPromptDismissed() {
        this.trackFeatureUsage('install_prompt', 'dismissed');
    }
    
    trackOfflineUsage() {
        this.trackFeatureUsage('offline', 'used');
    }
    
    trackBackgroundSync(actionCount) {
        this.trackFeatureUsage('background_sync', 'completed', {
            action_count: actionCount
        });
    }
    
    trackShareTarget(fileCount) {
        this.trackFeatureUsage('share_target', 'used', {
            file_count: fileCount
        });
    }
}

// Global error handling
window.addEventListener('error', (event) => {
    if (window.pwaAnalytics) {
        pwaAnalytics.trackError(event.error, 'global_error');
    }
});

window.addEventListener('unhandledrejection', (event) => {
    if (window.pwaAnalytics) {
        pwaAnalytics.trackError(event.reason, 'unhandled_promise');
    }
});

// PWA Analytics'i başlat
let pwaAnalytics;
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        pwaAnalytics = new PWAAnalytics();
        window.pwaAnalytics = pwaAnalytics;
    });
} else {
    pwaAnalytics = new PWAAnalytics();
    window.pwaAnalytics = pwaAnalytics;
}