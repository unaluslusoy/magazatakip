<?php require_once __DIR__ . '/../../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>
<div class="container-fluid py-5">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Mağaza - Depo Eşleştirme</h3>
            <div class="d-flex gap-2">
                <select id="platform" class="form-select form-select-sm" style="width:200px">
                    <option value="trendyolgo" selected>Trendyol GO</option>
                </select>
                <button id="btnReload" class="btn btn-sm btn-light">Yenile</button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle" id="tbMap">
                    <thead>
                    <tr>
                        <th>Platform</th>
                        <th>Store ID</th>
                        <th>Depo</th>
                        <th>Aktif</th>
                        <th>İşlem</th>
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
    const $tb = document.querySelector('#tbMap tbody');
    let depots = [];
    let stores = [];

    function depotOptions(selected){
        return depots.map(d=>`<option value="${d.id}" ${String(selected)===String(d.id)?'selected':''}>${d.id} - ${d.depo_adi||'-'}</option>`).join('');
    }

    async function load(){
        const platform = document.getElementById('platform').value;
        const r = await fetch(`/admin/match/stores/list?platform=${platform}`);
        const d = await r.json();
        depots = d.depots||[];
        stores = d.stores||[];
        $tb.innerHTML='';
        (d.rows||[]).forEach(row=>{
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><code>${row.platform}</code></td>
                <td>
                    <select class="form-select form-select-sm store">
                        ${stores.map(s=>`<option value="${s.store_id}" ${String(s.store_id)===String(row.store_id)?'selected':''}>${(s.magaza_adi||'-')} (#${s.store_id})</option>`).join('')}
                    </select>
                </td>
                <td>
                    <select class="form-select form-select-sm depo">${depotOptions(row.depo_id)}</select>
                </td>
                <td><input type="checkbox" class="form-check-input enabled" ${row.enabled? 'checked':''}/></td>
                <td><button class="btn btn-sm btn-primary js-save">Kaydet</button></td>
            `;
            tr.querySelector('.js-save').addEventListener('click', async ()=>{
                const storeId = tr.querySelector('.store').value.trim();
                const depoId = tr.querySelector('.depo').value;
                const enabled = tr.querySelector('.enabled').checked ? 1 : 0;
                const fd = new FormData(); fd.append('platform', platform); fd.append('store_id', storeId); fd.append('depo_id', depoId); fd.append('enabled', enabled); fd.append('_csrf', CSRF);
                const btn = tr.querySelector('.js-save'); btn.disabled = true; const old=btn.innerText; btn.innerText='Kaydediliyor...';
                try{ const rr = await fetch('/admin/match/stores/save', { method:'POST', body: fd, headers: { 'X-CSRF-Token': CSRF } }); const dd = await rr.json(); if (dd.success) { try{ showToast('Kaydedildi','success'); }catch(e){} } else { try{ showToast('Hata: '+(dd.error||''),'danger'); }catch(e){} } }
                catch(e){}
                finally{ btn.disabled=false; btn.innerText=old; }
            });
            $tb.appendChild(tr);
        });
        // boş satır
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><code>${platform}</code></td>
            <td>
                <select class="form-select form-select-sm store">
                    <option value="">Mağaza seçiniz</option>
                    ${stores.map(s=>`<option value="${s.store_id}">${(s.magaza_adi||'-')} (#${s.store_id})</option>`).join('')}
                </select>
            </td>
            <td><select class="form-select form-select-sm depo">${depotOptions('')}</select></td>
            <td><input type="checkbox" class="form-check-input enabled" checked/></td>
            <td><button class="btn btn-sm btn-success js-add">Ekle</button></td>
        `;
        tr.querySelector('.js-add').addEventListener('click', async ()=>{
            const storeId = tr.querySelector('.store').value.trim();
            const depoId = tr.querySelector('.depo').value;
            const enabled = tr.querySelector('.enabled').checked ? 1 : 0;
            if (!storeId){ try{ showToast('Store ID gerekli','danger'); }catch(e){} return; }
            const fd = new FormData(); fd.append('platform', platform); fd.append('store_id', storeId); fd.append('depo_id', depoId); fd.append('enabled', enabled); fd.append('_csrf', CSRF);
            const btn = tr.querySelector('.js-add'); btn.disabled = true; const old=btn.innerText; btn.innerText='Ekleniyor...';
            try{ const rr = await fetch('/admin/match/stores/save', { method:'POST', body: fd, headers: { 'X-CSRF-Token': CSRF } }); const dd = await rr.json(); if (dd.success) { load(); try{ showToast('Eklendi','success'); }catch(e){} } else { try{ showToast('Hata: '+(dd.error||''),'danger'); }catch(e){} } }
            catch(e){}
            finally{ btn.disabled=false; btn.innerText=old; }
        });
        $tb.appendChild(tr);
    }

    document.getElementById('btnReload').addEventListener('click', load);
    document.getElementById('platform').addEventListener('change', load);
    load();
})();
</script>


