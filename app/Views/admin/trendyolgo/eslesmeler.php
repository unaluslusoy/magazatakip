<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<div class="container-fluid py-5">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Eşleşmeler (Trendyol GO)</h3>
            <div class="d-flex gap-2">
                <select id="storeFilter" class="form-select form-select-sm" style="width:auto">
                    <option value="">Tüm Mağazalar</option>
                    <?php foreach (($stores ?? []) as $s): $sid=(string)($s['store_id']??''); ?>
                        <option value="<?= htmlspecialchars($sid) ?>" <?= (isset($store_id)&&$store_id===$sid)?'selected':'' ?>><?= htmlspecialchars(($s['magaza_adi']??'-').' (#'.$sid.')') ?></option>
                    <?php endforeach; ?>
                </select>
                <a href="/admin/match" class="btn btn-sm btn-light">Eşleştirme Sayfasına Dön</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-auto">
                    <span class="badge bg-success">Eşleşen: <?= (int)($matched_count ?? 0) ?></span>
                </div>
                <div class="col-auto">
                    <span class="badge bg-warning text-dark">Eşleşmeyen (Tamsoft): <?= (int)($unmatched_tamsoft_count ?? 0) ?></span>
                </div>
                <?php if (isset($unmatched_trendyol_count) && $unmatched_trendyol_count !== null): ?>
                <div class="col-auto">
                    <span class="badge bg-info text-dark">Eşleşmeyen (Trendyol<?= isset($store_id)&&$store_id!==''?' #'.$store_id:'' ?>): <?= (int)$unmatched_trendyol_count ?></span>
                </div>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-striped align-middle" id="tbMatches">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ext Ürün Kodu</th>
                        <th>Tamsoft Ürün</th>
                        <th>Barkod</th>
                        <th>Mağaza</th>
                        <th>Trendyol SKU</th>
                        <th>Skor</th>
                        <th>Kaynak</th>
                        <th>Manuel</th>
                        <th>Son Eşleşme</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
<script>
(function(){
    const CSRF = (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'))||'';
    const $storeFilter = document.getElementById('storeFilter');
    let dt = null;
    if ($storeFilter) {
        $storeFilter.addEventListener('change', ()=>{
            const v = $storeFilter.value;
            const u = new URL(window.location.href);
            if (v !== '') { u.searchParams.set('store_id', v); } else { u.searchParams.delete('store_id'); }
            window.location.href = u.toString();
        });
    }
    function openEditModal(row){
        const wrap = document.createElement('div');
        const checked = row.manual==='1' ? 'checked' : '';
        // PHP içindeki mağaza listesini JS stringine göm
        const stores = <?php echo json_encode(array_map(function($s){ return ['id'=>(string)($s['store_id']??''), 'name'=>(string)($s['magaza_adi']??'')]; }, ($stores ?? [])), JSON_UNESCAPED_UNICODE); ?>;
        const storeOpts = ['<option value="">(Platform-genel)</option>'].concat(stores.map(s=>`<option value="${s.id}" ${row.store===s.id?'selected':''}>${s.name} (#${s.id})</option>`)).join('');
        wrap.innerHTML = `
            <div class="modal fade" id="matchEditModal" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header"><h5 class="modal-title">Eşleşme Düzenle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                  <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Tamsoft Ürün</label><input type="text" class="form-control" value="${row.urun||''}" disabled></div>
                    <div class="mb-3"><label class="form-label">Ext Ürün Kodu</label><input type="text" class="form-control" value="${row.urun_kodu||''}" disabled></div>
                    <div class="mb-3"><label class="form-label">Barkod</label><input id="fmBarkod" type="text" class="form-control" value="${row.barkod||''}"></div>
                    <div class="mb-3"><label class="form-label">Trendyol SKU</label><input id="fmSku" type="text" class="form-control" value="${row.sku||''}"></div>
                    <div class="mb-3"><label class="form-label">Mağaza</label><select id="fmStore" class="form-select">${storeOpts}</select></div>
                    <div class="form-check"><input id="fmManual" class="form-check-input" type="checkbox" ${checked}><label class="form-check-label" for="fmManual">Manuel öncelik</label></div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
                    <button type="button" class="btn btn-danger" id="btnDelete">Sil</button>
                    <button type="button" class="btn btn-primary" id="btnSave">Kaydet</button>
                  </div>
                </div>
              </div>
            </div>`;
        document.body.appendChild(wrap);
        const el = wrap.querySelector('#matchEditModal');
        const modal = new bootstrap.Modal(el);
        modal.show();
        wrap.querySelector('#btnSave').addEventListener('click', async ()=>{
            const payload = {
                id: row.id,
                barkod: wrap.querySelector('#fmBarkod').value.trim(),
                trendyolgo_sku: wrap.querySelector('#fmSku').value.trim(),
                store_id: wrap.querySelector('#fmStore').value.trim(),
                manual_override: wrap.querySelector('#fmManual').checked ? 1 : 0
            };
            const r = await fetch('/admin/trendyolgo/eslesmeler/update', { method:'POST', headers:{ 'Content-Type':'application/json', 'X-CSRF-Token': CSRF, 'X-Requested-With':'XMLHttpRequest' }, body: JSON.stringify(payload) });
            const d = await r.json();
            if (d.success) { try{ showToast('Güncellendi','success'); }catch(e){} window.location.reload(); } else { try{ showToast('Hata: '+(d.error||''),'danger'); }catch(e){} }
        });
        wrap.querySelector('#btnDelete').addEventListener('click', async ()=>{
            if (!confirm('Silinsin mi?')) return;
            const fd = new FormData(); fd.append('id', String(row.id));
            const r = await fetch('/admin/trendyolgo/eslesmeler/delete', { method:'POST', headers:{ 'X-CSRF-Token': CSRF, 'X-Requested-With':'XMLHttpRequest' }, body: fd });
            const d = await r.json();
            if (d.success) { try{ showToast('Silindi','success'); }catch(e){} window.location.reload(); } else { try{ showToast('Hata: '+(d.error||''),'danger'); }catch(e){} }
        });
        el.addEventListener('hidden.bs.modal', ()=>{ wrap.remove(); });
    }
    // DataTables init (server-side)
    dt = window.jQuery('#tbMatches').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        lengthChange: true,
        pageLength: 50,
        lengthMenu: [[50,100,200,500],[50,100,200,500]],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json' },
        ajax: {
            url: '/admin/trendyolgo/eslesmeler/data',
            data: function(d){ d.store_id = (document.getElementById('storeFilter')?.value||'').trim(); }
        },
        columns: [
            { data: 'id' },
            { data: 'urun_kodu', render: function(d){ return `<code>${d||''}</code>`; } },
            { data: 'urun_adi' },
            { data: 'barkod' },
            { data: 'store_id', render: function(d){ return d||'-'; } },
            { data: 'trendyolgo_sku', render: function(d){ return `<code>${d||''}</code>`; } },
            { data: 'match_confidence' },
            { data: 'match_source' },
            { data: 'manual_override', render: function(d){ return d? 'Evet':'Hayır'; } },
            { data: 'last_matched_at' },
            { data: null, orderable:false, render: function(d,t,row){ return `<button class="btn btn-sm btn-primary js-edit" data-id="${row.id}" data-urun="${(row.urun_adi||'').replace(/"/g,'&quot;')}" data-urun_kodu="${row.urun_kodu||''}" data-barkod="${row.barkod||''}" data-sku="${row.trendyolgo_sku||''}" data-store="${row.store_id||''}" data-manual="${row.manual_override?1:0}">Düzenle</button>`; } }
        ]
    });
    // Delegate edit
    window.jQuery('#tbMatches tbody').on('click', 'button.js-edit', function(){
        const data = dt.row(window.jQuery(this).closest('tr')).data();
        const row = {
            id: data.id,
            urun: data.urun_adi||'',
            urun_kodu: data.urun_kodu||'',
            barkod: data.barkod||'',
            sku: data.trendyolgo_sku||'',
            store: data.store_id||'',
            manual: data.manual_override? '1':'0'
        };
        openEditModal(row);
    });
    if ($storeFilter) { $storeFilter.addEventListener('change', ()=>{ dt.ajax.reload(null, true); }); }
})();
</script>

