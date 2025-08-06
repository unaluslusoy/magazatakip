<?php
$title = "Bildirim Gönder";
$link = "Bildirim Gönder";

require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>

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
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <h2>Yeni Bildirim Gönder</h2>
                </div>
                <!--end::Card title-->
                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                        <a href="/admin/bildirimler" class="btn btn-light me-3">
                            <i class="ki-outline ki-arrow-left fs-2"></i>Geri
                        </a>
                    </div>
                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->

            <!--begin::Card body-->
            <div class="card-body py-4">
                <!--begin::Form-->
                <form action="/admin/bildirim_gonder" method="POST" class="form fv-plugins-bootstrap5 fv-plugins-framework">
                    <!--begin::Input group-->
                    <div class="row g-9 mb-8">
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2" for="baslik">Bildirim Başlığı:</label>
                            <input class="form-control form-control-solid" type="text" id="baslik" name="baslik" required>
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2" for="gonderim_kanali">Gönderim Kanalı:</label>
                            <select class="form-select form-select-solid" id="gonderim_kanali" name="gonderim_kanali" required>
                                <option value="">Seçiniz</option>
                                <option value="web">Web</option>
                                <option value="mobil">Mobil</option>
                                <option value="email">E-posta</option>

                            </select>
                        </div>
                    </div>
                    <!--end::Input group-->

                    <!--begin::Input group-->
                    <div class="row g-9 mb-8">
                        <div class="col-12 fv-row">
                            <label class="fs-6 fw-semibold mb-2" for="mesaj">Bildirim Mesajı:</label>
                            <textarea class="form-control form-control-solid" id="mesaj" name="mesaj" rows="4" required></textarea>
                        </div>
                    </div>
                    <!--end::Input group-->

                    <!--begin::Input group-->
                    <div class="row g-9 mb-8">
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2" for="alici_tipi">Alıcı Tipi:</label>
                            <select class="form-select form-select-solid" id="alici_tipi" name="alici_tipi" required>
                                <option value="tum">Tüm Kullanıcılar</option>
                                <option value="bireysel">Bireysel</option>
                                <option value="grup">Grup</option>
                            </select>
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2" for="oncelik">Öncelik:</label>
                            <select class="form-select form-select-solid" id="oncelik" name="oncelik" required>
                                <option value="normal">Normal</option>
                                <option value="yuksek">Yüksek</option>
                                <option value="dusuk">Düşük</option>
                            </select>
                        </div>
                    </div>
                    <!--end::Input group-->

                    <!--begin::Input group-->
                    <div class="row g-9 mb-8">
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2" for="url">Yönlendirme URL:</label>
                            <input class="form-control form-control-solid" type="url" id="url" name="url" placeholder="https://example.com">
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2" for="icon">İkon URL:</label>
                            <input class="form-control form-control-solid" type="url" id="icon" name="icon" placeholder="https://example.com/icon.png">
                        </div>
                    </div>
                    <!--end::Input group-->

                    <!--begin::Input group - Kullanıcı Seçimi (Bireysel/Grup için)-->
                    <div class="row g-9 mb-8" id="kullanici_secimi" style="display: none;">
                        <div class="col-12 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Kullanıcı Seçimi:</label>
                            <div class="form-control form-control-solid" style="max-height: 200px; overflow-y: auto;">
                                <?php if (!empty($kullanicilar)): ?>
                                    <?php foreach ($kullanicilar as $kullanici): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="kullanicilar[]" value="<?= $kullanici['id'] ?>" id="kullanici_<?= $kullanici['id'] ?>">
                                            <label class="form-check-label" for="kullanici_<?= $kullanici['id'] ?>">
                                                <?= htmlspecialchars($kullanici['ad'] . ' ' . $kullanici['soyad']) ?> 
                                                <span class="text-muted">(<?= htmlspecialchars($kullanici['email']) ?>)</span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">Kullanıcı bulunamadı.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->

                    <!--begin::Input group-->
                    <div class="row g-9 mb-8">
                        <div class="col-12 fv-row">
                            <label class="fs-6 fw-semibold mb-2" for="etiketler">Etiketler:</label>
                            <input class="form-control form-control-solid" type="text" id="etiketler" name="etiketler" placeholder="etiket1, etiket2, etiket3">
                            <div class="form-text">Virgülle ayırarak etiketler ekleyebilirsiniz.</div>
                        </div>
                    </div>
                    <!--end::Input group-->

                    <!--begin::Card footer-->
                    <div class="card-footer d-flex justify-content-end py-6 px-9">
                        <button type="button" class="btn btn-light me-3" onclick="history.back()">İptal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-outline ki-send fs-2"></i>Bildirimi Gönder
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
    <!--end::Content container-->
</div>
<!--end::Content-->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const aliciTipiSelect = document.getElementById('alici_tipi');
    const kullaniciSecimi = document.getElementById('kullanici_secimi');
    
    // Alıcı tipi değiştiğinde kullanıcı seçimini göster/gizle
    aliciTipiSelect.addEventListener('change', function() {
        if (this.value === 'bireysel' || this.value === 'grup') {
            kullaniciSecimi.style.display = 'block';
        } else {
            kullaniciSecimi.style.display = 'none';
        }
    });
    
    // Form gönderilmeden önce validasyon
    document.querySelector('form').addEventListener('submit', function(e) {
        const aliciTipi = aliciTipiSelect.value;
        const secilenKullanicilar = document.querySelectorAll('input[name="kullanicilar[]"]:checked');
        
        if ((aliciTipi === 'bireysel' || aliciTipi === 'grup') && secilenKullanicilar.length === 0) {
            e.preventDefault();
            alert('Lütfen en az bir kullanıcı seçin.');
            return false;
        }
    });
    
    // Tüm kullanıcıları seç/seçme
    const selectAllBtn = document.createElement('button');
    selectAllBtn.type = 'button';
    selectAllBtn.className = 'btn btn-sm btn-light-primary mb-3';
    selectAllBtn.textContent = 'Tümünü Seç';
    selectAllBtn.onclick = function() {
        const checkboxes = document.querySelectorAll('input[name="kullanicilar[]"]');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(cb => {
            cb.checked = !allChecked;
        });
        
        this.textContent = allChecked ? 'Tümünü Seç' : 'Seçimi Kaldır';
    };
    
    if (kullaniciSecimi.querySelector('.form-control')) {
        kullaniciSecimi.querySelector('.form-control').insertBefore(selectAllBtn, kullaniciSecimi.querySelector('.form-control').firstChild);
    }
});
</script>

<?php require_once 'app/Views/layouts/footer.php'; ?> 