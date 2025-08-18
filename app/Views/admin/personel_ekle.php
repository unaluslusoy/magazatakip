<?php
$title = "Personel Ekleme";
$link = "Personel Ekle";
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
                    Yeni Personel Ekleme
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
                    <li class="breadcrumb-item text-muted">Yeni Ekle</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            
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

            <!-- Form Container -->
            <div id="formContainer">
                <form id="personelCreateForm" class="form">
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
                                               placeholder="personel@ornek.com" name="email" id="email" required />
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
                            <button type="submit" class="btn btn-success" id="createBtn">
                                <span class="indicator-label">
                                    <i class="ki-duotone ki-plus fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    Personel Oluştur
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
    console.log('🚀 Personel ekleme sayfası başlatıldı');
    
    const errorState = document.getElementById('errorState');
    const formContainer = document.getElementById('formContainer');
    const form = document.getElementById('personelCreateForm');
    
    // Form doğrudan hazır, hata mesajını gizle
    hideError();
    
    // Form submit handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        createPersonel();
    });
    
    /**
     * Yeni personel oluştur
     */
    function createPersonel() {
        const createBtn = document.getElementById('createBtn');
        createBtn.setAttribute('data-kt-indicator', 'on');
        createBtn.disabled = true;
        
        const formData = {
            ad: document.getElementById('ad').value,
            soyad: document.getElementById('soyad').value,
            email: document.getElementById('email').value,
            telefon: document.getElementById('telefon').value,
            pozisyon: document.getElementById('pozisyon').value,
            ise_baslama_tarihi: document.getElementById('ise_baslama_tarihi').value
        };
        
        console.log('🔄 Personel oluşturma verisi:', formData);
        
        fetch('/admin/personel/api-create', {
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
            console.error('❌ Personel oluşturma hatası:', error);
            showError(`Sunucu hatası: ${error.message}`);
        })
        .finally(() => {
            createBtn.removeAttribute('data-kt-indicator');
            createBtn.disabled = false;
        });
    }
    
    function showError(message) {
        document.getElementById('errorMessage').textContent = message;
        errorState.classList.remove('d-none');
        errorState.style.display = 'block';
        // Sayfanın üstüne scroll et
        window.scrollTo(0, 0);
    }
    
    function hideError() {
        errorState.classList.add('d-none');
        errorState.style.display = 'none';
    }
});
</script>

<?php
require_once 'app/Views/layouts/footer.php';
?>