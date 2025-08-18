<?php
$title = "Cloudflare Entegrasyon";
require_once 'app/Views/layouts/header.php';
require_once 'app/Views/layouts/navbar.php';
?>
<div class="content d-flex flex-column flex-column-fluid" id="kt_app_content">
  <div class="container-fluid" id="kt_app_content_container">
    <?php if (!empty($message)): ?>
      <div class="alert alert-<?= htmlspecialchars($messageType ?: 'info'); ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    <div class="row g-5 g-xl-8">
      <div class="col-12">
    <div class="card shadow-sm w-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title mb-0">Cloudflare Durumu ve Ayarlar</h3>
        <button type="button" class="btn btn-sm btn-light-info" data-bs-toggle="modal" data-bs-target="#cfHelpModal">
          <i class="bi bi-info-circle"></i> API Yardım
        </button>
      </div>
      <div class="card-body">
        <form method="POST" action="/admin/cloudflare/save">
          <?= csrf_field(); ?>
          <div class="row g-6 mb-6">
            <div class="col-md-4">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="enabled" name="enabled" <?= !empty($config['enabled']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="enabled">Cloudflare Entegrasyonu Aktif</label>
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="real_ip_header">Gerçek IP Header</label>
              <input type="text" class="form-control form-control-solid" id="real_ip_header" name="real_ip_header" value="<?= htmlspecialchars($config['real_ip_header'] ?? 'CF-Connecting-IP'); ?>">
            </div>
            <div class="col-md-4">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="show_recommendations" name="show_recommendations" <?= !empty($config['show_recommendations']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="show_recommendations">Önerileri Göster</label>
              </div>
            </div>
          </div>

          <div class="row g-6 mb-6">
            <div class="col-md-4">
              <label class="form-label" for="api_token">API Token</label>
              <input type="password" class="form-control form-control-solid" id="api_token" name="api_token" value="<?= htmlspecialchars($config['api_token'] ?? '') ?>" placeholder="Cloudflare API Token">
              <small class="text-muted">Edit Zone/DNS ve Settings erişimli token önerilir.</small>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="account_id">Account ID</label>
              <input type="text" class="form-control form-control-solid" id="account_id" name="account_id" value="<?= htmlspecialchars($config['account_id'] ?? '') ?>" placeholder="Account ID">
            </div>
            <div class="col-md-4">
              <label class="form-label" for="zone_id">Zone ID</label>
              <input type="text" class="form-control form-control-solid" id="zone_id" name="zone_id" value="<?= htmlspecialchars($config['zone_id'] ?? '') ?>" placeholder="Zone ID">
            </div>
          </div>

          <div class="mb-6">
            <div class="alert alert-info">
              API Durumu: Token <?= !empty($api['token_set']) ? '✔' : '✖' ?>, Zone <?= !empty($api['zone_id_set']) ? '✔' : '✖' ?>
            </div>
          </div>

          <div class="mb-6">
            <h5>Gelen Header’lar (son istek)</h5>
            <pre class="p-3 bg-light border rounded" style="white-space:pre-wrap;">
CF-Connecting-IP: <?= htmlspecialchars($server['cf_connecting_ip'] ?? ''); ?>
True-Client-IP: <?= htmlspecialchars($server['true_client_ip'] ?? ''); ?>
X-Forwarded-For: <?= htmlspecialchars($server['x_forwarded_for'] ?? ''); ?>
CF-Visitor: <?= htmlspecialchars($server['cf_visitor'] ?? ''); ?>
Proto: <?= htmlspecialchars($server['proto'] ?? ''); ?>, RemoteAddr: <?= htmlspecialchars($server['remote_addr'] ?? ''); ?>
            </pre>
          </div>

          <?php if (!empty($config['show_recommendations'])): ?>
          <div class="mb-6">
            <h5>Cloudflare Önerilen Ayarlar</h5>
            <ul>
              <li>Always Use HTTPS: Açık</li>
              <li>Brotli: Açık</li>
              <li>Origin Cache-Control: Onurlandır</li>
              <li>Page Rule/Cache Rule: <code>/public/*</code> için Cache Everything + Edge Cache TTL (1-7 gün), <code>/*.php</code> hariç</li>
              <li>Rocket Loader: Dinamik sayfalar için kapalı (isteğe bağlı)</li>
              <li>Development Mode: Geçici değişikliklerde kısa süreli</li>
            </ul>
          </div>
          <?php endif; ?>

          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Ayarları Kaydet</button>
          </div>
        </form>

        <?php if (!empty($config['api_token']) && !empty($config['zone_id'])): ?>
        <div class="separator my-8"></div>
        <div class="row g-6 mb-6">
          <div class="col-md-6">
            <h5>Development Mode</h5>
            <form method="POST" action="/admin/cloudflare/devmode">
              <?= csrf_field(); ?>
              <div class="input-group">
                <select class="form-select" name="on">
                  <option value="1">Aç</option>
                  <option value="0">Kapat</option>
                </select>
                <button type="submit" class="btn btn-light-primary">Uygula</button>
              </div>
            </form>
            <?php if (!empty($devMode['result']['value'])): ?>
              <small class="text-muted">Mevcut: <?= htmlspecialchars($devMode['result']['value']); ?></small>
            <?php endif; ?>
          </div>
          <div class="col-md-6">
            <h5>Cache Purge</h5>
            <form method="POST" action="/admin/cloudflare/purge">
              <?= csrf_field(); ?>
              <div class="mb-2">
                <textarea class="form-control form-control-solid" name="files" rows="3" placeholder="İsteğe bağlı URL listesi (satır satır)"></textarea>
              </div>
              <button type="submit" class="btn btn-danger">Purge (Listedekiler veya Tümü)</button>
            </form>
          </div>
        </div>

        <div class="row g-6 mb-6">
          <div class="col-md-6">
            <h5>SSL/TLS Modu</h5>
            <form method="POST" action="/admin/cloudflare/ssl-set">
              <?= csrf_field(); ?>
              <div class="input-group">
                <select class="form-select" name="mode">
                  <option value="off">Off</option>
                  <option value="flexible">Flexible</option>
                  <option value="full" selected>Full</option>
                  <option value="strict">Full (Strict)</option>
                </select>
                <button type="submit" class="btn btn-light-primary">Güncelle</button>
              </div>
            </form>
          </div>
          <div class="col-md-6">
            <h5>DNS Kaydı Ekle</h5>
            <form method="POST" action="/admin/cloudflare/dns-create">
              <?= csrf_field(); ?>
              <div class="row g-3">
                <div class="col-3">
                  <select name="type" class="form-select">
                    <option>A</option>
                    <option>AAAA</option>
                    <option>CNAME</option>
                    <option>TXT</option>
                  </select>
                </div>
                <div class="col-5"><input class="form-control" name="name" placeholder="ad.example.com"></div>
                <div class="col-4"><input class="form-control" name="content" placeholder="IP veya hedef"></div>
                <div class="col-3"><input class="form-control" name="ttl" type="number" min="1" value="1" title="1=Auto"></div>
                <div class="col-3 form-check form-switch"><input class="form-check-input" type="checkbox" name="proxied" id="proxied"><label for="proxied" class="form-check-label">Proxy</label></div>
                <div class="col-12"><button type="submit" class="btn btn-success">Ekle</button></div>
              </div>
            </form>
          </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($zones['result'])): ?>
          <div class="mb-6">
            <h5>Hesaptaki Zone’lar</h5>
            <ul>
              <?php foreach (($zones['result'] ?? []) as $z): ?>
                <li><?= htmlspecialchars($z['name'] ?? '-') ?> (ID: <?= htmlspecialchars($z['id'] ?? '-') ?>)</li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if (!empty($zoneDetails['result'])): ?>
          <div class="mb-6">
            <h5>Zone Detayı</h5>
            <pre class="p-3 bg-light border rounded" style="white-space:pre-wrap; max-height:280px; overflow:auto;"><?= htmlspecialchars(json_encode($zoneDetails['result'], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) ?></pre>
          </div>
        <?php endif; ?>

        <?php if (!empty($dns['result'])): ?>
          <div class="mb-6">
            <h5>DNS Kayıtları (ilk <?= count($dns['result']) ?>)</h5>
            <pre class="p-3 bg-light border rounded" style="white-space:pre-wrap; max-height:280px; overflow:auto;"><?= htmlspecialchars(json_encode($dns['result'], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) ?></pre>
          </div>
        <?php endif; ?>
      </div>
    </div>
      </div>
    </div>
  </div>
</div>

<!-- API Yardım Modali -->
<div class="modal fade" id="cfHelpModal" tabindex="-1" aria-labelledby="cfHelpModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cfHelpModalLabel">Cloudflare API Bağlantı Yardımı</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <ol class="mb-0 ps-4">
          <li>Cloudflare hesabınıza girin ve <strong>My Profile → API Tokens</strong> sayfasına gidin.</li>
          <li><strong>Create Token</strong> deyin ve şablonlardan <strong>Edit Cloudflare Workers</strong> yerine <strong>Edit zone DNS</strong> ve <strong>Zone Settings Read</strong> gibi yetkileri içeren özel bir token oluşturun.
            <ul>
              <li>Permissions (öneri): Zone → Cache Purge (Edit), Zone → Zone Settings (Edit), Zone → DNS (Read), Account → Account Settings (Read)</li>
              <li>Resources: Account (Hesabınızı seçin), Zone (alan adınızı seçin veya All Zones)</li>
            </ul>
          </li>
          <li>Oluşan <strong>API Token</strong>’ı panelde <strong>API Token</strong> alanına yapıştırın.</li>
          <li><strong>Account ID</strong> ve <strong>Zone ID</strong> bilgilerini Cloudflare Dashboard → Overview sayfasında bulabilirsiniz. Panelde ilgili alanlara girin.</li>
          <li>Kaydedin ve ardından <strong>Development Mode</strong> veya <strong>Cache Purge</strong> işlemlerini test edin.</li>
        </ol>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
      </div>
    </div>
  </div>
  </div>
<?php require_once 'app/Views/layouts/footer.php'; ?>


