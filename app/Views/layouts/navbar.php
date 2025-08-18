<?php
// Kullanıcı bilgilerini veritabanından çek
if (isset($_SESSION['user_id'])) {
    $kullaniciModel = new \app\Models\Kullanici();
    $kullanici = $kullaniciModel->get($_SESSION['user_id']);
    
    // Personel bilgilerini de çek (varsa)
    $personelModel = new \app\Models\Personel();
    $personel = $personelModel->getByKullaniciId($_SESSION['user_id']);
    
    // Kullanıcı adı ve soyadını belirle (öncelik sırası: veritabanı > session)
    $userName = '';
    $userSurname = '';
    $userEmail = '';
    
    // Önce session'dan al (geçici çözüm)
    $userName = trim($_SESSION['user_name'] ?? '');
    $userSurname = trim($_SESSION['user_surname'] ?? '');
    $userEmail = trim($_SESSION['user_email'] ?? '');
    
    // Veritabanından da kontrol et
    if ($kullanici) {
        if (empty($userName) && !empty($kullanici['ad'])) {
            $userName = trim($kullanici['ad']);
        }
        if (empty($userSurname) && !empty($kullanici['soyad'])) {
            $userSurname = trim($kullanici['soyad']);
        }
        if (empty($userEmail) && !empty($kullanici['email'])) {
            $userEmail = trim($kullanici['email']);
        }
    }
    
    // Debug: Veritabanından gelen verileri logla
    error_log("DB Debug - kullanici: " . json_encode($kullanici));
    error_log("DB Debug - personel: " . json_encode($personel));
    
    // Kullanıcı görseli kontrolü
    $userImage = null;
    if ($personel && !empty($personel['foto'])) {
        $userImage = $personel['foto'];
    } elseif ($kullanici && !empty($kullanici['profil_foto'])) {
        $userImage = $kullanici['profil_foto'];
    }
    
    // Baş harfleri oluştur
    $initials = '';
    
    // Ad ve soyadı temizle
    $cleanName = trim($userName);
    $cleanSurname = trim($userSurname);
    
    // Ad varsa ilk harfini al (Türkçe karakter desteği ile)
    if (!empty($cleanName)) {
        $firstChar = mb_substr($cleanName, 0, 1, 'UTF-8');
        $initials .= mb_strtoupper($firstChar, 'UTF-8');
    }
    
    // Soyad varsa ilk harfini al (Türkçe karakter desteği ile)
    if (!empty($cleanSurname)) {
        $firstChar = mb_substr($cleanSurname, 0, 1, 'UTF-8');
        $initials .= mb_strtoupper($firstChar, 'UTF-8');
    }
    
    // Eğer hem ad hem soyad boşsa, e-posta adresinden al
    if (empty($initials) && !empty($userEmail)) {
        $emailParts = explode('@', $userEmail);
        if (!empty($emailParts[0])) {
            $firstChar = mb_substr($emailParts[0], 0, 1, 'UTF-8');
            $initials = mb_strtoupper($firstChar, 'UTF-8');
        }
    }
    
    // Hala boşsa varsayılan değer
    if (empty($initials)) {
        // Session'dan kullanıcı adını kontrol et
        $sessionName = trim($_SESSION['user_name'] ?? '');
        if (!empty($sessionName)) {
            $firstChar = mb_substr($sessionName, 0, 1, 'UTF-8');
            $initials = mb_strtoupper($firstChar, 'UTF-8');
        } else {
            $initials = 'U'; // User
        }
    }
    
    // Debug: Baş harfler oluşturma sürecini logla
    error_log("Initials Debug - cleanName: '$cleanName', cleanSurname: '$cleanSurname', userEmail: '$userEmail', final initials: '$initials'");
    
    // Debug bilgisi (geliştirme aşamasında)
    error_log("Navbar Debug - userName: '$userName', userSurname: '$userSurname', userEmail: '$userEmail', initials: '$initials'");
} else {
    // Session yoksa varsayılan değerler
    $userName = $_SESSION['user_name'] ?? '';
    $userSurname = $_SESSION['user_surname'] ?? '';
    $userEmail = $_SESSION['user_email'] ?? '';
    $userImage = null;
    $initials = 'U';
}
?>
<!--begin::Header-->
<div id="kt_app_header" class="app-header" data-kt-sticky="false" data-kt-sticky-activate="{default: false, lg: false}" data-kt-sticky-name="app-header-sticky" data-kt-sticky-offset="{default: '200px', lg: '300px'}">
	<!--begin::Header container-->
	<div class="app-container container-fluid d-flex flex-stack" id="kt_app_header_container">
		<!--begin::Header logo-->
		<div class="d-flex d-lg-none align-items-center me-lg-20 gap-1 gap-lg-2">
			<!--begin::Mobile toggle-->
			<div class="btn btn-icon btn-color-gray-500 btn-active-color-primary w-35px h-35px d-flex d-lg-none" id="kt_app_sidebar_toggle">
				<i class="ki-outline ki-abstract-14 lh-0 fs-1"></i>
			</div>
			<!--end::Mobile toggle-->
			<!--begin::Logo image-->
			<a href="/admin">
				<img alt="Logo" src="/public/media/logos/default.svg" class="h-25px theme-light-show" />
				<img alt="Logo" src="/public/media/logos/default-dark.svg" class="h-25px theme-dark-show" />
			</a>
			<!--end::Logo image-->
		</div>
		<!--end::Header logo-->
		<!--begin::Header wrapper-->
		<div class="d-flex flex-stack flex-lg-row-fluid" id="kt_app_header_wrapper">
			<!--begin::Page title-->
			<div class="app-page-title d-flex flex-column gap-1 me-3 mb-5 mb-lg-0" data-kt-swapper="true" data-kt-swapper-mode="{default: 'prepend', lg: 'prepend'}" data-kt-swapper-parent="{default: '#kt_app_content_container', lg: '#kt_app_header_wrapper'}">
				<!--begin::Title-->
				<h1 class="fs-2 text-gray-900 fw-bold m-0"><?=$title?></h1>
				<!--end::Title-->
				<!--begin::Breadcrumb-->
				<ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 mb-2">
					<li class="breadcrumb-item text-gray-600 fw-bold lh-1">
						<a href="/" class="text-gray-700 text-hover-primary me-1">
							<i class="ki-outline ki-home text-gray-500 fs-7"></i>
						</a>
					</li>
					<li class="breadcrumb-item">
						<i class="ki-outline ki-right fs-7 text-gray-500 mx-n1"></i>
					</li>
					<li class="breadcrumb-item text-gray-600 fw-bold lh-1"><?= htmlspecialchars($link ?? ($title ?? ''), ENT_QUOTES) ?></li>

				</ul>
				<!--end::Breadcrumb-->
			</div>
			<!--end::Page title-->
			<!--begin::Navbar-->
			<div class="app-navbar flex-shrink-0 gap-2 gap-lg-4">
                <div class="app-navbar-item d-flex align-items-center gap-2">
                    <a href="/admin/bildirimler" class="btn btn-icon rounded-circle w-35px h-35px bg-light-warning border border-warning-clarity" title="Bildirimler">
                        <i class="ki-outline ki-notification-on text-warning fs-3"></i>
                    </a>
                    <a href="/admin/activity-logs" class="btn btn-icon rounded-circle w-35px h-35px bg-light-secondary border" title="Aktivite Logları">
                        <i class="ki-outline ki-time text-secondary fs-3"></i>
                    </a>
                </div>
                
                <!--begin::Kullanıcı Bildirim İkonu-->
                <div class="app-navbar-item" id="kt_header_bildirim_menu_toggle" style="display: none;">
                    <div class="position-relative">
                        <a href="/kullanici/bildirimler" class="btn btn-icon rounded-circle w-35px h-35px bg-light-primary border border-primary-clarity position-relative">
                            <i class="ki-outline ki-notification-on text-primary fs-3"></i>
                            <!--begin::Bildirim sayacı-->
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="bildirim-sayaci" style="display: none; z-index: 1000; font-size: 0.7rem; min-width: 16px; height: 16px; border: 2px solid #fff;">
                                <span id="bildirim-sayi">0</span>
                            </span>
                            <!--end::Bildirim sayacı-->
                        </a>
                    </div>
                </div>
                <!--end::Kullanıcı Bildirim İkonu-->
				<!--begin::User menu-->
				<div class="app-navbar-item ms-lg-5" id="kt_header_user_menu_toggle">
					<!--begin::Menu wrapper-->
					<div class="d-flex align-items-center" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
						<!--begin:Info-->
						<div class="text-end d-none d-sm-flex flex-column justify-content-center me-3">

							<span class="text-gray-500 fs-8 fw-bold">Merhaba</span>
							<a href="#" class="text-gray-800 text-hover-primary fs-7 fw-bold d-block" title="Debug: <?= htmlspecialchars($userName . ' ' . $userSurname . ' (' . $userEmail . ')') ?>"><?= $userName ?> <?= $userSurname ?></a>
						</div>
						<!--end:Info-->
						<!--begin::User-->
						<div class="cursor-pointer symbol symbol symbol-circle symbol-35px symbol-md-40px">
							<?php if ($userImage): ?>
								<img class="" src="<?= $userImage ?>" alt="user" />
							<?php else: ?>
								<div class="symbol-label bg-primary text-white fw-bold fs-6 d-flex align-items-center justify-content-center" style="min-width: 35px; min-height: 35px;" title="Debug: <?= htmlspecialchars($userName . ' ' . $userSurname . ' (' . $userEmail . ')') ?>">
									<?= $initials ?>
								</div>
							<?php endif; ?>
							<div class="position-absolute translate-middle bottom-0 mb-1 start-100 ms-n1 bg-success rounded-circle h-8px w-8px"></div>
						</div>
						<!--end::User-->
					</div>
					<!--begin::User account menu-->
					<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px" data-kt-menu="true">
						<!--begin::Menu item-->
						<div class="menu-item px-3">
							<div class="menu-content d-flex align-items-center px-3">
								<!--begin::Avatar-->
								<div class="symbol symbol-50px me-5">
									<?php if ($userImage): ?>
										<img alt="Logo" src="<?= $userImage ?>" />
									<?php else: ?>
										<div class="symbol-label bg-primary text-white fw-bold fs-5 d-flex align-items-center justify-content-center" style="min-width: 50px; min-height: 50px;">
											<?= $initials ?>
										</div>
									<?php endif; ?>
								</div>
								<!--end::Avatar-->

								<!--begin::Username-->
								<div class="d-flex flex-column">
									<div class="fw-bold d-flex align-items-center fs-5">	<?= $userName  ?> <?= $userSurname ?>
									</div>
									<a href="#" class="fw-semibold text-muted text-hover-primary fs-7"><?= $userEmail ?></a>
								</div>
								<!--end::Username-->
							</div>
						</div>
						<!--end::Menu item-->
						<!--begin::Menu separator-->
						<div class="separator my-2"></div>
						<!--end::Menu separator-->
						<!--begin::Menu item-->
						<div class="menu-item px-5">
															<a href="/kullanici/profil" class="menu-link px-5">Profilim</a>
								<a href="/kullanici/bildirimler" class="menu-link px-5">Bildirimlerim</a>
						</div>
						<!--begin::Menu item-->
						<div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="left-start" data-kt-menu-offset="-15px, 0">
							<a href="#" class="menu-link px-5">
												<span class="menu-title position-relative">Tema Mod
												<span class="ms-5 position-absolute translate-middle-y top-50 end-0">
													<i class="ki-outline ki-night-day theme-light-show fs-2"></i>
													<i class="ki-outline ki-moon theme-dark-show fs-2"></i>
												</span></span>
							</a>
							<!--begin::Menu-->
							<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px" data-kt-menu="true" data-kt-element="theme-mode-menu">
								<!--begin::Menu item-->
								<div class="menu-item px-3 my-0">
									<a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
														<span class="menu-icon" data-kt-element="icon">
															<i class="ki-outline ki-night-day fs-2"></i>
														</span>
										<span class="menu-title">Aydınlık</span>
									</a>
								</div>
								<!--end::Menu item-->
								<!--begin::Menu item-->
								<div class="menu-item px-3 my-0">
									<a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
														<span class="menu-icon" data-kt-element="icon">
															<i class="ki-outline ki-moon fs-2"></i>
														</span>
										<span class="menu-title">Karanlık</span>
									</a>
								</div>
								<!--end::Menu item-->
								<!--begin::Menu item-->
								<div class="menu-item px-3 my-0">
									<a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
														<span class="menu-icon" data-kt-element="icon">
															<i class="ki-outline ki-screen fs-2"></i>
														</span>
										<span class="menu-title">Sistem</span>
									</a>
								</div>
								<!--end::Menu item-->
							</div>
							<!--end::Menu-->
						</div>
						<!--end::Menu item-->

						<!--begin::Menu item-->
						<div class="menu-item px-5 my-1">
							<a href="/admin/site-ayarlar" class="menu-link px-5">Site Ayarları</a>
						</div>
						<!--end::Menu item-->
						<!--begin::Menu item-->
                        <div class="menu-item px-5">
                            <a class="menu-link px-5" href="/logout.php" onclick="performLogout()" style="cursor: pointer;">Çıkış Yap</a>
                        </div>
						<!--end::Menu item-->
					</div>
					<!--end::User account menu-->
					<!--end::Menu wrapper-->
				</div>
				<!--end::User menu-->
			</div>
			<!--end::Navbar-->
		</div>
		<!--end::Header wrapper-->

	</div>
	<!--end::Header container-->
</div>
<!--end::Header-->
<!--begin::Wrapper-->
<div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
	<!--begin::Sidebar-->
	<?php require_once 'app/Views/layouts/sidebar.php'; ?>
	<!--end::Sidebar-->
</div>
    <!--begin::Main-->
	<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
		<!--begin::Content wrapper-->
		<div class="d-flex flex-column flex-column-fluid">
			<!--begin::Content-->
			<div id="kt_app_content" class="app-content flex-column-fluid">
				<!--begin::Content container-->
				<div id="kt_app_content_container" class="app-container container-fluid">