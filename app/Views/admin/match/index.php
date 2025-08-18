<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<div class="container-fluid py-5">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Ürün Eşleştirme (Tamsoft ↔ Trendyol GO)</h3>
            <div class="d-flex gap-2">
                <a href="/admin/match/stores" class="btn btn-sm btn-light">Mağaza-Depo Eşleştirme</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Platform</label>
                    <select id="platformSelect" class="form-select">
                        <option value="trendyolgo" selected>Trendyol GO</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Trendyol GO Mağaza</label>
                    <select id="storeSelect" class="form-select">
                        <option value="">Seçiniz</option>
                        <?php foreach (($stores ?? []) as $s): $sid = (string)($s['store_id'] ?? ''); ?>
                            <option value="<?= htmlspecialchars($sid) ?>"><?= htmlspecialchars(($s['magaza_adi'] ?? '-') . ' (#' . $sid . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end justify-content-end gap-2">
                    <div class="form-check me-3">
                        <input class="form-check-input" type="checkbox" id="onlyUnmatched"/>
                        <label class="form-check-label" for="onlyUnmatched">Sadece eşleşmemiş</label>
                    </div>
                    <button id="btnAnalyze" class="btn btn-primary"><span class="spinner-border spinner-border-sm d-none" id="spAnalyze"></span> Analiz et</button>
                </div>
            </div>
            <div id="selectionSummary" class="alert alert-secondary d-none">
                <div class="d-flex flex-wrap gap-3 align-items-center">
                    <div><strong>Seçimler:</strong></div>
                    <div id="selTamsoft" class="badge bg-light text-dark border d-none"></div>
                    <div id="selTrendyol" class="badge bg-light text-dark border d-none"></div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="table-responsive border rounded">
                        <div class="d-flex gap-2 p-2 align-items-center">
                            <input id="qTamsoft" class="form-control" placeholder="Tamsoft: ürün adı / barkod / kod"/>
                            <button id="btnSearchTamsoft" class="btn btn-light"><span class="spinner-border spinner-border-sm d-none" id="spSearchT"></span> Ara</button>
                        </div>
                        <table class="table table-striped align-middle mb-0" id="tbTamsoft">
                            <thead><tr><th>Kod</th><th>Barkod</th><th>Ürün</th><th></th></tr></thead>
                            <tbody></tbody>
                        </table>
                        <div class="small text-muted p-2" id="tamsoftCount"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="table-responsive border rounded">
                        <div class="d-flex gap-2 p-2 align-items-center">
                            <input id="qTrendyol" class="form-control" placeholder="Trendyol GO: başlık / barkod / kod"/>
                            <button id="btnSearchTrendyol" class="btn btn-light"><span class="spinner-border spinner-border-sm d-none" id="spSearchG"></span> Ara</button>
                        </div>
                        <table class="table table-striped align-middle mb-0" id="tbTrendyol">
                            <thead><tr><th>Kod</th><th>Barkod</th><th>Başlık</th><th>Marka</th><th>Görsel</th><th></th></tr></thead>
                            <tbody></tbody>
                        </table>
                        <div class="small text-muted p-2" id="trendyolCount"></div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button id="btnMatch" class="btn btn-primary" disabled>Eşleştir</button>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
<script>
(function(){
    const CSRF = (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'))||'';
    const $tbT = document.querySelector('#tbTamsoft tbody');
    const $tbG = document.querySelector('#tbTrendyol tbody');
    const $sum = document.getElementById('selectionSummary');
    const $selT = document.getElementById('selTamsoft');
    const $selG = document.getElementById('selTrendyol');
    let selectedTamsoft = null; let selectedTrendyol = null;
    let dtT = null, dtG = null;

    // DataTables init (server-side)
    dtT = window.jQuery('#tbTamsoft').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: true,
        pageLength: 50,
        lengthMenu: [[50,100,200,500],[50,100,200,500]],
        ajax: {
            url: '/admin/match/tamsoft/list',
            data: function(d){
                d.q = (document.getElementById('qTamsoft')?.value||'').trim();
                d.platform = (document.getElementById('platformSelect')?.value||'trendyolgo');
                d.unmatched = (document.getElementById('onlyUnmatched')?.checked ? 1 : 0);
            }
        },
        columns: [
            { data: 'ext_urun_id', render: function(d){ return `<code>${d||''}</code>`; } },
            { data: 'barkod' },
            { data: 'urun_adi' },
            { data: null, orderable:false, render: function(){ return '<button class="btn btn-sm btn-outline-secondary js-select-t">Seç (1)</button>'; } }
        ],
        drawCallback: function(){
            try { const info = this.api().page.info(); document.getElementById('tamsoftCount').textContent = `Yüklenen: ${info.end-info.start} / Toplam: ${info.recordsDisplay}`; } catch(e){}
        }
    });

    dtG = window.jQuery('#tbTrendyol').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: true,
        pageLength: 50,
        lengthMenu: [[50,100,200,500],[50,100,200,500]],
        ajax: {
            url: '/admin/match/trendyol/list',
            data: function(d){
                d.q = (document.getElementById('qTrendyol')?.value||'').trim();
                d.platform = (document.getElementById('platformSelect')?.value||'trendyolgo');
                d.unmatched = (document.getElementById('onlyUnmatched')?.checked ? 1 : 0);
                d.store_id = (document.getElementById('storeSelect')?.value||'').trim();
            }
        },
        columns: [
            { data: null, render: function(d,t,row){ return `<code>${row.sku||row.code||''}</code>`; } },
            { data: 'barcode' },
            { data: null, render: function(d,t,row){ return row.title||row.name||''; } },
            { data: 'brand' },
            { data: 'imageUrl', orderable:false, render: function(d){ return d?`<img src="${d}" style="width:40px;height:40px;object-fit:cover;border-radius:4px;"/>`:''; } },
            { data: null, orderable:false, render: function(){ return '<button class="btn btn-sm btn-outline-secondary js-select-g">Seç (2)</button>'; } }
        ],
        drawCallback: function(){
            try { const info = this.api().page.info(); document.getElementById('trendyolCount').textContent = `Yüklenen: ${info.end-info.start} / Toplam: ${info.recordsDisplay}`; } catch(e){}
        }
    });

    // Row selection handlers (delegated)
    window.jQuery('#tbTamsoft tbody').on('click', 'button.js-select-t', async function(){
        const data = dtT.row(window.jQuery(this).closest('tr')).data();
        selectedTamsoft = data; updateSummary(); updateMatchBtn();
        // Eşleşmemiş kontrol ve önizleme: barkod + kod (SKU/stock_code)
        try {
            const onlyUnmatched = document.getElementById('onlyUnmatched').checked;
            const sid = (document.getElementById('storeSelect')?.value||'').trim();
            if (!sid) return; // mağaza seçilmemişse önizleme açmayalım
            const qs = new URLSearchParams({ store_id: sid, ext_urun_id: (selectedTamsoft.ext_urun_id||'') });
            const r = await fetch(`/admin/match/trendyol/preview-single?${qs.toString()}`, { headers: { 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-Token': CSRF }});
            const d = await r.json();
            if (!d.success) return;
            // Eğer sadece eşleşmemişleri göster modunda isek, zaten eşleşmemiş kabul; modal göster
            const items = d.items||[];
            if (items.length === 0) { try{ showToast('Mağazada barkod/koda göre aday bulunamadı','warning'); }catch(e){} return; }
            const wrap = document.createElement('div');
            wrap.innerHTML = `
              <div class="modal fade" id="previewModal" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                  <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">Adaylar (Barkod/Kod)</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                      <div class="mb-2 small text-muted">Tamsoft: <code>${(d.tamsoft?.ext_urun_id)||''}</code> - ${(d.tamsoft?.urun_adi)||''} (Barkod: ${(d.tamsoft?.barkod)||''})</div>
                      <div class="table-responsive"><table class="table table-striped table-sm mb-0"><thead><tr><th>SKU/Kod</th><th>Barkod</th><th>Başlık</th><th>Skor</th><th></th></tr></thead><tbody id="pvBody"></tbody></table></div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Kapat</button></div>
                  </div>
                </div>
              </div>`;
            document.body.appendChild(wrap);
            const tb = wrap.querySelector('#pvBody');
            items.forEach(it=>{
                const tr = document.createElement('tr');
                tr.innerHTML = `<td><code>${it.candidate_code||''}</code></td><td>${it.candidate_barcode||''}</td><td>${it.candidate_title||''}</td><td>${(it.score??0).toFixed(2)}</td><td><button class="btn btn-sm btn-primary">Seç</button></td>`;
                tr.querySelector('button').addEventListener('click', ()=>{
                    // Trendyol seçim alanına yansıt
                    selectedTrendyol = { sku: it.candidate_code||'', code: it.candidate_code||'', barcode: it.candidate_barcode||'', title: it.candidate_title||'' };
                    updateSummary(); updateMatchBtn();
                    try { bootstrap.Modal.getInstance(wrap.querySelector('#previewModal')).hide(); } catch(e){}
                    wrap.remove();
                });
                tb.appendChild(tr);
            });
            const modal = new bootstrap.Modal(wrap.querySelector('#previewModal'));
            modal.show();
            wrap.querySelector('#previewModal').addEventListener('hidden.bs.modal', ()=>{ wrap.remove(); });
        } catch (e) {}
    });
    window.jQuery('#tbTrendyol tbody').on('click', 'button.js-select-g', function(){
        const data = dtG.row(window.jQuery(this).closest('tr')).data();
        selectedTrendyol = data; updateSummary(); updateMatchBtn();
    });

    function renderTamsoft(items){
        $tbT.innerHTML='';
        (items||[]).forEach(it=>{
            const tr = document.createElement('tr');
            if (selectedTamsoft && selectedTamsoft.ext_urun_id === it.ext_urun_id) { tr.classList.add('table-active'); }
            tr.innerHTML = `<td><code>${it.ext_urun_id||''}</code></td><td>${it.barkod||''}</td><td>${it.urun_adi||''}</td><td><button class=\"btn btn-sm ${'${'}selectedTamsoft&&selectedTamsoft.ext_urun_id===it.ext_urun_id?'btn-secondary':'btn-outline-secondary'${'}'}\">Seç (1)</button></td>`;
            tr.querySelector('button').addEventListener('click', ()=>{ selectedTamsoft = it; updateSummary(); updateMatchBtn(); renderTamsoft(items); });
            $tbT.appendChild(tr);
        });
    }
    function renderTrendyol(items){
        $tbG.innerHTML='';
        (items||[]).forEach(it=>{
            const tr = document.createElement('tr');
            const img = it.imageUrl ? `<img src="${it.imageUrl}" alt="img" style="width:40px;height:40px;object-fit:cover;border-radius:4px;"/>` : '';
            if (selectedTrendyol && ((selectedTrendyol.sku||selectedTrendyol.code)===(it.sku||it.code))) { tr.classList.add('table-active'); }
            tr.innerHTML = `<td><code>${it.sku||it.code||''}</code></td><td>${it.barcode||''}</td><td>${it.title||it.name||''}</td><td>${it.brand||''}</td><td>${img}</td><td><button class=\"btn btn-sm ${'${'}selectedTrendyol&&((selectedTrendyol.sku||selectedTrendyol.code)===(it.sku||it.code))?'btn-secondary':'btn-outline-secondary'${'}'}\">Seç (2)</button></td>`;
            tr.querySelector('button').addEventListener('click', ()=>{ selectedTrendyol = it; updateSummary(); updateMatchBtn(); renderTrendyol(items); });
            $tbG.appendChild(tr);
        });
    }
    function updateMatchBtn(){
        document.getElementById('btnMatch').disabled = !(selectedTamsoft && selectedTrendyol);
    }
    function updateSummary(){
        const any = !!(selectedTamsoft || selectedTrendyol);
        $sum.classList.toggle('d-none', !any);
        if (selectedTamsoft) {
            $selT.classList.remove('d-none');
            $selT.textContent = `1) ${selectedTamsoft.ext_urun_id || ''} - ${selectedTamsoft.urun_adi || ''}`;
        } else { $selT.classList.add('d-none'); $selT.textContent=''; }
        if (selectedTrendyol) {
            $selG.classList.remove('d-none');
            $selG.textContent = `2) ${(selectedTrendyol.sku||selectedTrendyol.code||'')} - ${(selectedTrendyol.title||selectedTrendyol.name||'')}`;
        } else { $selG.classList.add('d-none'); $selG.textContent=''; }
    }
    async function loadTamsoft(){
        const q = document.getElementById('qTamsoft').value.trim();
        const pf = document.getElementById('platformSelect').value || 'trendyolgo';
        const onlyUnmatched = document.getElementById('onlyUnmatched').checked ? 1 : 0;
        try { if (window.Swal) { Swal.fire({title:'Arama', text:'Tamsoft listesinde aranıyor...', timer:1200, showConfirmButton:false}); } } catch(e){}
        const r = await fetch(`/admin/match/tamsoft/list?q=${encodeURIComponent(q)}&platform=${encodeURIComponent(pf)}&unmatched=${onlyUnmatched}&page=1&per=50`, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': CSRF }});
        const d = await r.json();
        const items = d.items || (d.data||[]);
        renderTamsoft(items);
        try { document.getElementById('tamsoftCount').textContent = `Yüklenen kayıt: ${items.length}`; } catch(e){}
        if (!items || items.length === 0) {
            try { if (window.Swal) { Swal.fire({ icon:'info', title:'Tamsoft', text:'Aranan sonuç bulunamadı', timer:1500, showConfirmButton:false }); } } catch(e){}
        }
    }
    async function loadTrendyol(){
        const sid = document.getElementById('storeSelect').value.trim();
        const pf = document.getElementById('platformSelect').value || 'trendyolgo';
        const onlyUnmatched = document.getElementById('onlyUnmatched').checked ? 1 : 0;
        if (!sid) { renderTrendyol([]); return; }
        try { if (window.Swal) { Swal.fire({title:'Arama', text:'Trendyol listesinde aranıyor...', timer:1200, showConfirmButton:false}); } } catch(e){}
        const r = await fetch(`/admin/match/trendyol/list?store_id=${encodeURIComponent(sid)}&platform=${encodeURIComponent(pf)}&unmatched=${onlyUnmatched}&q=${encodeURIComponent(document.getElementById('qTrendyol').value.trim())}&page=1&per=50`, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': CSRF }});
        const d = await r.json();
        const items = d.items || (d.data||[]);
        renderTrendyol(items);
        try { document.getElementById('trendyolCount').textContent = `Yüklenen kayıt: ${items.length}`; } catch(e){}
        if (!items || items.length === 0) {
            try { if (window.Swal) { Swal.fire({ icon:'info', title:'Trendyol', text:'Aranan sonuç bulunamadı', timer:1500, showConfirmButton:false }); } } catch(e){}
        }
    }
    document.getElementById('btnSearchTamsoft').addEventListener('click', ()=>{ const sp = document.getElementById('spSearchT'); sp.classList.remove('d-none'); dtT.ajax.reload(()=>{ sp.classList.add('d-none'); }, false); });
    document.getElementById('btnSearchTrendyol').addEventListener('click', ()=>{ const sp = document.getElementById('spSearchG'); sp.classList.remove('d-none'); dtG.ajax.reload(()=>{ sp.classList.add('d-none'); }, false); });
    document.getElementById('storeSelect').addEventListener('change', ()=>{ dtG.ajax.reload(null, true); });
    document.getElementById('onlyUnmatched').addEventListener('change', ()=>{ dtT.ajax.reload(null,true); dtG.ajax.reload(null,true); });
    document.getElementById('qTamsoft').addEventListener('keydown', (e)=>{ if (e.key==='Enter') dtT.ajax.reload(null,true); });
    document.getElementById('qTrendyol').addEventListener('keydown', (e)=>{ if (e.key==='Enter') dtG.ajax.reload(null,true); });
    document.getElementById('btnAnalyze').addEventListener('click', async ()=>{
        const btn = document.getElementById('btnAnalyze');
        const sp = document.getElementById('spAnalyze');
        const sid = document.getElementById('storeSelect').value.trim();
        if (!sid) { try{ showToast('Mağaza seçiniz','warning'); }catch(e){ alert('Mağaza seçiniz'); } return; }
        const pf = document.getElementById('platformSelect').value || 'trendyolgo';
        const exactFirst = true;
        const qs = new URLSearchParams({ platform: pf, store_id: sid, min: '0.70', thr: '0.82', mode: exactFirst ? 'exact' : 'mixed' });
        // Bekleyiniz uyarısı + spinner
        btn.disabled = true; sp.classList.remove('d-none');
        let swalOpen = false;
        try { if (window.Swal) { window.Swal.fire({ title:'Analiz ediliyor', text:'Lütfen bekleyiniz...', allowOutsideClick:false, allowEscapeKey:false, didOpen: () => { window.Swal.showLoading(); } }); swalOpen = true; } } catch(e) {}
        const r = await fetch(`/admin/match/trendyol/analyze?${qs.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': CSRF }});
        const d = await r.json();
        if (swalOpen) { try { window.Swal.close(); } catch(e){} }
        btn.disabled = false; sp.classList.add('d-none');
        if (!d.success) { try{ showToast('Analiz başarısız','danger'); }catch(e){} return; }
        // Modal oluştur
        const wrap = document.createElement('div');
        wrap.innerHTML = `
            <div class="modal fade" id="analyzeModal" tabindex="-1">
              <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                  <div class="modal-header"><h5 class="modal-title">Otomatik Eşleştirme Önizleme</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                  <div class="modal-body">
                    <div class="table-responsive">
                      <table class="table table-sm align-middle">
                        <thead><tr><th>Tamsoft Kod</th><th>Tamsoft Barkod</th><th>Tamsoft Ürün</th><th>Trendyol Kod</th><th>Trendyol Barkod</th><th>Trendyol Başlık</th><th>Skor</th></tr></thead>
                        <tbody id="analyzeTbody"></tbody>
                      </table>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
                    <button type="button" class="btn btn-primary" id="btnApproveBatch">Toplu Kaydet</button>
                  </div>
                </div>
              </div>
            </div>`;
        document.body.appendChild(wrap);
        const tbody = wrap.querySelector('#analyzeTbody');
        (d.items||[]).forEach((it,idx)=>{
            const tr = document.createElement('tr');
            const cb = `<input type=\"checkbox\" class=\"form-check-input js-acb\" data-ext=\"${it.ext_urun_id||''}\" data-code=\"${it.candidate_code||''}\" data-bar=\"${it.candidate_barcode||''}\" data-score=\"${it.score||''}\" checked/>`;
            tr.innerHTML = `<td><code>${it.ext_urun_id||''}</code></td><td>${it.barkod||''}</td><td>${it.urun_adi||''}</td><td><code>${it.candidate_code||''}</code></td><td>${it.candidate_barcode||''}</td><td>${it.candidate_title||''}</td><td>${(it.score??0).toFixed(2)} ${cb}</td>`;
            tbody.appendChild(tr);
        });
        const modalEl = wrap.querySelector('#analyzeModal');
        modalEl.classList.add('modal-fullscreen');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        wrap.querySelector('#btnApproveBatch').addEventListener('click', async ()=>{
            const chosen = Array.from(wrap.querySelectorAll('.js-acb')).filter(x=>x.checked).map(x=>({
                ext_urun_id: x.getAttribute('data-ext')||'',
                candidate_code: x.getAttribute('data-code')||'',
                candidate_barcode: x.getAttribute('data-bar')||'',
                score: parseFloat(x.getAttribute('data-score')||'')||null
            }));
            const payload = { platform: 'trendyolgo', items: chosen };
            const res = await fetch('/admin/match/approve-batch', { method: 'POST', headers: { 'Content-Type':'application/json', 'X-CSRF-Token': CSRF, 'X-Requested-With':'XMLHttpRequest' }, body: JSON.stringify(payload) });
            const dd = await res.json();
            if (dd.success) { try{ showToast(`Kaydedildi: ${dd.ok}, Hata: ${dd.fail}`,'success'); }catch(e){} modal.hide(); wrap.remove(); }
            else { try{ showToast('Toplu kayıt başarısız','danger'); }catch(e){} }
        });
    });
    document.getElementById('btnMatch').addEventListener('click', async ()=>{
        if (!(selectedTamsoft && selectedTrendyol)) return;
        const fd = new FormData();
        fd.append('ext_urun_id', selectedTamsoft.ext_urun_id||'');
        fd.append('candidate_code', selectedTrendyol.sku||selectedTrendyol.code||'');
        fd.append('candidate_barcode', selectedTrendyol.barcode||'');
        fd.append('platform', 'trendyolgo');
        fd.append('_csrf', CSRF);
        const btn = document.getElementById('btnMatch'); btn.disabled = true; const old = btn.innerText; btn.innerText = 'Kaydediliyor...';
        try {
            const r = await fetch('/admin/match/approve', { method:'POST', body: fd, headers:{ 'X-CSRF-Token': CSRF }});
            const d = await r.json();
            if (d.success) { try{ showToast('Eşleştirme kaydedildi','success'); }catch(e){} }
            else { try{ showToast('Hata: '+(d.error||''),'danger'); }catch(e){} }
        } catch (e) {}
        finally { btn.disabled=false; btn.innerText = old; }
    });
    loadTamsoft();
})();
</script>


