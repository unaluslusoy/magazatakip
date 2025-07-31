<!DOCTYPE html>
<html lang="tr" data-bs-theme="light">
<head>
    <base href="/anasayfa"/>
    <title>Mağaza Yönetim Paneli</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta property="og:locale" content="tr_TR" />
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Mağaza Takip">

    
    <!-- Ana tema rengi -->
    <meta name="theme-color" content="#FFFFFF">
    <!-- iOS Safari -->
    <meta name="apple-mobile-web-app-status-bar-style" content="default"
    
    <link rel="icon" href="/public/media/logos/default.svg" type="image/svg+xml">
    <link rel="alternate icon" href="/public/media/logos/favicon.ico" type="image/x-icon">
    
    <!-- Apple touch icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="/public/images/apple-touch-icon.png">
    
    <!-- Primary App Icon for PWA -->
    <link rel="apple-touch-startup-image" href="https://magazatakip.com.tr/public/media/logos/default.svg">
    <meta name="apple-mobile-web-app-title" content="Mağaza Takip">
    <meta name="application-name" content="Mağaza Takip">
    <link rel="apple-touch-icon" sizes="152x152" href="/public/images/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/public/images/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/public/images/apple-touch-icon-120x120.png">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/public/manifest.json">

    <!-- OneSignal SDK -->
    <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" defer></script>
    
    <!-- PWA Core Scripts -->
    <script defer>
        // DOM ready kontrolü
        document.addEventListener('DOMContentLoaded', function() {
            // Script loading sırası kontrolü
            const scriptsToLoad = [
                '/public/js/dom-safety.js',
                '/public/js/compatibility-fix.js',
                '/public/js/error-handler.js',
                '/public/js/pwa-analytics.js',
                '/auth-guard.js',
                '/public/js/network-monitor.js',
                '/public/js/splash-screen.js',
                '/public/js/business-loader.js',
                '/public/js/page-transitions.js',
                '/public/js/pwa-install.js', 
                '/public/js/background-sync.js',
                '/public/js/pull-to-refresh.js',
                '/public/js/view-transitions.js',
                '/public/js/modern-pull-to-refresh.js',
                '/public/js/app-update-manager.js',
                '/public/js/pwa-features-demo.js',
                '/public/js/token-registration.js'
            ];
            
            function loadScriptSequentially(index) {
                if (index >= scriptsToLoad.length) {
                    // Tüm scriptler yüklendi, health check'i başlat
                    const healthCheckScript = document.createElement('script');
                    healthCheckScript.src = '/pwa-health-check.js';
                    healthCheckScript.onload = () => {
                        console.log('✅ Tüm PWA scriptleri yüklendi');
                        if (localStorage.getItem('pwa_debug') === 'true') {
                            console.log('🛠 Debug mode aktif - Console\'da pwaHealthCheck() çalıştırabilirsiniz');
                        }
                    };
                    document.head.appendChild(healthCheckScript);
                    return;
                }
                
                const script = document.createElement('script');
                script.src = scriptsToLoad[index];
                script.onload = () => {
                    console.log('Loaded:', scriptsToLoad[index]);
                    loadScriptSequentially(index + 1);
                };
                script.onerror = () => {
                    console.warn('Failed to load script:', scriptsToLoad[index]);
                    loadScriptSequentially(index + 1);
                };
                document.head.appendChild(script);
            }
            
            console.log('🚀 PWA Script loading başlatıldı...');
            loadScriptSequentially(0);
        });
    </script>

    <link rel="shortcut icon" href="/public/media/logos/favicon.ico" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="/public/plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />
    <link href="/public/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
    <link href="/public/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="/public/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="/public/css/pull-to-refresh-themes.css" rel="stylesheet" type="text/css" />
    
    <!-- Mobile Optimizations -->
    <style>
        /* Mobil dokunmatik iyileştirmeleri */
        @media (max-width: 991.98px) {
            .card {
                border-radius: 12px !important;
                box-shadow: 0 2px 12px rgba(0,0,0,0.08) !important;
            }
            
            .btn {
                min-height: 44px !important;
                border-radius: 8px !important;
            }
            
            .card-header {
                border-radius: 12px 12px 0 0 !important;
            }
            
            /* Pull to refresh desteği */
            body {
                overscroll-behavior: contain;
            }
            
            /* Dokunmatik geri bildirim */
            .btn:active, .card:active {
                transform: scale(0.98);
                transition: transform 0.1s ease;
            }
            
            /* Mobil için daha büyük hedef alanlar */
            .symbol {
                min-width: 44px !important;
                min-height: 44px !important;
            }
            
            /* Mobilde daha iyi görünürlük */
            .text-muted {
                color: #6c757d !important;
            }
            
            /* Pull-to-refresh desteği */
            body {
                overscroll-behavior-y: contain !important;
                -webkit-overflow-scrolling: touch;
            }
        }
        
        /* Mobil navigasyon iyileştirmeleri */
        @media (max-width: 576px) {
            .container-fluid {
                padding-left: 15px !important;
                padding-right: 15px !important;
            }
            
            .card-body {
                padding: 1rem !important;
            }
        }
    </style>

</head>
<body id="kt_app_body" data-kt-app-page-loading-enabled="true" data-kt-app-layout="dark-header" data-kt-app-header-fixed="true" data-kt-app-toolbar-enabled="true" class="antialiased flex h-full text-base text-foreground bg-background kt-header-fixed">
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/service-worker.js')
            .then(function(registration) {
                console.log('Service Worker registered with scope:', registration.scope);
            }).catch(function(error) {
            console.log('Service Worker registration failed:', error);
        });
    }
    if (window.top != window.self) { window.top.location.replace(window.self.location.href); }
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
<div class="d-flex flex-column flex-root app-root" id="kt_app_root">
    <!--begin::Page-->
    <div class="app-page  flex-column flex-column-fluid " id="kt_app_page">
