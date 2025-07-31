// PWA Features Demo & Testing
document.addEventListener('DOMContentLoaded', function() {
    // Debug mode toggle
    if (window.location.hash === '#pwa-debug') {
        localStorage.setItem('pwa_debug', 'true');
        console.log('PWA Debug mode enabled');
    }
    
    // Demo butonları oluştur (sadece debug modunda)
    if (localStorage.getItem('pwa_debug') === 'true') {
        createDebugPanel();
    }
    
    // Feature usage tracking
    trackFeatureUsage();
});

function createDebugPanel() {
    const debugPanel = document.createElement('div');
    debugPanel.id = 'pwa-debug-panel';
    debugPanel.style.cssText = `
        position: fixed;
        top: 10px;
        left: 10px;
        background: rgba(0,0,0,0.9);
        color: white;
        padding: 10px;
        border-radius: 8px;
        z-index: 10000;
        font-size: 12px;
        max-width: 300px;
    `;
    
    debugPanel.innerHTML = `
        <h4>🛠 PWA Debug Panel</h4>
        <button onclick="testInstallPrompt()">Test Install Prompt</button><br>
        <button onclick="testOfflineMode()">Test Offline Mode</button><br>
        <button onclick="testBackgroundSync()">Test Background Sync</button><br>
        <button onclick="testShareTarget()">Test Share Target</button><br>
        <button onclick="testPushNotification()">Test Push Notification</button><br>
        <button onclick="clearCaches()">Clear All Caches</button><br>
        <button onclick="exportAnalytics()">Export Analytics</button><br>
        <button onclick="testPullToRefresh()">Test Pull-to-Refresh</button><br>
        <button onclick="testPageLoader()">Test Page Loader</button><br>
        <button onclick="testPageTransitions()">Test Page Transitions</button><br>
        <button onclick="testSplashScreen()">Test Splash Screen</button><br>
        <button onclick="testNetworkMonitor()">Test Network Monitor</button><br>
        <button onclick="closeDebugPanel()">Close</button>
        <div id="debug-status"></div>
    `;
    
    document.body.appendChild(debugPanel);
}

function testInstallPrompt() {
    if (window.pwaManager) {
        window.pwaManager.showInstallPrompt();
        updateDebugStatus('Install prompt triggered');
    }
}

function testOfflineMode() {
    // Simulate offline
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.ready.then(registration => {
            if (registration.active) {
                registration.active.postMessage({
                    type: 'SIMULATE_OFFLINE'
                });
                updateDebugStatus('Offline mode simulated');
            }
        });
    }
}

function testBackgroundSync() {
    if (window.backgroundSyncManager) {
        const testAction = {
            type: 'test_action',
            url: '/test-endpoint',
            method: 'POST',
            data: { test: 'background sync', timestamp: Date.now() }
        };
        
        backgroundSyncManager.saveOfflineAction(testAction);
        updateDebugStatus('Background sync action queued');
    }
}

function testShareTarget() {
    if (navigator.share) {
        navigator.share({
            title: 'Test Share',
            text: 'Testing PWA share functionality',
            url: window.location.href
        }).then(() => {
            updateDebugStatus('Share API used');
        }).catch(err => {
            updateDebugStatus('Share failed: ' + err.message);
        });
    } else {
        updateDebugStatus('Share API not supported');
    }
}

function testPushNotification() {
    if ('Notification' in window) {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                new Notification('PWA Test Notification', {
                    body: 'This is a test notification from your PWA',
                    icon: '/public/images/icons/icon-192x192.png'
                });
                updateDebugStatus('Test notification sent');
            } else {
                updateDebugStatus('Notification permission denied');
            }
        });
    } else {
        updateDebugStatus('Notifications not supported');
    }
}

function clearCaches() {
    if ('caches' in window) {
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => caches.delete(cacheName))
            );
        }).then(() => {
            updateDebugStatus('All caches cleared');
            window.location.reload();
        });
    }
}

function exportAnalytics() {
    // Export analytics data from localStorage
    const analyticsData = {
        debug_mode: true,
        timestamp: new Date().toISOString(),
        user_agent: navigator.userAgent,
        pwa_features: {
            service_worker: 'serviceWorker' in navigator,
            push_notifications: 'Notification' in window,
            background_sync: 'serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype,
            share_api: 'share' in navigator,
            install_prompt: window.pwaManager && window.pwaManager.deferredPrompt !== null
        }
    };
    
    const blob = new Blob([JSON.stringify(analyticsData, null, 2)], {
        type: 'application/json'
    });
    
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'pwa-analytics-' + Date.now() + '.json';
    a.click();
    
    updateDebugStatus('Analytics exported');
}

function closeDebugPanel() {
    const panel = document.getElementById('pwa-debug-panel');
    if (panel) {
        panel.remove();
    }
}

function testPullToRefresh() {
    if (window.pullToRefresh) {
        pullToRefresh.triggerRefresh();
        updateDebugStatus('Pull-to-refresh triggered manually');
    } else {
        updateDebugStatus('Pull-to-refresh not available');
    }
}

function testPageLoader() {
    if (window.businessLoader || window.pageLoader) {
        const loader = window.businessLoader || window.pageLoader;
        loader.showWithMessage('Test loader - Manuel olarak tetiklendi');
        setTimeout(() => {
            if (loader.setProgress) loader.setProgress(50);
        }, 1000);
        setTimeout(() => {
            loader.complete();
        }, 3000);
        updateDebugStatus('Business loader test completed');
    } else {
        updateDebugStatus('Business loader not available');
    }
}

function testPageTransitions() {
    if (window.pageTransitions) {
        updateDebugStatus('Page transitions are enabled. Click any internal link to see transition effects.');
        
        // Show transition history
        const history = pageTransitions.getTransitionHistory();
        console.log('Transition History:', history);
        
        // Test different transition types
        const testLink = document.createElement('a');
        testLink.href = '#test-transition';
        testLink.style.display = 'none';
        testLink.textContent = 'Test Transition';
        document.body.appendChild(testLink);
        
        setTimeout(() => {
            testLink.remove();
        }, 100);
        
    } else {
        updateDebugStatus('Page transitions not available');
    }
}

function testSplashScreen() {
    if (window.splashScreen) {
        splashScreen.forceShow();
        updateDebugStatus('Splash screen test - Manuel olarak gösterildi');
        
        setTimeout(() => {
            updateDebugStatus('Splash screen test tamamlandı');
        }, 3000);
    } else {
        updateDebugStatus('Splash screen not available');
    }
}

function testNetworkMonitor() {
    if (window.networkMonitor) {
        updateDebugStatus('Network monitor test başlatıldı');
        
        // Force network check
        networkMonitor.forceCheck().then(isOnline => {
            updateDebugStatus(`Network status: ${isOnline ? 'Online' : 'Offline'}`);
        });
        
        // Force speed test
        networkMonitor.forceSpeedTest();
        
        // Show network info
        const info = networkMonitor.getNetworkInfo();
        console.log('Network Info:', info);
        
        setTimeout(() => {
            updateDebugStatus('Network monitor test tamamlandı');
        }, 2000);
    } else {
        updateDebugStatus('Network monitor not available');
    }
}

function updateDebugStatus(message) {
    const status = document.getElementById('debug-status');
    if (status) {
        status.innerHTML = `<small>${new Date().toLocaleTimeString()}: ${message}</small>`;
    }
    console.log('PWA Debug:', message);
}

function trackFeatureUsage() {
    // DOM hazır olduğundan emin ol
    if (!document.body) {
        setTimeout(trackFeatureUsage, 100);
        return;
    }
    
    // Ana sayfa özelliklerini track et
    document.querySelectorAll('[href^="/isemri/"]').forEach(link => {
        link.addEventListener('click', () => {
            if (window.pwaAnalytics) {
                pwaAnalytics.trackFeatureUsage('navigation', 'isemri_clicked');
            }
        });
    });
    
    document.querySelectorAll('[href^="/ciro/"]').forEach(link => {
        link.addEventListener('click', () => {
            if (window.pwaAnalytics) {
                pwaAnalytics.trackFeatureUsage('navigation', 'ciro_clicked');
            }
        });
    });
    
    document.querySelectorAll('[href^="/profil"]').forEach(link => {
        link.addEventListener('click', () => {
            if (window.pwaAnalytics) {
                pwaAnalytics.trackFeatureUsage('navigation', 'profil_clicked');
            }
        });
    });
    
    // Form submission tracking
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', () => {
            if (window.pwaAnalytics) {
                pwaAnalytics.trackFeatureUsage('form', 'submitted', {
                    form_action: form.action,
                    form_method: form.method
                });
            }
        });
    });
}

// Performance monitoring
if ('PerformanceObserver' in window) {
    // Track navigation timing
    window.addEventListener('load', () => {
        setTimeout(() => {
            const navigation = performance.getEntriesByType('navigation')[0];
            if (navigation && window.pwaAnalytics) {
                pwaAnalytics.sendAnalytics('page_load_timing', {
                    dom_content_loaded: navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart,
                    load_complete: navigation.loadEventEnd - navigation.loadEventStart,
                    total_load_time: navigation.loadEventEnd - navigation.fetchStart
                });
            }
        }, 1000);
    });
}