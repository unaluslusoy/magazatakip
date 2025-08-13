<?php
$title = "<h2>İş Emri Listesi</h2>";
$link = "İş Emirleri";
require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';

function getBadgeClass($durum) {
    switch ($durum) {
        case 'Yeni':
            return 'badge-light-primary';
        case 'Beklemede':
            return 'badge-light-warning';
        case 'Devam Ediyor':
            return 'badge-light-info';
        case 'Tamamlandı':
            return 'badge-light-success';
        case 'Durduruldu':
            return 'badge-light-danger';
        case 'Gözden Geçiriliyor':
            return 'badge-light-info';
        case 'Onay Bekliyor':
            return 'badge-light-warning';
        case 'Red Edildi':
            return 'badge-light-danger';
        case 'Revize Ediliyor':
            return 'badge-light-info';
        case 'Erteleme':
            return 'badge-light-warning';
        case 'İptal Edildi':
            return 'badge-light-danger';
        case 'Sorun Var':
            return 'badge-light-danger';
        case 'Tekrar Açıldı':
            return 'badge-light-primary';
        default:
            return 'badge-light-secondary';
    }
}
?>
<!--begin::Card-->
<div class="card" data-select2-id="select2-data-122-rqwu">
    <!--begin::Card header-->
    <div class="card-header border-0 pt-6" data-select2-id="select2-data-121-i5an">
        <!--begin::Card title-->
        <div class="card-title">
            <!--begin::Search-->
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                <input type="text" id="searchInput" class="form-control form-control-solid w-250px ps-13" placeholder="Arama">
            </div>
            <!--end::Search-->

        </div>
        <!--begin::Card title-->



        <!--begin::Card toolbar-->
        <div class="card-toolbar w-100">
            <div class="container-fluid px-0">
                <div class="row g-2 align-items-stretch">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <select id="magazaFilter" class="form-select form-select-solid w-100" data-control="select2" data-placeholder="Mağaza" aria-hidden="true">
                            <option value="">Tüm Mağazalar</option>
                            <?php foreach ($magazalar as $magaza): ?>
                                <option value="<?= htmlspecialchars($magaza['ad']); ?>"><?= htmlspecialchars($magaza['ad']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <select id="durumFilter" class="form-select form-select-solid w-100" data-control="select2" data-placeholder="Durum" aria-hidden="true">
                            <option value="">Tüm Durumlar</option>
                            <option value="Yeni">Yeni</option>
                            <option value="Beklemede">Beklemede</option>
                            <option value="Devam Ediyor">Devam Ediyor</option>
                            <option value="Tamamlandı">Tamamlandı</option>
                            <option value="Durduruldu">Durduruldu</option>
                            <option value="Gözden Geçiriliyor">Gözden Geçiriliyor</option>
                            <option value="Onay Bekliyor">Onay Bekliyor</option>
                            <option value="Red Edildi">Red Edildi</option>
                            <option value="Revize Ediliyor">Revize Ediliyor</option>
                            <option value="Erteleme">Erteleme</option>
                            <option value="İptal Edildi">İptal Edildi</option>
                            <option value="Sorun Var">Sorun Var</option>
                            <option value="Tekrar Açıldı">Tekrar Açıldı</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <select id="personelFilter" class="form-select form-select-solid w-100" data-control="select2" data-placeholder="Personel" aria-hidden="true">
                            <option value="">Tüm Personeller</option>
                            <?php if (!empty($personeller)): foreach ($personeller as $p): ?>
                                <?php $full = trim(($p['ad'] ?? '') . ' ' . ($p['soyad'] ?? '')); ?>
                                <option value="<?= htmlspecialchars($full); ?>"><?= htmlspecialchars($full); ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 d-flex justify-content-lg-end gap-2 mt-1 mt-lg-0">
                        <button type="button" id="btnClearFilters" class="btn btn-light w-50 w-lg-auto">Filtreleri Temizle</button>
                        <button type="button" class="btn btn-primary w-50 w-lg-auto" data-bs-toggle="modal" data-bs-target="#kt_modal_add_istek">İş Emri Ekle</button>
                    </div>
                </div>
            </div>
            <!--begin::Group actions-->
            <div class="d-flex justify-content-end align-items-center d-none" data-kt-customer-table-toolbar="selected">
                <div class="fw-bold me-5">
                    <span class="me-2" data-kt-customer-table-select="selected_count">1</span>Seçildi</div>
                <button type="button" class="btn btn-danger" data-kt-customer-table-select="delete_selected">Seçili Olan İş Listesini Sil</button>
            </div>
            <!--end::Group actions-->
        </div>
        <!--end::Card toolbar-->
    </div>
    <!--end::Card header-->
    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Table-->
        <div id="kt_customers_table_wrapper" class="dt-container dt-bootstrap5 dt-empty-footer">
            <div id="" class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_customers_table">
                    <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0" role="row">
                        <th class="w-100 dt-orderable-asc dt-orderable-desc">
                            <span class="dt-column-title" role="button">Mağaza Talep</span>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                    <?php foreach($istekler as $istek): ?>
                        <tr data-istek-id="<?= (int)$istek['id']; ?>">
                            <td>
                               
                                <div class="card">
                                     <div class="card-header">
                                        <span class="badge bg-warning"><?= htmlspecialchars($istek['derece']); ?></span>
                                        <span class="badge">#<?= $istek['id']; ?></span>
                                        
                                     <span class="badge bg-danger"><?= $istek['magaza']; ?></span>
                                    
                                    </div>
                                     <div class="card-body">
                                <?php $attachments = $istek['attachments'] ?? []; ?>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($attachments as $att): ?>
                                                <?php $href = '/public/uploads/isemri/' . htmlspecialchars($att['dosya_yolu']); ?>
                                                <a href="#" data-href="<?= $href ?>" class="d-inline-block js-attachment" title="<?= htmlspecialchars($att['dosya_adi'] ?? 'Dosya') ?>">
                                                    <img src="<?= $href ?>" alt="ek" style="width:40px;height:40px;object-fit:cover;border-radius:6px;border:1px solid #eee;" />
                                        </a>
                                    <?php endforeach; ?>
                                    <?php if (empty($attachments)): ?><span class="text-muted">-</span><?php endif; ?>
                                </div>
                                        <span class="badge">Oluşturan Personel # : <?= (int)$istek['kullanici_id']; ?> — <?= htmlspecialchars(($istek['kullanici_adi'] ?? '').' '.($istek['kullanici_soyad'] ?? '')); ?></span>
                                     
                                        <h2 class="card-title"><?= htmlspecialchars($istek['baslik']); ?></h2>
                                        <p class="card-text"><?= htmlspecialchars($istek['aciklama']); ?></p>
                                        <hr>
                                        <div class="fw-semibold"> Görevli : <?= htmlspecialchars($istek['personel_adi'] . ' ' . $istek['personel_soyad']); ?></div>
                                            Açıklama : <p class="card-text"><?= $istek['is_aciklamasi']; ?></p>
                                    </div>
                                      <div class="card-footer d-flex align-items-center gap-2 flex-wrap">
                                         <span class="badge <?= getBadgeClass($istek['durum']); ?>"><?= htmlspecialchars($istek['durum']); ?></span>
                                             <span class="badge bg-primary">Oluşturma:</span> <?= htmlspecialchars($istek['tarih']); ?>
                                             <span class="badge bg-primary">Başlangıç:</span> <?= htmlspecialchars($istek['baslangic_tarihi'] ?? '-') ?>
                                                 <?php if (!empty($istek['bitis_tarihi'])): ?> - <?= htmlspecialchars($istek['bitis_tarihi']) ?>
                                                 <?php endif; ?>
                                         <div class="ms-auto">
                                <a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">İşlemler
                                    <i class="ki-outline ki-down fs-5 ms-1"></i>
                                </a>
                                             <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-150px py-4" data-kt-menu="true">
                                    <div class="menu-item px-3">
                                                     <a href="/admin/istek/guncelle/<?= $istek['id']; ?>" class="menu-link px-3">Görüntüle</a>
                                    </div>
                                    <div class="menu-item px-3">
                                                     <a href="/admin/istek/sil/<?= $istek['id']; ?>" class="menu-link px-3">Sil</a>
                                                 </div>
                                             </div>
                                         </div>
                                    </div>
                                </div>

                            </td>
                         
                           

                        </tr>
                        <div class="modal fade" id="isAtama_<?= $istek['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered mw-650px">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">İş Emri Güncelle</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="post" action="/admin/istek/guncelle/<?= $istek['id']; ?>" class="js-istek-update-form" data-istek-id="<?= $istek['id']; ?>">
                                            <?= csrf_field(); ?>
                                            <div class="mb-3">
                                                <label for="baslik" class="form-label">Başlık:</label>
                                                <input type="text" name="baslik" id="baslik" class="form-control" value="<?= $istek['baslik']; ?>" disabled>
                                            </div>
                                            <div class="mb-3">
                                                <label for="aciklama" class="form-label">Açıklama:</label>
                                                <textarea name="aciklama" id="aciklama" class="form-control" disabled><?= $istek['aciklama']; ?></textarea>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Oluşturma Tarihi:</label>
                                                    <input type="text" class="form-control" value="<?= $istek['tarih']; ?>" disabled>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Kullanıcı ID:</label>
                                                    <input type="text" class="form-control" value="<?= $istek['kullanici_id']; ?>" disabled>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="magaza" class="form-label">Mağaza:</label>
                                                <input type="text" name="magaza" id="magaza" class="form-control" value="<?= $istek['magaza']; ?>" disabled>
                                            </div>
                                            <div class="mb-3">
                                                <label for="derece" class="form-label">İstek Derecesi:</label>
                                                <input type="text" name="derece" id="derece" class="form-control" value="<?= $istek['derece']; ?>" disabled>
                                            </div>
                                            <div class="mb-3">
                                                <label for="durum" class="form-label">Durum:</label>
                                                <select name="durum" id="durum" class="form-select" required>
                                                    <option value="Yeni" <?= $istek['durum'] == 'Yeni' ? 'selected' : ''; ?>>Yeni</option>
                                                    <option value="Beklemede" <?= $istek['durum'] == 'Beklemede' ? 'selected' : ''; ?>>Beklemede</option>
                                                    <option value="Devam Ediyor" <?= $istek['durum'] == 'Devam Ediyor' ? 'selected' : ''; ?>>Devam Ediyor</option>
                                                    <option value="Tamamlandı" <?= $istek['durum'] == 'Tamamlandı' ? 'selected' : ''; ?>>Tamamlandı</option>
                                                    <option value="Durduruldu" <?= $istek['durum'] == 'Durduruldu' ? 'selected' : ''; ?>>Durduruldu</option>
                                                    <option value="Gözden Geçiriliyor" <?= $istek['durum'] == 'Gözden Geçiriliyor' ? 'selected' : ''; ?>>Gözden Geçiriliyor</option>
                                                    <option value="Onay Bekliyor" <?= $istek['durum'] == 'Onay Bekliyor' ? 'selected' : ''; ?>>Onay Bekliyor</option>
                                                    <option value="Red Edildi" <?= $istek['durum'] == 'Red Edildi' ? 'selected' : ''; ?>>Red Edildi</option>
                                                    <option value="Revize Ediliyor" <?= $istek['durum'] == 'Revize Ediliyor' ? 'selected' : ''; ?>>Revize Ediliyor</option>
                                                    <option value="Red Edildi" <?= $istek['durum'] == 'Red Edildi' ? 'selected' : ''; ?>>Red Edildi</option>
                                                    <option value="Erteleme" <?= $istek['durum'] == 'bekliyor' ? 'selected' : ''; ?>>Erteleme</option>
                                                    <option value="İptal Edildi" <?= $istek['durum'] == 'İptal Edildi' ? 'selected' : ''; ?>>İptal Edildi</option>
                                                    <option value="Sorun Var" <?= $istek['durum'] == 'Sorun Var' ? 'selected' : ''; ?>>Sorun Var</option>
                                                    <option value="Tekrar Açıldı" <?= $istek['durum'] == 'Tekrar Açıldı' ? 'selected' : ''; ?>>Tekrar Açıldı</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="personel_id" class="form-label">Görevli:</label>
                                                <select name="personel_id" id="personel_id" class="form-select">
                                                    <?php if (!empty($personeller)): ?>
                                                        <?php foreach ($personeller as $personel): ?>
                                                            <option value="<?= $personel['id']; ?>" <?= $personel['id'] == $istek['personel_id'] ? 'selected' : ''; ?>><?= $personel['ad']; ?></option>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <option value="">Görevli atanmamış</option>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="is_aciklamasi" class="form-label">İş Açıklaması:</label>
                                                <textarea name="is_aciklamasi" id="is_aciklamasi" class="form-control"><?= $istek['is_aciklamasi']; ?></textarea>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                <label for="baslangic_tarihi" class="form-label">Başlangıç Tarihi:</label>
                                                <input type="date" name="baslangic_tarihi" id="baslangic_tarihi" class="form-control" value="<?= !empty($istek['baslangic_tarihi']) ? date('Y-m-d', strtotime($istek['baslangic_tarihi'])) : '' ?>">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                <label for="bitis_tarihi" class="form-label">Bitiş Tarihi:</label>
                                                <input type="date" name="bitis_tarihi" id="bitis_tarihi" class="form-control" value="<?= !empty($istek['bitis_tarihi']) ? date('Y-m-d', strtotime($istek['bitis_tarihi'])) : '' ?>">
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Güncelle</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
        <!--end::Table-->
    </div>
    <!--end::Card body-->
</div>

<div class="modal fade" id="kt_modal_add_istek" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-750px">
        <div class="modal-content">
            <?= include 'app/Views/admin/istek_ekle.php'; ?>
        </div>
    </div>
</div>

<?php
require_once 'app/Views/layouts/footer.php';
?>

<script>
    $(document).ready(function() {
        var table = $('#kt_customers_table').DataTable({
            language: {
                decimal: ",",
                thousands: ".",
                processing: "İşleniyor...",
                lengthMenu: "_MENU_ kayıt göster",
                zeroRecords: "Kayıt bulunamadı",
                info: "Toplam _TOTAL_ kayıttan _START_ - _END_ arası gösteriliyor",
                infoEmpty: "Gösterilecek kayıt yok",
                infoFiltered: "(_MAX_ kayıt içerisinden filtrelendi)",
                search: "Ara:",
                loadingRecords: "Yükleniyor...",
                paginate: {
                    first: "İlk",
                    last: "Son",
                    next: "İleri",
                    previous: "Geri"
                },
                aria: {
                    sortAscending: ": artan sütun sıralamasını aktifleştir",
                    sortDescending: ": azalan sütun sıralamasını aktifleştir"
                }
            },
            responsive: true,
            autoWidth: false
        });

        $('#searchInput').on('keyup', function() {
            table.search(this.value).draw();
        });

        // Çoklu filtreleme: mağaza, durum, personel (tek sütunluk tabloda içerik metnine göre filtrele)
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            try {
                var html = data && data[0] ? String(data[0]) : '';
                var text = $('<div>').html(html).text().toLowerCase();
                var magaza = ($('#magazaFilter').val() || '').toLowerCase();
                var durum = ($('#durumFilter').val() || '');
                var personel = ($('#personelFilter').val() || '');

                var ok = true;
                if (magaza) ok = ok && text.indexOf(magaza) !== -1;
                // Durum birebir eşleşme: kart içinde durum badge'i metni olarak geçiyor
                if (durum) {
                    var durumExact = new RegExp('(^|\\s|>)'+durum+'(\\s|<|$)', 'i');
                    ok = ok && durumExact.test(html);
                }
                if (personel) {
                    // Personel ad-soyad kart içinde farklı biçimlerde olabilir; parçalayıp her parçayı ara
                    var parts = String(personel).split(/\s+/).filter(Boolean);
                    for (var i=0; i<parts.length; i++) {
                        if (text.indexOf(parts[i].toLowerCase()) === -1) { ok = false; break; }
                    }
                }
                return ok;
            } catch (e) { return true; }
        });

        $('#magazaFilter, #durumFilter, #personelFilter').on('change', function() {
            table.draw();
        });

        $('#btnClearFilters').on('click', function() {
            try {
                $('#magazaFilter').val('').trigger('change');
                $('#durumFilter').val('').trigger('change');
                $('#personelFilter').val('').trigger('change');
                // Ayrıca genel aramayı da temizle
                table.search('').draw();
            } catch (e) {
                table.draw();
            }
        });

        function refreshIstekRow(istekId) {
            $.ajax({ url: '/admin/istekler', method: 'GET' })
            .done(function(html) {
                var $tmp = $('<div>').html(html);
                var $newCheckbox = $tmp.find('#kt_customers_table input.form-check-input[value="' + istekId + '"]');
                if ($newCheckbox.length === 0) return;
                var $newTr = $newCheckbox.closest('tr');
                var $oldCheckbox = $('#kt_customers_table input.form-check-input[value="' + istekId + '"]');
                var $oldTr = $oldCheckbox.closest('tr');
                if ($newTr.length && $oldTr.length) {
                    var tableApi = $('#kt_customers_table').DataTable();
                    tableApi.row($oldTr).remove();
                    tableApi.row.add($newTr.get(0)).draw(false);
                }
                var modalId = '#isAtama_' + istekId;
                var $newModal = $tmp.find(modalId);
                var $oldModal = $(modalId);
                if ($newModal.length && $oldModal.length) {
                    $oldModal.replaceWith($newModal);
                }
            }).fail(function(xhr){
                console.error('Satır yenileme hatası:', xhr && xhr.responseText);
            });
        }

        // Modal güncelleme formunu AJAX ile gönder ve modalı hemen kapat
        $(document).on('submit', '.js-istek-update-form', function(e) {
            e.preventDefault();
            var $form = $(this);
            var url = $form.attr('action');
            var data = $form.serialize();
            var istekId = $form.data('istek-id');

            // Modalı hemen kapat
            var modalEl = $form.closest('.modal').get(0);
            if (modalEl && window.bootstrap && window.bootstrap.Modal) {
                var instance = window.bootstrap.Modal.getInstance(modalEl) || new window.bootstrap.Modal(modalEl);
                instance.hide();
            }

            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }).done(function(res){
                // Başarılı ise küçük bir bildirim göster
                try {
                    if (res && res.success) {
                        console.log('İstek güncellendi');
                        if (istekId) { refreshIstekRow(istekId); }
                        try { if (typeof showToast === 'function') showToast('İş emri güncellendi', 'success'); } catch (e) {}
                    } else {
                        console.warn(res && res.message ? res.message : 'Güncelleme tamamlanamadı');
                        try { if (typeof showToast === 'function') showToast(res && res.message ? res.message : 'Güncelleme tamamlanamadı', 'danger'); } catch (e) {}
                    }
                } catch (err) { /* no-op */ }
            }).fail(function(xhr){
                console.error('Güncelleme hatası:', xhr && xhr.responseText);
                try { if (typeof showToast === 'function') showToast('Güncelleme hatası', 'danger'); } catch (e) {}
            });
        });

        // "İş Emri Ekle" modal formunu AJAX ile gönder ve modalı hemen kapat
        $(document).on('submit', '#kt_modal_add_istek_form', function(e) {
            e.preventDefault();
            var $form = $(this);
            var url = $form.attr('action');
            var data = $form.serialize();

            // Modalı hemen kapat
            var modalEl = document.getElementById('kt_modal_add_istek');
            if (modalEl && window.bootstrap && window.bootstrap.Modal) {
                var instance = window.bootstrap.Modal.getInstance(modalEl) || new window.bootstrap.Modal(modalEl);
                instance.hide();
            }

            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }).done(function(res){
                try {
                    if (res && res.success) {
                        console.log('İstek eklendi');
                        // Yeni satırı dinamik olarak ekleyelim
                        var newId = res.id;
                        if (newId) {
                            $.ajax({ url: '/admin/istekler', method: 'GET' }).done(function(html){
                                var $tmp = $('<div>').html(html);
                                var $newCheckbox = $tmp.find('#kt_customers_table input.form-check-input[value="' + newId + '"]');
                                if ($newCheckbox.length) {
                                    var $newTr = $newCheckbox.closest('tr');
                                    // DataTable kullanıldığı için HTML satırı doğrudan ekleyelim
                                    var tableApi = $('#kt_customers_table').DataTable();
                                    tableApi.row.add($newTr.get(0)).draw(false);
                                    try { if (typeof showToast === 'function') showToast('İş emri eklendi', 'success'); } catch (e) {}
                                } else {
                                    // Bulunamazsa son çare tam yenile
                                    setTimeout(function(){ window.location.reload(); }, 300);
                                }
                            });
                        } else {
                            setTimeout(function(){ window.location.reload(); }, 300);
                        }
                        // Formu temizle
                        $form.trigger('reset');
                    } else {
                        console.warn(res && res.message ? res.message : 'Ekleme tamamlanamadı');
                        try { if (typeof showToast === 'function') showToast(res && res.message ? res.message : 'Ekleme tamamlanamadı', 'danger'); } catch (e) {}
                    }
                } catch (err) { /* no-op */ }
            }).fail(function(xhr){
                console.error('Ekleme hatası:', xhr && xhr.responseText);
                try { if (typeof showToast === 'function') showToast('Ekleme hatası', 'danger'); } catch (e) {}
            });
        });

        // Görsel önizleme: js-attachment linklerine tıklandığında modalda tam boy göster
        $(document).on('click', 'a.js-attachment', function(e){
            try {
                var href = $(this).data('href') || $(this).attr('href');
                if (!href) return;
                var isImage = /(\.png|\.jpg|\.jpeg|\.webp|\.gif|\.bmp|\.svg)(\?.*)?$/i.test(href);
                if (!isImage) return; // yalnızca görseller
                e.preventDefault();
                var $img = $('#previewImage');
                if ($img.length) { $img.attr('src', href); }
                var modalEl = document.getElementById('previewModal');
                if (modalEl && window.bootstrap && window.bootstrap.Modal) {
                    var instance = window.bootstrap.Modal.getInstance(modalEl) || new window.bootstrap.Modal(modalEl);
                    instance.show();
                }
            } catch (err) { /* no-op */ }
        });
    });
</script>
