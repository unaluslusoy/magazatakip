<?php
$title = "<h2>Kullanıcı Yönetimi</h2>";
$link = "Kullanıcılar";
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
                    Kullanıcı Yönetimi
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="/admin" class="text-muted text-hover-primary">Anasayfa</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Kullanıcılar</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="/admin/kullanici_ekle" class="btn btn-sm btn-primary">
                    <i class="ki-duotone ki-plus fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Yeni Kullanıcı
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
                            <input type="text" data-kt-user-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="Kullanıcı ara" />
                        </div>
                        <!--end::Search-->
                    </div>
                    <!--begin::Card title-->
                    <!--begin::Card toolbar-->
                    <div class="card-toolbar">
                        <!--begin::Toolbar-->
                        <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                            <!--begin::Filter-->
                            <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                <i class="ki-duotone ki-filter fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Filtrele
                            </button>
                            <!--begin::Menu 1-->
                            <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                                <!--begin::Header-->
                                <div class="px-7 py-5">
                                    <div class="fs-5 text-dark fw-bold">Filtreleme Seçenekleri</div>
                                </div>
                                <!--end::Header-->
                                <!--begin::Separator-->
                                <div class="separator border-gray-200"></div>
                                <!--end::Separator-->
                                <!--begin::Content-->
                                <div class="px-7 py-5" data-kt-user-table-filter="form">
                                    <!--begin::Input group-->
                                    <div class="mb-10">
                                        <label class="form-label fs-6 fw-semibold">Rol:</label>
                                        <select class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="Rol seçin" data-allow-clear="true" data-kt-user-table-filter="role" data-hide-search="true">
                                            <option></option>
                                            <option value="Admin">Yönetici</option>
                                            <option value="User">Kullanıcı</option>
                                        </select>
                                    </div>
                                    <!--end::Input group-->
                                    <!--begin::Actions-->
                                    <div class="d-flex justify-content-end">
                                        <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6" data-kt-menu-dismiss="true" data-kt-user-table-filter="reset">Sıfırla</button>
                                        <button type="submit" class="btn btn-primary fw-semibold px-6" data-kt-menu-dismiss="true" data-kt-user-table-filter="filter">Uygula</button>
                                    </div>
                                    <!--end::Actions-->
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Menu 1-->
                            <!--end::Filter-->
                        </div>
                        <!--end::Toolbar-->
                    </div>
                    <!--end::Card toolbar-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body py-4">
                    <!--begin::Table-->
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="w-10px pe-2">
                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                        <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_table_users .form-check-input" value="1" />
                                    </div>
                                </th>
                                <th class="min-w-125px">Kullanıcı</th>
                                <th class="min-w-125px">Email</th>
                                <th class="min-w-125px">Mağaza</th>
                                <th class="min-w-125px">Rol</th>
                                <th class="min-w-125px">Kayıt Tarihi</th>
                                <th class="text-end min-w-100px">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold" id="userTableBody">
                            <tr>
                                <td colspan="7" class="text-center py-10">
                                    <div class="d-flex flex-column flex-center">
                                        <span class="loader"></span>
                                        <div class="text-gray-400 fs-6 fw-semibold mt-5">Kullanıcılar yükleniyor...</div>
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
    console.log('🚀 Kullanıcılar sayfası başlatıldı');
    
    let dataTable;
    
    // Kullanıcıları yükle
    loadUsers();
    
    /**
     * Kullanıcıları API'den getir ve DataTable'a yükle
     */
    function loadUsers() {
        fetch('/admin/kullanicilar/api-list', {
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
            console.log('✅ Kullanıcı verileri:', data);
            
            if (data.success) {
                populateTable(data.data);
                initializeDataTable();
            } else {
                showError(data.message || 'Kullanıcılar yüklenemedi');
            }
        })
        .catch(error => {
            console.error('❌ Kullanıcı yükleme hatası:', error);
            showError(`Sunucu hatası: ${error.message}`);
        });
    }
    
    /**
     * Tabloyu kullanıcı verileri ile doldur
     */
    function populateTable(users) {
        const tbody = document.getElementById('userTableBody');
        tbody.innerHTML = '';
        
        users.forEach(user => {
            const row = createUserRow(user);
            tbody.appendChild(row);
        });
    }
    
    /**
     * Kullanıcı satırı oluştur
     */
    function createUserRow(user) {
        const tr = document.createElement('tr');
        
        // Rol badge
        const roleBadge = user.yonetici == 1 ? 
            `<div class="badge badge-light-success fw-bold">Yönetici</div>` :
            `<div class="badge badge-light-primary fw-bold">Kullanıcı</div>`;
        
        // Mağaza adı
        const magazaAd = user.magaza_isim || '<span class="text-muted">-</span>';
        
        // Kayıt tarihi
        const kayitTarihi = user.created_at ? 
            new Date(user.created_at).toLocaleDateString('tr-TR') : 
            '<span class="text-muted">-</span>';
        
        tr.innerHTML = `
            <td>
                <div class="form-check form-check-sm form-check-custom form-check-solid">
                    <input class="form-check-input" type="checkbox" value="${user.id}" />
                </div>
            </td>
            <td class="d-flex align-items-center">
                <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                    <div class="symbol-label">
                        <div class="symbol-label fs-3 bg-light-primary text-primary">${user.ad.charAt(0).toUpperCase()}</div>
                    </div>
                </div>
                <div class="d-flex flex-column">
                    <a href="/admin/kullanici/duzenle/${user.id}" class="text-gray-800 text-hover-primary mb-1 fw-bold">${user.ad}</a>
                    <span class="text-muted fw-semibold text-muted d-block fs-7">ID: ${user.id}</span>
                </div>
            </td>
            <td>
                <a href="mailto:${user.email}" class="text-gray-600 text-hover-primary mb-1">${user.email}</a>
            </td>
            <td>
                <div class="d-flex flex-column">
                    <span class="text-gray-800 fw-bold">${magazaAd}</span>
                </div>
            </td>
            <td>
                ${roleBadge}
            </td>
            <td>
                <div class="text-gray-600">${kayitTarihi}</div>
            </td>
            <td class="text-end">
                <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                    İşlemler
                    <i class="ki-duotone ki-down fs-5 ms-1"></i>
                </a>
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                    <div class="menu-item px-3">
                        <a href="/admin/kullanici/duzenle/${user.id}" class="menu-link px-3">
                            <i class="ki-duotone ki-pencil fs-5 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Düzenle
                        </a>
                    </div>
                    <div class="menu-item px-3">
                        <a href="#" class="menu-link px-3" onclick="deleteUser(${user.id}, '${user.ad}')">
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
        
        dataTable = $('#kt_table_users').DataTable({
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
                searchPlaceholder: "Kullanıcı ara...",
                lengthMenu: "_MENU_ kayıt göster",
                info: "_START_ - _END_ arası, toplam _TOTAL_ kayıt",
                infoEmpty: "0 kayıt",
                infoFiltered: "(_MAX_ kayıt içinden filtrelendi)",
                paginate: {
                    previous: "Önceki",
                    next: "Sonraki"
                },
                emptyTable: "Hiç kullanıcı bulunamadı",
                zeroRecords: "Arama kriterlerine uygun kullanıcı bulunamadı"
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
        const filterSearch = document.querySelector('[data-kt-user-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dataTable.search(e.target.value).draw();
        });
        
        // Rol filtresini bağla
        const filterRole = document.querySelector('[data-kt-user-table-filter="role"]');
        if (filterRole) {
            $(filterRole).on('change', function() {
                const filterValue = this.value;
                if (filterValue === 'Admin') {
                    dataTable.column(4).search('Yönetici').draw();
                } else if (filterValue === 'User') {
                    dataTable.column(4).search('Kullanıcı').draw();
                } else {
                    dataTable.column(4).search('').draw();
                }
            });
        }
        
        // Reset filtresini bağla
        const resetButton = document.querySelector('[data-kt-user-table-filter="reset"]');
        if (resetButton) {
            resetButton.addEventListener('click', function() {
                filterSearch.value = '';
                if (filterRole) filterRole.value = '';
                dataTable.search('').columns().search('').draw();
            });
        }
    }
    
    /**
     * Hata göster
     */
    function showError(message) {
        const tbody = document.getElementById('userTableBody');
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
    window.deleteUser = function(userId, userName) {
        Swal.fire({
            text: `"${userName}" kullanıcısını silmek istediğinizden emin misiniz?`,
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
                fetch(`/admin/kullanici/api-delete/${userId}`, {
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
                            text: "Kullanıcı başarıyla silindi.",
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "Tamam",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary",
                            }
                        }).then(() => {
                            loadUsers(); // Tabloyu yenile
                        });
                    } else {
                        Swal.fire({
                            text: data.message || "Kullanıcı silinirken hata oluştu.",
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