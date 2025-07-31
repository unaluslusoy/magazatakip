// Compatibility fixes for existing JavaScript issues
(function() {
    'use strict';
    
    // Safe DOM ready check
    function domReady(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    }
    
    // Fix for scripts.bundle.js null element errors
    domReady(function() {
        // Add missing elements if they don't exist
        const missingElements = [
            { id: 'kt_app_header_menu_toggle', tag: 'div' },
            { id: 'kt_app_header', tag: 'div' },
            { id: 'kt_app_wrapper', tag: 'div' },
            { class: 'share-modal-trigger', tag: 'button' }
        ];
        
        missingElements.forEach(elementInfo => {
            let element = null;
            
            if (elementInfo.id) {
                element = document.getElementById(elementInfo.id);
            } else if (elementInfo.class) {
                element = document.querySelector('.' + elementInfo.class);
            }
            
            if (!element && elementInfo.id) {
                const newElement = document.createElement(elementInfo.tag);
                newElement.id = elementInfo.id;
                newElement.style.display = 'none';
                document.body.appendChild(newElement);
                console.log('Created missing element:', elementInfo.id);
            }
        });
        
        // Safe initialization of common components
        setTimeout(function() {
            try {
                // Initialize tooltips if Bootstrap is available
                if (window.bootstrap && bootstrap.Tooltip) {
                    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                    tooltips.forEach(tooltip => {
                        try {
                            new bootstrap.Tooltip(tooltip);
                        } catch (e) {
                            console.warn('Tooltip initialization failed:', e);
                        }
                    });
                }
                
                // Initialize modals if Bootstrap is available
                if (window.bootstrap && bootstrap.Modal) {
                    const modals = document.querySelectorAll('.modal');
                    modals.forEach(modal => {
                        try {
                            new bootstrap.Modal(modal);
                        } catch (e) {
                            console.warn('Modal initialization failed:', e);
                        }
                    });
                }
                
                // Initialize toasts if Bootstrap is available
                if (window.bootstrap && bootstrap.Toast) {
                    const toasts = document.querySelectorAll('.toast');
                    toasts.forEach(toast => {
                        try {
                            new bootstrap.Toast(toast);
                        } catch (e) {
                            console.warn('Toast initialization failed:', e);
                        }
                    });
                }
                
            } catch (error) {
                console.warn('Component initialization failed:', error);
            }
        }, 1000);
        
        // Handle share modal errors specifically
        try {
            const shareButtons = document.querySelectorAll('[data-share], .share-btn, .share-modal-trigger');
            shareButtons.forEach(button => {
                if (button && !button.hasAttribute('data-listener-added')) {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        // Use Web Share API if available
                        if (navigator.share) {
                            navigator.share({
                                title: document.title,
                                text: 'Mağaza Takip Uygulaması',
                                url: window.location.href
                            }).catch(err => console.log('Share failed:', err));
                        } else {
                            // Fallback: copy to clipboard
                            if (navigator.clipboard) {
                                navigator.clipboard.writeText(window.location.href)
                                    .then(() => alert('Link kopyalandı!'))
                                    .catch(() => console.log('Copy failed'));
                            }
                        }
                    });
                    button.setAttribute('data-listener-added', 'true');
                }
            });
        } catch (error) {
            console.warn('Share button setup failed:', error);
        }
        
        // Fix for menu toggles
        try {
            const menuToggles = document.querySelectorAll('[data-kt-menu-trigger], .menu-toggle');
            menuToggles.forEach(toggle => {
                if (toggle && !toggle.hasAttribute('data-listener-added')) {
                    toggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        const target = this.getAttribute('data-kt-menu-target') || 
                                      this.getAttribute('data-target') ||
                                      this.nextElementSibling;
                        
                        if (target) {
                            const targetElement = typeof target === 'string' ? 
                                                 document.querySelector(target) : target;
                            
                            if (targetElement) {
                                targetElement.classList.toggle('show');
                            }
                        }
                    });
                    toggle.setAttribute('data-listener-added', 'true');
                }
            });
        } catch (error) {
            console.warn('Menu toggle setup failed:', error);
        }
    });
    
    // Prevent browser extension errors
    window.addEventListener('error', function(e) {
        const message = e.message || '';
        
        // Ignore browser extension related errors
        if (message.includes('extension') || 
            message.includes('chrome-extension') || 
            message.includes('moz-extension') ||
            message.includes('Receiving end does not exist')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Console polyfills for older browsers
    if (!window.console) {
        window.console = {
            log: function() {},
            warn: function() {},
            error: function() {},
            info: function() {},
            debug: function() {}
        };
    }
    
    // Promise polyfill check
    if (!window.Promise) {
        console.warn('Promise not supported, some PWA features may not work');
    }
    
    // Fetch polyfill check
    if (!window.fetch) {
        console.warn('Fetch not supported, some PWA features may not work');
    }
    
    // Service Worker support check
    if (!('serviceWorker' in navigator)) {
        console.warn('Service Worker not supported, PWA features will be limited');
    }
    
})();