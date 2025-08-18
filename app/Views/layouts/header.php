<!DOCTYPE html>
<html lang="tr">
<head>
    <base href="/"/>
 
	<title>Mağaza Yönetim Paneli</title>
	<meta charset="utf-8" />
    <meta name="theme-color" content="#FFFFFF">
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta property="og:locale" content="tr_TR" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Mağaza Takip">
    <link rel="apple-touch-icon" href="/public/icons/icon-192x192.png">
    <link rel="manifest" href="/public/manifest.json">
    <link rel="shortcut icon" href="/public/media/logos/favicon.ico" />
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
	<link href="/public/plugins/custom/datatables/datatables.bundle.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
	<link href="/public/plugins/custom/vis-timeline/vis-timeline.bundle.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
	<link href="/public/plugins/global/plugins.bundle.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
	<link href="/public/css/style.bundle.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
    <script src="/public/plugins/global/plugins.bundle.js?v=<?php echo time(); ?>"></script>
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrf_token()); ?>">
	<script> if (window.top != window.self) { window.top.location.replace(window.self.location.href); }</script>
    <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js?v=<?php echo time(); ?>" defer></script>
    <script>
        // Oturum açıksa OneSignal alias eşlemesi için CURRENT_USER_ID'yi global olarak yayınla
        window.CURRENT_USER_ID = <?php echo isset($_SESSION['user_id']) ? json_encode((string)$_SESSION['user_id']) : 'null'; ?>;
    </script>
  
    <script src="/public/js/token-registration.js?v=<?php echo time(); ?>" defer></script>

</head>
<body id="kt_app_body" data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true" data-kt-app-sidebar-push-footer="true" class="app-default">
<!-- Global Page Loader Overlay -->
<div id="pageLoaderOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="z-index: 9000; background: rgba(255,255,255,0.8);">
    <div class="d-flex w-100 h-100 justify-content-center align-items-center">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
            <div class="mt-3 fw-semibold text-muted">Sayfa yükleniyor...</div>
        </div>
    </div>
</div>
<script>
    const defaultThemeMode = 'light'; // light|dark|system
    let themeMode;

    if ( document.documentElement ) {
        if ( localStorage.getItem('theme')) {
            themeMode = localStorage.getItem('theme');
        } else if ( document.documentElement.hasAttribute('data-theme-mode')) {
            themeMode = document.documentElement.getAttribute('data-theme-mode');
        } else {
            themeMode = defaultThemeMode;
        }

        if (themeMode === 'system') {
            themeMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }

        document.documentElement.classList.add(themeMode);
    }
</script>
<?php if (isset($_SESSION['alert_message'])): ?>
    <script>
        (function(){
            try {
                var msg = <?php echo json_encode($_SESSION['alert_message']['text'] ?? ''); ?>;
                var icon = <?php echo json_encode($_SESSION['alert_message']['icon'] ?? 'info'); ?>;
                var confirmText = <?php echo json_encode($_SESSION['alert_message']['confirmButtonText'] ?? 'Tamam'); ?>;
                if (window.Swal && msg) {
                    Swal.fire({
                        text: msg,
                        icon: icon || 'info',
                        buttonsStyling: false,
                        confirmButtonText: confirmText,
                        customClass: { confirmButton: 'btn btn-primary' }
                    });
                } else if (typeof showToast === 'function' && msg) {
                    showToast(msg, icon === 'success' ? 'success' : (icon === 'error' ? 'danger' : 'info'));
                }
            } catch (e) {}
        })();
    </script>
    <?php unset($_SESSION['alert_message']); ?>
<?php endif; ?>
<div class="d-flex flex-column flex-root app-root" id="kt_app_root">
	<!--begin::Page-->
	<div class="app-page flex-column flex-column-fluid" id="kt_app_page">













