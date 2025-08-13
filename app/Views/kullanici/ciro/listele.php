<?php
require_once __DIR__ . '/../layouts/layout/header.php';
require_once __DIR__ . '/../layouts/layout/navbar.php';

// Önbellek sorununu önlemek için unique timestamp
$timestamp = time();
?>

    <!-- Önbellek önleme meta tag'leri -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate, max-age=0, private">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="-1">
    <meta http-equiv="Last-Modified" content="<?= gmdate('D, d M Y H:i:s') ?> GMT">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- API Service header'da yükleniyor -->
    
    <script>
        // API tabanlı ciro yönetimi
        class CiroManager {
            constructor() {
                this.apiService = window.ciroApiService;
                this.init();
            }
            
            async init() {
                await this.loadCiroListesi();
                this.setupEventListeners();
            }
            
            async loadCiroListesi() {
                try {
                    const loadingDiv = document.getElementById('loading-indicator');
                    if (loadingDiv) loadingDiv.style.display = 'block';
                    
                    const response = await this.apiService.getCiroListesi();
                    
                    if (response.success) {
                        this.renderCiroListesi(response.data);
                        this.updateDebugInfo(response.count, response.timestamp);
                    } else {
                        this.showError('Veri yükleme hatası: ' + response.message);
                    }
                } catch (error) {
                    console.error('Ciro listesi yükleme hatası:', error);
                    this.showError('Veri yükleme hatası: ' + error.message);
                } finally {
                    const loadingDiv = document.getElementById('loading-indicator');
                    if (loadingDiv) loadingDiv.style.display = 'none';
                }
            }
            
            renderCiroListesi(ciroListesi) {
                const tbody = document.querySelector('#kt_ecommerce_products_table tbody');
                if (!tbody) return;
                
                if (ciroListesi.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td class="text-center" colspan="20">
                                <div class="d-flex flex-column align-items-center py-10">
                                    <i class="ki-outline ki-information-3 fs-3x text-muted mb-5"></i>
                                    <span class="text-muted fw-semibold">Henüz ciro kaydı bulunmuyor</span>
                                </div>
                            </td>
                        </tr>
                    `;
                    return;
                }
                
                tbody.innerHTML = ciroListesi.map((ciro, index) => `
                    <tr class="${index % 2 == 0 ? 'table-row-light' : 'table-row-dark'}">
                        <td>
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" value="${ciro.id}">
                            </div>
                        </td>
                        <td class="text-start">${ciro.magaza_adi || ciro.magaza_id}</td>
                        <td class="text-end">${this.formatDate(ciro.ekleme_tarihi)}</td>
                        <td class="text-end">${this.formatDate(ciro.gun)}</td>
                        <td class="text-end fw-bold text-success">${this.formatMoney(ciro.nakit)}</td>
                        <td class="text-end fw-bold text-primary">${this.formatMoney(ciro.kredi_karti)}</td>
                        <td class="text-end">${this.formatMoney(ciro.carliston)}</td>
                        <td class="text-end">${this.formatMoney(ciro.getir_carsi)}</td>
                        <td class="text-end">${this.formatMoney(ciro.trendyolgo)}</td>
                        <td class="text-end">${this.formatMoney(ciro.multinet)}</td>
                        <td class="text-end">${this.formatMoney(ciro.sodexo)}</td>
                        <td class="text-end">${this.formatMoney(ciro.edenred)}</td>
                        <td class="text-end">${this.formatMoney(ciro.setcard)}</td>
                        <td class="text-end">${this.formatMoney(ciro.tokenflex)}</td>
                        <td class="text-end">${this.formatMoney(ciro.iwallet)}</td>
                        <td class="text-end">${this.formatMoney(ciro.metropol)}</td>
                        <td class="text-end">${this.formatMoney(ciro.ticket)}</td>
                        <td class="text-end">${this.formatMoney(ciro.didi)}</td>
                        <td class="text-end fw-bold fs-6 text-success">${this.formatMoney(ciro.toplam)}</td>
                        <td class="text-end">
                            <a href="/ciro/duzenle/${ciro.id}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="Düzenle">
                                <i class="ki-outline ki-pencil fs-2"></i>
                            </a>
                            <button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" 
                                onclick="ciroManager.deleteCiro(${ciro.id})" title="Sil">
                                <i class="ki-outline ki-trash fs-2"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
            
            updateDebugInfo(count, timestamp) {
                // Debug bilgisini kaldır - kullanıcı görmesin
                const debugDiv = document.getElementById('debug-info');
                if (debugDiv) {
                    debugDiv.innerHTML = '';
                }
            }
            
            async deleteCiro(id) {
                if (!confirm('Bu ciro kaydını silmek istediğinizden emin misiniz?')) {
                    return;
                }
                
                try {
                    const response = await this.apiService.deleteCiro(id);
                    
                    if (response.success) {
                        this.showSuccess('Ciro kaydı başarıyla silindi');
                        await this.loadCiroListesi(); // Listeyi yenile
                    } else {
                        this.showError('Silme hatası: ' + response.message);
                    }
                } catch (error) {
                    console.error('Silme hatası:', error);
                    this.showError('Silme hatası: ' + error.message);
                }
            }
            
            setupEventListeners() {
                // Refresh butonu
                const refreshBtn = document.querySelector('button[onclick="forceRefresh()"]');
                if (refreshBtn) {
                    refreshBtn.onclick = () => this.loadCiroListesi();
                }
            }
            
            formatMoney(value) {
                if (!value || value == 0) return '0,00 ₺';
                return new Intl.NumberFormat('tr-TR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(value) + ' ₺';
            }
            
            formatDate(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString);
                return date.toLocaleDateString('tr-TR');
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
                
                const container = document.querySelector('.card-body');
                if (container) {
                    container.insertBefore(alertDiv, container.firstChild);
                    
                    // 5 saniye sonra otomatik kaldır
                    setTimeout(() => {
                        alertDiv.remove();
                    }, 5000);
                }
            }
        }
        
        // Sayfa yüklendiğinde CiroManager'ı başlat
        document.addEventListener('DOMContentLoaded', function() {
            window.ciroManager = new CiroManager();
            // Pull-to-refresh entegrasyonu
            window.refreshPageData = async function() {
                if (window.ciroManager) {
                    await window.ciroManager.loadCiroListesi();
                }
            }
        });
        
        // Eski fonksiyonları kaldır
        function forceRefresh() {
            if (window.ciroManager) {
                window.ciroManager.loadCiroListesi();
            }
        }
    </script>

<?php
// Para birimi formatlama fonksiyonu
function formatMoney($value) {
    if (empty($value) || $value == 0) return '0,00 ₺';
    return number_format($value, 2, ',', '.') . ' ₺';
}
?>
<div class="app-wrapper  flex-column flex-row-fluid " id="kt_app_wrapper">
    <!--begin::Main-->
    <div class="app-main flex-column flex-row-fluid " id="kt_app_main">
        <!--begin::Content wrapper-->

        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6 ">
                <!--begin::Toolbar container-->
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <!--begin::Page title-->
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3 ">
                        <!--begin::Title-->
                        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                            Ciro işlemleri
                        </h1>
                        <!--end::Title-->
                        <!--begin::Breadcrumb-->
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <!--begin::Item-->
                            <li class="breadcrumb-item text-muted">
                                <a href="/anasayfa" class="text-muted text-hover-primary">
                                    Anasayfa
                                </a>
                            </li>
                            <!--end::Item-->
                            <!--begin::Item-->
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-500 w-5px h-2px"></span>
                            </li>
                            <!--end::Item-->
                            <!--begin::Item-->
                            <li class="breadcrumb-item text-muted">
                                Ciro işlemleri
                            </li>
                            <!--end::Item-->
                        </ul>
                        <!--end::Breadcrumb-->
                    </div>
                    <!--end::Page title-->
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        <!--begin::Refresh button-->
                        <button type="button" class="btn btn-sm fw-bold btn-outline-secondary" onclick="forceRefresh()">
                            <i class="fas fa-sync-alt"></i> Zorla Yenile
                        </button>
                        <!--end::Refresh button-->
                        <!--begin::Primary button-->
                        <a href="/ciro/ekle" class="btn btn-sm fw-bold btn-primary" >
                            Ciro Ekle
                        </a>
                        <!--end::Primary button-->
                    </div>

                </div>
                <!--end::Toolbar container-->
             </div>
            <!--end::Toolbar-->
            <!--begin::Content-->
            <div id="kt_app_content" class="app-content  flex-column-fluid ">
                <!--begin::Content container-->
                <div id="kt_app_content_container" class="app-container  container-xxl ">
                    <!--begin::Products-->
                    <div class="card card-flush">
                        <!--begin::Card header-->
                        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                            <!--begin::Card title-->
                            <div class="card-title">

                            </div>
                            <!--end::Card title-->
                            <!--begin::Card toolbar-->
                            <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                                <div class="w-100 mw-150px">
                                    <!--begin::Select2-->
                                    <select class="form-select form-select-solid select2-hidden-accessible" data-control="select2" data-hide-search="true" data-placeholder="Status" data-kt-ecommerce-product-filter="status" data-select2-id="select2-data-9-lmpj" tabindex="-1" aria-hidden="true" data-kt-initialized="1">
                                        <option data-select2-id="select2-data-11-ja9s"></option>
                                        <option value="all">All</option>
                                        <option value="published">Published</option>
                                        <option value="scheduled">Scheduled</option>
                                        <option value="inactive">Inactive</option>
                                    </select><span class="select2 select2-container select2-container--bootstrap5" dir="ltr" data-select2-id="select2-data-10-yvq2" style="width: 100%;"><span class="selection"><span class="select2-selection select2-selection--single form-select form-select-solid" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-disabled="false" aria-labelledby="select2-gwdi-container" aria-controls="select2-gwdi-container"><span class="select2-selection__rendered" id="select2-gwdi-container" role="textbox" aria-readonly="true" title="Status"><span class="select2-selection__placeholder">Status</span></span><span class="select2-selection__arrow" role="presentation"><b role="presentation"></b></span></span></span><span class="dropdown-wrapper" aria-hidden="true"></span></span>
                                    <!--end::Select2-->
                                </div>
                            </div>
                            <!--end::Card toolbar-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!-- Loading Indicator -->
                            <div id="loading-indicator" class="alert alert-warning alert-dismissible fade show mb-4" style="display: none;">
                                <i class="ki-outline ki-loading fs-2 me-2"></i>
                                Veriler yükleniyor...
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            
                            <!-- Debug Bilgisi -->
                            <div id="debug-info"></div>
                            
                            <!-- Mesaj -->
                            <?php if (isset($_SESSION['message'])) : ?>
                                <div class="alert alert-<?= $_SESSION['message_type'] ?? 'info' ?> alert-dismissible fade show mb-4">
                                    <i class="ki-outline ki-<?= $_SESSION['message_type'] == 'success' ? 'check-circle' : 'cross-circle' ?> fs-2 me-2"></i>
                                    <?= htmlspecialchars($_SESSION['message']) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
                            <?php endif; ?>
                            <!--begin::Table-->
                            <div id="kt_ecommerce_products_table_wrapper" class="dt-container dt-bootstrap5 dt-empty-footer">
                                <div id="" class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_ecommerce_products_table">
                                        <thead>
                                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0" role="row">
                                            <th class="w-10px pe-2 dt-orderable-none" data-dt-column="0" rowspan="1" colspan="1" aria-label="">
                                                <span class="dt-column-title">
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_ecommerce_products_table .form-check-input" value="1">
                                                    </div>
                                                </span>
                                                <span class="dt-column-order"></span>
                                            </th>
                                            <th class="min-w-120px dt-orderable-asc dt-orderable-desc" data-dt-column="1" rowspan="1" colspan="1" aria-label="Product: Activate to sort" tabindex="0">
                                                <span class="dt-column-title" role="button">Şube</span>
                                                <span class="dt-column-order"></span>
                                            </th>
                                            <th class="text-end min-w-120px dt-type-numeric dt-orderable-asc dt-orderable-desc" data-dt-column="2" rowspan="1" colspan="1" aria-label="SKU: Activate to sort" tabindex="0">
                                                <span class="dt-column-title" role="button">Ekleme Tarihi</span>
                                                <span class="dt-column-order"></span>
                                            </th>
                                            <th class="text-end min-w-120px dt-type-numeric dt-orderable-asc dt-orderable-desc" data-dt-column="3" rowspan="1" colspan="1" aria-label="Qty: Activate to sort" tabindex="0">
                                                <span class="dt-column-title" role="button">Gün</span>
                                                <span class="dt-column-order"></span>
                                            </th>
                                            <th class="text-end min-w-120px dt-type-numeric dt-orderable-asc dt-orderable-desc" data-dt-column="4" rowspan="1" colspan="1" aria-label="Price: Activate to sort" tabindex="0">
                                                <span class="dt-column-title" role="button">Nakit</span>
                                                <span class="dt-column-order"> </span>
                                            </th>
                                            <th class="text-end min-w-120px dt-orderable-asc dt-orderable-desc" data-dt-column="5" rowspan="1" colspan="1" aria-label="Rating: Activate to sort" tabindex="0">
                                                <span class="dt-column-title" role="button">Kredi Kartı</span>
                                                <span class="dt-column-order"></span>
                                            </th>
                                            <th class="text-end min-w-120px dt-orderable-asc dt-orderable-desc" data-dt-column="6" rowspan="1" colspan="1" aria-label="Status: Activate to sort" tabindex="0">
                                                <span class="dt-column-title" role="button">YemekSepeti</span>
                                                <span class="dt-column-order"></span>
                                            </th>
                                            <th class="text-end min-w-120px dt-orderable-asc dt-orderable-desc" data-dt-column="7" rowspan="1" colspan="1" aria-label="Status: Activate to sort" tabindex="0">
                                                <span class="dt-column-title" role="button">Getir Çarşı</span>
                                                <span class="dt-column-order"></span>
                                            </th>
                                            <th class="text-end min-w-120px dt-orderable-asc dt-orderable-desc" data-dt-column="8" rowspan="1" colspan="1" aria-label="Status: Activate to sort" tabindex="0">
                                                <span class="dt-column-title" role="button">TrendyolGO</span>
                                                <span class="dt-column-order"></span>
                                            </th>
                                            <th class="text-end min-w-120px">Multinet</th>
                                            <th class="text-end min-w-120px">Sodexo</th>
                                            <th class="text-end min-w-120px">Edenred</th>
                                            <th class="text-end min-w-120px">Setcard</th>
                                            <th class="text-end min-w-120px">Tokenflex</th>
                                            <th class="text-end min-w-120px">iWallet</th>
                                            <th class="text-end min-w-120px">Metropol</th>
                                            <th class="text-end min-w-120px">Ticket</th>
                                            <th class="text-end min-w-120px">Didi</th>
                                            <th class="text-end min-w-120px dt-orderable-asc dt-orderable-desc" data-dt-column="9" rowspan="1" colspan="1" aria-label="Status: Activate to sort" tabindex="0">
                                                <span class="dt-column-title" role="button">Toplam</span>
                                                <span class="dt-column-order"></span>
                                            </th>
                                            <th class="text-end min-w-100px dt-orderable-none" data-dt-column="10" rowspan="1" colspan="1" aria-label="Actions">
                                                <span class="dt-column-title">Aksiyon</span>
                                                <span class="dt-column-order"></span>
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600">

                                        <?php if (!empty($ciroListesi)) : ?>
                                            <?php foreach ($ciroListesi as $index => $ciro) : ?>
                                                <tr class="<?= $index % 2 == 0 ? 'table-row-light' : 'table-row-dark' ?>">
                                                    <td>
                                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                            <input class="form-check-input" type="checkbox" value="<?= $ciro['id'] ?>">
                                                        </div>
                                                    </td>
                                                    <td class="text-start"><?= htmlspecialchars($ciro['magaza_adi'] ?? $ciro['magaza_id']) ?></td>
                                                    <td class="text-end"><?= date('d.m.Y', strtotime($ciro['ekleme_tarihi'])) ?></td>
                                                    <td class="text-end"><?= date('d.m.Y', strtotime($ciro['gun'])) ?></td>
                                                    <td class="text-end fw-bold text-success"><?= formatMoney($ciro['nakit']) ?></td>
                                                    <td class="text-end fw-bold text-primary"><?= formatMoney($ciro['kredi_karti']) ?></td>
                                                    <td class="text-end"><?= formatMoney($ciro['carliston']) ?></td>
                                                    <td class="text-end"><?= formatMoney($ciro['getir_carsi']) ?></td>
                                                    <td class="text-end"><?= formatMoney($ciro['trendyolgo']) ?></td>
                                                    <td class="text-end"><?= formatMoney($ciro['multinet']) ?></td>
                                                    <td class="text-end"><?= formatMoney($ciro['sodexo']) ?></td>
                                                    <td class="text-end"><?= formatMoney($ciro['edenred']) ?></td>
                                                    <td class="text-end"><?= formatMoney($ciro['setcard']) ?></td>
                                                    <td class="text-end"><?= formatMoney($ciro['tokenflex']) ?></td>
                                                    <td class="text-end"><?= formatMoney($ciro['iwallet']) ?></td>
                                                    <td class="text-end"><?= formatMoney($ciro['metropol']) ?></td>
                                                    <td class="text-end"><?= formatMoney($ciro['ticket']) ?></td>
                                                    <td class="text-end"><?= formatMoney($ciro['didi']) ?></td>
                                                    <td class="text-end fw-bold fs-6 text-success"><?= formatMoney($ciro['toplam']) ?></td>
                                                    <td class="text-end">
                                                        <a href="/ciro/duzenle/<?= $ciro['id'] ?>" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="Düzenle">
                                                            <i class="ki-outline ki-pencil fs-2"></i>
                                                        </a>
                                                        <a href="/ciro/sil/<?= $ciro['id'] ?>" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" 
                                                            onclick="return confirm('Bu ciro kaydını silmek istediğinizden emin misiniz?')" title="Sil">
                                                             <i class="ki-outline ki-trash fs-2"></i>
                                                         </a>
                                                     </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td class="text-center" colspan="20">
                                                    <div class="d-flex flex-column align-items-center py-10">
                                                        <i class="ki-outline ki-information-3 fs-3x text-muted mb-5"></i>
                                                        <span class="text-muted fw-semibold">Henüz ciro kaydı bulunmuyor</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                        </tbody>
                                        <tfoot>

                                        </tfoot>
                                    </table>
                                </div>
                                <div id="" class="row">
                                    <div id="" class="col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start dt-toolbar"><div>
                                            <select name="kt_ecommerce_products_table_length" aria-controls="kt_ecommerce_products_table" class="form-select form-select-solid form-select-sm" id="dt-length-0">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select>
                                            <label for="dt-length-0"></label>
                                        </div>
                                    </div>
                                    <div id="" class="col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end">
                                        <div class="dt-paging paging_simple_numbers">
                                            <ul class="pagination">
                                                <li class="dt-paging-button page-item disabled">
                                                    <a class="page-link previous" aria-controls="kt_ecommerce_products_table" aria-disabled="true" aria-label="Previous" data-dt-idx="previous" tabindex="-1"><i class="previous"></i></a>
                                                </li>
                                                <li class="dt-paging-button page-item active">
                                                    <a href="#" class="page-link" aria-controls="kt_ecommerce_products_table" aria-current="page" data-dt-idx="0" tabindex="0">1</a>
                                                </li>
                                                <li class="dt-paging-button page-item">
                                                    <a href="#" class="page-link next" aria-controls="kt_ecommerce_products_table" aria-label="Next" data-dt-idx="next" tabindex="0"><i class="next"></i></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Table-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Products-->
                </div>
                <!--end::Content container-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Content wrapper-->
    </div>
    <!--End::Main-->
</div>

<style>
.table-row-light {
    background-color: #f8f9fa;
}
.table-row-dark {
    background-color: #ffffff;
}
.table-row-light:hover,
.table-row-dark:hover {
    background-color: #e9ecef !important;
}

/* Para birimi değerlerinin alt satıra kaymaması için */
#kt_ecommerce_products_table th,
#kt_ecommerce_products_table td {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding: 0.75rem 0.5rem;
}

/* Para birimi sütunları için özel genişlik */
#kt_ecommerce_products_table th.text-end,
#kt_ecommerce_products_table td.text-end {
    min-width: 120px;
    max-width: 120px;
}

/* Şube sütunu için daha geniş alan */
#kt_ecommerce_products_table th:first-child + th,
#kt_ecommerce_products_table td:first-child + td {
    min-width: 150px;
    max-width: 200px;
    white-space: normal;
    word-wrap: break-word;
}

/* Tarih sütunları için */
#kt_ecommerce_products_table th:nth-child(3),
#kt_ecommerce_products_table th:nth-child(4),
#kt_ecommerce_products_table td:nth-child(3),
#kt_ecommerce_products_table td:nth-child(4) {
    min-width: 110px;
    max-width: 110px;
}

/* Toplam sütunu için daha geniş alan */
#kt_ecommerce_products_table th:nth-last-child(2),
#kt_ecommerce_products_table td:nth-last-child(2) {
    min-width: 130px;
    max-width: 130px;
    font-weight: bold;
}

/* Aksiyon sütunu için */
#kt_ecommerce_products_table th:last-child,
#kt_ecommerce_products_table td:last-child {
    min-width: 100px;
    max-width: 100px;
}

/* Tablo responsive yapısı */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* Mobilde daha iyi görünüm */
@media (max-width: 768px) {
    #kt_ecommerce_products_table th,
    #kt_ecommerce_products_table td {
        min-width: 100px;
        font-size: 0.875rem;
        padding: 0.5rem 0.25rem;
    }
}
</style>

<!-- Mobil sabit aksiyon çubuğu -->
<div class="d-md-none" style="position: fixed; bottom: 0; left: 0; right: 0; z-index: 1030;">
    <div class="bg-white border-top d-flex justify-content-around align-items-center py-2 shadow-sm">
        <button type="button" class="btn btn-light d-flex flex-column align-items-center" onclick="window.refreshPageData && window.refreshPageData()">
            <i class="ki-outline ki-refresh fs-2"></i>
            <small>Yenile</small>
        </button>
        <a href="/ciro/ekle" class="btn btn-primary d-flex flex-column align-items-center">
            <i class="ki-outline ki-plus fs-2"></i>
            <small>Ekle</small>
        </a>
    </div>
    <!-- Alt çubuk yüksekliği için boşluk -->
    <div style="height: 64px; background: transparent;"></div>
 </div>

<?php require_once __DIR__ . '/../layouts/layout/footer.php';?>

