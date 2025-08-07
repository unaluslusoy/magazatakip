<?php require_once __DIR__ . '/../layouts/layout/header.php'; ?>

<!-- API Service -->
<script src="/app/Views/kullanici/api-service.js"></script>

<div class="d-flex flex-column flex-root app-root" id="kt_app_root">
    <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
        <?php require_once __DIR__ . '/../layouts/layout/navbar.php'; ?>
        
        <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">

            
            <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                <div class="d-flex flex-column flex-column-fluid">
                    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                                    Gider Listesi
                                </h1>
                                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                                    <li class="breadcrumb-item text-muted">
                                        <a href="/anasayfa" class="text-muted text-hover-primary">Anasayfa</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                                    </li>
                                    <li class="breadcrumb-item text-muted">Giderler</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div id="kt_app_content" class="app-content flex-column-fluid">
                        <div id="kt_app_content_container" class="app-container container-xxl">
                            <div class="card">
                                <div class="card-header border-0 pt-6">
                                    <div class="card-title">
                                        <h3>Gider Yönetimi</h3>
                                    </div>
                                    <div class="card-toolbar">
                                        <a href="/gider/ekle" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Yeni Gider Ekle
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <!-- Loading Indicator -->
                                    <div id="loading-indicator" class="alert alert-warning alert-dismissible fade show mb-4" style="display: none;">
                                        <i class="ki-outline ki-loading fs-2 me-2"></i>
                                        Veriler yükleniyor...
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                    
                                    <!-- Mesaj Alanı -->
                                    <div id="message-area"></div>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                                            <thead>
                                                <tr class="fw-bold text-muted">
                                                    <th class="w-25px">
                                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                            <input class="form-check-input" type="checkbox" value="1" data-kt-check="true" data-kt-check-target=".widget-9-check"/>
                                                        </div>
                                                    </th>
                                                    <th class="min-w-150px">Başlık</th>
                                                    <th class="min-w-140px">Kategori</th>
                                                    <th class="min-w-120px">Miktar</th>
                                                    <th class="min-w-120px">Tarih</th>
                                                    <th class="min-w-100px text-end">İşlemler</th>
                                                </tr>
                                            </thead>
                                            <tbody id="gider-tbody">
                                                <!-- Veriler API'den yüklenecek -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// API tabanlı gider yönetimi
class GiderManager {
    constructor() {
        this.apiService = window.giderApiService;
        this.init();
    }
    
    async init() {
        await this.loadGiderListesi();
        this.setupEventListeners();
    }
    
    async loadGiderListesi() {
        try {
            const loadingDiv = document.getElementById('loading-indicator');
            if (loadingDiv) loadingDiv.style.display = 'block';
            
            const response = await this.apiService.getGiderListesi();
            
            if (response.success) {
                this.renderGiderListesi(response.data);
            } else {
                this.showError('Veri yükleme hatası: ' + response.message);
            }
        } catch (error) {
            console.error('Gider listesi yükleme hatası:', error);
            this.showError('Veri yükleme hatası: ' + error.message);
        } finally {
            const loadingDiv = document.getElementById('loading-indicator');
            if (loadingDiv) loadingDiv.style.display = 'none';
        }
    }
    
    renderGiderListesi(giderListesi) {
        const tbody = document.getElementById('gider-tbody');
        if (!tbody) return;
        
        if (giderListesi.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center">
                        <div class="d-flex flex-column align-items-center py-10">
                            <i class="ki-outline ki-information-3 fs-3x text-muted mb-5"></i>
                            <span class="text-muted fw-semibold">Henüz gider kaydı bulunmuyor</span>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = giderListesi.map((gider, index) => `
            <tr class="${index % 2 == 0 ? 'table-row-light' : 'table-row-dark'}">
                <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input widget-9-check" type="checkbox" value="${gider.id}"/>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="d-flex justify-content-start flex-column">
                            <a href="#" class="text-dark fw-bold text-hover-primary fs-6">${this.escapeHtml(gider.baslik)}</a>
                            ${gider.aciklama ? `<span class="text-muted fw-semibold text-muted d-block fs-7">${this.escapeHtml(gider.aciklama)}</span>` : ''}
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge badge-light-primary fs-7 fw-bold">${this.escapeHtml(gider.kategori)}</span>
                </td>
                <td>
                    <span class="text-dark fw-bold text-hover-primary d-block fs-6">${this.formatMoney(gider.miktar)}</span>
                </td>
                <td>
                    <span class="text-muted fw-semibold text-muted d-block fs-7">${this.formatDate(gider.tarih)}</span>
                </td>
                <td class="text-end">
                    <a href="/gider/duzenle/${gider.id}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="Düzenle">
                        <i class="ki-outline ki-pencil fs-2"></i>
                    </a>
                    <button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" 
                        onclick="giderManager.deleteGider(${gider.id})" title="Sil">
                        <i class="ki-outline ki-trash fs-2"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }
    
    async deleteGider(id) {
        if (!confirm('Bu gider kaydını silmek istediğinizden emin misiniz?')) {
            return;
        }
        
        try {
            const response = await this.apiService.deleteGider(id);
            
            if (response.success) {
                this.showSuccess('Gider kaydı başarıyla silindi');
                await this.loadGiderListesi(); // Listeyi yenile
            } else {
                this.showError('Silme hatası: ' + response.message);
            }
        } catch (error) {
            console.error('Silme hatası:', error);
            this.showError('Silme hatası: ' + error.message);
        }
    }
    
    setupEventListeners() {
        // Refresh butonu ekle
        const refreshBtn = document.createElement('button');
        refreshBtn.className = 'btn btn-sm btn-outline-secondary ms-2';
        refreshBtn.innerHTML = '<i class="ki-outline ki-refresh fs-7 me-1"></i> Yenile';
        refreshBtn.onclick = () => this.loadGiderListesi();
        
        const toolbar = document.querySelector('.card-toolbar');
        if (toolbar) {
            toolbar.appendChild(refreshBtn);
        }
    }
    
    formatMoney(value) {
        if (!value || value == 0) return '0,00 ₺';
        return new Intl.NumberFormat('tr-TR', {
            style: 'currency',
            currency: 'TRY'
        }).format(value);
    }
    
    formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('tr-TR');
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    showSuccess(message) {
        this.showAlert(message, 'success');
    }
    
    showError(message) {
        this.showAlert(message, 'danger');
    }
    
    showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show mb-4`;
        alertDiv.innerHTML = `
            <i class="ki-outline ki-${type === 'success' ? 'check-circle' : 'cross-circle'} fs-2 me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const messageArea = document.getElementById('message-area');
        if (messageArea) {
            messageArea.innerHTML = '';
            messageArea.appendChild(alertDiv);
            
            // 5 saniye sonra otomatik kaldır
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    }
}

// Sayfa yüklendiğinde GiderManager'ı başlat
document.addEventListener('DOMContentLoaded', function() {
    window.giderManager = new GiderManager();
});
</script>

<?php require_once __DIR__ . '/../layouts/layout/footer.php'; ?> 