<?php /** @var array $depots */ /** @var array $items */ ?>
<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<?php require_once __DIR__ . '/../../layouts/sidebar.php'; ?>

<div class="container-fluid py-5">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Tamsoft Stok Listesi</h3>
            <div>
                <form method="get" class="d-flex align-items-center gap-2">
                    <input type="date" name="tarih" value="<?= htmlspecialchars($_GET['tarih'] ?? '1900-01-01') ?>" class="form-control form-control-sm" style="width: 180px;" />
                    <input type="number" name="depoid" value="<?= htmlspecialchars($_GET['depoid'] ?? '') ?>" class="form-control form-control-sm" placeholder="Depo ID (boş=hepsi)" style="width: 180px;" />
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="pozitif" value="1" id="pozitifSwitch" <?= (isset($_GET['pozitif']) ? 'checked' : 'checked') ?>>
                        <label class="form-check-label" for="pozitifSwitch">Sadece miktarı > 0</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="use_stok" value="1" id="useStokSwitch" <?= (isset($_GET['use_stok']) ? 'checked' : '') ?>>
                        <label class="form-check-label" for="useStokSwitch">StokListesi endpoint</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="last_barcode" value="1" id="lastBarcodeSwitch" <?= (isset($_GET['last_barcode']) ? 'checked' : '') ?>>
                        <label class="form-check-label" for="lastBarcodeSwitch">Son barkod</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="only_ecom" value="1" id="onlyEcomSwitch" <?= (isset($_GET['only_ecom']) ? 'checked' : '') ?>>
                        <label class="form-check-label" for="onlyEcomSwitch">Sadece e‑ticaret</label>
                    </div>
                    <button class="btn btn-sm btn-light" type="submit">Uygula</button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex gap-2 mb-4">
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#tokenTestModal">Token Test</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#depoPreviewModal">Depolar</button>
                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#stokPreviewModal">Stok Önizleme</button>
            </div>
            <?php if (empty($items)): ?>
                <div class="alert alert-info">Henüz veri yok. Tamsoft entegrasyonu yapılandırıldığında burada stoklar listelenecek.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table id="tamsoftStokTable" class="table table-row-dashed align-middle">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th>Ürün Adı</th>
                                <th>Ürün Kodu</th>
                                <th>Barkod</th>
                                <?php foreach ($depots as $depo): ?>
                                    <th>Stok (<?= htmlspecialchars($depo) ?>)</th>
                                <?php endforeach; ?>
                                <th>KDV</th>
                                <th>Alış Fiyatı</th>
                                <th>Satış Fiyatı</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['urun_adi'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['urun_kodu'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['barkod'] ?? '-') ?></td>
                                    <?php foreach ($depots as $depo): ?>
                                        <td><?= (int)($row['stoklar'][$depo] ?? 0) ?></td>
                                    <?php endforeach; ?>
                                    <td>%<?= (int)($row['kdv'] ?? 0) ?></td>
                                    <td><?= number_format((float)($row['alis_fiyati'] ?? 0), 2, ',', '.') ?></td>
                                    <td><?= number_format((float)($row['satis_fiyati'] ?? 0), 2, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Token Test Modal -->
<div class="modal fade" id="tokenTestModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Token Test</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="tokenTestResult" class="text-muted">Yükleniyor...</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
      </div>
    </div>
  </div>
 </div>

<!-- Depo Listesi Modal -->
<div class="modal fade" id="depoPreviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Depo Listesi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <pre id="depolarJson" class="bg-light p-3 rounded" style="max-height: 400px; overflow: auto;">Yükleniyor...</pre>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
      </div>
    </div>
  </div>
 </div>

<!-- Stok Önizleme Modal -->
<div class="modal fade" id="stokPreviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Stok Önizleme</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3 align-items-end mb-3">
            <div class="col-md-3">
                <label class="form-label">Depo ID</label>
                <input type="number" class="form-control" id="previewDepoId" placeholder="1" />
            </div>
            <div class="col-md-3">
                <label class="form-label">Tarih</label>
                <input type="date" class="form-control" id="previewTarih" value="<?= htmlspecialchars($_GET['tarih'] ?? '1900-01-01') ?>" />
            </div>
            <div class="col-md-3 form-check form-switch">
                <input class="form-check-input" type="checkbox" id="previewPozitif" checked />
                <label class="form-check-label" for="previewPozitif">Sadece miktarı > 0</label>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary" id="btnPreviewFetch">Önizlemeyi Getir</button>
            </div>
        </div>
        <pre id="stokPreviewJson" class="bg-light p-3 rounded" style="max-height: 500px; overflow: auto;">Bekleniyor...</pre>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
      </div>
    </div>
  </div>
 </div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // DataTable init
    if (window.jQuery && $('#tamsoftStokTable').length) {
        $('#tamsoftStokTable').DataTable({
            pageLength: 25,
            ordering: true,
            order: [[0, 'asc']],
            language: {
                url: '/public/plugins/custom/datatables/Turkish.json'
            }
        });
    }

    // Token Test modal
    const tokenModal = document.getElementById('tokenTestModal');
    tokenModal?.addEventListener('show.bs.modal', () => {
        const el = document.getElementById('tokenTestResult');
        el.textContent = 'Yükleniyor...';
        fetch('/admin/tamsoft/token-test')
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    el.innerHTML = '<div class="alert alert-success">Token hazır: <b>' + (d.token_preview || '') + '</b><br/>Bitiş: ' + (d.expires_at || '-') + '</div>';
                } else {
                    el.innerHTML = '<div class="alert alert-danger">Hata: ' + (d.error || 'unknown') + '</div>';
                }
            })
            .catch(() => { el.innerHTML = '<div class="alert alert-danger">İstek hatası</div>'; });
    });

    // Depo modal
    const depoModal = document.getElementById('depoPreviewModal');
    depoModal?.addEventListener('show.bs.modal', () => {
        const el = document.getElementById('depolarJson');
        el.textContent = 'Yükleniyor...';
        fetch('/admin/tamsoft/depolar')
            .then(r => r.json())
            .then(d => {
                el.textContent = JSON.stringify(d, null, 2);
            })
            .catch(() => { el.textContent = 'İstek hatası'; });
    });

    // Stok önizleme
    document.getElementById('btnPreviewFetch')?.addEventListener('click', () => {
        const depoId = parseInt(document.getElementById('previewDepoId').value || '0', 10);
        const tarih = document.getElementById('previewTarih').value || '1900-01-01';
        const pozitif = document.getElementById('previewPozitif').checked ? '1' : '0';
        const el = document.getElementById('stokPreviewJson');
        el.textContent = 'Yükleniyor...';
        if (!depoId) { el.textContent = 'Lütfen geçerli bir Depo ID girin.'; return; }
        const qs = new URLSearchParams({ depoid: String(depoId), tarih, pozitif });
        fetch('/admin/tamsoft/estok-preview?' + qs.toString())
            .then(r => r.json())
            .then(d => { el.textContent = JSON.stringify(d, null, 2); })
            .catch(() => { el.textContent = 'İstek hatası'; });
    });
});
</script>


