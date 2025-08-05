<?php
require_once 'app/Views/kullanici/layout/header.php';
require_once 'app/Views/kullanici/layout/navbar.php';
?>
<div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="container-fluid mt-3 mt-lg-5">
            <div class="col-12">
                <div class="card card-bordered">
                    <div class="card-header px-3 px-lg-6 py-3 py-lg-4">
                    <h2 class="card-title col-12 fs-3 fs-lg-2">Mağaza Yönetim Paneli</h2>
                    <strong class="fs-6 fs-lg-base"><?= isset($kullanici['magaza_isim']) ? htmlspecialchars($kullanici['magaza_isim']) : 'Mağaza'; ?> Mağazası</strong>
                    
                </div>
                    <div class="card-body px-3 px-lg-6">
                    <div class="row g-3 g-lg-4">
                        <div class="col-lg-4 col-md-6 col-12">
                            <a href="/isemri/listesi?durum=Yeni&derece=&kategori=&tarih_baslangic=&tarih_bitis=" class="card card-bordered hover-elevate-up h-100" data-bs-toggle="tooltip" title="Henüz başlamamış iş emirlerinizi görüntüleyin">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="symbol symbol-45px me-3">
                                            <span class="symbol-label bg-light-success">
                                                <i class="ki-outline ki-document text-success fs-2"></i>
                                            </span>
                                        </div>
                                        <h3 class="card-title fw-bold text-gray-800 mb-0">Başlamayan İşler</h3>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-auto">
                                        <div>
                                            <span class="text-gray-900 fs-2 fw-bold me-2"><?= $istek['acikGorevler']; ?></span>
                                            <span class="text-muted fw-semibold">İş Emri</span>
                                        </div>
                                        <span class="badge badge-light-success">Detay</span>
                                    </div>
                                    <small class="text-muted mt-2">Bekleyen ve henüz başlamamış iş emirleri</small>
                                </div>
                            </a>
                        </div>

                        <div class="col-lg-4 col-md-6 col-12">
                            <a href="/isemri/listesi?durum=Devam+Ediyor&derece=&kategori=&tarih_baslangic=&tarih_bitis=" class="card card-bordered hover-elevate-up h-100" data-bs-toggle="tooltip" title="Şu anda devam eden iş emirlerinizi görüntüleyin">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="symbol symbol-45px me-3">
                                            <span class="symbol-label bg-light-primary">
                                                <i class="ki-outline ki-arrows-loop text-primary fs-2"></i>
                                            </span>
                                        </div>
                                        <h3 class="card-title fw-bold text-gray-800 mb-0">Devam Eden İşler</h3>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-auto">
                                        <div>
                                            <span class="text-gray-900 fs-2 fw-bold me-2"><?= $istek['devamEdenGorevler']; ?></span>
                                            <span class="text-muted fw-semibold">İş Emri</span>
                                        </div>
                                        <span class="badge badge-light-primary">Detay</span>
                                    </div>
                                    <small class="text-muted mt-2">Şu anda aktif olarak çalışılan iş emirleri</small>
                                </div>
                            </a>
                        </div>

                        <div class="col-lg-4 col-md-6 col-12">
                    <a href="/isemri/listesi?durum=Tamamlandı&derece=&kategori=&tarih_baslangic=&tarih_bitis=" class="card card-bordered hover-elevate-up h-100" data-bs-toggle="tooltip" title="Tamamlanmış iş emirlerinizi görüntüleyin">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center mb-3">
                                <div class="symbol symbol-45px me-3">
                                    <span class="symbol-label bg-light-warning">
                                        <i class="ki-outline ki-check-square text-warning fs-2"></i>
                                    </span>
                                </div>
                                <h3 class="card-title fw-bold text-gray-800 mb-0">Tamamlanan İşler</h3>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-auto">
                                <div>
                                    <span class="text-gray-900 fs-2 fw-bold me-2"><?= $istek['kapaliGorevler']; ?></span>
                                    <span class="text-muted fw-semibold">İş Emri</span>
                                </div>
                                <span class="badge badge-light-warning">Detay</span>
                            </div>
                            <small class="text-muted mt-2">Başarıyla tamamlanmış iş emirleri</small>
                        </div>
                    </a>
                </div>
            </div>           
                    </div>
                    
                </div>
                
            </div>            

            <!-- İş Yönetimi Bölümü -->
            <div class="row mt-3 mt-lg-5">
                <div class="col-12">
                    <div class="card card-bordered">
                        <div class="card-header px-3 px-lg-6">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <span class="symbol-label bg-light-primary">
                                        <i class="ki-outline ki-element-11 text-primary fs-2"></i>
                                    </span>
                                </div>
                                <div>
                                    <h3 class="card-title fs-4 fs-lg-3 mb-1">İş Yönetimi</h3>
                                    <p class="text-muted fs-7 mb-0">İş emirleri ve müşteri yönetimi</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body px-3 px-lg-6">
                            <div class="row g-3 g-lg-3">
                                <div class="col-lg-4 col-md-6 col-12">
                                    <a href="/isemri/olustur" class="btn btn-flex btn-outline btn-text-gray-700 btn-active-light-primary w-100 py-4 py-lg-3">
                                        <i class="ki-outline ki-plus-circle fs-1 fs-lg-2 me-2"></i>
                                        <span class="fs-6 fs-lg-base">Yeni İş Emri</span>
                                    </a>
                                </div>
                                <div class="col-lg-4 col-md-6 col-12">
                                    <a href="/isemri/listesi" class="btn btn-flex btn-outline btn-text-gray-700 btn-active-light-primary w-100 py-4 py-lg-3">
                                        <i class="ki-outline ki-notepad-edit fs-1 fs-lg-2 me-2"></i>
                                        <span class="fs-6 fs-lg-base">İş Emri Listesi</span>
                                    </a>
                                </div>
                             
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Finansal İşlemler -->
            <div class="row mt-3 mt-lg-5">
                <div class="col-12">
                    <div class="card card-bordered">
                        <div class="card-header px-3 px-lg-6">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <span class="symbol-label bg-light-success">
                                        <i class="ki-outline ki-bill text-success fs-2"></i>
                                    </span>
                                </div>
                                <div>
                                    <h3 class="card-title fs-4 fs-lg-3 mb-1">Finansal İşlemler</h3>
                                    <p class="text-muted fs-7 mb-0">Gelir gider ve fatura yönetimi</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body px-3 px-lg-6">
                            <div class="row g-3 g-lg-3">
                                <div class="col-lg-4 col-md-6 col-12">
                                    <a href="/ciro/ekle" class="btn btn-flex btn-outline btn-text-gray-700 btn-active-light-success w-100 py-4 py-lg-3">
                                        <i class="ki-outline ki-chart-line-up fs-1 fs-lg-2 me-2"></i>
                                        <span class="fs-6 fs-lg-base">Ciro Ekle</span>
                                    </a>
                                </div>
                               
                                <div class="col-lg-4 col-md-6 col-12">
                                    <a href="/gider/listesi" class="btn btn-flex btn-outline btn-text-gray-700 btn-active-light-success w-100 py-4 py-lg-3">
                                        <i class="ki-outline ki-chart-line-down fs-1 fs-lg-2 me-2"></i>
                                        <span class="fs-6 fs-lg-base">Giderler</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

           
            <!-- Hesap ve Profil -->
            <div class="row mt-3 mt-lg-5">
                <div class="col-12">
                    <div class="card card-bordered">
                        <div class="card-header px-3 px-lg-6">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <span class="symbol-label bg-light-warning">
                                        <i class="ki-outline ki-profile-circle text-warning fs-2"></i>
                                    </span>
                                </div>
                                <div>
                                    <h3 class="card-title fs-4 fs-lg-3 mb-1">Hesap & Profil</h3>
                                    <p class="text-muted fs-7 mb-0">Kişisel bilgiler ve ayarlar</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body px-3 px-lg-6">
                            <div class="row g-3 g-lg-3">
                                <div class="col-lg-4 col-md-6 col-12">
                                    <a href="/profil" class="btn btn-flex btn-outline btn-text-gray-700 btn-active-light-warning w-100 py-4 py-lg-3">
                                        <i class="ki-outline ki-profile-user fs-1 fs-lg-2 me-2"></i>
                                        <span class="fs-6 fs-lg-base">Profil Görüntüle</span>
                                    </a>
                                </div>
                                <!--<div class="col-lg-4 col-md-6 col-12">
                                    <a href="/profil/guncelle" class="btn btn-flex btn-outline btn-text-gray-700 btn-active-light-warning w-100 py-4 py-lg-3">
                                        <i class="ki-outline ki-setting-2 fs-1 fs-lg-2 me-2"></i>
                                        <span class="fs-6 fs-lg-base">Profili Düzenle</span>
                                    </a>
                                </div>-->
                                <div class="col-lg-4 col-md-6 col-12">
                                    <a href="#" onclick="performLogout()" class="btn btn-flex btn-outline btn-text-gray-700 btn-active-light-danger w-100 py-4 py-lg-3" style="cursor: pointer;">
                                        <i class="ki-outline ki-exit-right fs-1 fs-lg-2 me-2"></i>
                                        <span class="fs-6 fs-lg-base">Çıkış Yap</span>
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
<?php
require_once 'app/Views/kullanici/layout/footer.php';
?>
