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
        <div id="kt_app_sidebar_menu_wrapper" class="hover-scroll-overlay-y my-5 my-lg-5" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_header, #kt_app_sidebar_footer" data-kt-scroll-wrappers="#kt_app_sidebar_menu_wrapper" data-kt-scroll-offset="0">
            <div class="app-sidebar-navs-default px-5 mb-10">
                <div id="kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false" class="menu menu-column menu-rounded menu-sub-indention">
                    <div class="menu-item pb-0 pt-0">
                        <div class="menu-content">
                            <span class="menu-heading">Hızlı Menü</span>
                        </div>
                    </div>
                    <div class="separator mb-4 mx-4"></div>
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
                    <!--begin::Menu item-->
                    <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                        <a href="#" class="menu-link py-3">
                            <span class="menu-bullet">
                                <i class="bi bi-diagram-3"></i>
                            </span>
                            <span class="menu-title">Entegrasyonlar</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="menu-sub menu-sub-accordion pt-3">
                            <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                                <a href="#" class="menu-link py-3">
                                    <span class="menu-bullet"><i class="bi bi-box-seam"></i></span>
                                    <span class="menu-title">Tamsoft ERP</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="menu-sub menu-sub-accordion pt-3">
                                    <div class="menu-item">
                                        <a href="/admin/tamsoft-stok" class="menu-link py-3">
                                            <span class="menu-bullet"><i class="bi bi-speedometer2"></i></span>
                                            <span class="menu-title">Dashboard</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a href="/admin/tamsoft-stok/ayarlar" class="menu-link py-3">
                                            <span class="menu-bullet"><i class="bi bi-gear"></i></span>
                                            <span class="menu-title">Ayarlar</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a href="/admin/tamsoft-stok/envanter" class="menu-link py-3">
                                            <span class="menu-bullet"><i class="bi bi-box"></i></span>
                                            <span class="menu-title">Stok Envanter</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a href="/admin/tamsoft-stok/import" class="menu-link py-3">
                                            <span class="menu-bullet"><i class="bi bi-upload"></i></span>
                                            <span class="menu-title">Import (Manuel)</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <!-- Trendyol Go -->
                            <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                                <a href="#" class="menu-link py-3">
                                    <span class="menu-bullet">
                                        <i class="bi bi-lightning"></i>
                                    </span>
                                    <span class="menu-title">Trendyol Go</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="menu-sub menu-sub-accordion pt-3">
                                    <div class="menu-item">
                                        <a href="/admin/trendyolgo" class="menu-link py-3">
                                            <span class="menu-bullet"><i class="bi bi-speedometer2"></i></span>
                                            <span class="menu-title">Anasayfa</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a href="/admin/trendyolgo/urunler" class="menu-link py-3">
                                            <span class="menu-bullet"><i class="bi bi-list"></i></span>
                                            <span class="menu-title">Ürün Listesi</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a href="/admin/trendyolgo/magazalar" class="menu-link py-3">
                                            <span class="menu-bullet"><i class="bi bi-shop"></i></span>
                                            <span class="menu-title">Mağaza Listesi</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a href="/admin/trendyolgo/ayarlar" class="menu-link py-3">
                                            <span class="menu-bullet"><i class="bi bi-gear"></i></span>
                                            <span class="menu-title">Ayarlar</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a href="/admin/trendyolgo/eslesmeler" class="menu-link py-3">
                                            <span class="menu-bullet"><i class="bi bi-link-45deg"></i></span>
        									<span class="menu-title">Eşleşmeler</span>
                                        </a>
                                    </div>
                                </div>
                             <!-- GetirÇarşı -->
                             <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                                 <a href="#" class="menu-link py-3">
                                     <span class="menu-bullet">
                                         <i class="bi bi-bag"></i>
                                     </span>
                                     <span class="menu-title">GetirÇarşı</span>
                                     <span class="menu-arrow"></span>
                                 </a>
                                 <div class="menu-sub menu-sub-accordion pt-3">
                                     <div class="menu-item">
                                         <a href="/admin/getir" class="menu-link py-3">
                                             <span class="menu-bullet"><i class="bi bi-speedometer2"></i></span>
                                             <span class="menu-title">Anasayfa</span>
                                         </a>
                                     </div>
                                     <div class="menu-item">
                                         <a href="/admin/getir/ayarlar" class="menu-link py-3">
                                             <span class="menu-bullet"><i class="bi bi-gear"></i></span>
                                             <span class="menu-title">Ayarlar</span>
                                         </a>
                                     </div>
                                 </div>
                             </div>
                            
                            <div class="menu-item">
                                <a href="/admin/match" class="menu-link py-3">
                                <span class="menu-bullet">
                                    <i class="bi bi-diagram-2"></i>
                                </span>
                                    <span class="menu-title">Ürün Eşleştirme</span>
                                </a>
                            </div>
                        </div>
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
                    <div class="menu-item">
                        <a href="/admin/cloudflare" class="menu-link py-3">
                        <span class="menu-bullet">
                            <i class="bi bi-cloud"></i>
                        </span>
                            <span class="menu-title">Cloudflare</span>
                        </a>
                    </div>

                    <!--begin::Menu item (Ayarlar) -->
                    <div class="menu-item">
                        <a href="/admin/site-ayarlar" class="menu-link py-3">
                            <span class="menu-bullet">
                                <i class="bi bi-gear"></i>
                            </span>
                            <span class="menu-title">Ayarlar</span>
                        </a>
                    </div>
                    <!--end::Menu item (Ayarlar) -->

                </div>
            </div>



        </div>
    </div>
    <!--begin::Footer-->


    <div class="separator mb-3 mx-4"></div>
    <div class="d-flex flex-stack px-6 pb-8" id="kt_app_sidebar_footer">

		


    </div>
    <!--end::Footer-->
</div>
<!--end::Sidebar menu toggle-->