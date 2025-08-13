<?php
$title = "<h2>Giriş</h2>";
$link = "Oturum Aç";
require_once 'app/Views/layouts/header.php';
?>

    <style>body { background-image: url('/public/media/auth/bg10.jpeg'); } [data-bs-theme="dark"] body { background-image: url('/public/media/auth/bg10-dark.jpeg'); }</style>
    <!--end::Page bg image-->
    <!--begin::Authentication - Sign-in -->
    <div class="d-flex flex-column flex-lg-row flex-column-fluid">
        <!--begin::Aside-->

        <div class="d-flex flex-lg-row-fluid">
            <!--begin::Content-->
            <div class="d-flex flex-column flex-center pb-0 pb-lg-10 p-10 w-100">
                <!--begin::Image-->
                <img class="theme-light-show mx-auto mw-100 w-95px  w-lg-300px mb-10 mb-lg-20" src="/public/media/auth/agency.png" alt="" />
                <img class="theme-dark-show mx-auto mw-100 w-95px w-lg-300px mb-10 mb-lg-20" src="/public/media/auth/agency-dark.png" alt="" />
                <!--end::Image-->
                <!--begin::Title-->
                <h1 class="text-gray-800 fs-2qx fw-bold text-center mb-7">İş ve Mağaza Takibi</h1>
                <!--end::Title-->
                <!--begin::Text-->
                <div class="text-gray-600 fs-base text-center fw-semibold">
                    Uygulamamız, mağaza envanter yönetiminden personel görev takibine kadar geniş bir yelpazede hizmet sunar.

                </div>
                <!--end::Text-->
            </div>
            <!--end::Content-->
        </div>
        <!--begin::Aside-->
        <!--begin::Body-->
        <div class="flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12">
            <!--begin::Wrapper-->
            <div class="bg-body  flex-column flex-center rounded-4 w-md-750px p-10">
                <!--begin::Content-->
                <div class="d-flex flex-center flex-column align-items-stretch h-lg-100 w-md-400px">
                    <!--begin::Wrapper-->
                    <div class="d-flex flex-center flex-column flex-column-fluid pb-15 pb-lg-20">
                        <!--begin::Form-->
                        <form class="form w-100" novalidate="novalidate" id="kt_sign_in_form" method="post" action="/auth/giris">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="cihaz_token" id="cihaz_token">
                            <input type="hidden" name="isletim_sistemi" id="isletim_sistemi">
                            <!--begin::Heading-->
                            <div class="text-center mb-11">
                                <!--begin::Title-->
                                <h1 class="text-gray-900 fw-bolder mb-3">Oturum Aç</h1>
                                <!--end::Title-->
                            </div>
                            <!--begin::Heading-->

                            <!--begin::Input group=-->
                            <div class="fv-row mb-3">
                                <label for="email"></label>
                                <input type="email" placeholder="E-Posta" class="form-control" name="email" id="email" required>
                            </div>
                            <!--end::Input group=-->
                            <div class="fv-row mb-3">
                                <label for="password"></label>
                                <div class="position-relative">
                                    <input type="password" placeholder="Şifre" class="form-control" name="password" id="password" required>
                                    <span class="btn btn-sm btn-icon position-absolute translate-middle-y top-50 end-0 me-n2" id="toggle-password" style="cursor: pointer;">
                                        <i class="bi bi-eye-slash fs-2" id="password-eye"></i>
                                        <i class="bi bi-eye fs-2 d-none" id="password-eye-open"></i>
                                    </span>
                                </div>
                            </div>
                            <!--end::Input group=-->
                            <!--begin::Wrapper-->

                            <br>
                            <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
                                <div>
                                    <input type="checkbox" class="form-check-input" name="remember" id="remember">
                                    <label class="form-check-label" for="remember">Beni hatırla</label>
                                </div>
                                <!--begin::Link-->
                                <a href="#" class="link-primary">Şifremi unuttum</a>
                                <!--end::Link-->
                            </div>
                            <!--end::Wrapper-->
                            <!--begin::Submit button-->
                            <div class="d-grid mb-10">
                                <?php if (!empty($error)): ?>
                                    <div class="alert alert-danger">
                                        <?php 
                                        // Hata mesajını güvenli bir şekilde göster
                                        $error_message = '';
                                        if (is_array($error)) {
                                            // Dizinin her bir elemanını kontrol et
                                            foreach ($error as $err) {
                                                if (is_string($err)) {
                                                    $error_message .= $err . ' ';
                                                }
                                            }
                                        } elseif (is_string($error)) {
                                            $error_message = $error;
                                        }
                                        
                                        // Eğer hata mesajı boşsa, genel bir hata mesajı göster
                                        echo !empty(trim($error_message)) ? $error_message : 'Bilinmeyen bir hata oluştu';
                                        ?>
                                    </div>
                                <?php endif; ?>
                                <button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
                                    <!--begin::Indicator label-->
                                    <span class="indicator-label">Giriş Yap</span>
                                    <!--end::Indicator label-->
                                    <!--begin::Indicator progress-->
                                    <span class="indicator-progress">Lütfen bekleyiniz...
											<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    <!--end::Indicator progress-->
                                </button>
                            </div>
                            <!--end::Submit button-->
                        </form>
                        <!--end::Form-->
                    </div>
                    <!--end::Wrapper-->
                    <!--begin::Footer-->
                    <div class="d-flex flex-stack">

                    </div>
                    <!--end::Footer-->
                </div>
                <!--end::Content-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Body-->
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Parola göster/gizle fonksiyonu
        const togglePassword = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('password-eye');
        const eyeOpenIcon = document.getElementById('password-eye-open');

        togglePassword.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.add('d-none');
                eyeOpenIcon.classList.remove('d-none');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('d-none');
                eyeOpenIcon.classList.add('d-none');
            }
        });

        // GÜVENLI: Sadece email'i hatırla, şifreyi ASLA localStorage'da saklama
        if (localStorage.getItem('rememberEmail') === 'true') {
            const savedEmail = localStorage.getItem('email');
            
            if (savedEmail) {
                document.getElementById('email').value = savedEmail;
                document.getElementById('remember').checked = true;
                // Şifreyi otomatik doldurmuyoruz - güvenlik riski
                document.getElementById('password').focus(); // Kullanıcı şifreyi manuel girmeli
            }
        }

        // Form submit olduğunda çalışacak fonksiyon
        document.getElementById('kt_sign_in_form').addEventListener('submit', function() {
            if (document.getElementById('remember').checked) {
                // Sadece email'i hatırla
                localStorage.setItem('email', document.getElementById('email').value);
                localStorage.setItem('rememberEmail', 'true');
            } else {
                // Tüm bilgileri temizle
                localStorage.removeItem('email');
                localStorage.removeItem('rememberEmail');
            }
            // Şifreyi ASLA localStorage'da saklama!
        });

        // OneSignal entegrasyonu
        if (typeof OneSignal !== 'undefined') {
            OneSignal.getUserId().then(function(userId) {
                if (userId) {
                    document.getElementById('cihaz_token').value = userId;
                }
            });
        }

        // İşletim sistemi tespiti
        var userAgent = navigator.userAgent || navigator.vendor || window.opera;
        var isletimSistemi = "bilinmeyen";
        if (/windows phone/i.test(userAgent)) {
            isletimSistemi = "Windows Phone";
        } else if (/android/i.test(userAgent)) {
            isletimSistemi = "Android";
        } else if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
            isletimSistemi = "iOS";
        }
        document.getElementById('isletim_sistemi').value = isletimSistemi;
    });
</script>
<?php
require_once 'app/Views/layouts/footer.php';
?>