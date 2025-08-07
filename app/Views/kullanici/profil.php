<?php
$title = "Profilim";
$link = "Profil";

require_once __DIR__ . '/layouts/layout/header.php';
require_once __DIR__ . '/layouts/layout/navbar.php';
?>

<!-- API Service -->
<script src="/app/Views/kullanici/api-service.js"></script>

<?php if (isset($_SESSION['message']) && isset($_SESSION['message_type'])): ?>
    <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
        <?= $_SESSION['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
<?php endif; ?>

<!--begin::Content-->
<div class="content d-flex flex-column flex-column-fluid" id="kt_app_content">
    <!--begin::Content container-->
    <div class="container-fluid" id="kt_app_content_container">
        <!--begin::Row-->
        <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
            <!--begin::Col-->
            <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                <!--begin::Card-->
                <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Info-->
                            <div class="d-flex align-items-center">
                                <!--begin::Avatar-->
                                <div class="symbol symbol-50px me-5">
                                    <?php if ($personel && !empty($personel['foto'])): ?>
                                        <img src="<?= $personel['foto'] ?>" alt="Profil Fotoğrafı" />
                                    <?php elseif ($kullanici && !empty($kullanici['profil_foto'])): ?>
                                        <img src="<?= $kullanici['profil_foto'] ?>" alt="Profil Fotoğrafı" />
                                    <?php else: ?>
                                        <div class="symbol-label bg-primary text-white fw-bold fs-3">
                                            <?= strtoupper(substr($kullanici['ad'] ?? '', 0, 1)) . strtoupper(substr($kullanici['soyad'] ?? '', 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <!--end::Avatar-->
                                <!--begin::Details-->
                                <div class="d-flex flex-column">
                                    <a href="#" class="text-gray-800 text-hover-primary fw-bold fs-4"><?= $kullanici['ad'] ?? '' ?> <?= $kullanici['soyad'] ?? '' ?></a>
                                    <span class="text-gray-500 fw-semibold d-block fs-5"><?= $kullanici['email'] ?? '' ?></span>
                                </div>
                                <!--end::Details-->
                            </div>
                            <!--end::Info-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-2">
                        <!--begin::Table wrapper-->
                        <div class="table-responsive">
                            <!--begin::Table-->
                            <table class="table align-middle gs-0 gy-4 my-0">
                                <!--begin::Table head-->
                                <thead>
                                    <tr class="fs-7 fw-bold text-gray-500 text-uppercase">
                                        <th class="ps-0 min-w-175px rounded-start">Bilgi</th>
                                        <th class="min-w-100px text-end rounded-end">Değer</th>
                                    </tr>
                                </thead>
                                <!--end::Table head-->
                                <!--begin::Table body-->
                                <tbody class="fs-6">
                                    <tr>
                                        <td class="ps-0">
                                            <span class="text-gray-600 fw-semibold d-block fs-6">Ad Soyad</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-800 fw-bold d-block fs-6"><?= $kullanici['ad'] ?? '' ?> <?= $kullanici['soyad'] ?? '' ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-0">
                                            <span class="text-gray-600 fw-semibold d-block fs-6">E-posta</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-800 fw-bold d-block fs-6"><?= $kullanici['email'] ?? '' ?></span>
                                        </td>
                                    </tr>
                                    <?php if ($personel): ?>
                                    <tr>
                                        <td class="ps-0">
                                            <span class="text-gray-600 fw-semibold d-block fs-6">Çalışan No</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-800 fw-bold d-block fs-6"><?= $personel['calisan_no'] ?? '' ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-0">
                                            <span class="text-gray-600 fw-semibold d-block fs-6">Pozisyon</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-800 fw-bold d-block fs-6"><?= $personel['pozisyon'] ?? '' ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-0">
                                            <span class="text-gray-600 fw-semibold d-block fs-6">Departman</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-800 fw-bold d-block fs-6"><?= $personel['departman'] ?? '' ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-0">
                                            <span class="text-gray-600 fw-semibold d-block fs-6">İşe Başlama</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-800 fw-bold d-block fs-6"><?= $personel['ise_baslama_tarihi'] ?? '' ?></span>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                                <!--end::Table body-->
                            </table>
                            <!--end::Table-->
                        </div>
                        <!--end::Table wrapper-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Col-->

            <!--begin::Col-->
            <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-9 mb-md-5 mb-xl-10">
                <!--begin::Card-->
                <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <h3 class="fw-bold">Profil Bilgilerini Düzenle</h3>
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-2">
                        <!--begin::Form-->
                        <form action="/kullanici/profil/guncelle" method="POST" class="form fv-plugins-bootstrap5 fv-plugins-framework">
                            <!--begin::Input group-->
                            <div class="row g-9 mb-8">
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="ad">Ad:</label>
                                    <input class="form-control form-control-solid" type="text" id="ad" name="ad" value="<?= $kullanici['ad'] ?? '' ?>" required>
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="soyad">Soyad:</label>
                                    <input class="form-control form-control-solid" type="text" id="soyad" name="soyad" value="<?= $kullanici['soyad'] ?? '' ?>" required>
                                </div>
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="row g-9 mb-8">
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="email">E-posta:</label>
                                    <input class="form-control form-control-solid" type="email" id="email" name="email" value="<?= $kullanici['email'] ?? '' ?>" required>
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="telefon">Telefon:</label>
                                    <input class="form-control form-control-solid" type="tel" id="telefon" name="telefon" value="<?= $kullanici['telefon'] ?? '' ?>">
                                </div>
                            </div>
                            <!--end::Input group-->

                            <?php if ($personel): ?>
                            <!--begin::Input group-->
                            <div class="row g-9 mb-8">
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="cep_telefonu">Cep Telefonu:</label>
                                    <input class="form-control form-control-solid" type="tel" id="cep_telefonu" name="cep_telefonu" value="<?= $personel['cep_telefonu'] ?? '' ?>">
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="dogum_tarihi">Doğum Tarihi:</label>
                                    <input class="form-control form-control-solid" type="date" id="dogum_tarihi" name="dogum_tarihi" value="<?= $personel['dogum_tarihi'] ?? '' ?>">
                                </div>
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="row g-9 mb-8">
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="tc_kimlik_no">TC Kimlik No:</label>
                                    <input class="form-control form-control-solid" type="text" id="tc_kimlik_no" name="tc_kimlik_no" value="<?= $personel['tc_kimlik_no'] ?? '' ?>" maxlength="11">
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="cinsiyet">Cinsiyet:</label>
                                    <select class="form-select form-select-solid" id="cinsiyet" name="cinsiyet">
                                        <option value="">Seçiniz</option>
                                        <option value="Erkek" <?= ($personel['cinsiyet'] ?? '') === 'Erkek' ? 'selected' : '' ?>>Erkek</option>
                                        <option value="Kadın" <?= ($personel['cinsiyet'] ?? '') === 'Kadın' ? 'selected' : '' ?>>Kadın</option>
                                    </select>
                                </div>
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="row g-9 mb-8">
                                <div class="col-12 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="ev_adresi">Ev Adresi:</label>
                                    <textarea class="form-control form-control-solid" id="ev_adresi" name="ev_adresi" rows="3"><?= $personel['ev_adresi'] ?? '' ?></textarea>
                                </div>
                            </div>
                            <!--end::Input group-->
                            <?php endif; ?>

                            <!--begin::Card footer-->
                            <div class="card-footer d-flex justify-content-end py-6 px-9">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ki-outline ki-check fs-2"></i>Güncelle
                                </button>
                            </div>
                            <!--end::Card footer-->
                        </form>
                        <!--end::Form-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->

                <!--begin::Card-->
                <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <h3 class="fw-bold">Şifre Değiştir</h3>
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-2">
                        <!--begin::Form-->
                        <form action="/kullanici/profil/sifre-degistir" method="POST" class="form fv-plugins-bootstrap5 fv-plugins-framework">
                            <!--begin::Input group-->
                            <div class="row g-9 mb-8">
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="mevcut_sifre">Mevcut Şifre:</label>
                                    <input class="form-control form-control-solid" type="password" id="mevcut_sifre" name="mevcut_sifre" required>
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="yeni_sifre">Yeni Şifre:</label>
                                    <input class="form-control form-control-solid" type="password" id="yeni_sifre" name="yeni_sifre" required>
                                </div>
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="row g-9 mb-8">
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="yeni_sifre_tekrar">Yeni Şifre (Tekrar):</label>
                                    <input class="form-control form-control-solid" type="password" id="yeni_sifre_tekrar" name="yeni_sifre_tekrar" required>
                                </div>
                            </div>
                            <!--end::Input group-->

                            <!--begin::Card footer-->
                            <div class="card-footer d-flex justify-content-end py-6 px-9">
                                <button type="submit" class="btn btn-warning">
                                    <i class="ki-outline ki-lock fs-2"></i>Şifre Değiştir
                                </button>
                            </div>
                            <!--end::Card footer-->
                        </form>
                        <!--end::Form-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
    </div>
    <!--end::Content container-->
</div>
<!--end::Content-->

<script>
// API tabanlı profil yönetimi
class ProfilManager {
    constructor() {
        this.apiService = window.userApiService;
        this.init();
    }
    
    async init() {
        await this.loadProfilBilgileri();
        this.setupEventListeners();
    }
    
    async loadProfilBilgileri() {
        try {
            const response = await this.apiService.getProfile();
            
            if (response.success) {
                this.updateProfilBilgileri(response.data);
            } else {
                console.error('Profil bilgileri yüklenemedi:', response.message);
            }
        } catch (error) {
            console.error('Profil bilgileri yükleme hatası:', error);
        }
    }
    
    updateProfilBilgileri(profil) {
        // Profil bilgilerini güncelle (eğer gerekirse)
        const adSoyadElement = document.querySelector('.text-gray-800.text-hover-primary');
        if (adSoyadElement && profil.ad && profil.soyad) {
            adSoyadElement.textContent = `${profil.ad} ${profil.soyad}`;
        }
        
        const emailElement = document.querySelector('.text-gray-500.fw-semibold');
        if (emailElement && profil.email) {
            emailElement.textContent = profil.email;
        }
    }
    
    setupEventListeners() {
        // Şifre değiştirme formunu API ile entegre et
        const sifreForm = document.querySelector('form[action="/kullanici/profil/sifre-degistir"]');
        if (sifreForm) {
            sifreForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.changePassword();
            });
        }
        
        // Refresh butonu ekle
        const refreshBtn = document.createElement('button');
        refreshBtn.className = 'btn btn-sm btn-outline-primary ms-2';
        refreshBtn.innerHTML = '<i class="ki-outline ki-refresh fs-7 me-1"></i> Yenile';
        refreshBtn.onclick = () => this.loadProfilBilgileri();
        
        const header = document.querySelector('.card-header');
        if (header) {
            header.appendChild(refreshBtn);
        }
    }
    
    async changePassword() {
        const mevcutSifre = document.getElementById('mevcut_sifre').value;
        const yeniSifre = document.getElementById('yeni_sifre').value;
        const yeniSifreTekrar = document.getElementById('yeni_sifre_tekrar').value;
        
        if (yeniSifre !== yeniSifreTekrar) {
            alert('Yeni şifreler eşleşmiyor!');
            return;
        }
        
        if (yeniSifre.length < 6) {
            alert('Şifre en az 6 karakter olmalıdır!');
            return;
        }
        
        try {
            const response = await this.apiService.changePassword(mevcutSifre, yeniSifre);
            
            if (response.success) {
                alert('Şifre başarıyla değiştirildi!');
                // Formu temizle
                document.getElementById('mevcut_sifre').value = '';
                document.getElementById('yeni_sifre').value = '';
                document.getElementById('yeni_sifre_tekrar').value = '';
            } else {
                alert('Şifre değiştirme hatası: ' + response.message);
            }
        } catch (error) {
            console.error('Şifre değiştirme hatası:', error);
            alert('Şifre değiştirme hatası: ' + error.message);
        }
    }
}

// Sayfa yüklendiğinde ProfilManager'ı başlat
document.addEventListener('DOMContentLoaded', function() {
    window.profilManager = new ProfilManager();
});
</script>

<?php require_once __DIR__ . '/layouts/layout/footer.php'; ?>


