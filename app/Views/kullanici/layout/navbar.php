
<div id="kt_app_header" class="app-header " data-kt-sticky="true" data-kt-sticky-activate="{default: true, lg: true}" data-kt-sticky-name="app-header-minimize" data-kt-sticky-offset="{default: '200px', lg: '0'}" data-kt-sticky-animation="false">
    <!--begin::Header container-->
    <div class="app-container  container-fluid d-flex align-items-stretch justify-content-between " id="kt_app_header_container">
        <!--begin::Logo-->
        <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0 me-lg-15">
            <a href="/anasayfa">
                <img alt="Logo" src="/public/media/logos/default.svg" class="h-35px h-lg-35px app-sidebar-logo-default theme-light-show">
                <img alt="Logo" src="/public/media/logos/default-dark.svg" class="h-35px h-lg-35px app-sidebar-logo-default theme-dark-show">
            </a>
        </div>
        <!--end::Logo-->
        <!--begin::Header wrapper-->
        <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1" id="kt_app_header_wrapper">
            
            <!--begin::Navbar-->
            <div class="app-navbar flex-shrink-0">
               
                <!--begin::Theme mode-->
                <div class="app-navbar-item ms-1 ms-md-4">

                    <!--begin::Menu toggle-->
                    <a href="#" class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-40px h-40px w-lg-35px h-lg-35px" data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                        <i class="ki-outline ki-night-day theme-light-show fs-1"></i>    <i class="ki-outline ki-moon theme-dark-show fs-1"></i></a>
                    <!--begin::Menu toggle-->

                    <!--begin::Menu-->
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px" data-kt-menu="true" data-kt-element="theme-mode-menu">
                        <!--begin::Menu item-->
                        <div class="menu-item px-3 my-0">
                            <a href="#" class="menu-link px-3 py-2 active" data-kt-element="mode" data-kt-value="light">
                            <span class="menu-icon" data-kt-element="icon">
                                <i class="ki-outline ki-night-day fs-2"></i>
                            </span>
                            <span class="menu-title">
                                Light
                            </span>
                            </a>
                        </div>
                        <!--end::Menu item-->

                        <!--begin::Menu item-->
                        <div class="menu-item px-3 my-0">
                            <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
                                <span class="menu-icon" data-kt-element="icon">
                                    <i class="ki-outline ki-moon fs-2"></i>
                                </span>
                                <span class="menu-title">
                                    Dark
                                </span>
                            </a>
                        </div>
                        <!--end::Menu item-->
                        <!--begin::Menu item-->
                        <div class="menu-item px-3 my-0">
                            <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
                                <span class="menu-icon" data-kt-element="icon">
                                    <i class="ki-outline ki-screen fs-2"></i>
                                </span>
                                <span class="menu-title">
                                    System
                                </span>
                            </a>
                        </div>
                        <!--end::Menu item-->
                    </div>
                    <!--end::Menu-->
                </div>
                <!--end::Theme mode-->
                
                <!--begin::Bildirim menu-->
                <div class="app-navbar-item ms-1 ms-md-4" id="kt_header_bildirim_menu_toggle">
                    <!--begin::Menu wrapper-->
                    <div class="cursor-pointer symbol symbol-40px symbol-lg-35px position-relative" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                        <a href="/kullanici/bildirimler" class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-40px h-40px w-lg-35px h-lg-35px position-relative">
                            <i class="ki-outline ki-notification-on fs-1"></i>
                            <!--begin::Bildirim sayacı-->
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="bildirim-sayaci" style="display: none; z-index: 1000; font-size: 0.7rem; min-width: 16px; height: 16px; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                <span id="bildirim-sayi">0</span>
                            </span>
                            <!--end::Bildirim sayacı-->
                        </a>
                    </div>
                    <!--begin::Bildirim dropdown menu-->
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-350px" data-kt-menu="true" id="bildirim-dropdown">
                        <!--begin::Menu header-->
                        <div class="menu-item px-3">
                            <div class="menu-content d-flex align-items-center px-3">
                                <div class="symbol symbol-50px me-3">
                                    <div class="symbol-label bg-light-primary">
                                        <i class="ki-outline ki-notification-on text-primary fs-2"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <div class="fw-bold d-flex align-items-center fs-5">
                                        Bildirimler
                                    </div>
                                    <a href="/kullanici/bildirimler" class="fw-semibold text-muted text-hover-primary fs-7">
                                        Tümünü görüntüle
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!--end::Menu header-->
                        
                        <!--begin::Menu separator-->
                        <div class="separator my-2"></div>
                        <!--end::Menu separator-->
                        
                        <!--begin::Bildirim listesi-->
                        <div id="bildirim-liste" class="px-3">
                            <div class="text-center py-4">
                                <i class="ki-outline ki-notification-off fs-3x text-muted mb-3"></i>
                                <div class="text-muted">Yeni bildirim bulunmuyor</div>
                            </div>
                        </div>
                        <!--end::Bildirim listesi-->
                    </div>
                    <!--end::Bildirim dropdown menu-->
                </div>
                <!--end::Bildirim menu-->
                
                <!--begin::User menu-->
                <div class="app-navbar-item ms-1 ms-md-4" id="kt_header_user_menu_toggle">
                    <!--begin::Menu wrapper-->
                    <div class="cursor-pointer symbol symbol-40px symbol-lg-35px" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                        <?php 
                        $user_name = $_SESSION['user_name'] ?? '';
                        $user_email = $_SESSION['user_email'] ?? '';
                        $user_role = $_SESSION['user_role'] ?? false;
                        
                        // Ad soyaddan baş harfleri al
                        $name_parts = explode(' ', trim($user_name));
                        $first_initial = !empty($name_parts[0]) ? strtoupper(substr($name_parts[0], 0, 1)) : '';
                        $last_initial = !empty($name_parts[1]) ? strtoupper(substr($name_parts[1], 0, 1)) : '';
                        $initials = $first_initial . $last_initial;
                        
                        // Avatar alanı olmadığı için her zaman baş harfleri göster
                        ?>
                        <div class="symbol-label bg-primary text-white fw-bold fs-6"><?= $initials ?></div>
                    </div>
                    <!--begin::User account menu-->
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px" data-kt-menu="true">
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <div class="menu-content d-flex align-items-center px-3">
                                <!--begin::Avatar-->
                                <div class="symbol symbol-50px me-5">
                                    <div class="symbol-label bg-primary text-white fw-bold fs-4"><?= $initials ?></div>
                                </div>
                                <!--end::Avatar-->
                                <!--begin::Username-->
                                <div class="d-flex flex-column">
                                    <div class="fw-bold d-flex align-items-center fs-5">
                                        <?= htmlspecialchars($user_name) ?>
                                        <span class="badge badge-light-success fw-bold fs-8 px-2 py-1 ms-2">
                                            <?= $user_role ? 'Yönetici' : 'Mağaza Personeli' ?>
                                        </span>
                                    </div>
                                    <a href="#" class="fw-semibold text-muted text-hover-primary fs-7">
                                        <?= htmlspecialchars($user_email) ?>
                                    </a>
                                </div>
                                <!--end::Username-->
                            </div>
                        </div>
                        <!--end::Menu item-->

                        <!--begin::Menu separator-->
                        <div class="separator my-2"></div>
                        <!--end::Menu separator-->
                        <!--begin::Menu item-->
                        
                        <div class="menu-item px-5 my-1">
                            <a class="menu-link px-5" href="/profil">Profil</a>
                        </div>
                        <div class="menu-item px-5 my-1">
                            <a class="menu-link px-5" href="/kullanici/bildirimler">
                                <i class="ki-outline ki-notification-on me-2"></i>Bildirimlerim
                            </a>
                        </div>
                        <div class="menu-item px-5">
                            <a class="menu-link px-5" href="#" onclick="performLogout()" style="cursor: pointer;">Çıkış Yap</a>
                        </div>
                        <!--End::Menu item-->
                    </div>
                    <!--end::User account menu-->
                    <!--end::Menu wrapper-->
                </div>
                <!--end::User menu-->
                      
                <!--begin::Aside toggle-->
                <!--end::Header menu toggle-->
            </div>
            <!--end::Navbar-->
        </div>
        <!--end::Header wrapper-->
    </div>
    <!--end::Header container-->
</div>

<script>
// "Beni hatırla" verilerini temizleme fonksiyonu
function clearRememberMe() {
    localStorage.removeItem('email');
    localStorage.removeItem('rememberEmail');
}

// Güvenli çıkış işlemi
function performLogout() {
    // Client-side temizlik
    clearRememberMe();
    
    // AuthGuard varsa onu kullan, yoksa direkt git
    if (window.authGuard) {
        window.authGuard.logout();
    } else {
        // Fallback - direkt server logout
        window.location.href = '/auth/logout';
    }
}

// Bildirim sistemi
document.addEventListener('DOMContentLoaded', function() {
    let bildirimSayaci = document.getElementById('bildirim-sayaci');
    let bildirimSayi = document.getElementById('bildirim-sayi');
    let bildirimListe = document.getElementById('bildirim-liste');
    let yanipSonmeInterval;
    let pulseInterval;
    let lastCount = 0;
    
    // Okunmamış bildirim sayısını al
    function getUnreadCount() {
        fetch('/kullanici/bildirimler/unread-count')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const count = data.count;
                    bildirimSayi.textContent = count;
                    
                    if (count > 0) {
                        bildirimSayaci.style.display = 'block';
                        
                        // Yeni bildirim geldiğinde pulse efekti
                        if (count > lastCount && lastCount > 0) {
                            startPulse();
                        }
                        
                        startBlinking();
                    } else {
                        bildirimSayaci.style.display = 'none';
                        stopBlinking();
                        stopPulse();
                    }
                    
                    lastCount = count;
                }
            })
            .catch(error => {
                console.error('Bildirim sayısı alınırken hata:', error);
            });
    }
    
    // Yanıp sönme efekti
    function startBlinking() {
        if (yanipSonmeInterval) return;
        
        yanipSonmeInterval = setInterval(() => {
            bildirimSayaci.style.opacity = bildirimSayaci.style.opacity === '0.5' ? '1' : '0.5';
        }, 800);
    }
    
    function stopBlinking() {
        if (yanipSonmeInterval) {
            clearInterval(yanipSonmeInterval);
            yanipSonmeInterval = null;
            bildirimSayaci.style.opacity = '1';
        }
    }
    
    // Pulse efekti (yeni bildirim geldiğinde)
    function startPulse() {
        bildirimSayaci.classList.add('animate');
        
        setTimeout(() => {
            stopPulse();
        }, 3000);
    }
    
    function stopPulse() {
        bildirimSayaci.classList.remove('animate');
    }
    
    // Sayfa yüklendiğinde bildirim sayısını al
    getUnreadCount();
    
    // Her 30 saniyede bir güncelle
    setInterval(getUnreadCount, 30000);
    
    // Bildirim ikonuna tıklandığında sayacı gizle
    document.querySelector('#kt_header_bildirim_menu_toggle a').addEventListener('click', function() {
        bildirimSayaci.style.display = 'none';
        stopBlinking();
        stopPulse();
    });
    
    // Sayfa görünür olduğunda bildirim sayısını güncelle
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            getUnreadCount();
        }
    });
    
    // Bildirim sesi çal (opsiyonel)
    function playNotificationSound() {
        try {
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT');
            audio.volume = 0.3;
            audio.play().catch(e => console.log('Ses çalınamadı:', e));
        } catch (e) {
            console.log('Ses dosyası yüklenemedi:', e);
        }
    }
    
    // Yeni bildirim geldiğinde ses çal
    let previousCount = 0;
    setInterval(() => {
        const currentCount = parseInt(bildirimSayi.textContent) || 0;
        if (currentCount > previousCount && previousCount > 0) {
            playNotificationSound();
        }
        previousCount = currentCount;
    }, 5000);
});
</script>

<style>
/* Bildirim sayacı stilleri */
#bildirim-sayaci {
    transition: opacity 0.3s ease;
    z-index: 1000;
    font-size: 0.75rem;
    font-weight: bold;
    min-width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

#bildirim-sayaci.animate {
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
    }
    50% {
        transform: translate(-50%, -50%) scale(1.1);
        opacity: 0.8;
    }
    100% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
    }
}

/* Mobil optimizasyonları */
@media (max-width: 768px) {
    #bildirim-sayaci {
        font-size: 0.65rem;
        min-width: 14px;
        height: 14px;
    }
    
    .app-navbar-item {
        margin-left: 0.5rem !important;
    }
    
    .btn-icon {
        width: 35px !important;
        height: 35px !important;
    }
    
    .symbol {
        width: 35px !important;
        height: 35px !important;
    }
    
    #bildirim-dropdown {
        width: 300px !important;
        max-width: 90vw;
    }
}

/* Bildirim dropdown stilleri */
#bildirim-dropdown {
    max-height: 400px;
    overflow-y: auto;
}

.bildirim-item {
    padding: 12px;
    border-bottom: 1px solid #e4e6ea;
    transition: background-color 0.2s ease;
}

.bildirim-item:hover {
    background-color: #f8f9fa;
}

.bildirim-item:last-child {
    border-bottom: none;
}

.bildirim-baslik {
    font-weight: 600;
    font-size: 0.9rem;
    color: #181c32;
    margin-bottom: 4px;
}

.bildirim-mesaj {
    font-size: 0.8rem;
    color: #6c7293;
    line-height: 1.4;
}

.bildirim-tarih {
    font-size: 0.75rem;
    color: #a1a5b7;
    margin-top: 4px;
}

.bildirim-yeni {
    background-color: #fff3cd;
    border-left: 3px solid #ffc107;
}
</style>