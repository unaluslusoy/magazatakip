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
				<img alt="Logo" src="/public/media/logos/demo63.svg" class="h-25px theme-light-show" />
				<img alt="Logo" src="/public/media/logos/demo63-dark.svg" class="h-25px theme-dark-show" />
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
					<li class="breadcrumb-item text-gray-600 fw-bold lh-1"><?=$link?></li>

				</ul>
				<!--end::Breadcrumb-->
			</div>
			<!--end::Page title-->
			<!--begin::Navbar-->
			<div class="app-navbar flex-shrink-0 gap-2 gap-lg-4">
                <div class="app-navbar-item">
                    <a href="/admin/bildirimler" class="btn btn-icon rounded-circle w-35px h-35px bg-light-warning border border-warning-clarity" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end" id="kt_menu_item_wow">
                        <i class="ki-outline ki-notification-on text-warning fs-3"></i>
                    </a>
                </div>
				<!--begin::User menu-->
				<div class="app-navbar-item ms-lg-5" id="kt_header_user_menu_toggle">
					<!--begin::Menu wrapper-->
					<div class="d-flex align-items-center" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
						<!--begin:Info-->
						<div class="text-end d-none d-sm-flex flex-column justify-content-center me-3">

							<span class="text-gray-500 fs-8 fw-bold">Merhaba</span>
							<a href="#" class="text-gray-800 text-hover-primary fs-7 fw-bold d-block"><?=  $_SESSION['user_name']?></a>
						</div>
						<!--end:Info-->
						<!--begin::User-->
						<div class="cursor-pointer symbol symbol symbol-circle symbol-35px symbol-md-40px">
							<img class="" src="/public/media/avatars/300-3.jpg" alt="user" />
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
									<img alt="Logo" src="/public/media/avatars/300-3.jpg" />
								</div>
								<!--end::Avatar-->

								<!--begin::Username-->
								<div class="d-flex flex-column">
									<div class="fw-bold d-flex align-items-center fs-5">	<?=  $_SESSION['user_name']?>
									</div>
									<a href="#" class="fw-semibold text-muted text-hover-primary fs-7"><?=  $_SESSION['user_email']?></a>
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
							<a href="#" class="menu-link px-5">Profilim</a>
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
							<a href="/" class="menu-link px-5">Ayarlar</a>
						</div>
						<!--end::Menu item-->
						<!--begin::Menu item-->
						<div class="menu-item px-5">
							<a class="menu-link px-5" href="/admin/logout">Çıkış Yap</a>

						</div>
						<!--end::Menu item-->
					</div>
					<!--end::User account menu-->
					<!--end::Menu wrapper-->
				</div>
				<!--end::User menu-->

            <?php require_once 'app/Views/layouts/sidebar.php'; ?>
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
<!--end::Sidebar-->
<!--begin::Main-->
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
	<!--begin::Content wrapper-->
	<div class="d-flex flex-column flex-column-fluid">
		<!--begin::Content-->
		<div id="kt_app_content" class="app-content flex-column-fluid">
			<!--begin::Content container-->
			<div id="kt_app_content_container" class="app-container container-fluid">