<?php
require_once __DIR__ . '/layouts/layout/header.php';
require_once __DIR__ . '/layouts/layout/navbar.php';

// Para birimi formatlama fonksiyonu
function formatMoney($value) {
    if (empty($value) || $value == 0) return '0,00 ₺';
    return number_format($value, 2, ',', '.') . ' ₺';
}
?>


<div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="container-fluid mt-3 mt-lg-5">
            
            <!-- Basit Hoş Geldin Alanı -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                        <div>
                            <h4 class="mb-1 fw-bold text-dark page-title">
                                Hoş Geldiniz!
                            </h4>
                            <p class="mb-0 text-muted">
                                <?= isset($kullanici['magaza_isim']) ? htmlspecialchars($kullanici['magaza_isim']) : 'Mağaza'; ?> Mağazası Yönetim Paneli
                            </p>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">Bugün</small>
                            <strong class="text-dark"><?= date('d.m.Y') ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- İstatistik Kartları -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="card border h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-success bg-opacity-10 rounded p-2">
                                        <i class="ki-outline ki-document text-success fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1 small">Bekleyen İşler</h6>
                                    <h4 class="fw-bold text-dark mb-0" id="bekleyen-isler"><?= $istek['acikGorevler']; ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top p-2">
                            <a href="/isemri/listesi?durum=Yeni" class="btn btn-sm btn-outline-success w-100 d-flex align-items-center justify-content-center">
                                <i class="ki-outline ki-eye fs-7 me-1"></i> Görüntüle
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="card border h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary bg-opacity-10 rounded p-2">
                                        <i class="ki-outline ki-arrows-loop text-primary fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1 small">Aktif İşler</h6>
                                    <h4 class="fw-bold text-dark mb-0" id="aktif-isler"><?= $istek['devamEdenGorevler']; ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top p-2">
                            <a href="/isemri/listesi?durum=Devam+Ediyor" class="btn btn-sm btn-outline-primary w-100 d-flex align-items-center justify-content-center">
                                <i class="ki-outline ki-eye fs-7 me-1"></i> Görüntüle
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="card border h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-success bg-opacity-10 rounded p-2">
                                        <i class="ki-outline ki-dollar text-success fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1 small">Aylık Gelir</h6>
                                    <h4 class="fw-bold text-dark mb-0" id="aylik-gelir"><?= number_format($aylikCiro, 0, ',', '.') ?> ₺</h4>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top p-2">
                            <a href="/ciro/listele" class="btn btn-sm btn-outline-success w-100 d-flex align-items-center justify-content-center">
                                <i class="ki-outline ki-eye fs-7 me-1"></i> Detaylar
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="card border h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-<?= $netKar >= 0 ? 'success' : 'danger' ?> bg-opacity-10 rounded p-2">
                                        <i class="ki-outline ki-<?= $netKar >= 0 ? 'trending-up' : 'trending-down' ?> text-<?= $netKar >= 0 ? 'success' : 'danger' ?> fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1 small">Net Ciro</h6>
                                    <h4 class="fw-bold text-dark mb-0" id="net-ciro"><?= number_format($netKar, 0, ',', '.') ?> ₺</h4>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top p-2">
                            <a href="/gider/listesi" class="btn btn-sm btn-outline-<?= $netKar >= 0 ? 'success' : 'danger' ?> w-100 d-flex align-items-center justify-content-center">
                                <i class="ki-outline ki-chart-pie fs-7 me-1"></i> Analiz
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hızlı İşlemler -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header bg-light border-bottom p-3">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="ki-outline ki-speed-1 text-primary me-2"></i>
                                Hızlı İşlemler
                            </h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-6 col-lg-3">
                                    <a href="/isemri/olustur" class="card border text-decoration-none h-100 d-flex flex-column align-items-center justify-content-center text-center p-4 hover-dark">
                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                            <i class="ki-outline ki-plus-circle text-primary fs-1"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">Yeni İş Talebi</h6>
                                        <p class="text-muted small mb-0">İş Talebi oluştur</p>
                                    </a>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <a href="/ciro/ekle" class="card border text-decoration-none h-100 d-flex flex-column align-items-center justify-content-center text-center p-4 hover-dark">
                                        <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                            <i class="ki-outline ki-dollar text-success fs-1"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">Ciro Ekle</h6>
                                        <p class="text-muted small mb-0">Gelir kaydı</p>
                                    </a>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <a href="/gider/ekle" class="card border text-decoration-none h-100 d-flex flex-column align-items-center justify-content-center text-center p-4 hover-dark">
                                        <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                            <i class="ki-outline ki-minus-circle text-danger fs-1"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">Gider Ekle</h6>
                                        <p class="text-muted small mb-0">Gider kaydı</p>
                                    </a>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <a href="/isemri/listesi" class="card border text-decoration-none h-100 d-flex flex-column align-items-center justify-content-center text-center p-4 hover-dark">
                                        <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                            <i class="ki-outline ki-notepad-edit text-warning fs-1"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2">İş Taleblerim</h6>
                                        <p class="text-muted small mb-0">Tüm listeleri görüntüle</p>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ana Bölümler -->
            <div class="row g-4">
                <!-- İş Yönetimi -->
                <div class="col-lg-6 col-12">
                    <div class="card border h-100">
                        <div class="card-header bg-light border-bottom p-3">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="ki-outline ki-element-11 text-primary me-2"></i>
                                İş Yönetimi
                            </h5>
                        </div>
                        <div class="card-body p-3">
                                <div class="d-grid gap-2">
                                <a href="/isemri/listesi" class="btn btn-light text-start p-3 border shadow-sm">
                                    <i class="ki-outline ki-notepad-edit fs-3 me-3 text-primary"></i>
                                    <div>
                                        <strong class="text-dark">İş Emri Listesi</strong>
                                        <small class="d-block text-muted">Tüm iş emirlerini görüntüle ve yönet</small>
                                    </div>
                                </a>
                                <a href="/isemri/olustur" class="btn btn-light text-start p-3 border shadow-sm">
                                    <i class="ki-outline ki-plus-circle fs-3 me-3 text-primary"></i>
                                    <div>
                                        <strong class="text-dark">Yeni Talep Oluştur</strong>
                                        <small class="d-block text-muted">Yeni talep veya istek oluştur</small>
                                    </div>
                                </a>
                             
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Finansal Yönetim -->
                <div class="col-lg-6 col-12">
                    <div class="card border h-100">
                        <div class="card-header bg-light border-bottom p-3">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="ki-outline ki-bill text-success me-2"></i>
                                Finansal Yönetim
                            </h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-grid gap-2">
                                <a href="/ciro/listele" class="btn btn-light text-start p-3 border-2 shadow-sm">
                                    <i class="ki-outline ki-chart-line-up fs-3 me-3 text-success"></i>
                                    <div>
                                        <strong class="text-dark">Gelir Listesi</strong>
                                        <small class="d-block text-muted">Tüm gelir kayıtlarını görüntüle</small>
                                    </div>
                                </a>
                                <a href="/gider/listesi" class="btn btn-light text-start p-3 border-2 shadow-sm">
                                    <i class="ki-outline ki-chart-line-down fs-3 me-3 text-danger"></i>
                                    <div>
                                        <strong class="text-dark">Gider Listesi</strong>
                                        <small class="d-block text-muted">Tüm gider kayıtlarını görüntüle</small>
                                    </div>
                                </a>
                              
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hesap ve Profil -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header bg-light border-bottom p-3">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="ki-outline ki-profile-circle text-warning me-2"></i>
                                Hesap & Profil
                            </h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-6 col-lg-3">
                                    <a href="/profil" class="btn btn-outline-warning w-100 text-start p-3">
                                        <i class="ki-outline ki-profile-user fs-3 me-2"></i>
                                        <div>
                                            <strong>Profil</strong>
                                            <small class="d-block text-muted">Kişisel bilgiler</small>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <a href="/kullanici/bildirimler" class="btn btn-outline-info w-100 text-start p-3">
                                        <i class="ki-outline ki-notification fs-3 me-2"></i>
                                        <div>
                                            <strong>Bildirimler</strong>
                                            <small class="d-block text-muted">Sistem bildirimleri</small>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <button onclick="clearBrowserCache()" class="btn btn-outline-secondary w-100 text-start p-3">
                                        <i class="ki-outline ki-refresh fs-3 me-2"></i>
                                        <div>
                                            <strong>Önbellek Temizle</strong>
                                            <small class="d-block text-muted">Tarayıcı önbelleği</small>
                                        </div>
                                    </button>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <a href="/ayarlar" class="btn btn-outline-primary w-100 text-start p-3">
                                        <i class="ki-outline ki-setting-2 fs-3 me-2"></i>
                                        <div>
                                            <strong>Ayarlar</strong>
                                            <small class="d-block text-muted">Bildirim & PWA izinleri</small>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <a href="#" onclick="performLogout()" class="btn btn-outline-danger w-100 text-start p-3" style="cursor: pointer;">
                                        <i class="ki-outline ki-exit-right fs-3 me-2"></i>
                                        <div>
                                            <strong>Çıkış Yap</strong>
                                            <small class="d-block text-muted">Güvenli çıkış</small>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
        </div>
    </div>
</div>
<div class="d-md-none" >
  <div class="bg-white border-top d-flex justify-content-around align-items-center py-2 shadow-sm guncelleme-zamani">
    
  </div>
</div>

<script>
// API tabanlı dashboard yönetimi
class DashboardManager {
    constructor() {
        this.apiService = window.userApiService;
        this.init();
    }
    
    async init() {
        await this.loadDashboardStats();
        if (typeof this.setupEventListeners === 'function') {
            this.setupEventListeners();
        }
        this.setupAutoRefresh();
    }
    
    async loadDashboardStats() {
        try {
            console.log('Dashboard istatistikleri yükleniyor...');
            const response = await this.apiService.getDashboardStats();
            
            if (response.success) {
                console.log('Dashboard verileri:', response.data);
                this.updateDashboardStats(response.data);
            } else {
                console.error('Dashboard istatistikleri yüklenemedi:', response.message);
            }
        } catch (error) {
            console.error('Dashboard istatistikleri yükleme hatası:', error);
        }
    }
    
    updateDashboardStats(stats) {
        // İstatistik kartlarını güncelle
        this.updateStatCard('bekleyen-isler', stats.bekleyenIsler || 0, false);
        this.updateStatCard('aktif-isler', stats.aktifIsler || 0, false);
        this.updateStatCard('aylik-gelir', stats.aylikGelir || 0, true);
        this.updateStatCard('net-ciro', stats.netCiro || 0, true);
        
        // Sayfa başlığına son güncelleme zamanını ekle
        this.updateLastRefreshTime();
    }
    
    updateStatCard(elementId, value, isMoney = false) {
        const element = document.getElementById(elementId);
        if (element) {
            if (isMoney) {
                element.textContent = this.formatMoney(value);
            } else {
                element.textContent = value.toString();
            }
            
            // Güncelleme animasyonu
            element.style.animation = 'fadeIn 0.5s ease-in-out';
            setTimeout(() => {
                element.style.animation = '';
            }, 500);
        }
    }
    
    formatMoney(value) {
        if (!value || value == 0) return '0,00 ₺';
        return new Intl.NumberFormat('tr-TR', {
            style: 'currency',
            currency: 'TRY'
        }).format(value);
    }
    
    updateLastRefreshTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('tr-TR');
        
        // Son güncelleme zamanını göster
        let refreshIndicator = document.getElementById('last-refresh-time');
        if (!refreshIndicator) {
            refreshIndicator = document.createElement('small');
            refreshIndicator.id = 'last-refresh-time';
            refreshIndicator.className = 'text-muted ms-2';
            document.querySelector('.guncelleme-zamani')?.appendChild(refreshIndicator);
        }
        refreshIndicator.textContent = `Son güncelleme: ${timeString}`;
    }
    
   
    
    setupAutoRefresh() {
        // Her 30 saniyede bir otomatik yenile
        setInterval(() => {
            this.loadDashboardStats();
        }, 30000);
    }

    renderCharts(stats) {
        try {
            if (window.Chart) {
                const ctx1 = document.getElementById('chartCiro');
                const ctx2 = document.getElementById('chartGider');
                if (ctx1 && !ctx1._chart) {
                    ctx1._chart = new Chart(ctx1, {
                        type: 'bar',
                        data: {
                            labels: ['Bugün','Bu Ay'],
                            datasets: [{
                                label: 'Ciro',
                                backgroundColor: 'rgba(25,135,84,.35)',
                                borderColor: 'rgba(25,135,84,1)',
                                data: [stats.bugunGelir || 0, stats.aylikGelir || 0]
                            }]
                        },
                        options: {responsive: true, maintainAspectRatio: false}
                    });
                }
                if (ctx2 && !ctx2._chart) {
                    ctx2._chart = new Chart(ctx2, {
                        type: 'bar',
                        data: {
                            labels: ['Bugün','Bu Ay'],
                            datasets: [{
                                label: 'Gider',
                                backgroundColor: 'rgba(220,53,69,.35)',
                                borderColor: 'rgba(220,53,69,1)',
                                data: [stats.bugunGider || 0, stats.aylikGider || 0]
                            }]
                        },
                        options: {responsive: true, maintainAspectRatio: false}
                    });
                }
            }
        } catch (e) { console.log('Chart render skipped:', e); }
    }

    renderLatest(items = []) {
        const tbody = document.getElementById('table-latest');
        const cards = document.getElementById('card-latest');
        if (!tbody || !cards) return;
        if (!items.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Kayıt bulunamadı</td></tr>';
            cards.innerHTML = '<div class="text-center text-muted py-3">Kayıt bulunamadı</div>';
            return;
        }
        tbody.innerHTML = items.map(it => `
            <tr>
                <td>${it.tarih}</td>
                <td>${it.tur}</td>
                <td>${it.baslik}</td>
                <td class="text-end">${it.tutar}</td>
                <td class="text-end"><a href="${it.url}" class="btn btn-sm btn-light">Detay</a></td>
            </tr>
        `).join('');
        cards.innerHTML = items.map(it => `
            <div class="card mb-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">${it.baslik}</div>
                        <small class="text-muted">${it.tarih} • ${it.tur}</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">${it.tutar}</div>
                        <a href="${it.url}" class="btn btn-sm btn-light mt-1">Detay</a>
                    </div>
                </div>
            </div>
        `).join('');
    }
}

// Sayfa yüklendiğinde DashboardManager'ı başlat
document.addEventListener('DOMContentLoaded', function() {
    window.dashboardManager = new DashboardManager();
    // Pull-to-refresh entegrasyonu & butonlar
    window.refreshPageData = async function() {
        if (window.dashboardManager) {
            await window.dashboardManager.loadDashboardStats();
        }
    }
    const tableRefresh = document.getElementById('btn-table-refresh');
    const mobRefresh = document.getElementById('mob-refresh');
    [tableRefresh, mobRefresh].forEach(el => el && (el.onclick = () => window.refreshPageData()));
});

// CSS animasyonu ekle
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0.5; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    
    .stat-card-updated {
        animation: fadeIn 0.5s ease-in-out;
    }
`;
document.head.appendChild(style);

function clearBrowserCache() {
    if (confirm('Tarayıcı önbelleğini temizlemek istediğinizden emin misiniz? Bu işlem sayfayı yenileyecektir.')) {
        const timestamp = new Date().getTime();
        const currentUrl = window.location.href;
        const separator = currentUrl.includes('?') ? '&' : '?';
        const newUrl = currentUrl + separator + '_cache=' + timestamp;
        window.location.href = newUrl;
    }
}

function performLogout() {
    if (confirm('Çıkış yapmak istediğinizden emin misiniz?')) {
        window.location.href = '/auth/logout';
    }
}
</script>

<!-- Filtre Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasFilters">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Filtreler</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <div class="mb-3">
      <label class="form-label">Tarih Aralığı</label>
      <input type="date" class="form-control mb-2" id="filter-start">
      <input type="date" class="form-control" id="filter-end">
    </div>
    <div class="mb-3">
      <label class="form-label">Tür</label>
      <select class="form-select" id="filter-type">
        <option value="">Tümü</option>
        <option value="ciro">Ciro</option>
        <option value="gider">Gider</option>
        <option value="isemri">İş Emri</option>
      </select>
    </div>
    <div class="d-grid">
      <button class="btn btn-primary" id="btn-apply-filters"><i class="ki-outline ki-filter me-1"></i>Uygula</button>
    </div>
  </div>
</div>
<?php
require_once __DIR__ . '/layouts/layout/footer.php';
?>





