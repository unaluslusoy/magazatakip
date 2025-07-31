// PWA Health Check Tool
(function() {
    'use strict';
    
    function performHealthCheck() {
        const results = {
            timestamp: new Date().toISOString(),
            overall: 'UNKNOWN',
            checks: {},
            errors: [],
            warnings: [],
            recommendations: []
        };
        
        // 1. Service Worker Check
        results.checks.serviceWorker = {
            supported: 'serviceWorker' in navigator,
            registered: false,
            active: false,
            status: 'FAIL'
        };
        
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(registrations => {
                results.checks.serviceWorker.registered = registrations.length > 0;
                results.checks.serviceWorker.active = registrations.some(reg => reg.active);
                results.checks.serviceWorker.status = results.checks.serviceWorker.active ? 'PASS' : 'WARN';
            });
        }
        
        // 2. Manifest Check
        results.checks.manifest = {
            linked: !!document.querySelector('link[rel="manifest"]'),
            status: 'FAIL'
        };
        
        const manifestLink = document.querySelector('link[rel="manifest"]');
        if (manifestLink) {
            fetch(manifestLink.href)
                .then(response => response.json())
                .then(manifest => {
                    results.checks.manifest.valid = true;
                    results.checks.manifest.name = manifest.name;
                    results.checks.manifest.icons = manifest.icons ? manifest.icons.length : 0;
                    results.checks.manifest.status = 'PASS';
                })
                .catch(() => {
                    results.checks.manifest.valid = false;
                    results.checks.manifest.status = 'FAIL';
                    results.errors.push('Manifest dosyası yüklenemedi');
                });
        } else {
            results.errors.push('Manifest linki bulunamadı');
        }
        
        // 3. Icons Check
        results.checks.icons = {
            favicon: !!document.querySelector('link[rel="icon"]'),
            appleTouchIcon: !!document.querySelector('link[rel="apple-touch-icon"]'),
            status: 'WARN'
        };
        
        if (results.checks.icons.favicon && results.checks.icons.appleTouchIcon) {
            results.checks.icons.status = 'PASS';
        } else if (!results.checks.icons.favicon) {
            results.warnings.push('Favicon eksik');
        }
        
        // 4. HTTPS Check
        results.checks.https = {
            secure: location.protocol === 'https:',
            status: location.protocol === 'https:' ? 'PASS' : 'FAIL'
        };
        
        if (!results.checks.https.secure) {
            results.errors.push('HTTPS gerekli - PWA HTTP üzerinde çalışmaz');
        }
        
        // 5. JavaScript APIs Check
        results.checks.apis = {
            fetch: !!window.fetch,
            promise: !!window.Promise,
            localStorage: !!window.localStorage,
            indexedDB: !!window.indexedDB,
            notifications: 'Notification' in window,
            share: !!navigator.share,
            geolocation: !!navigator.geolocation,
            status: 'PASS'
        };
        
        const missingApis = Object.keys(results.checks.apis)
            .filter(api => api !== 'status' && !results.checks.apis[api]);
        
        if (missingApis.length > 0) {
            results.warnings.push(`Eksik API'ler: ${missingApis.join(', ')}`);
            if (missingApis.includes('fetch') || missingApis.includes('promise')) {
                results.checks.apis.status = 'FAIL';
            } else {
                results.checks.apis.status = 'WARN';
            }
        }
        
        // 6. PWA Managers Check
        results.checks.pwaManagers = {
            pwaAnalytics: !!window.pwaAnalytics,
            pwaManager: !!window.pwaManager,
            backgroundSyncManager: !!window.backgroundSyncManager,
            errorHandler: !!window.errorHandler,
            status: 'WARN'
        };
        
        const loadedManagers = Object.keys(results.checks.pwaManagers)
            .filter(manager => manager !== 'status' && results.checks.pwaManagers[manager]).length;
        
        if (loadedManagers >= 3) {
            results.checks.pwaManagers.status = 'PASS';
        } else if (loadedManagers >= 1) {
            results.checks.pwaManagers.status = 'WARN';
            results.warnings.push('Bazı PWA managers yüklenmedi');
        } else {
            results.checks.pwaManagers.status = 'FAIL';
            results.errors.push('PWA managers yüklenemedi');
        }
        
        // 7. OneSignal Check
        results.checks.oneSignal = {
            loaded: !!window.OneSignal,
            initialized: window.OneSignal && typeof window.OneSignal.init === 'function',
            status: 'WARN'
        };
        
        if (results.checks.oneSignal.initialized) {
            results.checks.oneSignal.status = 'PASS';
        } else if (!results.checks.oneSignal.loaded) {
            results.warnings.push('OneSignal SDK yüklenmedi');
        }
        
        // 8. Offline Support Check
        results.checks.offline = {
            serviceWorkerCaching: false,
            offlinePage: false,
            status: 'FAIL'
        };
        
        // 9. Pull-to-Refresh Check
        results.checks.pullToRefresh = {
            loaded: !!window.pullToRefresh,
            enabled: window.pullToRefresh ? window.pullToRefresh.options.enabled : false,
            status: 'WARN'
        };
        
        if (results.checks.pullToRefresh.loaded && results.checks.pullToRefresh.enabled) {
            results.checks.pullToRefresh.status = 'PASS';
        } else if (!results.checks.pullToRefresh.loaded) {
            results.warnings.push('Pull-to-refresh özelliği yüklenmedi');
        }
        
        // 10. Page Loader Check
        results.checks.pageLoader = {
            loaded: !!window.pageLoader,
            initialized: window.pageLoader ? window.pageLoader.state.isInitialized : false,
            status: 'WARN'
        };
        
        if (results.checks.pageLoader.loaded && results.checks.pageLoader.initialized) {
            results.checks.pageLoader.status = 'PASS';
        } else if (!results.checks.pageLoader.loaded) {
            results.warnings.push('Page loader sistemi yüklenmedi');
        }
        
        // 11. Page Transitions Check
        results.checks.pageTransitions = {
            loaded: !!window.pageTransitions,
            enabled: window.pageTransitions ? !window.pageTransitions.state.isTransitioning : false,
            status: 'WARN'
        };
        
        if (results.checks.pageTransitions.loaded) {
            results.checks.pageTransitions.status = 'PASS';
        } else {
            results.warnings.push('Page transitions sistemi yüklenmedi');
        }
        
        // 12. Splash Screen Check
        results.checks.splashScreen = {
            loaded: !!window.splashScreen,
            canShow: window.splashScreen ? typeof window.splashScreen.forceShow === 'function' : false,
            status: 'WARN'
        };
        
        if (results.checks.splashScreen.loaded && results.checks.splashScreen.canShow) {
            results.checks.splashScreen.status = 'PASS';
        } else if (!results.checks.splashScreen.loaded) {
            results.warnings.push('Splash screen sistemi yüklenmedi');
        }
        
        // 13. Network Monitor Check
        results.checks.networkMonitor = {
            loaded: !!window.networkMonitor,
            monitoring: window.networkMonitor ? window.networkMonitor.state.isMonitoring : false,
            online: navigator.onLine,
            status: 'WARN'
        };
        
        if (results.checks.networkMonitor.loaded && results.checks.networkMonitor.monitoring) {
            results.checks.networkMonitor.status = 'PASS';
        } else if (!results.checks.networkMonitor.loaded) {
            results.warnings.push('Network monitor sistemi yüklenmedi');
        }
        
        fetch('/offline.html', { method: 'HEAD' })
            .then(response => {
                results.checks.offline.offlinePage = response.ok;
                if (results.checks.offline.offlinePage) {
                    results.checks.offline.status = 'PASS';
                }
            })
            .catch(() => {
                results.warnings.push('Offline sayfası bulunamadı');
            });
        
        // Overall Status Calculation
        const failCount = Object.values(results.checks)
            .filter(check => check.status === 'FAIL').length;
        const warnCount = Object.values(results.checks)
            .filter(check => check.status === 'WARN').length;
        
        if (failCount === 0 && warnCount === 0) {
            results.overall = 'EXCELLENT';
        } else if (failCount === 0 && warnCount <= 2) {
            results.overall = 'GOOD';
        } else if (failCount <= 1) {
            results.overall = 'FAIR';
        } else {
            results.overall = 'POOR';
        }
        
        // Recommendations
        if (!results.checks.https.secure) {
            results.recommendations.push('HTTPS protokolünü etkinleştirin');
        }
        
        if (!results.checks.serviceWorker.active) {
            results.recommendations.push('Service Worker\'ı etkinleştirin');
        }
        
        if (!results.checks.manifest.linked) {
            results.recommendations.push('Web App Manifest ekleyin');
        }
        
        if (!results.checks.icons.appleTouchIcon) {
            results.recommendations.push('Apple Touch Icon ekleyin');
        }
        
        if (!results.checks.oneSignal.initialized) {
            results.recommendations.push('OneSignal yapılandırmasını kontrol edin');
        }
        
        return results;
    }
    
    function displayHealthReport(results) {
        console.group('🔍 PWA Health Check Report');
        console.log(`Overall Status: ${results.overall}`);
        console.log(`Timestamp: ${results.timestamp}`);
        
        console.group('✅ Checks');
        Object.keys(results.checks).forEach(checkName => {
            const check = results.checks[checkName];
            const icon = check.status === 'PASS' ? '✅' : 
                        check.status === 'WARN' ? '⚠️' : '❌';
            console.log(`${icon} ${checkName}: ${check.status}`);
        });
        console.groupEnd();
        
        if (results.errors.length > 0) {
            console.group('❌ Errors');
            results.errors.forEach(error => console.error(error));
            console.groupEnd();
        }
        
        if (results.warnings.length > 0) {
            console.group('⚠️ Warnings');
            results.warnings.forEach(warning => console.warn(warning));
            console.groupEnd();
        }
        
        if (results.recommendations.length > 0) {
            console.group('💡 Recommendations');
            results.recommendations.forEach(rec => console.info(rec));
            console.groupEnd();
        }
        
        console.groupEnd();
        
        return results;
    }
    
    // Global health check function
    window.pwaHealthCheck = function() {
        const results = performHealthCheck();
        return displayHealthReport(results);
    };
    
    // Auto health check in debug mode
    if (localStorage.getItem('pwa_debug') === 'true') {
        setTimeout(() => {
            console.log('🏥 Running automatic PWA health check...');
            window.pwaHealthCheck();
        }, 3000);
    }
    
})();