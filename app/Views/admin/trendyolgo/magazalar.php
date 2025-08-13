<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<div class="container-fluid py-5">
    <div class="card mb-5">
        <div class="card-header">
            <h3 class="card-title mb-0">Trendyol Mağaza Ekle</h3>
        </div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Mağaza Adı</label>
                    <input type="text" name="magaza_adi" class="form-control" required />
                </div>
                <div class="col-md-4">
                    <label class="form-label">Store ID</label>
                    <input type="text" name="store_id" class="form-control" required />
                </div>
                <div class="col-md-4">
                    <label class="form-label">Telefon</label>
                    <input type="text" name="telefon" class="form-control" />
                </div>
                <div class="col-12">
                    <label class="form-label">Adres</label>
                    <textarea name="adres" class="form-control" rows="2"></textarea>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Ekle</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Kayıtlı Mağazalar</h3>
        </div>
        <div class="card-body">
            <?php if (empty($magazalar)): ?>
                <div class="alert alert-info">Henüz mağaza eklenmemiş.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-row-dashed align-middle">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th>Mağaza Adı</th>
                                <th>Store ID</th>
                                <th>Telefon</th>
                                <th>Adres</th>
                                <th class="text-end">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($magazalar as $m): ?>
                            <tr>
                                <td><?= htmlspecialchars($m['magaza_adi']) ?></td>
                                <td><?= htmlspecialchars($m['store_id']) ?></td>
                                <td><?= htmlspecialchars($m['telefon'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($m['adres'] ?? '-') ?></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?= (int)$m['id'] ?>" data-magaza="<?= htmlspecialchars($m['magaza_adi']) ?>" data-store="<?= htmlspecialchars($m['store_id']) ?>" data-tel="<?= htmlspecialchars($m['telefon'] ?? '') ?>" data-adres="<?= htmlspecialchars($m['adres'] ?? '') ?>">Düzenle</button>
                                    <a href="/admin/trendyolgo/magaza/sil/<?= (int)$m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Silinsin mi?');">Sil</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Düzenleme Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Mağaza Düzenle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post">
        <div class="modal-body row g-3">
            <input type="hidden" name="id" id="edit-id" />
            <div class="col-md-6">
                <label class="form-label">Mağaza Adı</label>
                <input type="text" class="form-control" name="magaza_adi" id="edit-magaza" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Store ID</label>
                <input type="text" class="form-control" name="store_id" id="edit-store" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Telefon</label>
                <input type="text" class="form-control" name="telefon" id="edit-tel" />
            </div>
            <div class="col-12">
                <label class="form-label">Adres</label>
                <textarea class="form-control" name="adres" id="edit-adres" rows="2"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
            <button type="submit" class="btn btn-primary">Kaydet</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
document.getElementById('editModal')?.addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    if (!btn) return;
    document.getElementById('edit-id').value = btn.getAttribute('data-id') || '';
    document.getElementById('edit-magaza').value = btn.getAttribute('data-magaza') || '';
    document.getElementById('edit-store').value = btn.getAttribute('data-store') || '';
    document.getElementById('edit-tel').value = btn.getAttribute('data-tel') || '';
    document.getElementById('edit-adres').value = btn.getAttribute('data-adres') || '';
});
</script>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

