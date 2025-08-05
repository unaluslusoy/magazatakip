<?php
$title = "<h2>Kullanıcı Ekleme</h2>";
$link = "Kullanıcı Ekle";
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
                    <i class="ki-duotone ki-user-plus fs-1 text-success me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Yeni Kullanıcı Ekleme
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="/admin" class="text-muted text-hover-primary">Anasayfa</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="/admin/kullanicilar" class="text-muted text-hover-primary">Kullanıcılar</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Yeni Ekle</li>
                </ul>
        </div>
        </div>
        </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            
                        <!-- Loading State -->
            <div id="loadingState" class="d-flex flex-column flex-center min-h-400px d-none">
                <span class="loader"></span>
                <div class="text-gray-400 fs-6 fw-semibold mt-5">Mağaza ve personel listesi yükleniyor...</div>
            </div>

            <!-- Error State -->
            <div id="errorState" class="alert alert-danger d-flex align-items-center p-5 d-none">
                <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-danger">Hata Oluştu!</h4>
                    <span id="errorMessage"></span>
                </div>
            </div>

            <!-- Success State -->
            <div id="successState" class="alert alert-primary d-flex align-items-center p-5 d-none">
                <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-success">Başarılı!</h4>
                    <span id="successMessage"></span>
                </div>
            </div>

            <!-- Form Container -->
            <div id="formContainer">
                <form id="userCreateForm" class="form">
                    <div class="row">
                        <!-- Sol Kolon -->
                        <div class="col-xl-6">
                            <!--begin::Kişisel Bilgiler-->
                            <div class="card card-flush mb-6 mb-xl-9">
                                <div class="card-header">
                                    <div class="card-title">
                                        <h3 class="fw-bolder m-0">
                                            <i class="ki-duotone ki-profile-user fs-1 text-primary me-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                            </i>
                                            Kişisel Bilgiler
                                        </h3>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <!--begin::Ad-->
                                    <div class="mb-8 fv-row">
                                        <label class="required fs-6 fw-semibold mb-2">Ad Soyad</label>
                                        <input type="text" class="form-control form-control-solid" 
                                               placeholder="Kullanıcının adını girin" name="ad" id="ad" required />
                                    </div>
                                    <!--end::Ad-->

                                    <!--begin::Email-->
                                    <div class="mb-8 fv-row">
                                        <label class="required fs-6 fw-semibold mb-2">Email Adresi</label>
                                        <input type="email" class="form-control form-control-solid" 
                                               placeholder="kullanici@ornek.com" name="email" id="email" required />
                                    </div>
                                    <!--end::Email-->

                                    <!--begin::Şifre-->
                                    <div class="mb-8 fv-row">
                                        <label class="required fs-6 fw-semibold mb-2">Şifre</label>
                                        <input type="password" class="form-control form-control-solid" 
                                               placeholder="En az 8 karakter" name="password" id="password" required />
                                    </div>
                                    <!--end::Şifre-->
                                </div>
                            </div>
                            <!--end::Kişisel Bilgiler-->
        </div>

                        <!-- Sağ Kolon -->
                        <div class="col-xl-6">
                            <!--begin::İş Bilgileri-->
                            <div class="card card-flush mb-6 mb-xl-9">
                                <div class="card-header">
                                    <div class="card-title">
                                        <h3 class="fw-bolder m-0">
                                            <i class="ki-duotone ki-briefcase fs-1 text-success me-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            İş Bilgileri
                                        </h3>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <!--begin::Mağaza-->
                                    <div class="mb-8 fv-row">
                                        <label class="fs-6 fw-semibold mb-2">Mağaza</label>
                                        <select class="form-select form-select-solid" name="magaza_id" id="magaza_id" data-control="select2" data-placeholder="Mağaza seçin">
                                            <option value="">Mağaza Seçin</option>
                                        </select>
                                    </div>
                                    <!--end::Mağaza-->

                                    <!--begin::Personel-->
                                    <div class="mb-8 fv-row">
                                        <label class="fs-6 fw-semibold mb-2">Personel Kaydı</label>
                                        <select class="form-select form-select-solid" name="personel_id" id="personel_id" data-control="select2" data-placeholder="Personel seçin">
                                            <option value="">Personel Seçin (Opsiyonel)</option>
                                        </select>
                                    </div>
                                    <!--end::Personel-->

                                    <!--begin::Yetki-->
                                    <div class="mb-8">
                                        <label class="fs-6 fw-semibold mb-2">Yetki Seviyesi</label>
                                        <div class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" name="yonetici" id="yonetici" />
                                            <label class="form-check-label fw-semibold text-gray-400 ms-3" for="yonetici">
                                                Yönetici Yetkisi
                                            </label>
                                        </div>
                                    </div>
                                    <!--end::Yetki-->
                                </div>
                            </div>
                            <!--end::İş Bilgileri-->
                        </div>
                    </div>

        <!--begin::Actions-->
                    <div class="card card-flush">
                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                            <a href="/admin/kullanicilar" class="btn btn-light btn-active-light-primary me-2">
                                <i class="ki-duotone ki-arrow-left fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                İptal
                            </a>
                            <button type="submit" class="btn btn-success" id="createBtn">
                                <span class="indicator-label">
                                    <i class="ki-duotone ki-plus fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    Kullanıcı Oluştur
                                </span>
                                <span class="indicator-progress">
                                    Oluşturuluyor...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
            </button>
                        </div>
        </div>
        <!--end::Actions-->
    </form>
            </div>
        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Kullanıcı ekleme sayfası başlatıldı');
    
    const loadingState = document.getElementById('loadingState');
    const errorState = document.getElementById('errorState');
    const successState = document.getElementById('successState');
    const formContainer = document.getElementById('formContainer');
    const form = document.getElementById('userCreateForm');
    
    // Başlangıçta hata mesajını gizle ve formu göster
    hideAllStates();
    showForm();
    
    // Veri listelerini arka planda yükle
    loadInitialData();
    
    // Form submit handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        createUser();
    });
    
    /**
     * Başlangıç verilerini getir
     */
    function loadInitialData() {
        console.log('📡 Başlangıç verileri getiriliyor...');
        showLoading();
        
        // Paralel olarak mağaza ve personel listelerini getir
        Promise.all([
            loadMagazalar(),
            loadPersoneller()
        ])
        .then(() => {
            console.log('✅ Tüm başlangıç verileri yüklendi');
            showForm();
        })
        .catch(error => {
            console.error('❌ Başlangıç veri hatası:', error);
            console.log('⚠️ Dropdownlar boş kalacak ama form çalışacak');
            showForm(); // Hata olsa bile formu göster
        });
    }
    
    /**
     * Mağaza listesini getir
     */
    function loadMagazalar() {
        return fetch('/api/magazalar', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include'
        })
        .then(response => {
            if (!response.ok) {
                console.warn('⚠️ Mağaza listesi alınamadı');
                return { success: false, data: [] };
            }
            return response.json();
        })
        .then(data => {
            const magazaSelect = document.getElementById('magaza_id');
            if (data.success && data.data) {
                data.data.forEach(magaza => {
                    const option = document.createElement('option');
                    option.value = magaza.id;
                    option.textContent = magaza.ad;
                    magazaSelect.appendChild(option);
                });
                console.log(`✅ ${data.data.length} mağaza yüklendi`);
            }
            // Select2 initialize et
            $('#magaza_id').select2();
        })
        .catch(error => {
            console.warn('⚠️ Mağaza yükleme hatası:', error.message);
            $('#magaza_id').select2();
        });
    }
    
    /**
     * Personel listesini getir
     */
    function loadPersoneller() {
        return fetch('/api/personeller', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include'
        })
        .then(response => {
            if (!response.ok) {
                console.warn('⚠️ Personel listesi alınamadı');
                return { success: false, data: [] };
            }
            return response.json();
        })
        .then(data => {
            const personelSelect = document.getElementById('personel_id');
            if (data.success && data.data) {
                // Sadece kullanıcı_id null olan personelleri göster
                const unassignedPersonel = data.data.filter(p => !p.kullanici_id);
                unassignedPersonel.forEach(personel => {
                    const option = document.createElement('option');
                    option.value = personel.id;
                    option.textContent = `${personel.ad} ${personel.soyad}`.trim();
                    personelSelect.appendChild(option);
                });
                console.log(`✅ ${unassignedPersonel.length} atanmamış personel yüklendi`);
            }
            // Select2 initialize et
            $('#personel_id').select2();
        })
        .catch(error => {
            console.warn('⚠️ Personel yükleme hatası:', error.message);
            $('#personel_id').select2();
        });
    }
    
    /**
     * Yeni kullanıcı oluştur
     */
    function createUser() {
        const createBtn = document.getElementById('createBtn');
        createBtn.setAttribute('data-kt-indicator', 'on');
        createBtn.disabled = true;
        
        const formData = {
            ad: document.getElementById('ad').value,
            email: document.getElementById('email').value,
            magaza_id: document.getElementById('magaza_id').value || null,
            personel_id: document.getElementById('personel_id').value || null,
            yonetici: document.getElementById('yonetici').checked,
            password: document.getElementById('password').value
        };
        
        console.log('🔄 Kullanıcı oluşturma verisi:', formData);
        
        fetch('/admin/kullanici/api-create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include',
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showSuccess(data.message);
                form.reset(); // Formu temizle
                setTimeout(() => {
                    window.location.href = '/admin/kullanicilar';
                }, 1500);
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            console.error('❌ Kullanıcı oluşturma hatası:', error);
            showError(`Sunucu hatası: ${error.message}`);
        })
        .finally(() => {
            createBtn.removeAttribute('data-kt-indicator');
            createBtn.disabled = false;
        });
    }
    
    function showLoading() {
        loadingState.classList.remove('d-none');
        loadingState.style.display = 'flex';
        formContainer.style.display = 'none';
        hideError();
        hideSuccess();
    }
    
    function showForm() {
        hideLoading();
        formContainer.style.display = 'block';
        hideError();
        hideSuccess();
    }
    
    function showError(message) {
        document.getElementById('errorMessage').textContent = message;
        errorState.classList.remove('d-none');
        errorState.style.display = 'block';
        hideSuccess();
        // Sayfanın üstüne scroll et
        window.scrollTo(0, 0);
    }
    
    function showSuccess(message) {
        document.getElementById('successMessage').textContent = message;
        successState.classList.remove('d-none');
        successState.style.display = 'block';
        hideError();
    }
    
    function hideAllStates() {
        hideLoading();
        hideError();
        hideSuccess();
    }
    
    function hideLoading() {
        loadingState.classList.add('d-none');
        loadingState.style.display = 'none';
    }
    
    function hideError() {
        errorState.classList.add('d-none');
        errorState.style.display = 'none';
    }
    
    function hideSuccess() {
        successState.classList.add('d-none');
        successState.style.display = 'none';
    }
});
</script>

<?php
require_once 'app/Views/layouts/footer.php';
?>