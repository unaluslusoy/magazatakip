<?php
$title = "<h2>Kullanıcı Güncelleme</h2>";
$link = "Kullanıcı Düzenle";
require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';

// URL'den ID'yi al
$urlPath = $_SERVER['REQUEST_URI'];
$pathParts = explode('/', trim($urlPath, '/'));
$userId = end($pathParts);
?>

<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    <i class="ki-duotone ki-user-edit fs-1 text-primary me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Kullanıcı Düzenleme
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
                    <li class="breadcrumb-item text-muted">Düzenle</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            
            <!-- Loading State -->
            <div id="loadingState" class="d-flex flex-column flex-center min-h-80px " style="display: none;">
                <span class="loader"></span>
            </div>

            <!-- Error State -->
            <div id="errorState" class="card" style="display: none;">
                <div class="card-body text-center py-20">
                    <i class="ki-duotone ki-cross-circle fs-5x text-danger mb-5">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <div class="text-center">
                        <h1 class="fw-bolder text-danger mb-3">Hata Oluştu</h1>
                        <div class="fw-semibold fs-6 text-gray-500 mb-7" id="errorMessage"></div>
                        <a href="/admin/kullanicilar" class="btn btn-primary">
                            <i class="ki-duotone ki-arrow-left fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Kullanıcı Listesine Dön
                        </a>
                    </div>
                </div>
            </div>



            <!-- Form Container -->
            <div id="formContainer" style="display: none;">
                <form id="userEditForm" class="form">
                    <input type="hidden" id="userId" value="<?php echo htmlspecialchars($userId); ?>">
                    
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
                                        <label class="fs-6 fw-semibold mb-2">Şifre</label>
                                        <input type="password" class="form-control form-control-solid" 
                                               placeholder="Boş bırakılırsa değişmez" name="password" id="password" />
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
                            <button type="submit" class="btn btn-primary" id="updateBtn">
                                <span class="indicator-label">
                                    <i class="ki-duotone ki-check fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Kullanıcıyı Güncelle
                                </span>
                                <span class="indicator-progress">
                                    Güncelleniyor...
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
    console.log('🚀 Kullanıcı düzenleme sayfası başlatıldı');
    
    const userId = document.getElementById('userId').value;
    const loadingState = document.getElementById('loadingState');
    const errorState = document.getElementById('errorState');
    const formContainer = document.getElementById('formContainer');
    const form = document.getElementById('userEditForm');
    
    // Sayfa yüklenince kullanıcı verilerini getir
    loadUserData(userId);
    
    // Form submit handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        updateUser(userId);
    });
    
    /**
     * API üzerinden kullanıcı verilerini getir
     */
    function loadUserData(id) {
        console.log('📡 API isteği başlatılıyor - ID:', id);
        
        // Direkt controller çağrısı yap
        fetch(`/admin/kullanici/api-get/${id}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include'
        })
        .then(response => {
            console.log('📊 Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            return response.json();
        })
        .then(data => {
            console.log('✅ API Response:', data);
            
            if (data.success) {
                populateForm(data.data);
                showForm();
            } else {
                showError(data.message || 'Kullanıcı verileri alınamadı');
            }
        })
        .catch(error => {
            console.error('❌ API Hatası:', error);
            showError(`Sunucu hatası: ${error.message}`);
        });
    }
    
    /**
     * Form alanlarını doldur
     */
    function populateForm(data) {
        const kullanici = data.kullanici;
        const magazalar = data.magazalar || [];
        const personeller = data.personeller || [];
        
        console.log('📝 Form doldurulacak veri:', kullanici);
        
        // Kullanıcı bilgilerini doldur
        document.getElementById('ad').value = kullanici.ad || '';
        document.getElementById('email').value = kullanici.email || '';
        document.getElementById('yonetici').checked = kullanici.yonetici == 1;
        
        // Mağaza dropdown'unu doldur
        const magazaSelect = document.getElementById('magaza_id');
        magazaSelect.innerHTML = '<option value="">Mağaza Seçin</option>';
        
        magazalar.forEach(magaza => {
            const option = document.createElement('option');
            option.value = magaza.id;
            option.textContent = magaza.ad;
            option.selected = (kullanici.magaza_id == magaza.id);
            magazaSelect.appendChild(option);
        });
        
        // Personel dropdown'unu doldur
        const personelSelect = document.getElementById('personel_id');
        personelSelect.innerHTML = '<option value="">Personel Seçin (Opsiyonel)</option>';
        
        personeller.forEach(personel => {
            const option = document.createElement('option');
            option.value = personel.id;
            option.textContent = `${personel.ad || ''} ${personel.soyad || ''} ${personel.pozisyon ? '(' + personel.pozisyon + ')' : ''}`.trim();
            option.selected = (personel.kullanici_id == kullanici.id);
            personelSelect.appendChild(option);
        });
        
        // Select2 initialize et
        $('#magaza_id, #personel_id').select2();
        
        console.log('✅ Form dolduruldu');
    }
    
    /**
     * Kullanıcı güncelle
     */
    function updateUser(id) {
        const updateBtn = document.getElementById('updateBtn');
        updateBtn.setAttribute('data-kt-indicator', 'on');
        updateBtn.disabled = true;
        
        const formData = {
            ad: document.getElementById('ad').value,
            email: document.getElementById('email').value,
            magaza_id: document.getElementById('magaza_id').value || null,
            personel_id: document.getElementById('personel_id').value || null,
            yonetici: document.getElementById('yonetici').checked,
            password: document.getElementById('password').value
        };
        
        console.log('🔄 Güncelleme verisi:', formData);
        
        fetch(`/admin/kullanici/api-update/${id}`, {
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
                // Başarılı durumda direkt yönlendir, mesaj gösterme
                window.location.href = '/admin/kullanicilar';
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            console.error('❌ Güncelleme hatası:', error);
            showError(`Sunucu hatası: ${error.message}`);
        })
        .finally(() => {
            updateBtn.removeAttribute('data-kt-indicator');
            updateBtn.disabled = false;
        });
    }
    
    function showForm() {
        loadingState.style.display = 'none';
        errorState.style.display = 'none';
        formContainer.style.display = 'block';
    }
    
    function showError(message) {
        document.getElementById('errorMessage').textContent = message;
        loadingState.style.display = 'none';
        errorState.style.display = 'block';
        formContainer.style.display = 'none';
    }
});
</script>

<?php
require_once 'app/Views/layouts/footer.php';
?>