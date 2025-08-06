<!--begin::Sidebar menu toggle-->
<div id="kt_app_sidebar" class="app-sidebar" data-kt-drawer="true" data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="250px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_toggle">
    <!--begin::Header-->
    <div class="d-none d-lg-flex flex-center px-6 pt-10 pb-10" id="kt_app_sidebar_header">
        
        <a href="/admin">			
				<img alt="Logo" src="/public/media/logos/default-dark.svg" class="h-45px " />
			</a>
    </div>
    <!--end::Header-->
    <div class="flex-grow-1">
        <div id="kt_app_sidebar_menu_wrapper" >
            <div class="app-sidebar-navs-default px-5 mb-10">
                <div id="#kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false" class="menu menu-column menu-rounded menu-sub-indention">
                    <div class="menu-item pb-0 pt-0">
                        <div class="menu-content">
                            <span class="menu-heading">Hızlı Menü</span>
                        </div>
                    </div>
                    <div class="separator mb-4 mx-4"></div>
                    <!--begin::Menu item-->
                    <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                        <!--begin::Menu link-->
                        <a href="#" class="menu-link py-3">
                        <span class="menu-bullet">
                            <i class="bi bi-gear"></i>
                        </span>
                            <span class="menu-title">Tanımlama</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <!--end::Menu link-->
                        <!--begin::Menu sub-->
                        <div class="menu-sub menu-sub-accordion pt-3">
                            <!--begin::Menu item-->
                            <div class="menu-item">
                                <a href="/admin/kullanicilar" class="menu-link py-3">
                                <span class="menu-bullet">
                                   <i class="bi bi-person-lines-fill"></i>
                                </span>
                                    <span class="menu-title">Kullanıcı</span>
                                </a>
                            </div>
                            <!--end::Menu item-->
                        </div>
                        <!--end::Menu sub-->
                        <!--begin::Menu sub-->
                        <div class="menu-sub menu-sub-accordion pt-3">
                            <!--begin::Menu item-->
                            <div class="menu-item">
                                <a href="/admin/onesignal/ayarlar" class="menu-link py-3">
                                <span class="menu-bullet">
                                   <i class="bi bi-person-lines-fill"></i>
                                </span>
                                    <span class="menu-title">Ayarlar</span>
                                </a>
                            </div>
                            <!--end::Menu item-->
                        </div>
                        <!--end::Menu sub-->
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div class="menu-item">
                        <a href="/admin/magazalar" class="menu-link py-3">
                        <span class="menu-bullet">
                            <i class="bi bi-shop-window"></i>
                        </span>
                            <span class="menu-title">Mağaza İşlemleri</span>
                        </a>
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div class="menu-item">
                        <a href="/admin/istekler" class="menu-link py-3">
                        <span class="menu-bullet">
                            <i class="bi bi-shop-window"></i>
                        </span>
                            <span class="menu-title">Mağaza Talepleri</span>
                        </a>
                    </div>
                    <!--end::Menu item-->
                    
                    <!--begin::Menu item-->
                    <div class="menu-item">
                        <a href="/admin/bildirim_gonder" class="menu-link py-3">
                        <span class="menu-bullet">
                            <i class="bi bi-shop-window"></i>
                        </span>
                            <span class="menu-title">Bildirim Gönder</span>
                        </a>
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                        <!--begin::Menu link-->
                        <a href="#" class="menu-link py-3">
                                <span class="menu-bullet">
                                    <i class="bi bi-people-fill"></i>
                                </span>
                            <span class="menu-title">Çalışan İşlemleri</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <!--end::Menu link-->

                        <!--begin::Menu sub-->
                        <div class="menu-sub menu-sub-accordion pt-3">
                            <!--begin::Menu item-->
                            <div class="menu-item">
                                <a href="/admin/personeller" class="menu-link py-3">
                                        <span class="menu-bullet">
                                            <i class="bi bi-person-badge-fill"></i>
                                        </span>
                                    <span class="menu-title">Personel</span>
                                </a>
                            </div>
                            <!--end::Menu item-->

                        </div>
                        <!--end::Menu sub-->
                    </div>
                    <!--end::Menu item-->

                </div>
            </div>



        </div>
    </div>
    <!--begin::Footer-->


    <div class="app-sidebar-navs-default px-5">
        <div class="menu menu-rounded menu-column">
            <div class="menu-item pb-0 pt-0">
                <div class="menu-content">
                    <span class="menu-heading">Program Geri Bildirim</span>
                </div>
            </div>
            <div class="separator mb-3 mx-4"></div>
            <!--end::Menu Item-->
            <div class="menu-item">
                <!--begin::Menu link-->
                <a class="menu-link" href="/admin/geri_bildirimler">
                    <!--begin::Bullet-->
                    <span class="menu-bullet">
			    <span class="bullet bullet-dot"></span>
			</span>
                    <!--end::Bullet-->
                    <!--begin::Title-->
                    <span class="menu-title">Geri Bildirim</span>
                    <!--end::Title-->
                </a>
                <!--end::Menu link-->
            </div>
            <!--end::Menu Item-->
        </div>
    </div>
    <div class="separator mb-3 mx-4"></div>
    <div class="d-flex flex-stack px-10 px-lg-15 pb-8" id="kt_app_sidebar_footer">

		<span class="d-flex flex-center gap-1 text-white theme-light-show fs-5 px-0">
		<i class="ki-outline ki-night-day text-gray-500 fs-2"></i>Karanlık Mod</span>
        <span class="d-flex flex-center gap-1 text-white theme-dark-show fs-5 px-0">
		<i class="ki-outline ki-moon text-gray-500 fs-2"></i>Aydınlık Mod</span>
        <div data-bs-theme="dark">
            <div class="form-check form-switch form-check-custom form-check-solid">
                <input class="form-check-input h-25px w-45px" type="checkbox" value="1" id="kt_sidebar_theme_mode_toggle">
            </div>
        </div>



    </div>
    <!--end::Footer-->
</div>
<!--end::Sidebar menu toggle-->