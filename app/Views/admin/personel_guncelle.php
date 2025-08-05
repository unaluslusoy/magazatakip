<?php
$title = "<h2>Personel Güncelleme</h2>";
$link = "Personel Düzenle";
require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';

// URL'den ID'yi al
$urlPath = $_SERVER['REQUEST_URI'];
$pathParts = explode('/', trim($urlPath, '/'));
$personelId = end($pathParts);
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
                    Personel Düzenleme
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="/admin" class="text-muted text-hover-primary">Anasayfa</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="/admin/personeller" class="text-muted text-hover-primary">Personeller</a>
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
                        <a href="/admin/personeller" class="btn btn-primary">
                            <i class="ki-duotone ki-arrow-left fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Personel Listesine Dön
                        </a>
                    </div>
                </div>
            </div>

            <!-- Form Container -->
            <div id="formContainer" style="display: none;">
                <form id="personelEditForm" class="form">
                    <input type="hidden" id="personelId" value="<?php echo htmlspecialchars($personelId); ?>">
                    
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
                                        <label class="required fs-6 fw-semibold mb-2">Ad</label>
                                        <input type="text" class="form-control form-control-solid" 
                                               placeholder="Personelin adını girin" name="ad" id="ad" required />
                                    </div>
                                    <!--end::Ad-->

                                    <!--begin::Soyad-->
                                    <div class="mb-8 fv-row">
                                        <label class="required fs-6 fw-semibold mb-2">Soyad</label>
                                        <input type="text" class="form-control form-control-solid" 
                                               placeholder="Personelin soyadını girin" name="soyad" id="soyad" required />
                                    </div>
                                    <!--end::Soyad-->

                                    <!--begin::Email-->
                                    <div class="mb-8 fv-row">
                                        <label class="required fs-6 fw-semibold mb-2">Email Adresi</label>
                                        <input type="email" class="form-control form-control-solid" 
                                               placeholder="personel@ornek.com" name="eposta" id="eposta" required />
                                    </div>
                                    <!--end::Email-->
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
                                    <!--begin::Telefon-->
                                    <div class="mb-8 fv-row">
                                        <label class="fs-6 fw-semibold mb-2">Telefon</label>
                                        <input type="tel" class="form-control form-control-solid" 
                                               placeholder="0555 123 45 67" name="telefon" id="telefon" />
                                    </div>
                                    <!--end::Telefon-->

                                    <!--begin::Pozisyon-->
                                    <div class="mb-8 fv-row">
                                        <label class="required fs-6 fw-semibold mb-2">Pozisyon</label>
                                        <input type="text" class="form-control form-control-solid" 
                                               placeholder="Ör: Satış Danışmanı" name="pozisyon" id="pozisyon" required />
                                    </div>
                                    <!--end::Pozisyon-->

                                    <!--begin::İşe Başlama Tarihi-->
                                    <div class="mb-8 fv-row">
                                        <label class="fs-6 fw-semibold mb-2">İşe Başlama Tarihi</label>
                                        <input type="date" class="form-control form-control-solid" 
                                               name="ise_baslama_tarihi" id="ise_baslama_tarihi" />
                                    </div>
                                    <!--end::İşe Başlama Tarihi-->
                                </div>
                            </div>
                            <!--end::İş Bilgileri-->
                        </div>
                    </div>

                    <!--begin::Actions-->
                    <div class="card card-flush">
                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                            <a href="/admin/personeller" class="btn btn-light btn-active-light-primary me-2">
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
                                    Personeli Güncelle
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
    console.log('🚀 Personel düzenleme sayfası başlatıldı');
    
    const personelId = document.getElementById('personelId').value;
    const errorState = document.getElementById('errorState');
    const formContainer = document.getElementById('formContainer');
    const form = document.getElementById('personelEditForm');
    
    // Sayfa yüklenince personel verilerini getir
    loadPersonelData(personelId);
    
    // Form submit handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        updatePersonel(personelId);
    });
    
    /**
     * API üzerinden personel verilerini getir
     */
    function loadPersonelData(id) {
        console.log('📡 API isteği başlatılıyor - ID:', id);
        
        fetch(`/admin/personel/api-get/${id}`, {
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
                showError(data.message || 'Personel verileri alınamadı');
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
    function populateForm(personel) {
        console.log('📝 Form doldurulacak veri:', personel);
        
        document.getElementById('ad').value = personel.ad || '';
        document.getElementById('soyad').value = personel.soyad || '';
        document.getElementById('eposta').value = personel.eposta || '';
        document.getElementById('telefon').value = personel.telefon || '';
        document.getElementById('pozisyon').value = personel.pozisyon || '';
        document.getElementById('ise_baslama_tarihi').value = personel.ise_baslama_tarihi || '';
        
        console.log('✅ Form dolduruldu');
    }
    
    /**
     * Personel güncelle
     */
    function updatePersonel(id) {
        const updateBtn = document.getElementById('updateBtn');
        updateBtn.setAttribute('data-kt-indicator', 'on');
        updateBtn.disabled = true;
        
        const formData = {
            ad: document.getElementById('ad').value,
            soyad: document.getElementById('soyad').value,
            eposta: document.getElementById('eposta').value,
            telefon: document.getElementById('telefon').value,
            pozisyon: document.getElementById('pozisyon').value,
            ise_baslama_tarihi: document.getElementById('ise_baslama_tarihi').value
        };
        
        console.log('🔄 Güncelleme verisi:', formData);
        
        fetch(`/admin/personel/api-update/${id}`, {
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
                // Başarılı durumda direkt yönlendir
                window.location.href = '/admin/personeller';
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
        errorState.style.display = 'none';
        formContainer.style.display = 'block';
    }
    
    function showError(message) {
        document.getElementById('errorMessage').textContent = message;
        errorState.style.display = 'block';
        formContainer.style.display = 'none';
    }
});
</script>

<?php
require_once 'app/Views/layouts/footer.php';
?>