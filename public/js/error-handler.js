// Global Error Handler ve Recovery System
class ErrorHandler {
    constructor() {
        this.errorCount = 0;
        this.maxErrors = 10;
        this.isRecovering = false;
        
        this.init();
    }
    
    init() {
        this.setupGlobalErrorHandlers();
        this.setupPromiseRejectionHandler();
        this.setupConsoleErrorHandler();
    }
    
    setupGlobalErrorHandlers() {
        // Global JavaScript hataları
        window.addEventListener('error', (event) => {
            this.handleError({
                type: 'javascript_error',
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
                error: event.error,
                stack: event.error ? event.error.stack : null
            });
        });
        
        // Resource loading hataları
        window.addEventListener('error', (event) => {
            if (event.target !== window) {
                this.handleResourceError({
                    type: 'resource_error',
                    element: event.target.tagName,
                    source: event.target.src || event.target.href,
                    message: 'Failed to load resource'
                });
            }
        }, true);
    }
    
    setupPromiseRejectionHandler() {
        window.addEventListener('unhandledrejection', (event) => {
            this.handleError({
                type: 'unhandled_promise_rejection',
                message: event.reason ? event.reason.toString() : 'Unknown promise rejection',
                stack: event.reason ? event.reason.stack : null
            });
            
            // Prevent browser console error
            event.preventDefault();
        });
    }
    
    setupConsoleErrorHandler() {
        const originalConsoleError = console.error;
        console.error = (...args) => {
            // OneSignal hatalarını özel olarak handle et
            const message = args.join(' ');
            if (message.includes('OneSignal') || message.includes('onesignal')) {
                this.handleOneSignalError(message);
            }
            
            // Orijinal console.error'u çağır
            originalConsoleError.apply(console, args);
        };
    }
    
    handleError(errorInfo) {
        this.errorCount++;
        
        // Error rate limiting
        if (this.errorCount > this.maxErrors) {
            console.warn('Too many errors, stopping error reporting');
            return;
        }
        
        // Log error
        console.error('Handled Error:', errorInfo);
        
        // Send to analytics if available
        if (window.pwaAnalytics) {
            pwaAnalytics.trackError(errorInfo, 'global_handler');
        }
        
        // Specific error recovery
        this.attemptErrorRecovery(errorInfo);
    }
    
    handleResourceError(errorInfo) {
        console.warn('Resource Error:', errorInfo);
        
        // Attempt to reload critical resources
        if (errorInfo.element === 'SCRIPT') {
            this.reloadScript(errorInfo.source);
        } else if (errorInfo.element === 'LINK') {
            this.reloadStylesheet(errorInfo.source);
        }
    }
    
    handleOneSignalError(message) {
        console.warn('OneSignal Error Detected:', message);
        
        // OneSignal recovery attempts
        if (message.includes('not defined')) {
            this.recoverOneSignal();
        }
    }
    
    attemptErrorRecovery(errorInfo) {
        if (this.isRecovering) return;
        
        this.isRecovering = true;
        
        setTimeout(() => {
            // PWA feature recovery
            if (errorInfo.message && errorInfo.message.includes('pwa')) {
                this.recoverPWAFeatures();
            }
            
            // Service Worker recovery
            if (errorInfo.message && errorInfo.message.includes('service')) {
                this.recoverServiceWorker();
            }
            
            this.isRecovering = false;
        }, 1000);
    }
    
    recoverOneSignal() {
        console.log('Attempting OneSignal recovery...');
        
        // OneSignal SDK'yı yeniden yükle
        if (!window.OneSignal) {
            const script = document.createElement('script');
            script.src = 'https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js';
            script.onload = () => {
                console.log('OneSignal SDK reloaded successfully');
                // Token registration'ı yeniden başlat
                if (window.initializeOneSignal) {
                    setTimeout(window.initializeOneSignal, 1000);
                }
            };
            script.onerror = () => {
                console.error('Failed to reload OneSignal SDK');
            };
            document.head.appendChild(script);
        }
    }
    
    recoverPWAFeatures() {
        console.log('Attempting PWA features recovery...');
        
        // PWA managers'ı yeniden başlat
        try {
            if (!window.pwaManager && window.PWAManager) {
                window.pwaManager = new PWAManager();
            }
            
            if (!window.backgroundSyncManager && window.BackgroundSyncManager) {
                window.backgroundSyncManager = new BackgroundSyncManager();
            }
            
            if (!window.pwaAnalytics && window.PWAAnalytics) {
                window.pwaAnalytics = new PWAAnalytics();
            }
        } catch (error) {
            console.error('PWA recovery failed:', error);
        }
    }
    
    recoverServiceWorker() {
        console.log('Attempting Service Worker recovery...');
        
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(registrations => {
                if (registrations.length === 0) {
                    // Service Worker'ı yeniden kaydet
                    navigator.serviceWorker.register('/service-worker.js')
                        .then(registration => {
                            console.log('Service Worker re-registered:', registration);
                        })
                        .catch(error => {
                            console.error('Service Worker re-registration failed:', error);
                        });
                }
            });
        }
    }
    
    reloadScript(src) {
        console.log('Attempting to reload script:', src);
        
        // Eski script'i kaldır
        const oldScript = document.querySelector(`script[src="${src}"]`);
        if (oldScript) {
            oldScript.remove();
        }
        
        // Yeni script ekle
        const newScript = document.createElement('script');
        newScript.src = src;
        newScript.onload = () => console.log('Script reloaded successfully:', src);
        newScript.onerror = () => console.error('Script reload failed:', src);
        document.head.appendChild(newScript);
    }
    
    reloadStylesheet(href) {
        console.log('Attempting to reload stylesheet:', href);
        
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href;
        link.onload = () => console.log('Stylesheet reloaded successfully:', href);
        link.onerror = () => console.error('Stylesheet reload failed:', href);
        document.head.appendChild(link);
    }
    
    // Manual error reporting
    reportError(error, context = '') {
        this.handleError({
            type: 'manual_report',
            message: error.message || error,
            stack: error.stack || '',
            context: context
        });
    }
    
    // Health check
    healthCheck() {
        const healthStatus = {
            oneSignal: !!window.OneSignal,
            pwaManager: !!window.pwaManager,
            backgroundSyncManager: !!window.backgroundSyncManager,
            pwaAnalytics: !!window.pwaAnalytics,
            serviceWorker: 'serviceWorker' in navigator,
            errorCount: this.errorCount,
            timestamp: Date.now()
        };
        
        console.log('PWA Health Status:', healthStatus);
        return healthStatus;
    }
}

// Error Handler'ı başlat
const errorHandler = new ErrorHandler();
window.errorHandler = errorHandler;

// Health check interval
setInterval(() => {
    if (localStorage.getItem('pwa_debug') === 'true') {
        errorHandler.healthCheck();
    }
}, 30000); // 30 saniyede bir