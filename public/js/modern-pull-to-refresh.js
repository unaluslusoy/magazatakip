/**
 * Modern Pull-to-Refresh Implementation
 * Touch optimized ve performans odaklı
 * v1.2.0
 */

class ModernPullToRefresh {
    constructor(options = {}) {
        this.options = {
            container: options.container || document.documentElement,
            threshold: options.threshold || 80,
            resistance: options.resistance || 2.5,
            pullElementHeight: options.pullElementHeight || 80,
            onRefresh: options.onRefresh || (() => window.location.reload()),
            animationDuration: options.animationDuration || 300,
            iconColor: options.iconColor || '#1976d2',
            backgroundColor: options.backgroundColor || '#ffffff',
            ...options
        };

        this.state = {
            pulling: false,
            pullDistance: 0,
            canPull: false,
            isRefreshing: false
        };

        this.touchStartY = 0;
        this.currentY = 0;
        this.pullElement = null;
        
        this.init();
    }

    init() {
        this.createPullElement();
        this.attachEventListeners();
        
        console.log('📲 Modern Pull-to-Refresh aktif!');
    }

    createPullElement() {
        this.pullElement = document.createElement('div');
        this.pullElement.className = 'modern-pull-to-refresh';
        this.pullElement.innerHTML = `
            <div class="pull-content">
                <div class="pull-icon">
                    <svg class="refresh-icon" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z" fill="currentColor"/>
                    </svg>
                    <div class="loading-spinner" style="display: none;">
                        <div class="spinner"></div>
                    </div>
                </div>
                <div class="pull-text">
                    <span class="pull-message">Yenilemek için aşağı çekin</span>
                    <span class="release-message" style="display: none;">Yenilemek için bırakın</span>
                    <span class="loading-message" style="display: none;">Yenileniyor...</span>
                </div>
            </div>
        `;

        this.injectStyles();
        document.body.insertBefore(this.pullElement, document.body.firstChild);
        this.updatePullElement(0);
    }

    injectStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .modern-pull-to-refresh {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: ${this.options.pullElementHeight}px;
                background: ${this.options.backgroundColor};
                color: ${this.options.iconColor};
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                border-radius: 0 0 16px 16px;
                transform: translateY(-100%);
                transition: transform 0.2s ease-out;
                backdrop-filter: blur(10px);
                background: rgba(255, 255, 255, 0.95);
            }

            .modern-pull-to-refresh.pulling {
                transition: none;
            }

            .modern-pull-to-refresh.refreshing {
                transform: translateY(0);
                transition: transform 0.3s ease-out;
            }

            .pull-content {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 0 20px;
            }

            .pull-icon {
                position: relative;
                width: 24px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .refresh-icon {
                transition: transform 0.3s ease;
                color: ${this.options.iconColor};
            }

            .modern-pull-to-refresh.can-refresh .refresh-icon {
                transform: rotate(180deg);
            }

            .loading-spinner {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .spinner {
                width: 20px;
                height: 20px;
                border: 2px solid rgba(25, 118, 210, 0.3);
                border-left-color: ${this.options.iconColor};
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                to { transform: rotate(360deg); }
            }

            .pull-text {
                font-size: 14px;
                font-weight: 500;
                white-space: nowrap;
            }

            /* Mobil optimizasyon */
            @media (max-width: 768px) {
                .modern-pull-to-refresh {
                    height: 60px;
                }
                
                .pull-content {
                    gap: 8px;
                }
                
                .pull-text {
                    font-size: 13px;
                }
            }

            /* Dark mode support */
            @media (prefers-color-scheme: dark) {
                .modern-pull-to-refresh {
                    background: rgba(33, 37, 41, 0.95);
                    color: #ffffff;
                }
            }

            /* Reduced motion */
            @media (prefers-reduced-motion: reduce) {
                .modern-pull-to-refresh,
                .refresh-icon,
                .spinner {
                    transition: none;
                    animation: none;
                }
            }

            /* iOS Safari scroll bounce fix */
            body.pull-to-refresh-active {
                overflow-y: hidden;
                height: 100vh;
                position: fixed;
                width: 100%;
            }
        `;
        document.head.appendChild(style);
    }

    attachEventListeners() {
        // Touch events (mobil)
        document.addEventListener('touchstart', this.onTouchStart.bind(this), { passive: false });
        document.addEventListener('touchmove', this.onTouchMove.bind(this), { passive: false });
        document.addEventListener('touchend', this.onTouchEnd.bind(this), { passive: false });

        // Mouse events (desktop test için)
        if (this.isDevelopment()) {
            document.addEventListener('mousedown', this.onMouseDown.bind(this));
            document.addEventListener('mousemove', this.onMouseMove.bind(this));
            document.addEventListener('mouseup', this.onMouseUp.bind(this));
        }

        // Page visibility change
        document.addEventListener('visibilitychange', () => {
            if (document.hidden && this.state.isRefreshing) {
                this.resetPull();
            }
        });
    }

    onTouchStart(e) {
        if (!this.canStartPull()) return;
        
        this.touchStartY = e.touches[0].clientY;
        this.state.canPull = true;
        
        // iOS Safari bounce önleme
        if (window.scrollY === 0) {
            document.body.classList.add('pull-to-refresh-active');
        }
    }

    onTouchMove(e) {
        if (!this.state.canPull || this.state.isRefreshing) return;

        this.currentY = e.touches[0].clientY;
        const pullDistance = (this.currentY - this.touchStartY) / this.options.resistance;

        if (pullDistance > 0 && window.scrollY === 0) {
            e.preventDefault(); // Prevent scroll
            
            this.state.pulling = true;
            this.state.pullDistance = Math.min(pullDistance, this.options.threshold * 1.5);
            
            this.updatePullElement(this.state.pullDistance);
        }
    }

    onTouchEnd(e) {
        document.body.classList.remove('pull-to-refresh-active');
        
        if (!this.state.pulling) return;

        if (this.state.pullDistance >= this.options.threshold) {
            this.triggerRefresh();
        } else {
            this.resetPull();
        }
    }

    // Desktop development support
    onMouseDown(e) {
        if (!this.canStartPull() || e.clientY > 100) return;
        this.touchStartY = e.clientY;
        this.state.canPull = true;
        document.addEventListener('mousemove', this.onMouseMove.bind(this));
        document.addEventListener('mouseup', this.onMouseUp.bind(this));
    }

    onMouseMove(e) {
        if (!this.state.canPull) return;
        this.currentY = e.clientY;
        const pullDistance = (this.currentY - this.touchStartY) / this.options.resistance;
        
        if (pullDistance > 0 && window.scrollY === 0) {
            this.state.pulling = true;
            this.state.pullDistance = Math.min(pullDistance, this.options.threshold * 1.5);
            this.updatePullElement(this.state.pullDistance);
        }
    }

    onMouseUp(e) {
        document.removeEventListener('mousemove', this.onMouseMove);
        document.removeEventListener('mouseup', this.onMouseUp);
        this.onTouchEnd(e);
    }

    canStartPull() {
        return window.scrollY === 0 && 
               !this.state.isRefreshing && 
               !document.querySelector('.modal.show') &&
               !document.querySelector('.offcanvas.show');
    }

    updatePullElement(distance) {
        const progress = Math.min(distance / this.options.threshold, 1);
        const translateY = -100 + (progress * 100);
        
        this.pullElement.style.transform = `translateY(${translateY}%)`;
        this.pullElement.classList.toggle('pulling', this.state.pulling);
        this.pullElement.classList.toggle('can-refresh', distance >= this.options.threshold);

        // Message updates
        const canRefresh = distance >= this.options.threshold;
        this.pullElement.querySelector('.pull-message').style.display = canRefresh ? 'none' : 'block';
        this.pullElement.querySelector('.release-message').style.display = canRefresh ? 'block' : 'none';
        
        // Icon rotation
        const icon = this.pullElement.querySelector('.refresh-icon');
        if (icon) {
            icon.style.transform = `rotate(${progress * 180}deg)`;
        }
    }

    async triggerRefresh() {
        if (this.state.isRefreshing) return;
        
        this.state.isRefreshing = true;
        this.pullElement.classList.add('refreshing');
        this.pullElement.classList.remove('pulling', 'can-refresh');

        // UI updates
        this.pullElement.querySelector('.refresh-icon').style.display = 'none';
        this.pullElement.querySelector('.loading-spinner').style.display = 'block';
        this.pullElement.querySelector('.pull-message').style.display = 'none';
        this.pullElement.querySelector('.release-message').style.display = 'none';
        this.pullElement.querySelector('.loading-message').style.display = 'block';

        try {
            // Haptic feedback (desteklenirse)
            if (navigator.vibrate) {
                navigator.vibrate(50);
            }

            // Refresh callback çalıştır
            await this.options.onRefresh();
            
            console.log('✅ Pull-to-refresh tamamlandı');
            
            // Success feedback
            setTimeout(() => this.resetPull(), 500);
            
        } catch (error) {
            console.error('❌ Pull-to-refresh hatası:', error);
            this.resetPull();
        }
    }

    resetPull() {
        this.state = {
            pulling: false,
            pullDistance: 0,
            canPull: false,
            isRefreshing: false
        };

        this.pullElement.classList.remove('pulling', 'can-refresh', 'refreshing');
        this.pullElement.style.transform = 'translateY(-100%)';
        
        // UI reset
        this.pullElement.querySelector('.refresh-icon').style.display = 'block';
        this.pullElement.querySelector('.loading-spinner').style.display = 'none';
        this.pullElement.querySelector('.pull-message').style.display = 'block';
        this.pullElement.querySelector('.release-message').style.display = 'none';
        this.pullElement.querySelector('.loading-message').style.display = 'none';
        
        document.body.classList.remove('pull-to-refresh-active');
    }

    isDevelopment() {
        return window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    }

    // Public API
    destroy() {
        if (this.pullElement) {
            this.pullElement.remove();
        }
        document.body.classList.remove('pull-to-refresh-active');
    }

    updateOptions(newOptions) {
        this.options = { ...this.options, ...newOptions };
    }
}

// Auto initialization
document.addEventListener('DOMContentLoaded', () => {
    if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
        window.pullToRefresh = new ModernPullToRefresh({
            onRefresh: async () => {
                // Mevcut sayfa verilerini yenile
                if (typeof window.refreshPageData === 'function') {
                    await window.refreshPageData();
                } else {
                    // Fallback: sayfa yenile
                    window.location.reload();
                }
            }
        });
        
        console.log('📲 Pull-to-Refresh mobil cihazda aktif!');
    }
});

// Export for modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ModernPullToRefresh;
}

console.log('📲 Modern Pull-to-Refresh hazır!');