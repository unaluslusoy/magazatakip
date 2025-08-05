<?php
$title = "<h2>Personel Yönetimi</h2>";
$link = "Personeller";
require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>

<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    <i class="ki-duotone ki-people fs-1 text-primary me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                        <span class="path5"></span>
                    </i>
                    Personel Yönetimi
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="/admin" class="text-muted text-hover-primary">Anasayfa</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Personeller</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="/admin/personel_ekle" class="btn btn-sm btn-primary">
                    <i class="ki-duotone ki-plus fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Yeni Personel
                </a>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header border-0 pt-6">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <!--begin::Search-->
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <input type="text" data-kt-personel-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="Personel ara" />
                        </div>
                        <!--end::Search-->
                    </div>
                    <!--begin::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body py-4">
                    <!--begin::Table-->
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_personeller">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="w-10px pe-2">
                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                        <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_table_personeller .form-check-input" value="1" />
                                    </div>
                                </th>
                                <th class="min-w-125px">Personel</th>
                                <th class="min-w-125px">Email</th>
                                <th class="min-w-125px">Telefon</th>
                                <th class="min-w-125px">Pozisyon</th>
                                <th class="min-w-125px">Başlangıç Tarihi</th>
                                <th class="text-end min-w-100px">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold" id="personelTableBody">
                            <tr>
                                <td colspan="7" class="text-center py-10">
                                    <div class="d-flex flex-column flex-center">
                                        <span class="loader"></span>
                                        <div class="text-gray-400 fs-6 fw-semibold mt-5">Personeller yükleniyor...</div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <!--end::Table-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Personeller sayfası başlatıldı');
    
    let dataTable;
    
    // Personelleri yükle
    loadPersoneller();
    
    /**
     * Personelleri veritabanından getir
     */
    function loadPersoneller() {
        console.log('📡 Veritabanından personel listesi getiriliyor...');
        
        fetch('/admin/personel/liste-json', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ Veritabanından gelen personel verileri:', data);
            
            if (data.success) {
                if (data.data && data.data.length > 0) {
                    populateTable(data.data);
                    initializeDataTable();
                    console.log(`📊 ${data.data.length} personel kaydı yüklendi`);
                } else {
                    showEmptyState();
                }
            } else {
                showError(data.message || 'Personeller yüklenemedi');
            }
        })
        .catch(error => {
            console.error('❌ Personel yükleme hatası:', error);
            showError(`Veritabanı bağlantı hatası: ${error.message}`);
        });
    }
    
    /**
     * Boş durumu göster
     */
    function showEmptyState() {
        const tbody = document.getElementById('personelTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-10">
                    <div class="d-flex flex-column flex-center">
                        <i class="ki-duotone ki-questionnaire-tablet fs-5x text-primary mb-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <div class="text-gray-400 fs-6 fw-semibold mb-3">Henüz personel kaydı bulunmuyor</div>
                        <a href="/admin/personel_ekle" class="btn btn-primary">
                            <i class="ki-duotone ki-plus fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            İlk Personeli Ekle
                        </a>
                    </div>
                </td>
            </tr>
        `;
    }
    
    /**
     * Tabloyu personel verileri ile doldur
     */
    function populateTable(personeller) {
        const tbody = document.getElementById('personelTableBody');
        tbody.innerHTML = '';
        
        personeller.forEach(personel => {
            const row = createPersonelRow(personel);
            tbody.appendChild(row);
        });
    }
    
    /**
     * Personel satırı oluştur (Kapsamlı veritabanı alanları ile)
     */
    function createPersonelRow(personel) {
        const tr = document.createElement('tr');
        
        // Başlangıç tarihi
        const baslangicTarihi = personel.ise_baslama_tarihi ? 
            new Date(personel.ise_baslama_tarihi).toLocaleDateString('tr-TR') : 
            '<span class="text-muted">-</span>';
        
        // Çalışan durumu badge
        let durumBadge = '';
        if (personel.durum == 1) {
            durumBadge = '<div class="badge badge-light-success">Aktif</div>';
        } else if (personel.durum == 0) {
            durumBadge = '<div class="badge badge-light-danger">Pasif</div>';
        }
        
        // Tam ad ve baş harfler
        const tamAd = `${personel.ad || ''} ${personel.soyad || ''}`.trim();
        const adBasHarf = (personel.ad || '').charAt(0).toUpperCase();
        const soyadBasHarf = (personel.soyad || '').charAt(0).toUpperCase();
        const basHarfler = adBasHarf + soyadBasHarf || 'PN';
        
        // Renk paleti (her personel için farklı renk)
        const renkPaleti = [
            { bg: 'bg-light-primary', text: 'text-primary' },
            { bg: 'bg-light-success', text: 'text-success' },
            { bg: 'bg-light-info', text: 'text-info' },
            { bg: 'bg-light-warning', text: 'text-warning' },
            { bg: 'bg-light-danger', text: 'text-danger' },
            { bg: 'bg-light-dark', text: 'text-dark' },
            { bg: 'bg-light-secondary', text: 'text-secondary' },
            { bg: 'bg-primary bg-opacity-10', text: 'text-primary' },
            { bg: 'bg-success bg-opacity-10', text: 'text-success' },
            { bg: 'bg-info bg-opacity-10', text: 'text-info' }
        ];
        
        // Personel ID'sine göre renk seç
        const renkIndex = (personel.id || 0) % renkPaleti.length;
        const secilenRenk = renkPaleti[renkIndex];
        
        // Çalışan numarası
        const calisanNo = personel.calisan_no ? `Çalışan #${personel.calisan_no}` : `ID: ${personel.id}`;
        
        // Departman + Pozisyon
        let pozisyonBilgi = '';
        if (personel.departman && personel.pozisyon) {
            pozisyonBilgi = `${personel.departman} - ${personel.pozisyon}`;
        } else if (personel.pozisyon) {
            pozisyonBilgi = personel.pozisyon;
        } else if (personel.departman) {
            pozisyonBilgi = personel.departman;
        } else {
            pozisyonBilgi = '-';
        }
        
        tr.innerHTML = `
            <td>
                <div class="form-check form-check-sm form-check-custom form-check-solid">
                    <input class="form-check-input" type="checkbox" value="${personel.id}" />
                </div>
            </td>
            <td class="d-flex align-items-center">
                <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                    <div class="symbol-label">
                        <div class="symbol-label fs-3 ${secilenRenk.bg} ${secilenRenk.text}">${basHarfler}</div>
                    </div>
                </div>
                <div class="d-flex flex-column">
                    <a href="/admin/personel/detay/${personel.id}" class="text-gray-800 text-hover-primary mb-1 fw-bold">${tamAd}</a>
                    <span class="text-muted fw-semibold text-muted d-block fs-7">${calisanNo}</span>
                </div>
            </td>
            <td>
                <a href="mailto:${personel.eposta || ''}" class="text-gray-600 text-hover-primary mb-1">${personel.eposta || '-'}</a>
            </td>
            <td>
                <div class="text-gray-600">${personel.telefon || personel.cep_telefonu || '-'}</div>
            </td>
            <td>
                <div class="d-flex flex-column">
                    <div class="badge badge-light-info fw-bold mb-1">${pozisyonBilgi}</div>
                    ${durumBadge}
                </div>
            </td>
            <td>
                <div class="text-gray-600">${baslangicTarihi}</div>
            </td>
            <td class="text-end">
                <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                    İşlemler
                    <i class="ki-duotone ki-down fs-5 ms-1"></i>
                </a>
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                    <div class="menu-item px-3">
                        <a href="/admin/personel/detay/${personel.id}" class="menu-link px-3">
                            <i class="ki-duotone ki-profile-circle fs-5 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            Detay
                        </a>
                    </div>
                    <div class="menu-item px-3">
                        <a href="/admin/personel/guncelle/${personel.id}" class="menu-link px-3">
                            <i class="ki-duotone ki-pencil fs-5 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Düzenle
                        </a>
                    </div>
                    <div class="menu-item px-3">
                        <a href="#" class="menu-link px-3" onclick="deletePersonel(${personel.id}, '${tamAd}')">
                            <i class="ki-duotone ki-trash fs-5 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                                <span class="path5"></span>
                            </i>
                            Sil
                        </a>
                    </div>
                </div>
            </td>
        `;
        
        return tr;
    }
    
    /**
     * DataTable'ı başlat
     */
    function initializeDataTable() {
        if (dataTable) {
            dataTable.destroy();
        }
        
        dataTable = $('#kt_table_personeller').DataTable({
            info: true,
            order: [],
            pageLength: 20,
            lengthChange: true,
            lengthMenu: [
                [20, 50, 100, -1],
                [20, 50, 100, "Tümü"]
            ],
            language: {
                search: "",
                searchPlaceholder: "Personel ara...",
                lengthMenu: "_MENU_ kayıt göster",
                info: "_START_ - _END_ arası, toplam _TOTAL_ kayıt",
                infoEmpty: "0 kayıt",
                infoFiltered: "(_MAX_ kayıt içinden filtrelendi)",
                paginate: {
                    previous: "Önceki",
                    next: "Sonraki"
                },
                emptyTable: "Hiç personel bulunamadı",
                zeroRecords: "Arama kriterlerine uygun personel bulunamadı"
            },
            columnDefs: [{
                orderable: false,
                targets: [0, 6] // checkbox ve işlemler sütunları
            }],
            drawCallback: function() {
                // Her DataTable yeniden çizildiğinde Metronic menülerini yeniden başlat
                KTMenu.createInstances();
            }
        });
        
        // Arama filtresini bağla
        const filterSearch = document.querySelector('[data-kt-personel-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dataTable.search(e.target.value).draw();
        });
    }
    
    /**
     * Hata göster
     */
    function showError(message) {
        const tbody = document.getElementById('personelTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-10">
                    <div class="d-flex flex-column flex-center">
                        <i class="ki-duotone ki-cross-circle fs-5x text-danger mb-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <div class="text-gray-400 fs-6 fw-semibold">${message}</div>
                    </div>
                </td>
            </tr>
        `;
    }
    
    // Global delete function
    window.deletePersonel = function(personelId, personelName) {
        Swal.fire({
            text: `"${personelName}" personelini silmek istediğinizden emin misiniz?`,
            icon: "warning",
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: "Evet, Sil!",
            cancelButtonText: "İptal",
            customClass: {
                confirmButton: "btn fw-bold btn-danger",
                cancelButton: "btn fw-bold btn-active-light-primary"
            }
        }).then(function (result) {
            if (result.value) {
                // Silme işlemi
                fetch(`/admin/personel/api-delete/${personelId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'include'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            text: "Personel başarıyla silindi.",
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "Tamam",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary",
                            }
                        }).then(() => {
                            loadPersoneller(); // Tabloyu yenile
                        });
                    } else {
                        Swal.fire({
                            text: data.message || "Personel silinirken hata oluştu.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Tamam",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary",
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Silme hatası:', error);
                    Swal.fire({
                        text: "Sunucu hatası oluştu.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Tamam",
                        customClass: {
                            confirmButton: "btn fw-bold btn-primary",
                        }
                    });
                });
            }
        });
    }
    
    // Sayfa yüklendiğinde Metronic menü sistemi başlat
    KTMenu.createInstances();
});
</script>

<?php
require_once 'app/Views/layouts/footer.php';
?>