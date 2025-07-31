// Advanced Network Monitor for PWA
class NetworkMonitor {
    constructor(options = {}) {
        this.options = {
            // Monitoring settings
            checkInterval: 5000,        // 5 saniye
            speedTestInterval: 30000,   // 30 saniye
            timeoutDuration: 8000,      // 8 saniye timeout
            
            // Connection thresholds
            slowConnectionThreshold: 1000,  // 1 saniye = yavaş
            fastConnectionThreshold: 500,   // 0.5 saniye = hızlı
            
            // Notification settings
            showNotifications: true,
            autoRetry: true,
            maxRetries: 3,
            
            // Test endpoints
            testEndpoints: [
                '/api/ping',
                '/public/media/logos/default.svg',
                'https://www.google.com/favicon.ico'
            ],
            
            ...options
        };
        
        this.state = {
            isOnline: navigator.onLine,
            connectionType: 'unknown',
            connectionSpeed: 'unknown',
            lastSpeedTest: null,
            downlink: null,
            rtt: null,
            effectiveType: 'unknown',
            isMonitoring: false,
            retryCount: 0,
            offlineStartTime: null
        };
        
        this.timers = {
            monitoring: null,
            speedTest: null,
            retry: null
        };
        
        this.elements = {};
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.detectConnectionInfo();
        this.createOfflineWarning();
        this.startMonitoring();
        
        // PWA analytics integration
        if (window.pwaAnalytics) {
            this.analytics = window.pwaAnalytics;
        }
        
        console.log('🌐 Network Monitor initialized');
    }
    
    setupEventListeners() {
        // Basic online/offline events
        window.addEventListener('online', () => {
            this.handleOnline();
        });
        
        window.addEventListener('offline', () => {
            this.handleOffline();
        });
        
        // Page visibility change
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && this.state.isMonitoring) {
                this.checkConnection();
            }
        });
        
        // Network Information API if available
        if ('connection' in navigator) {
            const connection = navigator.connection;
            connection.addEventListener('change', () => {
                this.updateConnectionInfo();
            });
        }
    }
    
    detectConnectionInfo() {
        if ('connection' in navigator) {
            const connection = navigator.connection;
            this.state.connectionType = connection.type || 'unknown';
            this.state.downlink = connection.downlink;
            this.state.rtt = connection.rtt;
            this.state.effectiveType = connection.effectiveType;
        }
    }
    
    updateConnectionInfo() {
        this.detectConnectionInfo();
        this.updateNetworkDisplay();
        
        // Log connection change
        console.log('📡 Connection changed:', {
            type: this.state.connectionType,
            effectiveType: this.state.effectiveType,
            downlink: this.state.downlink,
            rtt: this.state.rtt
        });
        
        // Analytics
        if (this.analytics) {
            this.analytics.trackFeatureUsage('network-monitor', 'connection_change', {
                type: this.state.connectionType,
                effectiveType: this.state.effectiveType
            });
        }
    }
    
    createOfflineWarning() {
        // Remove existing warning
        const existingWarning = document.getElementById('network-warning');
        if (existingWarning) {
            existingWarning.remove();
        }
        
        const warning = document.createElement('div');
        warning.id = 'network-warning';
        warning.className = 'network-warning-overlay';
        
        warning.innerHTML = `
            <div class="network-warning-backdrop"></div>
            <div class="network-warning-content">
                <div class="warning-header">
                    <div class="warning-icon">
                        <i class="ki-outline ki-wifi-off"></i>
                    </div>
                    <h2>İnternet Bağlantısı Yok</h2>
                    <p class="warning-subtitle">Lütfen bağlantınızı kontrol edin</p>
                </div>
                
                <div class="warning-body">
                    <div class="connection-status">
                        <div class="status-item">
                            <i class="ki-outline ki-wifi"></i>
                            <span class="status-label">WiFi</span>
                            <span class="status-value" id="wifi-status">Kontrol ediliyor...</span>
                        </div>
                        <div class="status-item">
                            <i class="ki-outline ki-phone"></i>
                            <span class="status-label">Mobil Veri</span>
                            <span class="status-value" id="mobile-status">Kontrol ediliyor...</span>
                        </div>
                        <div class="status-item">
                            <i class="ki-outline ki-timer"></i>
                            <span class="status-label">Son Bağlantı</span>
                            <span class="status-value" id="last-connection">Bilinmiyor</span>
                        </div>
                    </div>
                    
                    <div class="retry-section">
                        <button class="retry-btn" onclick="networkMonitor.retryConnection()">
                            <i class="ki-outline ki-arrows-circle"></i>
                            <span>Tekrar Dene</span>
                        </button>
                        <div class="retry-info">
                            <span id="retry-count">Deneme: 0/${this.options.maxRetries}</span>
                        </div>
                    </div>
                    
                    <div class="offline-tips">
                        <h4>Yapabilecekleriniz:</h4>
                        <ul>
                            <li><i class="ki-outline ki-wifi"></i> WiFi bağlantınızı kontrol edin</li>
                            <li><i class="ki-outline ki-phone"></i> Mobil veri açık olduğundan emin olun</li>
                            <li><i class="ki-outline ki-router"></i> Modem/router'ınızı yeniden başlatın</li>
                            <li><i class="ki-outline ki-setting-2"></i> Ağ ayarlarınızı kontrol edin</li>
                        </ul>
                    </div>
                </div>
                
                <div class="warning-footer">
                    <div class="offline-indicator">
                        <div class="offline-icon">
                            <div class="pulse-ring"></div>
                            <i class="ki-outline ki-cross-circle"></i>
                        </div>
                        <span>Çevrimdışı Mod</span>
                    </div>
                </div>
            </div>
        `;
        
        // Inject styles
        this.injectNetworkStyles();
        
        // Store elements
        this.elements = {
            warning,
            wifiStatus: warning.querySelector('#wifi-status'),
            mobileStatus: warning.querySelector('#mobile-status'),
            lastConnection: warning.querySelector('#last-connection'),
            retryCount: warning.querySelector('#retry-count')
        };
        
        // Add to document
        document.body.appendChild(warning);
    }
    
    injectNetworkStyles() {
        if (document.getElementById('network-monitor-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'network-monitor-styles';
        style.textContent = `
            .network-warning-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 10001;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease, visibility 0.3s ease;
            }
            
            .network-warning-overlay.active {
                opacity: 1;
                visibility: visible;
            }
            
            .network-warning-backdrop {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
                background-size: 400% 400%;
                animation: networkGradient 6s ease-in-out infinite;
            }
            
            @keyframes networkGradient {
                0%, 100% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
            }
            
            .network-warning-content {
                position: relative;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                height: 100%;
                padding: 2rem;
                text-align: center;
                color: white;
                animation: warningSlideIn 0.8s ease-out;
            }
            
            @keyframes warningSlideIn {
                0% {
                    opacity: 0;
                    transform: translateY(30px);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .warning-header {
                margin-bottom: 3rem;
            }
            
            .warning-icon {
                margin-bottom: 1.5rem;
                animation: warningIconPulse 2s ease-in-out infinite;
            }
            
            .warning-icon i {
                font-size: 4rem;
                color: #ff6b6b;
                filter: drop-shadow(0 4px 8px rgba(255, 107, 107, 0.3));
            }
            
            @keyframes warningIconPulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.1); }
            }
            
            .warning-header h2 {
                font-size: 2.5rem;
                font-weight: 700;
                margin: 0 0 0.5rem 0;
                color: white;
                text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            }
            
            .warning-subtitle {
                font-size: 1.1rem;
                color: rgba(255,255,255,0.8);
                margin: 0;
            }
            
            .warning-body {
                max-width: 500px;
                width: 100%;
                margin-bottom: 2rem;
            }
            
            .connection-status {
                background: rgba(255,255,255,0.1);
                backdrop-filter: blur(10px);
                border-radius: 15px;
                padding: 1.5rem;
                margin-bottom: 2rem;
                border: 1px solid rgba(255,255,255,0.2);
            }
            
            .status-item {
                display: flex;
                align-items: center;
                padding: 0.75rem 0;
                border-bottom: 1px solid rgba(255,255,255,0.1);
            }
            
            .status-item:last-child {
                border-bottom: none;
            }
            
            .status-item i {
                font-size: 1.25rem;
                margin-right: 1rem;
                color: rgba(255,255,255,0.8);
                width: 20px;
            }
            
            .status-label {
                flex: 1;
                text-align: left;
                font-weight: 500;
            }
            
            .status-value {
                font-size: 0.875rem;
                color: rgba(255,255,255,0.7);
            }
            
            .retry-section {
                margin-bottom: 2rem;
            }
            
            .retry-btn {
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                color: white;
                border: none;
                border-radius: 25px;
                padding: 1rem 2rem;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                margin: 0 auto 1rem;
                box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
            }
            
            .retry-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(40, 167, 69, 0.4);
            }
            
            .retry-btn:disabled {
                background: #6c757d;
                cursor: not-allowed;
                transform: none;
                box-shadow: none;
            }
            
            .retry-btn i {
                animation: retryRotate 2s linear infinite;
            }
            
            .retry-btn:not(:disabled):hover i {
                animation-duration: 0.5s;
            }
            
            @keyframes retryRotate {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            .retry-info {
                font-size: 0.875rem;
                color: rgba(255,255,255,0.7);
            }
            
            .offline-tips {
                background: rgba(255,255,255,0.05);
                border-radius: 15px;
                padding: 1.5rem;
                text-align: left;
                margin-bottom: 2rem;
            }
            
            .offline-tips h4 {
                color: white;
                font-size: 1.1rem;
                margin: 0 0 1rem 0;
                text-align: center;
            }
            
            .offline-tips ul {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            
            .offline-tips li {
                display: flex;
                align-items: center;
                padding: 0.5rem 0;
                font-size: 0.9rem;
                color: rgba(255,255,255,0.8);
            }
            
            .offline-tips li i {
                margin-right: 0.75rem;
                color: #20c997;
                width: 16px;
            }
            
            .warning-footer {
                position: absolute;
                bottom: 2rem;
                left: 50%;
                transform: translateX(-50%);
            }
            
            .offline-indicator {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                font-size: 0.9rem;
                color: rgba(255,255,255,0.7);
            }
            
            .offline-icon {
                position: relative;
            }
            
            .pulse-ring {
                position: absolute;
                top: 50%;
                left: 50%;
                width: 30px;
                height: 30px;
                border: 2px solid #ff6b6b;
                border-radius: 50%;
                transform: translate(-50%, -50%);
                animation: pulseRing 2s ease-out infinite;
            }
            
            @keyframes pulseRing {
                0% {
                    transform: translate(-50%, -50%) scale(0.8);
                    opacity: 1;
                }
                100% {
                    transform: translate(-50%, -50%) scale(1.4);
                    opacity: 0;
                }
            }
            
            .offline-icon i {
                font-size: 1.25rem;
                color: #ff6b6b;
                position: relative;
                z-index: 1;
            }
            
            /* Network status indicator (top bar) */
            .network-status-bar {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                background: #dc3545;
                color: white;
                padding: 0.5rem;
                text-align: center;
                font-size: 0.875rem;
                font-weight: 500;
                z-index: 1000;
                transform: translateY(-100%);
                transition: transform 0.3s ease;
            }
            
            .network-status-bar.active {
                transform: translateY(0);
            }
            
            .network-status-bar.online {
                background: #28a745;
            }
            
            .network-status-bar.slow {
                background: #ffc107;
                color: #212529;
            }
            
            /* Mobile optimizations */
            @media (max-width: 768px) {
                .network-warning-content {
                    padding: 1.5rem;
                }
                
                .warning-header h2 {
                    font-size: 2rem;
                }
                
                .warning-icon i {
                    font-size: 3rem;
                }
                
                .connection-status {
                    padding: 1rem;
                }
                
                .offline-tips {
                    padding: 1rem;
                }
                
                .retry-btn {
                    padding: 0.875rem 1.5rem;
                    font-size: 0.9rem;
                }
            }
            
            /* Reduced motion */
            @media (prefers-reduced-motion: reduce) {
                .network-warning-overlay *,
                .network-warning-overlay *::before,
                .network-warning-overlay *::after {
                    animation-duration: 0.1s !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.1s !important;
                }
            }
        `;
        
        document.head.appendChild(style);
    }
    
    startMonitoring() {
        if (this.state.isMonitoring) return;
        
        this.state.isMonitoring = true;
        
        // Initial check
        this.checkConnection();
        
        // Periodic monitoring
        this.timers.monitoring = setInterval(() => {
            this.checkConnection();
        }, this.options.checkInterval);
        
        // Speed test
        this.timers.speedTest = setInterval(() => {
            if (this.state.isOnline) {
                this.performSpeedTest();
            }
        }, this.options.speedTestInterval);
        
        console.log('🔄 Network monitoring started');
    }
    
    stopMonitoring() {
        this.state.isMonitoring = false;
        
        Object.values(this.timers).forEach(timer => {
            if (timer) clearInterval(timer);
        });
        
        console.log('⏹️ Network monitoring stopped');
    }
    
    async checkConnection() {
        try {
            const isOnline = await this.testConnection();
            
            if (isOnline !== this.state.isOnline) {
                this.state.isOnline = isOnline;
                
                if (isOnline) {
                    this.handleOnline();
                } else {
                    this.handleOffline();
                }
            }
            
            return isOnline;
            
        } catch (error) {
            console.warn('Connection check failed:', error);
            return false;
        }
    }
    
    async testConnection() {
        const promises = this.options.testEndpoints.map(endpoint => 
            this.pingEndpoint(endpoint)
        );
        
        try {
            const results = await Promise.allSettled(promises);
            const successCount = results.filter(result => result.status === 'fulfilled').length;
            
            return successCount > 0;
        } catch (error) {
            return false;
        }
    }
    
    async pingEndpoint(url) {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), this.options.timeoutDuration);
        
        try {
            const startTime = performance.now();
            
            const response = await fetch(url, {
                method: 'HEAD',
                mode: 'no-cors',
                cache: 'no-cache',
                signal: controller.signal
            });
            
            const endTime = performance.now();
            const responseTime = endTime - startTime;
            
            clearTimeout(timeoutId);
            
            return {
                url,
                responseTime,
                success: true
            };
            
        } catch (error) {
            clearTimeout(timeoutId);
            throw error;
        }
    }
    
    async performSpeedTest() {
        try {
            const testUrl = this.options.testEndpoints[0];
            const result = await this.pingEndpoint(testUrl);
            
            this.state.lastSpeedTest = {
                timestamp: Date.now(),
                responseTime: result.responseTime,
                speed: this.categorizeSpeed(result.responseTime)
            };
            
            this.updateSpeedDisplay();
            
            // Analytics
            if (this.analytics) {
                this.analytics.trackPerformance('network-speed', result.responseTime);
            }
            
        } catch (error) {
            console.warn('Speed test failed:', error);
        }
    }
    
    categorizeSpeed(responseTime) {
        if (responseTime < this.options.fastConnectionThreshold) {
            return 'fast';
        } else if (responseTime < this.options.slowConnectionThreshold) {
            return 'medium';
        } else {
            return 'slow';
        }
    }
    
    handleOnline() {
        console.log('🌐 Connection restored');
        
        this.state.retryCount = 0;
        
        if (this.state.offlineStartTime) {
            const offlineDuration = Date.now() - this.state.offlineStartTime;
            console.log(`📶 Was offline for ${Math.round(offlineDuration / 1000)} seconds`);
            this.state.offlineStartTime = null;
        }
        
        // Hide warning
        this.hideOfflineWarning();
        
        // Show brief online notification
        this.showNetworkStatusBar('Bağlantı geri geldi', 'online');
        
        // Update displays
        this.updateConnectionInfo();
        this.updateNetworkDisplay();
        
        // Analytics
        if (this.analytics) {
            this.analytics.trackFeatureUsage('network-monitor', 'online');
        }
    }
    
    handleOffline() {
        console.log('📵 Connection lost');
        
        this.state.offlineStartTime = Date.now();
        
        // Show warning
        this.showOfflineWarning();
        
        // Update status
        this.updateOfflineStatus();
        
        // Auto retry if enabled
        if (this.options.autoRetry) {
            this.scheduleRetry();
        }
        
        // Analytics
        if (this.analytics) {
            this.analytics.trackFeatureUsage('network-monitor', 'offline');
        }
    }
    
    showOfflineWarning() {
        const warning = this.elements.warning;
        if (warning) {
            warning.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }
    
    hideOfflineWarning() {
        const warning = this.elements.warning;
        if (warning) {
            warning.classList.remove('active');
            document.body.style.overflow = '';
        }
    }
    
    updateOfflineStatus() {
        if (!this.elements.wifiStatus) return;
        
        // Update connection status display
        if (this.state.connectionType === 'wifi') {
            this.elements.wifiStatus.textContent = 'Bağlantı kesildi';
            this.elements.mobileStatus.textContent = 'Kullanılamıyor';
        } else if (this.state.connectionType === 'cellular') {
            this.elements.wifiStatus.textContent = 'Bağlı değil';
            this.elements.mobileStatus.textContent = 'Bağlantı kesildi';
        } else {
            this.elements.wifiStatus.textContent = 'Kontrol ediliyor...';
            this.elements.mobileStatus.textContent = 'Kontrol ediliyor...';
        }
        
        // Last connection time
        if (this.elements.lastConnection) {
            this.elements.lastConnection.textContent = new Date().toLocaleTimeString();
        }
    }
    
    scheduleRetry() {
        if (this.state.retryCount >= this.options.maxRetries) return;
        
        const retryDelay = Math.pow(2, this.state.retryCount) * 1000; // Exponential backoff
        
        this.timers.retry = setTimeout(() => {
            this.retryConnection();
        }, retryDelay);
    }
    
    async retryConnection() {
        if (this.state.retryCount >= this.options.maxRetries) return;
        
        this.state.retryCount++;
        
        // Update retry display
        if (this.elements.retryCount) {
            this.elements.retryCount.textContent = `Deneme: ${this.state.retryCount}/${this.options.maxRetries}`;
        }
        
        console.log(`🔄 Retry attempt ${this.state.retryCount}/${this.options.maxRetries}`);
        
        const isOnline = await this.checkConnection();
        
        if (!isOnline && this.state.retryCount < this.options.maxRetries) {
            this.scheduleRetry();
        }
    }
    
    showNetworkStatusBar(message, type = 'info') {
        // Remove existing status bar
        const existingBar = document.getElementById('network-status-bar');
        if (existingBar) {
            existingBar.remove();
        }
        
        const statusBar = document.createElement('div');
        statusBar.id = 'network-status-bar';
        statusBar.className = `network-status-bar ${type}`;
        statusBar.innerHTML = `
            <i class="ki-outline ${type === 'online' ? 'ki-wifi' : 'ki-wifi-off'}"></i>
            ${message}
        `;
        
        document.body.appendChild(statusBar);
        
        // Show briefly
        setTimeout(() => {
            statusBar.classList.add('active');
        }, 100);
        
        setTimeout(() => {
            statusBar.classList.remove('active');
            setTimeout(() => {
                statusBar.remove();
            }, 300);
        }, 3000);
    }
    
    updateNetworkDisplay() {
        // Update business loader network status if available
        if (window.businessLoader && window.businessLoader.updateNetworkStatus) {
            window.businessLoader.updateNetworkStatus(this.state.isOnline);
        }
        
        // Update any other network displays
        const networkDisplays = document.querySelectorAll('.network-status');
        networkDisplays.forEach(display => {
            display.classList.toggle('online', this.state.isOnline);
            display.classList.toggle('offline', !this.state.isOnline);
        });
    }
    
    updateSpeedDisplay() {
        if (!this.state.lastSpeedTest) return;
        
        const speedInfo = {
            fast: { icon: 'ki-rocket', text: 'Hızlı', color: '#28a745' },
            medium: { icon: 'ki-timer', text: 'Normal', color: '#ffc107' },
            slow: { icon: 'ki-warning', text: 'Yavaş', color: '#dc3545' }
        };
        
        const speed = this.state.lastSpeedTest.speed;
        const info = speedInfo[speed];
        
        if (speed === 'slow') {
            this.showNetworkStatusBar(`Bağlantı yavaş (${Math.round(this.state.lastSpeedTest.responseTime)}ms)`, 'slow');
        }
    }
    
    // Public API
    getNetworkInfo() {
        return {
            isOnline: this.state.isOnline,
            connectionType: this.state.connectionType,
            effectiveType: this.state.effectiveType,
            downlink: this.state.downlink,
            rtt: this.state.rtt,
            lastSpeedTest: this.state.lastSpeedTest,
            offlineDuration: this.state.offlineStartTime ? Date.now() - this.state.offlineStartTime : 0
        };
    }
    
    forceCheck() {
        return this.checkConnection();
    }
    
    forceSpeedTest() {
        return this.performSpeedTest();
    }
    
    destroy() {
        this.stopMonitoring();
        
        if (this.elements.warning) {
            this.elements.warning.remove();
        }
        
        const statusBar = document.getElementById('network-status-bar');
        if (statusBar) {
            statusBar.remove();
        }
        
        const styles = document.getElementById('network-monitor-styles');
        if (styles) {
            styles.remove();
        }
        
        console.log('Network monitor destroyed');
    }
}

// Auto-initialize network monitor
let networkMonitor = null;

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        networkMonitor = new NetworkMonitor({
            checkInterval: 5000,
            speedTestInterval: 30000,
            showNotifications: true,
            autoRetry: true,
            maxRetries: 3
        });
        
        // Make globally available
        window.networkMonitor = networkMonitor;
        
        console.log('✅ Network Monitor system ready');
        
    }, 1200);
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NetworkMonitor;
}