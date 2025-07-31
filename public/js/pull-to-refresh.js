// Pull to Refresh PWA Feature
class PullToRefresh {
    constructor(options = {}) {
        this.options = {
            threshold: 80,           // Minimum pull distance
            maxDistance: 120,        // Maximum pull distance
            resistance: 2.5,         // Pull resistance
            refreshThreshold: 60,    // Threshold to trigger refresh
            onRefresh: null,         // Refresh callback
            container: document.body, // Container element
            enabled: true,           // Enable/disable
            ...options
        };
        
        this.state = {
            pulling: false,
            startY: 0,
            currentY: 0,
            distance: 0,
            canRefresh: false,
            refreshing: false
        };
        
        this.elements = {};
        this.init();
    }
    
    init() {
        this.createElements();
        this.setupEventListeners();
        this.setupStyles();
        
        // PWA analytics integration
        if (window.pwaAnalytics) {
            this.analytics = window.pwaAnalytics;
        }
        
        console.log('Pull-to-refresh initialized');
    }
    
    createElements() {
        // Pull indicator container
        this.elements.indicator = document.createElement('div');
        this.elements.indicator.className = 'pull-to-refresh-indicator';
        this.elements.indicator.innerHTML = `
            <div class="pull-indicator">
                <div class="pull-icon">
                    <svg class="pull-arrow" viewBox="0 0 24 24" width="24" height="24">
                        <path fill="currentColor" d="M7.41,8.58L12,13.17L16.59,8.58L18,10L12,16L6,10L7.41,8.58Z"/>
                    </svg>
                    <div class="pull-spinner">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                    </div>
                </div>
                <div class="pull-text">
                    <span class="pull-text-default">Yenilemek için aşağı çekin</span>
                    <span class="pull-text-release">Yenilemek için bırakın</span>
                    <span class="pull-text-refreshing">Yenileniyor...</span>
                </div>
            </div>
        `;
        
        // Insert at the beginning of container
        this.options.container.insertBefore(this.elements.indicator, this.options.container.firstChild);
    }
    
    setupStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .pull-to-refresh-indicator {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                height: 80px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(180deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.8) 100%);
                backdrop-filter: blur(10px);
                transform: translateY(-100%);
                transition: transform 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
                z-index: 1000;
                border-bottom: 1px solid rgba(0,0,0,0.1);
            }
            
            .pull-to-refresh-indicator.pulling {
                transition: none;
            }
            
            .pull-to-refresh-indicator.refreshing {
                transform: translateY(0) !important;
                transition: transform 0.3s ease;
            }
            
            .pull-indicator {
                display: flex;
                align-items: center;
                gap: 12px;
                color: #6c757d;
                font-size: 14px;
                font-weight: 500;
            }
            
            .pull-icon {
                position: relative;
                width: 24px;
                height: 24px;
                transition: transform 0.2s ease;
            }
            
            .pull-arrow {
                position: absolute;
                top: 0;
                left: 0;
                transition: transform 0.2s ease, opacity 0.2s ease;
            }
            
            .pull-spinner {
                position: absolute;
                top: 0;
                left: 0;
                opacity: 0;
                transition: opacity 0.2s ease;
            }
            
            .pull-text span {
                display: none;
            }
            
            .pull-text .pull-text-default {
                display: inline;
            }
            
            /* Pull states */
            .pull-to-refresh-indicator.can-refresh .pull-arrow {
                transform: rotate(180deg);
                color: #0d6efd;
            }
            
            .pull-to-refresh-indicator.can-refresh .pull-text-default {
                display: none;
            }
            
            .pull-to-refresh-indicator.can-refresh .pull-text-release {
                display: inline;
                color: #0d6efd;
            }
            
            .pull-to-refresh-indicator.refreshing .pull-arrow {
                opacity: 0;
            }
            
            .pull-to-refresh-indicator.refreshing .pull-spinner {
                opacity: 1;
            }
            
            .pull-to-refresh-indicator.refreshing .pull-text-default,
            .pull-to-refresh-indicator.refreshing .pull-text-release {
                display: none;
            }
            
            .pull-to-refresh-indicator.refreshing .pull-text-refreshing {
                display: inline;
                color: #0d6efd;
            }
            
            /* Dark mode support */
            @media (prefers-color-scheme: dark) {
                .pull-to-refresh-indicator {
                    background: linear-gradient(180deg, rgba(33,37,41,0.95) 0%, rgba(33,37,41,0.8) 100%);
                    color: #dee2e6;
                    border-bottom-color: rgba(255,255,255,0.1);
                }
            }
            
            /* Disable native pull-to-refresh on mobile browsers */
            body {
                overscroll-behavior-y: contain;
            }
            
            /* Animation for smooth refresh */
            @keyframes refreshPulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.1); }
            }
            
            .pull-to-refresh-indicator.refreshing .pull-spinner {
                animation: refreshPulse 1s ease-in-out infinite;
            }
        `;
        
        document.head.appendChild(style);
    }
    
    setupEventListeners() {
        // Touch events for mobile
        this.options.container.addEventListener('touchstart', this.onTouchStart.bind(this), { passive: false });
        this.options.container.addEventListener('touchmove', this.onTouchMove.bind(this), { passive: false });
        this.options.container.addEventListener('touchend', this.onTouchEnd.bind(this), { passive: true });
        
        // Mouse events for desktop testing
        this.options.container.addEventListener('mousedown', this.onMouseDown.bind(this));
        this.options.container.addEventListener('mousemove', this.onMouseMove.bind(this));
        this.options.container.addEventListener('mouseup', this.onMouseUp.bind(this));
        
        // Prevent native pull-to-refresh
        document.addEventListener('touchmove', (e) => {
            if (this.state.pulling) {
                e.preventDefault();
            }
        }, { passive: false });
        
        // Keyboard shortcut for testing
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                this.triggerRefresh();
            }
        });
    }
    
    onTouchStart(e) {
        if (!this.options.enabled || this.state.refreshing) return;
        
        const touch = e.touches[0];
        const scrollTop = this.options.container.scrollTop || window.pageYOffset;
        
        // Only activate at the top of the page
        if (scrollTop > 10) return;
        
        this.state.startY = touch.clientY;
        this.state.pulling = false;
        
        // Track analytics
        if (this.analytics) {
            this.analytics.trackFeatureUsage('pull-to-refresh', 'touch_start');
        }
    }
    
    onTouchMove(e) {
        if (!this.options.enabled || this.state.refreshing) return;
        
        const touch = e.touches[0];
        const scrollTop = this.options.container.scrollTop || window.pageYOffset;
        
        if (scrollTop > 10) return;
        
        const deltaY = touch.clientY - this.state.startY;
        
        // Start pulling only if moving down
        if (deltaY > 0) {
            this.state.pulling = true;
            this.state.currentY = touch.clientY;
            
            // Calculate pull distance with resistance
            this.state.distance = Math.min(
                deltaY / this.options.resistance,
                this.options.maxDistance
            );
            
            this.updateIndicator();
            e.preventDefault();
        }
    }
    
    onTouchEnd(e) {
        if (!this.state.pulling || this.state.refreshing) return;
        
        if (this.state.distance >= this.options.refreshThreshold) {
            this.triggerRefresh();
        } else {
            this.resetIndicator();
        }
        
        this.state.pulling = false;
    }
    
    // Mouse events for desktop testing
    onMouseDown(e) {
        if (!this.options.enabled || this.state.refreshing) return;
        
        const scrollTop = this.options.container.scrollTop || window.pageYOffset;
        if (scrollTop > 10) return;
        
        this.state.startY = e.clientY;
        this.state.mouseDown = true;
    }
    
    onMouseMove(e) {
        if (!this.state.mouseDown || !this.options.enabled || this.state.refreshing) return;
        
        const deltaY = e.clientY - this.state.startY;
        
        if (deltaY > 0) {
            this.state.pulling = true;
            this.state.distance = Math.min(
                deltaY / this.options.resistance,
                this.options.maxDistance
            );
            
            this.updateIndicator();
            e.preventDefault();
        }
    }
    
    onMouseUp(e) {
        if (!this.state.mouseDown) return;
        
        this.state.mouseDown = false;
        
        if (this.state.pulling) {
            if (this.state.distance >= this.options.refreshThreshold) {
                this.triggerRefresh();
            } else {
                this.resetIndicator();
            }
        }
        
        this.state.pulling = false;
    }
    
    updateIndicator() {
        const indicator = this.elements.indicator;
        const progress = Math.min(this.state.distance / this.options.refreshThreshold, 1);
        
        // Update position
        const translateY = -100 + (progress * 100);
        indicator.style.transform = `translateY(${translateY}%)`;
        
        // Update state classes
        indicator.classList.add('pulling');
        
        if (this.state.distance >= this.options.refreshThreshold) {
            indicator.classList.add('can-refresh');
            this.state.canRefresh = true;
            
            // Haptic feedback if available
            if (navigator.vibrate && !this.state.hapticFired) {
                navigator.vibrate(50);
                this.state.hapticFired = true;
            }
        } else {
            indicator.classList.remove('can-refresh');
            this.state.canRefresh = false;
            this.state.hapticFired = false;
        }
    }
    
    async triggerRefresh() {
        if (this.state.refreshing) return;
        
        this.state.refreshing = true;
        const indicator = this.elements.indicator;
        
        // Update UI to refreshing state
        indicator.classList.remove('pulling', 'can-refresh');
        indicator.classList.add('refreshing');
        indicator.style.transform = 'translateY(0)';
        
        // Track analytics
        if (this.analytics) {
            this.analytics.trackFeatureUsage('pull-to-refresh', 'triggered');
        }
        
        try {
            // Call custom refresh function or default behavior
            if (this.options.onRefresh && typeof this.options.onRefresh === 'function') {
                await this.options.onRefresh();
            } else {
                await this.defaultRefresh();
            }
            
            // Success feedback
            if (this.analytics) {
                this.analytics.trackFeatureUsage('pull-to-refresh', 'success');
            }
            
        } catch (error) {
            console.error('Pull-to-refresh failed:', error);
            
            if (this.analytics) {
                this.analytics.trackError(error, 'pull-to-refresh');
            }
            
            // Show error notification
            this.showErrorNotification();
        }
        
        // Reset after a delay
        setTimeout(() => {
            this.resetIndicator();
            this.state.refreshing = false;
        }, 1000);
    }
    
    async defaultRefresh() {
        // Default refresh behavior
        return new Promise((resolve) => {
            // Simulate refresh delay
            setTimeout(() => {
                window.location.reload();
                resolve();
            }, 1500);
        });
    }
    
    resetIndicator() {
        const indicator = this.elements.indicator;
        
        indicator.classList.remove('pulling', 'can-refresh', 'refreshing');
        indicator.style.transform = 'translateY(-100%)';
        
        this.state.distance = 0;
        this.state.canRefresh = false;
        this.state.hapticFired = false;
    }
    
    showErrorNotification() {
        // Create error toast
        const toast = document.createElement('div');
        toast.className = 'toast position-fixed top-0 start-50 translate-middle-x mt-3';
        toast.style.zIndex = '1070';
        toast.innerHTML = `
            <div class="toast-header bg-danger text-white">
                <i class="ki-outline ki-warning fs-3 me-2"></i>
                <strong class="me-auto">Yenileme Hatası</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                Sayfa yenilenemedi. İnternet bağlantınızı kontrol edin.
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Show toast
        if (window.bootstrap && bootstrap.Toast) {
            const bsToast = new bootstrap.Toast(toast, { delay: 4000 });
            bsToast.show();
            
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }
    }
    
    // Public methods
    enable() {
        this.options.enabled = true;
    }
    
    disable() {
        this.options.enabled = false;
        this.resetIndicator();
    }
    
    setRefreshCallback(callback) {
        this.options.onRefresh = callback;
    }
    
    destroy() {
        // Remove event listeners
        this.options.container.removeEventListener('touchstart', this.onTouchStart);
        this.options.container.removeEventListener('touchmove', this.onTouchMove);
        this.options.container.removeEventListener('touchend', this.onTouchEnd);
        
        // Remove elements
        if (this.elements.indicator && this.elements.indicator.parentNode) {
            this.elements.indicator.parentNode.removeChild(this.elements.indicator);
        }
        
        console.log('Pull-to-refresh destroyed');
    }
}

// Initialize Pull-to-Refresh
let pullToRefresh = null;

document.addEventListener('DOMContentLoaded', function() {
    // Wait for other PWA components to load
    setTimeout(() => {
        pullToRefresh = new PullToRefresh({
            onRefresh: async () => {
                // Custom refresh logic
                try {
                    // Check if we're on specific pages that need special refresh
                    const path = window.location.pathname;
                    
                    if (path.includes('/anasayfa') || path === '/') {
                        // Refresh dashboard data
                        await refreshDashboardData();
                    } else if (path.includes('/profil')) {
                        // Refresh profile data  
                        await refreshProfileData();
                    } else {
                        // Default page refresh
                        window.location.reload();
                    }
                } catch (error) {
                    console.error('Custom refresh failed:', error);
                    // Fallback to page reload
                    window.location.reload();
                }
            }
        });
        
        // Make globally available
        window.pullToRefresh = pullToRefresh;
        
        console.log('✅ Pull-to-refresh initialized');
        
    }, 1000);
});

// Custom refresh functions
async function refreshDashboardData() {
    // Simulate API call to refresh dashboard
    return new Promise((resolve) => {
        console.log('Refreshing dashboard data...');
        
        // Add refresh animation to cards
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.style.opacity = '0.7';
            card.style.transform = 'scale(0.98)';
        });
        
        setTimeout(() => {
            // Restore cards
            cards.forEach(card => {
                card.style.opacity = '1';
                card.style.transform = 'scale(1)';
                card.style.transition = 'all 0.3s ease';
            });
            
            // Show success message
            showRefreshSuccess('Dashboard yenilendi');
            resolve();
        }, 1200);
    });
}

async function refreshProfileData() {
    return new Promise((resolve) => {
        console.log('Refreshing profile data...');
        
        setTimeout(() => {
            showRefreshSuccess('Profil bilgileri yenilendi');
            resolve();
        }, 800);
    });
}

function showRefreshSuccess(message) {
    const toast = document.createElement('div');
    toast.className = 'toast position-fixed top-0 end-0 m-3';
    toast.style.zIndex = '1060';
    toast.innerHTML = `
        <div class="toast-header bg-success text-white">
            <i class="ki-outline ki-check fs-3 me-2"></i>
            <strong class="me-auto">Başarılı</strong>
        </div>
        <div class="toast-body bg-success-subtle">
            ${message}
        </div>
    `;
    
    document.body.appendChild(toast);
    
    if (window.bootstrap && bootstrap.Toast) {
        const bsToast = new bootstrap.Toast(toast, { delay: 2000 });
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
}