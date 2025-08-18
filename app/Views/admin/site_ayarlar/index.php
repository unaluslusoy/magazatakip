<?php
$title = "Site Ayarları";
$link = "Ayarlar";

require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>

<?php if (isset($message) && isset($messageType)): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
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
                    <h2>Site Ayarları</h2>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->

            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Tabs wrapper-->
                <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6" id="ayarlarTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="genel-tab" data-bs-toggle="tab" href="#genel" role="tab" aria-controls="genel" aria-selected="true">
                            <i class="ki-outline ki-gear fs-4 me-2"></i>Genel Ayarlar
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="onesignal-tab" data-bs-toggle="tab" href="#onesignal" role="tab" aria-controls="onesignal" aria-selected="false">
                            <i class="ki-outline ki-notification-on fs-4 me-2"></i>Bildirim Ayarları
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="iletisim-tab" data-bs-toggle="tab" href="#iletisim" role="tab" aria-controls="iletisim" aria-selected="false">
                            <i class="ki-outline ki-phone fs-4 me-2"></i>İletişim Bilgileri
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="sosyal-tab" data-bs-toggle="tab" href="#sosyal" role="tab" aria-controls="sosyal" aria-selected="false">
                            <i class="ki-outline ki-share fs-4 me-2"></i>Sosyal Medya
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="mail-tab" data-bs-toggle="tab" href="#mail" role="tab" aria-controls="mail" aria-selected="false">
                            <i class="ki-outline ki-sms fs-4 me-2"></i>Mail Ayarları
                        </a>
                    </li>
                </ul>
                <!--end::Tabs wrapper-->

                <!--begin::Tab content-->
                <div class="tab-content" id="ayarlarTabContent">
                    <!--begin::Genel Ayarlar Tab-->
                    <div class="tab-pane fade show active" id="genel" role="tabpanel" aria-labelledby="genel-tab">
                        <form action="/admin/site-ayarlar/genel-kaydet" method="POST" class="form fv-plugins-bootstrap5 fv-plugins-framework">
                            <?= csrf_field(); ?>
                            <div class="row g-9 mb-8">
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="site_adi">Site Adı:</label>
                                    <input class="form-control form-control-solid" type="text" id="site_adi" name="site_adi" value="<?= $siteAyarlar['site_adi'] ?? '' ?>" required>
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="site_aciklama">Site Açıklaması:</label>
                                    <input class="form-control form-control-solid" type="text" id="site_aciklama" name="site_aciklama" value="<?= $siteAyarlar['site_aciklama'] ?? '' ?>">
                                </div>
                            </div>

                            <div class="row g-9 mb-8">
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="site_keywords">Anahtar Kelimeler:</label>
                                    <input class="form-control form-control-solid" type="text" id="site_keywords" name="site_keywords" value="<?= $siteAyarlar['site_keywords'] ?? '' ?>" placeholder="kelime1, kelime2, kelime3">
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="site_logo">Site Logo URL:</label>
                                    <input class="form-control form-control-solid" type="text" id="site_logo" name="site_logo" value="<?= $siteAyarlar['site_logo'] ?? '' ?>">
                                </div>
                            </div>

                            <div class="row g-9 mb-8">
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="site_favicon">Favicon URL:</label>
                                    <input class="form-control form-control-solid" type="text" id="site_favicon" name="site_favicon" value="<?= $siteAyarlar['site_favicon'] ?? '' ?>">
                                </div>
                                <div class="col-md-6 fv-row">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="bakim_modu" name="bakim_modu" <?= ($siteAyarlar['bakim_modu'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="bakim_modu">Bakım Modu</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-9 mb-8">
                                <div class="col-12 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="bakim_mesaji">Bakım Modu Mesajı:</label>
                                    <textarea class="form-control form-control-solid" id="bakim_mesaji" name="bakim_mesaji" rows="3"><?= $siteAyarlar['bakim_mesaji'] ?? '' ?></textarea>
                                </div>
                            </div>

                            <div class="row g-9 mb-8">
                                <div class="col-md-6 fv-row">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="log_enabled" name="log_enabled" <?= (($logAyarlar['enabled'] ?? true) ? 'checked' : '') ?>>
                                        <label class="form-check-label" for="log_enabled">Uygulama Logları Açık</label>
                                    </div>
                                </div>
                                <div class="col-md-6 fv-row">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="log_slow" name="log_slow" <?= (($logAyarlar['slow'] ?? true) ? 'checked' : '') ?>>
                                        <label class="form-check-label" for="log_slow">Yavaş İstek Logları Açık</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-9 mb-8">
                                <div class="col-md-6 fv-row">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="log_router" name="log_router" <?= (($logAyarlar['router'] ?? false) ? 'checked' : '') ?>>
                                        <label class="form-check-label" for="log_router">Router İstek Logları (DEBUG hariç)</label>
                                    </div>
                                </div>
                                <div class="col-md-6 fv-row d-flex align-items-center text-muted">
                                    <small>Yavaş istek eşiğini ENV `SLOW_LOG_MS` ile belirleyebilirsiniz.</small>
                                </div>
                            </div>

                            <div class="row g-9 mb-8">
                                <div class="col-12">
                                    <h4 class="mb-3">Rate Limit</h4>
                                </div>
                                <div class="col-md-3 fv-row">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="rl_enabled" name="rl_enabled" <?= (($rateLimit['enabled'] ?? true) ? 'checked' : '') ?>>
                                        <label class="form-check-label" for="rl_enabled">Rate Limit Aktif</label>
                                    </div>
                                </div>
                                <div class="col-md-3 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="rl_window">Pencere (sn):</label>
                                    <input class="form-control form-control-solid" type="number" min="1" id="rl_window" name="rl_window" value="<?= (int)($rateLimit['window_seconds'] ?? 60) ?>">
                                </div>
                                <div class="col-md-3 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="rl_default_max">Varsayılan Limit:</label>
                                    <input class="form-control form-control-solid" type="number" min="1" id="rl_default_max" name="rl_default_max" value="<?= (int)($rateLimit['default']['max_requests'] ?? 60) ?>">
                                </div>
                                <div class="col-md-3 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="rl_login_max">/api/auth/login Limiti:</label>
                                    <input class="form-control form-control-solid" type="number" min="1" id="rl_login_max" name="rl_login_max" value="<?= (int)($rateLimit['overrides']['api/auth/login']['max_requests'] ?? 10) ?>">
                                </div>
                            </div>

                            <div class="card-footer d-flex justify-content-end py-6 px-9">
                                <button formaction="/admin/site-ayarlar/log-kaydet" type="submit" class="btn btn-light-primary me-3">
                                    <i class="ki-outline ki-setting fs-2"></i>Log Ayarlarını Kaydet
                                </button>
                                <button formaction="/admin/site-ayarlar/rate-limit-kaydet" type="submit" class="btn btn-light-warning me-3">
                                    <i class="ki-outline ki-time fs-2"></i>Rate Limit Ayarlarını Kaydet
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ki-outline ki-check fs-2"></i>Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                    <!--end::Genel Ayarlar Tab-->

                    <!--begin::Bildirim Ayarları Tab-->
                    <div class="tab-pane fade" id="onesignal" role="tabpanel" aria-labelledby="onesignal-tab">
                        <form action="/admin/site-ayarlar/onesignal-kaydet" method="POST" class="form fv-plugins-bootstrap5 fv-plugins-framework">
                            <?= csrf_field(); ?>
                            <div class="row g-9 mb-8">
                                <div class="col-12">
                                    <h4 class="mb-4">OneSignal Ayarları</h4>
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="onesignal_app_id">OneSignal App ID:</label>
                                    <input class="form-control form-control-solid" type="text" id="onesignal_app_id" name="onesignal_app_id" value="<?= $oneSignalAyarlar['onesignal_app_id'] ?? '' ?>">
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="onesignal_api_key">OneSignal API Key:</label>
                                    <input class="form-control form-control-solid" type="text" id="onesignal_api_key" name="onesignal_api_key" value="<?= $oneSignalAyarlar['onesignal_api_key'] ?? '' ?>">
                                </div>
                            </div>

                            <div class="row g-9 mb-8">
                                <div class="col-12">
                                    <h4 class="mb-4">Twilio SMS Ayarları</h4>
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="twilio_sid">Twilio SID:</label>
                                    <input class="form-control form-control-solid" type="text" id="twilio_sid" name="twilio_sid" value="<?= $oneSignalAyarlar['twilio_sid'] ?? '' ?>">
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="twilio_token">Twilio Token:</label>
                                    <input class="form-control form-control-solid" type="text" id="twilio_token" name="twilio_token" value="<?= $oneSignalAyarlar['twilio_token'] ?? '' ?>">
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="twilio_phone">Twilio Telefon:</label>
                                    <input class="form-control form-control-solid" type="text" id="twilio_phone" name="twilio_phone" value="<?= $oneSignalAyarlar['twilio_phone'] ?? '' ?>">
                                </div>
                            </div>

                            <div class="row g-9 mb-8">
                                <div class="col-12">
                                    <h4 class="mb-4">SendGrid E-posta Ayarları</h4>
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="sendgrid_api_key">SendGrid API Key:</label>
                                    <input class="form-control form-control-solid" type="text" id="sendgrid_api_key" name="sendgrid_api_key" value="<?= $oneSignalAyarlar['sendgrid_api_key'] ?? '' ?>">
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="sendgrid_from_email">Gönderen E-posta:</label>
                                    <input class="form-control form-control-solid" type="email" id="sendgrid_from_email" name="sendgrid_from_email" value="<?= $oneSignalAyarlar['sendgrid_from_email'] ?? '' ?>">
                                </div>
                            </div>

                            <div class="card-footer d-flex justify-content-end py-6 px-9">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ki-outline ki-check fs-2"></i>Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                    <!--end::Bildirim Ayarları Tab-->

                    <!--begin::İletişim Bilgileri Tab-->
                    <div class="tab-pane fade" id="iletisim" role="tabpanel" aria-labelledby="iletisim-tab">
                        <form action="/admin/site-ayarlar/iletisim-kaydet" method="POST" class="form fv-plugins-bootstrap5 fv-plugins-framework">
                            <?= csrf_field(); ?>
                            <div class="row g-9 mb-8">
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="iletisim_email">İletişim E-posta:</label>
                                    <input class="form-control form-control-solid" type="email" id="iletisim_email" name="iletisim_email" value="<?= $siteAyarlar['iletisim_email'] ?? '' ?>">
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="iletisim_telefon">İletişim Telefon:</label>
                                    <input class="form-control form-control-solid" type="tel" id="iletisim_telefon" name="iletisim_telefon" value="<?= $siteAyarlar['iletisim_telefon'] ?? '' ?>">
                                </div>
                            </div>

                            <div class="row g-9 mb-8">
                                <div class="col-12 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="iletisim_adres">İletişim Adresi:</label>
                                    <textarea class="form-control form-control-solid" id="iletisim_adres" name="iletisim_adres" rows="3"><?= $siteAyarlar['iletisim_adres'] ?? '' ?></textarea>
                                </div>
                            </div>

                            <div class="card-footer d-flex justify-content-end py-6 px-9">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ki-outline ki-check fs-2"></i>Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                    <!--end::İletişim Bilgileri Tab-->

                    <!--begin::Sosyal Medya Tab-->
                    <div class="tab-pane fade" id="sosyal" role="tabpanel" aria-labelledby="sosyal-tab">
                        <form action="/admin/site-ayarlar/sosyal-kaydet" method="POST" class="form fv-plugins-bootstrap5 fv-plugins-framework">
                            <?= csrf_field(); ?>
                            <div class="row g-9 mb-8">
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="sosyal_medya_facebook">Facebook URL:</label>
                                    <input class="form-control form-control-solid" type="url" id="sosyal_medya_facebook" name="sosyal_medya_facebook" value="<?= $siteAyarlar['sosyal_medya_facebook'] ?? '' ?>">
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="sosyal_medya_twitter">Twitter URL:</label>
                                    <input class="form-control form-control-solid" type="url" id="sosyal_medya_twitter" name="sosyal_medya_twitter" value="<?= $siteAyarlar['sosyal_medya_twitter'] ?? '' ?>">
                                </div>
                            </div>

                            <div class="row g-9 mb-8">
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="sosyal_medya_instagram">Instagram URL:</label>
                                    <input class="form-control form-control-solid" type="url" id="sosyal_medya_instagram" name="sosyal_medya_instagram" value="<?= $siteAyarlar['sosyal_medya_instagram'] ?? '' ?>">
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="sosyal_medya_linkedin">LinkedIn URL:</label>
                                    <input class="form-control form-control-solid" type="url" id="sosyal_medya_linkedin" name="sosyal_medya_linkedin" value="<?= $siteAyarlar['sosyal_medya_linkedin'] ?? '' ?>">
                                </div>
                            </div>

                            <div class="card-footer d-flex justify-content-end py-6 px-9">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ki-outline ki-check fs-2"></i>Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                    <!--end::Sosyal Medya Tab-->

                    <!--begin::Mail Ayarları Tab-->
                    <div class="tab-pane fade" id="mail" role="tabpanel" aria-labelledby="mail-tab">
                        <form action="/admin/site-ayarlar/mail-kaydet" method="POST" class="form fv-plugins-bootstrap5 fv-plugins-framework">
                            <?= csrf_field(); ?>
                            <div class="row g-9 mb-8">
                                <div class="col-12">
                                    <h4 class="mb-4">Yandex SMTP Ayarları</h4>
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="smtp_driver">Driver:</label>
                                    <select class="form-select form-select-solid" id="smtp_driver" name="smtp_driver">
                                        <option value="smtp" <?= (($mailAyarlar['smtp_driver'] ?? 'smtp') === 'smtp') ? 'selected' : '' ?>>SMTP</option>
                                    </select>
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="smtp_host">Sunucu (Host):</label>
                                    <input class="form-control form-control-solid" type="text" id="smtp_host" name="smtp_host" value="<?= $mailAyarlar['smtp_host'] ?? 'smtp.yandex.com' ?>" placeholder="smtp.yandex.com">
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="smtp_port">Port:</label>
                                    <input class="form-control form-control-solid" type="number" id="smtp_port" name="smtp_port" value="<?= $mailAyarlar['smtp_port'] ?? 465 ?>" placeholder="465">
                                </div>
                            </div>

                            <div class="row g-9 mb-8">
                                <div class="col-md-4 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="smtp_encryption">Şifreleme:</label>
                                    <select class="form-select form-select-solid" id="smtp_encryption" name="smtp_encryption">
                                        <?php $enc = $mailAyarlar['smtp_encryption'] ?? 'ssl'; ?>
                                        <option value="ssl" <?= ($enc === 'ssl') ? 'selected' : '' ?>>SSL</option>
                                        <option value="tls" <?= ($enc === 'tls') ? 'selected' : '' ?>>TLS</option>
                                        <option value="" <?= ($enc === '' || $enc === null) ? 'selected' : '' ?>>Yok</option>
                                    </select>
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="smtp_username">Kullanıcı Adı (E-posta):</label>
                                    <input class="form-control form-control-solid" type="text" id="smtp_username" name="smtp_username" value="<?= $mailAyarlar['smtp_username'] ?? '' ?>" placeholder="noreply@alanadiniz.com.tr">
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="smtp_password">Parola:</label>
                                    <input class="form-control form-control-solid" type="password" id="smtp_password" name="smtp_password" value="<?= $mailAyarlar['smtp_password'] ?? '' ?>" autocomplete="new-password">
                                </div>
                            </div>

                            <div class="row g-9 mb-8">
                                <div class="col-md-4 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="from_email">Gönderen E-posta:</label>
                                    <input class="form-control form-control-solid" type="email" id="from_email" name="from_email" value="<?= $mailAyarlar['from_email'] ?? '' ?>">
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="from_name">Gönderen Adı:</label>
                                    <input class="form-control form-control-solid" type="text" id="from_name" name="from_name" value="<?= $mailAyarlar['from_name'] ?? 'MagazaTakip' ?>">
                                </div>
                                <div class="col-md-4 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="reply_to_email">Yanıt E-postası (Reply-To):</label>
                                    <input class="form-control form-control-solid" type="email" id="reply_to_email" name="reply_to_email" value="<?= $mailAyarlar['reply_to_email'] ?? '' ?>">
                                </div>
                            </div>

                            <div class="card-footer d-flex justify-content-end py-6 px-9">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ki-outline ki-check fs-2"></i>Kaydet
                                </button>
                            </div>
                        </form>

                        <div class="separator my-10"></div>
                        <form action="/admin/site-ayarlar/mail-test" method="POST" class="form fv-plugins-bootstrap5 fv-plugins-framework">
                            <?= csrf_field(); ?>
                            <div class="row g-9 mb-8">
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2" for="test_email">Test E-posta Adresi:</label>
                                    <input class="form-control form-control-solid" type="email" id="test_email" name="test_email" placeholder="ornek@alan.com" required>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="submit" class="btn btn-light-primary">
                                        <i class="ki-outline ki-paper-plane fs-2"></i> Test Mail Gönder
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!--end::Mail Ayarları Tab-->
                </div>
                <!--end::Tab content-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Content container-->
</div>
<!--end::Content-->

<script>
// URL'deki hash'e göre tab'ı aktif et
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash;
    if (hash) {
        const tab = document.querySelector(`a[href="${hash}"]`);
        if (tab) {
            const tabInstance = new bootstrap.Tab(tab);
            tabInstance.show();
        }
    }
});
</script>

<?php require_once 'app/Views/layouts/footer.php'; ?> 