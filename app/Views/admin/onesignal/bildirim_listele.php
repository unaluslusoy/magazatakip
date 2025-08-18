<?php
$title = "Gönderilen Bildirimler";
$link = "Bildirimler";


require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>

    <div class="d-flex flex-column flex-lg-row">
        <!-- Sidebar -->
        <div class="d-none d-lg-flex flex-column flex-lg-row-auto w-100 w-lg-275px">
            <div class="card card-flush mb-0">
                <div class="card-body">
                    <a href="<?= site_url('/admin/bildirim_gonder') ?>" class="btn btn-primary fw-bold w-100 mb-8">Yeni Bildirim Gönder</a>
                    <div class="menu menu-column menu-rounded menu-state-bg menu-state-title-primary">
                        <?php
                        $menuItems = [
                            'today' => ['Bugün', 'danger'],
                            'yesterday' => ['Dün', 'success'],
                            'last_week' => ['Geçmiş Haftalar', 'info']
                        ];
                        foreach ($menuItems as $key => $item): ?>
                            <div class="menu-item mb-3">
                                <a href="<?= site_url('/admin/bildirimler?date_filter=' . $key) ?>" class="menu-link <?= $dateFilter === $key ? 'active' : '' ?>">
                                <span class="menu-icon">
                                    <i class="ki-outline ki-abstract-8 fs-5 text-<?= $item[1] ?> me-3 lh-0"></i>
                                </span>
                                    <span class="menu-title fw-semibold"><?= $item[0] ?></span>
                                    <span class="badge badge-light-<?= $item[1] ?>"><?= $notificationCounts[$key] ?></span>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-lg-row-fluid ms-lg-7 ms-xl-10">
            <div class="card">
                <div class="card-body">
                    <?php if (empty($bildirimler['data'])): ?>
                        <div class="alert alert-info" role="alert">
                            Henüz hiç bildirim gönderilmemiş.
                        </div>
                    <?php else: ?>
                        <table class="table table-hover table-row-dashed fs-6 gy-5 my-0" id="kt_inbox_listing">
                            <thead>
                            <tr>
                                <th>Başlık</th>
                                <th>Alıcı Tipi</th>
                                <th>Mesaj</th>
                                <th>Gönderim Tarihi</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($bildirimler['data'] as $bildirim): ?>
                                <tr>
                                    <td><?= htmlspecialchars($bildirim['baslik']) ?></td>
                                    <td><?= htmlspecialchars($bildirim['alici_tipi']) ?></td>
                                    <td><?= htmlspecialchars($bildirim['mesaj']) ?></td>
                                    <td><?= htmlspecialchars($bildirim['gonderim_tarihi']) ?></td>
                                    <td><?= $bildirim['okundu'] ? 'Okundu' : 'Okunmadı' ?></td>
                                    <td>
                                        <?php if (!$bildirim['okundu']): ?>
                                            <a href="/admin/bildirimler/okundu/<?= $bildirim['id'] ?>" class="btn btn-sm btn-light btn-active-light-primary">Okundu İşaretle</a>
                                        <?php endif; ?>
                                        <a href="/admin/bildirimler/sil/<?= $bildirim['id'] ?>" class="btn btn-sm btn-light btn-active-light-danger" onclick="return confirm('Bu bildirimi silmek istediğinizden emin misiniz?')">Sil</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center flex-wrap mt-5">
                            <div class="d-flex flex-wrap py-2 mr-3">
                                <?php for ($i = 1; $i <= $bildirimler['lastPage']; $i++): ?>
                                    <a href="<?= site_url('/admin/bildirimler?page=' . $i . '&per_page=' . $perPage . '&date_filter=' . $dateFilter) ?>" class="btn btn-icon btn-sm btn-light-primary mr-2 my-1 <?= $i == $bildirimler['currentPage'] ? 'active' : '' ?>"><?= $i ?></a>
                                <?php endfor; ?>
                            </div>
                            <div class="d-flex align-items-center py-3">
                                <select class="form-select form-select-sm form-select-solid" onchange="window.location.href=this.value">
                                    <?php foreach ([10, 25, 50, 100] as $pp): ?>
                                        <option value="<?= site_url('/admin/bildirimler?page=1&per_page=' . $pp . '&date_filter=' . $dateFilter) ?>" <?= $pp == $perPage ? 'selected' : '' ?>><?= $pp ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>


<?php
require_once 'app/Views/layouts/footer.php';
?>