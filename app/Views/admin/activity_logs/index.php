<?php require_once 'app/Views/layouts/header.php'; ?>
<?php require_once 'app/Views/layouts/navbar.php'; ?>

<div class="container-fluid py-4">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Kullanıcı Aktivite Logları</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Kullanıcı</th>
              <th>İşlem</th>
              <th>Varlık</th>
              <th>Varlık ID</th>
              <th>IP</th>
              <th>Tarih</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!empty($logs)): foreach ($logs as $log): ?>
            <tr>
              <td><?= (int)$log['id'] ?></td>
              <td><?= htmlspecialchars($log['user_id']) ?></td>
              <td><span class="badge bg-secondary"><?= htmlspecialchars($log['action']) ?></span></td>
              <td><?= htmlspecialchars($log['entity_type']) ?></td>
              <td><?= htmlspecialchars($log['entity_id']) ?></td>
              <td><code><?= htmlspecialchars($log['ip_address']) ?></code></td>
              <td><?= htmlspecialchars($log['created_at']) ?></td>
            </tr>
          <?php endforeach; else: ?>
            <tr>
              <td colspan="7" class="text-center text-muted">Kayıt bulunamadı</td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once 'app/Views/layouts/footer.php'; ?>


