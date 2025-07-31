<?php
$title="<h2>Ayarlar</h2>";
$link = "OneSignal" ;

require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>
    <?php if (isset($message) && isset($messageType)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form  class="form fv-plugins-bootstrap5 fv-plugins-framework" action="/admin/onesignal/kaydet" method="POST">
        <!--begin::Input group-->
        <div class="row g-9 mb-8">
            <h2>OneSignal Ayarları</h2>
            <div class="col-md-6 fv-row fv-plugins-icon-container">
                <label class="fs-6 fw-semibold mb-2" for="onesignal_app_id">OneSignal App ID:</label><br>
                <input class="form-control form-control-solid" type="text" id="onesignal_app_id" name="onesignal_app_id" value="<?php echo $ayarlar['onesignal_app_id'] ?? ''; ?>" >

            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-md-6 fv-row">
                <label class="fs-6 fw-semibold mb-2" for="onesignal_api_key">OneSignal API Key:</label><br>
                <input class="form-control form-control-solid" type="text" id="onesignal_api_key" name="onesignal_api_key" value="<?php echo $ayarlar['onesignal_api_key'] ?? ''; ?>" >

            </div>
            <!--end::Col-->
        </div>
        <!--end::Input group-->

        <!--begin::Input group-->
        <div  class="row g-9 mb-8">
            <h2>Twilio (SMS) Ayarları</h2>
            <div class="col-md-6 fv-row fv-plugins-icon-container">
                <label class="fs-6 fw-semibold mb-2" for="twilio_sid">Twilio SID:</label><br>
                <input class="form-control form-control-solid" type="text" id="twilio_sid" name="twilio_sid" value="<?php echo $ayarlar['twilio_sid'] ?? ''; ?>" >
            </div>
            <div class="col-md-6 fv-row fv-plugins-icon-container">
                <label class="fs-6 fw-semibold mb-2" for="twilio_token">Twilio Token:</label><br>
                <input class="form-control form-control-solid" type="text" id="twilio_token" name="twilio_token" value="<?php echo $ayarlar['twilio_token'] ?? ''; ?>" >
            </div>
        </div>
        <div  class="row g-9 mb-8">
            <div class="col-md-6 fv-row fv-plugins-icon-container">
                <label class="fs-6 fw-semibold mb-2" for="twilio_phone">Twilio Telefon Numarası:</label><br>
                <input class="form-control form-control-solid" type="text" id="twilio_phone" name="twilio_phone" value="<?php echo $ayarlar['twilio_phone'] ?? ''; ?>" >
            </div>
        </div>
        <!--begin::Input group-->
        <div class="row g-9 mb-8">
            <h2>SendGrid (E-posta) Ayarları</h2><br>
              <div class="col-md-6 fv-row fv-plugins-icon-container">
                <label class="fs-6 fw-semibold mb-2" for="sendgrid_api_key">SendGrid API Key:</label>
                <input class="form-control form-control-solid" type="text" id="sendgrid_api_key" name="sendgrid_api_key" value="<?php echo $ayarlar['sendgrid_api_key'] ?? ''; ?>" >
             </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-md-6 fv-row">
                <label class="fs-6 fw-semibold mb-2" for="sendgrid_from_email">SendGrid Gönderen E-posta:</label><br>
                <input  class="form-control form-control-solid"  type="email" id="sendgrid_from_email" name="sendgrid_from_email" value="<?php echo $ayarlar['sendgrid_from_email'] ?? ''; ?>" >
            </div>
            <!--end::Col-->
        </div>
        <!--end::Input group-->
        <!--begin::Actions-->
        <div class="text-left">
            <button type="submit" id="kt_modal_new_target_submit" class="btn btn-primary">
                <span class="indicator-label">Kaydet</span>
                <span class="indicator-progress">lütfen Bekleyin..
				<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>
        </div>
        <!--end::Actions-->
    </form>
<?php
require_once 'app/Views/layouts/footer.php';