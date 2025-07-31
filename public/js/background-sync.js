// Background Sync Manager
class BackgroundSyncManager {
    constructor() {
        this.dbName = 'MagazaTakipDB';
        this.dbVersion = 1;
        this.db = null;
        
        this.init();
    }
    
    async init() {
        await this.initDB();
        this.setupSyncHandlers();
    }
    
    initDB() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);
            
            request.onerror = () => reject(request.error);
            request.onsuccess = () => {
                this.db = request.result;
                resolve(this.db);
            };
            
            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                
                // Offline actions store
                if (!db.objectStoreNames.contains('offlineActions')) {
                    const store = db.createObjectStore('offlineActions', { 
                        keyPath: 'id', 
                        autoIncrement: true 
                    });
                    store.createIndex('timestamp', 'timestamp', { unique: false });
                    store.createIndex('type', 'type', { unique: false });
                }
                
                // Cached data store
                if (!db.objectStoreNames.contains('cachedData')) {
                    const dataStore = db.createObjectStore('cachedData', { 
                        keyPath: 'key' 
                    });
                    dataStore.createIndex('timestamp', 'timestamp', { unique: false });
                }
            };
        });
    }
    
    setupSyncHandlers() {
        // Online/offline event listeners
        window.addEventListener('online', () => {
            console.log('App came online, starting sync...');
            this.syncOfflineActions();
        });
        
        window.addEventListener('offline', () => {
            console.log('App went offline, enabling offline mode...');
            this.showOfflineNotification();
        });
        
        // Service Worker message handler
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.addEventListener('message', (event) => {
                if (event.data.type === 'BACKGROUND_SYNC') {
                    this.syncOfflineActions();
                }
            });
        }
    }
    
    // Offline action'ları kaydet
    async saveOfflineAction(action) {
        if (!this.db) await this.initDB();
        
        const transaction = this.db.transaction(['offlineActions'], 'readwrite');
        const store = transaction.objectStore('offlineActions');
        
        const actionData = {
            ...action,
            timestamp: Date.now(),
            synced: false
        };
        
        return new Promise((resolve, reject) => {
            const request = store.add(actionData);
            request.onsuccess = () => {
                console.log('Offline action saved:', actionData);
                this.requestBackgroundSync();
                resolve(request.result);
            };
            request.onerror = () => reject(request.error);
        });
    }
    
    // Background sync talep et
    requestBackgroundSync() {
        if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
            navigator.serviceWorker.ready.then((registration) => {
                return registration.sync.register('background-sync');
            }).catch((error) => {
                console.log('Background sync registration failed:', error);
                // Fallback: immediate sync attempt
                this.syncOfflineActions();
            });
        } else {
            // Background sync desteklenmiyorsa immediate sync
            this.syncOfflineActions();
        }
    }
    
    // Offline action'ları sync et
    async syncOfflineActions() {
        if (!navigator.onLine) {
            console.log('Still offline, skipping sync');
            return;
        }
        
        if (!this.db) await this.initDB();
        
        const transaction = this.db.transaction(['offlineActions'], 'readwrite');
        const store = transaction.objectStore('offlineActions');
        const index = store.index('timestamp');
        
        const request = index.getAll();
        request.onsuccess = async () => {
            const actions = request.result.filter(action => !action.synced);
            
            if (actions.length === 0) {
                console.log('No pending actions to sync');
                return;
            }
            
            console.log(`Syncing ${actions.length} offline actions...`);
            
            for (const action of actions) {
                try {
                    await this.syncSingleAction(action);
                    await this.markActionAsSynced(action.id);
                } catch (error) {
                    console.error('Failed to sync action:', action, error);
                }
            }
            
            this.showSyncCompleteNotification(actions.length);
        };
    }
    
    // Tek bir action'ı sync et
    async syncSingleAction(action) {
        const { type, data, url, method } = action;
        
        const fetchOptions = {
            method: method || 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        };
        
        const response = await fetch(url, fetchOptions);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        console.log(`Successfully synced ${type} action`);
        return response;
    }
    
    // Action'ı synced olarak işaretle
    async markActionAsSynced(actionId) {
        const transaction = this.db.transaction(['offlineActions'], 'readwrite');
        const store = transaction.objectStore('offlineActions');
        
        const getRequest = store.get(actionId);
        getRequest.onsuccess = () => {
            const action = getRequest.result;
            if (action) {
                action.synced = true;
                action.syncedAt = Date.now();
                store.put(action);
            }
        };
    }
    
    // Cache'den veri oku
    async getCachedData(key) {
        if (!this.db) await this.initDB();
        
        const transaction = this.db.transaction(['cachedData'], 'readonly');
        const store = transaction.objectStore('cachedData');
        
        return new Promise((resolve, reject) => {
            const request = store.get(key);
            request.onsuccess = () => {
                const result = request.result;
                if (result) {
                    resolve(result.data);
                } else {
                    resolve(null);
                }
            };
            request.onerror = () => reject(request.error);
        });
    }
    
    // Veriyi cache'e kaydet
    async setCachedData(key, data) {
        if (!this.db) await this.initDB();
        
        const transaction = this.db.transaction(['cachedData'], 'readwrite');
        const store = transaction.objectStore('cachedData');
        
        const cacheData = {
            key,
            data,
            timestamp: Date.now()
        };
        
        return new Promise((resolve, reject) => {
            const request = store.put(cacheData);
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }
    
    // Offline bildirim göster
    showOfflineNotification() {
        if (!document.body || document.querySelector('.offline-toast')) return;
        
        const toast = document.createElement('div');
        toast.className = 'toast offline-toast position-fixed bottom-0 start-50 translate-middle-x mb-3';
        toast.style.zIndex = '1060';
        toast.innerHTML = `
            <div class="toast-header bg-warning text-dark">
                <i class="ki-outline ki-wifi-off fs-3 me-2"></i>
                <strong class="me-auto">Çevrimdışı Mod</strong>
            </div>
            <div class="toast-body bg-warning-subtle">
                İnternet bağlantısı yok. Verileriniz bağlantı geldiğinde senkronize edilecek.
            </div>
        `;
        
        document.body.appendChild(toast);
        
        if (window.bootstrap && bootstrap.Toast) {
            const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
            bsToast.show();
            
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }
    }
    
    // Sync tamamlandı bildirimi
    showSyncCompleteNotification(count) {
        if (!document.body) {
            setTimeout(() => this.showSyncCompleteNotification(count), 100);
            return;
        }
        
        const toast = document.createElement('div');
        toast.className = 'toast position-fixed bottom-0 end-0 me-3 mb-3';
        toast.style.zIndex = '1060';
        toast.innerHTML = `
            <div class="toast-header bg-success text-white">
                <i class="ki-outline ki-check-circle fs-3 me-2"></i>
                <strong class="me-auto">Senkronizasyon Tamamlandı</strong>
            </div>
            <div class="toast-body bg-success-subtle">
                ${count} bekleyen işlem başarıyla senkronize edildi.
            </div>
        `;
        
        document.body.appendChild(toast);
        
        if (window.bootstrap && bootstrap.Toast) {
            const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
            bsToast.show();
            
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }
    }
}

// Form submission'ları intercept et
function interceptFormSubmissions() {
    document.addEventListener('submit', async (event) => {
        if (!navigator.onLine) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            const action = {
                type: 'form_submission',
                url: form.action || window.location.href,
                method: form.method || 'POST',
                data: data
            };
            
            await backgroundSyncManager.saveOfflineAction(action);
            
            // Kullanıcıya bildirim göster
            const alert = document.createElement('div');
            alert.className = 'alert alert-info alert-dismissible fade show';
            alert.innerHTML = `
                <i class="ki-outline ki-information fs-3 me-2"></i>
                <strong>Çevrimdışı Kayıt:</strong> Verileriniz kaydedildi ve internet bağlantısı geldiğinde gönderilecek.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            form.parentNode.insertBefore(alert, form);
            
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }
    });
}

// Background Sync Manager'ı başlat
let backgroundSyncManager;
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        backgroundSyncManager = new BackgroundSyncManager();
        interceptFormSubmissions();
    });
} else {
    backgroundSyncManager = new BackgroundSyncManager();
    interceptFormSubmissions();
}