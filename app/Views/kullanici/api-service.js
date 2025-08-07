/**
 * Genel API Service
 * Tüm sistem API'leri için ortak servis
 */

class ApiService {
    constructor() {
        this.baseUrl = '/api';
        this.defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache'
            },
            cache: 'no-store'
        };
    }

    /**
     * API isteği gönder
     */
    async makeRequest(url, options = {}) {
        const finalOptions = { ...this.defaultOptions, ...options };
        
        try {
            const response = await fetch(url, finalOptions);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('API isteği hatası:', error);
            throw error;
        }
    }

    /**
     * GET isteği
     */
    async get(endpoint) {
        const url = `${this.baseUrl}${endpoint}?_t=${Date.now()}`;
        return await this.makeRequest(url);
    }

    /**
     * POST isteği
     */
    async post(endpoint, data) {
        const options = {
            method: 'POST',
            body: JSON.stringify(data)
        };
        return await this.makeRequest(`${this.baseUrl}${endpoint}`, options);
    }

    /**
     * PUT isteği
     */
    async put(endpoint, data) {
        const options = {
            method: 'PUT',
            body: JSON.stringify(data)
        };
        return await this.makeRequest(`${this.baseUrl}${endpoint}`, options);
    }

    /**
     * DELETE isteği
     */
    async delete(endpoint) {
        const options = {
            method: 'DELETE'
        };
        return await this.makeRequest(`${this.baseUrl}${endpoint}`, options);
    }
}

/**
 * Kullanıcı API Service
 */
class UserApiService extends ApiService {
    constructor() {
        super();
        this.endpoint = '/user';
    }

    // Profil işlemleri
    async getProfile() {
        return await this.get(`${this.endpoint}/profile`);
    }

    async updateProfile(profileData) {
        return await this.put(`${this.endpoint}/profile`, profileData);
    }

    async changePassword(currentPassword, newPassword) {
        return await this.put(`${this.endpoint}/password`, {
            current_password: currentPassword,
            new_password: newPassword
        });
    }

    async getDashboardStats() {
        return await this.get(`${this.endpoint}/dashboard-stats`);
    }

    async getSystemStatus() {
        return await this.get(`${this.endpoint}/system-status`);
    }
}

/**
 * Ciro API Service
 */
class CiroApiService extends ApiService {
    constructor() {
        super();
        this.endpoint = '/ciro';
    }

    async getCiroListesi() {
        return await this.get(`${this.endpoint}/liste`);
    }

    async getCiro(id) {
        return await this.get(`${this.endpoint}/${id}`);
    }

    async addCiro(ciroData) {
        return await this.post(`${this.endpoint}/ekle`, ciroData);
    }

    async updateCiro(id, ciroData) {
        return await this.put(`${this.endpoint}/guncelle/${id}`, ciroData);
    }

    async deleteCiro(id) {
        return await this.delete(`${this.endpoint}/sil/${id}`);
    }

    async getMagazalar() {
        return await this.get('/magazalar');
    }
}

/**
 * Gider API Service
 */
class GiderApiService extends ApiService {
    constructor() {
        super();
        this.endpoint = '/gider';
    }

    async getGiderListesi() {
        return await this.get(`${this.endpoint}/liste`);
    }

    async getGider(id) {
        return await this.get(`${this.endpoint}/${id}`);
    }

    async addGider(giderData) {
        return await this.post(`${this.endpoint}/ekle`, giderData);
    }

    async updateGider(id, giderData) {
        return await this.put(`${this.endpoint}/guncelle/${id}`, giderData);
    }

    async deleteGider(id) {
        return await this.delete(`${this.endpoint}/sil/${id}`);
    }

    async getGiderStats() {
        return await this.get(`${this.endpoint}/stats`);
    }
}

/**
 * İş Emri API Service
 */
class IsEmriApiService extends ApiService {
    constructor() {
        super();
        this.endpoint = '/is-emri';
    }

    async getIsEmriListesi() {
        return await this.get(`${this.endpoint}/liste`);
    }

    async getIsEmri(id) {
        return await this.get(`${this.endpoint}/${id}`);
    }

    async createIsEmri(isEmriData) {
        return await this.post(`${this.endpoint}/olustur`, isEmriData);
    }

    async updateIsEmri(id, isEmriData) {
        return await this.put(`${this.endpoint}/guncelle/${id}`, isEmriData);
    }

    async deleteIsEmri(id) {
        return await this.delete(`${this.endpoint}/sil/${id}`);
    }

    async updateIsEmriStatus(id, status) {
        return await this.put(`${this.endpoint}/durum/${id}`, { durum: status });
    }

    async getIsEmriStats() {
        return await this.get(`${this.endpoint}/stats`);
    }
}

/**
 * Bildirim API Service
 */
class BildirimApiService extends ApiService {
    constructor() {
        super();
        this.endpoint = '/bildirim';
    }

    async getBildirimListesi() {
        return await this.get(`${this.endpoint}/liste`);
    }

    async getBildirim(id) {
        return await this.get(`${this.endpoint}/${id}`);
    }

    async markAsRead(id) {
        return await this.put(`${this.endpoint}/okundu/${id}`);
    }

    async markAllAsRead() {
        return await this.put(`${this.endpoint}/tumunu-okundu`);
    }

    async deleteBildirim(id) {
        return await this.delete(`${this.endpoint}/sil/${id}`);
    }

    async getUnreadCount() {
        return await this.get(`${this.endpoint}/okunmamis-sayi`);
    }

    async getBildirimStats() {
        return await this.get(`${this.endpoint}/stats`);
    }
}

/**
 * Cihaz Token API Service
 */
class CihazTokenApiService extends ApiService {
    constructor() {
        super();
        this.endpoint = '/device';
    }

    async saveDeviceToken(deviceToken, platform, notificationPermission = true) {
        return await this.post(`${this.endpoint}/token/save`, {
            device_token: deviceToken,
            platform: platform,
            notification_permission: notificationPermission
        });
    }

    async removeDeviceToken() {
        return await this.delete(`${this.endpoint}/token/remove`);
    }

    async getDeviceInfo() {
        return await this.get(`${this.endpoint}/info`);
    }

    async updateNotificationPermission(permission) {
        return await this.put(`${this.endpoint}/notification-permission`, {
            permission: permission
        });
    }
}

/**
 * OneSignal Ayarları API Service
 */
class OneSignalAyarlarApiService extends ApiService {
    constructor() {
        super();
        this.endpoint = '/onesignal';
    }

    async getConfig() {
        return await this.get(`${this.endpoint}/config`);
    }

    async getStatus() {
        return await this.get(`${this.endpoint}/status`);
    }
}

/**
 * Test Bildirimi API Service
 */
class TestBildirimApiService extends ApiService {
    constructor() {
        super();
        this.endpoint = '/notification';
    }

    async sendTestNotification(title = 'Test Bildirimi', message = 'Bu bir test bildirimidir.', url = null) {
        return await this.post(`${this.endpoint}/test`, {
            title: title,
            message: message,
            url: url
        });
    }

    async sendTestNotificationToAll(title = 'Toplu Test Bildirimi', message = 'Bu bir toplu test bildirimidir.', url = null) {
        return await this.post(`${this.endpoint}/test-all`, {
            title: title,
            message: message,
            url: url
        });
    }
}

/**
 * Yardımcı fonksiyonlar
 */
class ApiHelpers {
    /**
     * Para birimi formatlama
     */
    static formatMoney(value) {
        if (!value || value == 0) return '0,00 ₺';
        return new Intl.NumberFormat('tr-TR', {
            style: 'currency',
            currency: 'TRY'
        }).format(value);
    }

    /**
     * Para birimi değerini temizle (API için)
     */
    static cleanMoneyValue(value) {
        if (!value) return '0.00';
        
        // Sadece rakam ve nokta bırak
        let cleaned = value.toString().replace(/[^\d.]/g, '');
        
        // Nokta varsa sadece ilkini al
        const parts = cleaned.split('.');
        if (parts.length > 2) {
            cleaned = parts[0] + '.' + parts[1];
        }
        
        return parseFloat(cleaned).toFixed(2);
    }

    /**
     * Tarih formatlama
     */
    static formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('tr-TR');
    }

    /**
     * Form verilerini API formatına çevir
     */
    static prepareFormData(formData, moneyFields = []) {
        const apiData = {};
        
        for (let [key, value] of formData.entries()) {
            if (moneyFields.includes(key)) {
                apiData[key] = this.cleanMoneyValue(value);
            } else {
                apiData[key] = value;
            }
        }
        
        return apiData;
    }

    /**
     * Başarı mesajı göster
     */
    static showSuccess(message, duration = 5000) {
        this.showAlert(message, 'success', duration);
    }

    /**
     * Hata mesajı göster
     */
    static showError(message, duration = 5000) {
        this.showAlert(message, 'danger', duration);
    }

    /**
     * Uyarı mesajı göster
     */
    static showWarning(message, duration = 5000) {
        this.showAlert(message, 'warning', duration);
    }

    /**
     * Bilgi mesajı göster
     */
    static showInfo(message, duration = 5000) {
        this.showAlert(message, 'info', duration);
    }

    /**
     * Alert mesajı göster
     */
    static showAlert(message, type, duration = 5000) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        
        const iconMap = {
            'success': 'check-circle',
            'danger': 'cross-circle',
            'warning': 'warning',
            'info': 'information'
        };
        
        alertDiv.innerHTML = `
            <i class="ki-outline ki-${iconMap[type]} fs-2 me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Otomatik kaldır
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, duration);
    }

    /**
     * Loading göster
     */
    static showLoading(container = document.body) {
        const loadingDiv = document.createElement('div');
        loadingDiv.id = 'api-loading';
        loadingDiv.className = 'position-fixed d-flex justify-content-center align-items-center';
        loadingDiv.style.cssText = 'top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;';
        loadingDiv.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Yükleniyor...</span>
            </div>
        `;
        
        container.appendChild(loadingDiv);
    }

    /**
     * Loading gizle
     */
    static hideLoading() {
        const loadingDiv = document.getElementById('api-loading');
        if (loadingDiv) {
            loadingDiv.remove();
        }
    }
}

// Global instances oluştur
window.apiService = new ApiService();
window.userApiService = new UserApiService();
window.ciroApiService = new CiroApiService();
window.giderApiService = new GiderApiService();
window.isEmriApiService = new IsEmriApiService();
window.bildirimApiService = new BildirimApiService();
window.cihazTokenApiService = new CihazTokenApiService();
window.oneSignalAyarlarApiService = new OneSignalAyarlarApiService();
window.testBildirimApiService = new TestBildirimApiService();
window.apiHelpers = ApiHelpers; 