// DOM Safety Utilities
class DOMSafety {
    static waitForElement(selector, timeout = 5000) {
        return new Promise((resolve, reject) => {
            const startTime = Date.now();
            
            function checkElement() {
                const element = document.querySelector(selector);
                if (element) {
                    resolve(element);
                } else if (Date.now() - startTime < timeout) {
                    setTimeout(checkElement, 100);
                } else {
                    reject(new Error(`Element ${selector} not found within ${timeout}ms`));
                }
            }
            
            checkElement();
        });
    }
    
    static safelyExecute(fn, context = 'unknown') {
        try {
            return fn();
        } catch (error) {
            console.warn(`Safe execution failed in ${context}:`, error);
            if (window.errorHandler) {
                errorHandler.reportError(error, context);
            }
            return null;
        }
    }
    
    static addEventListenerSafely(element, event, handler, options = {}) {
        if (!element || typeof element.addEventListener !== 'function') {
            console.warn('Invalid element for event listener:', element);
            return false;
        }
        
        try {
            element.addEventListener(event, handler, options);
            return true;
        } catch (error) {
            console.warn(`Failed to add event listener for ${event}:`, error);
            return false;
        }
    }
    
    static createElementSafely(tag, properties = {}, parent = null) {
        try {
            const element = document.createElement(tag);
            
            Object.keys(properties).forEach(key => {
                if (key === 'innerHTML') {
                    element.innerHTML = properties[key];
                } else if (key === 'textContent') {
                    element.textContent = properties[key];
                } else {
                    element.setAttribute(key, properties[key]);
                }
            });
            
            if (parent && parent.appendChild) {
                parent.appendChild(element);
            }
            
            return element;
        } catch (error) {
            console.warn('Failed to create element:', tag, error);
            return null;
        }
    }
    
    static removeElementSafely(element) {
        try {
            if (element && element.parentNode) {
                element.parentNode.removeChild(element);
                return true;
            }
            return false;
        } catch (error) {
            console.warn('Failed to remove element:', error);
            return false;
        }
    }
}

// Global safety wrapper for common DOM operations
window.DOMSafety = DOMSafety;

// Override problematic functions with safe alternatives
(function() {
    const originalAddEventListener = EventTarget.prototype.addEventListener;
    EventTarget.prototype.addEventListener = function(type, listener, options) {
        try {
            return originalAddEventListener.call(this, type, listener, options);
        } catch (error) {
            console.warn('addEventListener failed:', error);
            if (window.errorHandler) {
                errorHandler.reportError(error, 'addEventListener_override');
            }
        }
    };
    
    const originalQuerySelector = Document.prototype.querySelector;
    Document.prototype.querySelector = function(selector) {
        try {
            return originalQuerySelector.call(this, selector);
        } catch (error) {
            console.warn('querySelector failed for:', selector, error);
            return null;
        }
    };
    
    const originalQuerySelectorAll = Document.prototype.querySelectorAll;
    Document.prototype.querySelectorAll = function(selector) {
        try {
            return originalQuerySelectorAll.call(this, selector);
        } catch (error) {
            console.warn('querySelectorAll failed for:', selector, error);
            return [];
        }
    };
})();