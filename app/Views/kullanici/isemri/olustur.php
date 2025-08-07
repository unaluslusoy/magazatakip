<?php require_once __DIR__ . '/../layouts/layout/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/layout/navbar.php'; ?>

<div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="row g-7">
                <div class="col-xl-12">
                    <div class="card card-flush h-lg-100" id="kt_contacts_main">
                        <div class="card-header pt-7" id="kt_chat_contacts_header">
                            <div class="card-title">
                                <i class="ki-outline ki-badge fs-1 me-2"></i>
                                <h2>Yeni İş Emri Oluştur</h2>
                            </div>
                        </div>
                        <div class="card-body pt-5">
                            <?php if(isset($hata)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Hata!</strong> <?= htmlspecialchars($hata) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
                            </div>
                            <?php endif; ?>
                            <form class="form fv-plugins-bootstrap5 fv-plugins-framework" method="post" action="/isemri/olustur" enctype="multipart/form-data">
                                <div class="fv-row mb-7 fv-plugins-icon-container">
                                    <label class="fs-6 fw-semibold form-label mt-3">
                                        <span class="required">Başlık</span>
                                    </label>
                                    <input type="text" name="baslik" id="baslik" class="form-control form-control-solid" required>
                                </div>

                                <div class="fv-row mb-7">
                                    <label for="aciklama" class="form-label fs-6 fw-semibold form-label mt-3">
                                        <span class="required">Detay Mesaj</span>
                                    </label>
                                    <textarea name="aciklama" id="aciklama" class="form-control form-control-solid" required></textarea>
                                </div>

                                <div class="fv-row mb-7">
                                    <label class="form-label fs-6 fw-semibold">Dosya Ekle</label>
                                    <div class="card card-bordered">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="nav nav-tabs nav-line-tabs mb-5">
                                                        <li class="nav-item">
                                                            <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_dosya_yukle">
                                                                <span class="svg-icon svg-icon-3 me-2">
                                                                    <i class="ki-outline ki-folder-up fs-2"></i>
                                                                </span>
                                                                Dosya Yükle
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_kamera_cek">
                                                                <span class="svg-icon svg-icon-3 me-2">
                                                                    <i class="ki-outline ki-camera fs-2"></i>
                                                                </span>
                                                                Kamera ile Çek
                                                            </a>
                                                        </li>
                                                    </div>
                                                    <div class="tab-content" id="dosya_ekleme_tab">
                                                        <div class="tab-pane fade show active" id="kt_tab_dosya_yukle">
                                                            <div class="mb-3">
                                                                <label for="dosya_yukle" class="form-label">Dosya Seç</label>
                                                                <input type="file" name="dosyalar[]" id="dosya_yukle" class="form-control" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane fade" id="kt_tab_kamera_cek">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="mb-3 text-center">
                                                                        <video id="kamera_onizleme" style="max-width: 100%; display: none;" playsinline></video>
                                                                        <canvas id="kamera_canvas" style="display: none;"></canvas>
                                                                        <div class="mt-2">
                                                                            <button type="button" id="kamera_ac_btn" class="btn btn-primary btn-sm">
                                                                                <i class="ki-outline ki-camera fs-2 me-2"></i>Kamerayı Aç
                                                                            </button>
                                                                            <button type="button" id="foto_cek_btn" class="btn btn-success btn-sm" style="display: none;">
                                                                                <i class="ki-outline ki-camera-add fs-2 me-2"></i>Fotoğraf Çek
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div id="kamera_aciklama" class="alert alert-info" style="display: none;">
                                                                        <p>Kamera açıldığında, fotoğrafı çekmek için "Fotoğraf Çek" butonuna basın.</p>
                                                                        <ul>
                                                                            <li>Kamerayı düz tutun</li>
                                                                            <li>İyi aydınlatılmış ortamda çekin</li>
                                                                            <li>Gerekirse yakınlaştırma yapın</li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div id="yuklenen_dosyalar" class="mt-3 row"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="fv-row mb-7">
                                    <label for="kategori" class="fs-6 fw-semibold form-label mt-3">Kategori</label>
                                    <select name="kategori" id="kategori" class="form-select form-control form-control-solid">
                                        <option value="">Kategori Seçiniz</option>
                                        <option value="Elektrik">Elektrik</option>
                                        <option value="Su Tesisatı">Su Tesisatı</option>
                                        <option value="Klima">Klima</option>
                                        <option value="Bilgisayar">Bilgisayar</option>
                                        <option value="Temizlik">Temizlik</option>
                                        <option value="Güvenlik">Güvenlik</option>
                                        <option value="Diğer">Diğer</option>
                                    </select>
                                </div>

                                <div class="row row-cols-1 row-cols-sm-2 rol-cols-md-1 row-cols-lg-2">
                                    <div class="col">
                                        <div class="fv-row mb-7 fv-plugins-icon-container">
                                            <label for="magaza_id" class="fs-6 fw-semibold form-label mt-3 required">Mağaza Listesi</label>
                                            <select name="magaza_id" id="magaza_id" class="form-select form-control form-control-solid" required onchange="setMagazaAd()">
                                                <option value="">Seçim Yapınız</option>
                                                <?php foreach ($magazalar as $magaza): ?>
                                                    <option value="<?= $magaza['id'] ?>" 
                                                        <?= isset($kullanici_magaza_id) && $magaza['id'] == $kullanici_magaza_id ? 'selected' : '' ?> 
                                                        data-ad="<?= htmlspecialchars($magaza['ad'], ENT_QUOTES, 'UTF-8') ?>">
                                                        <?= htmlspecialchars($magaza['ad'], ENT_QUOTES, 'UTF-8') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="magaza_ad" id="magaza_ad">

                                            <script>
                                                // Sayfa yüklendiğinde mağaza adını otomatik olarak ayarla
                                                document.addEventListener('DOMContentLoaded', function() {
                                                    setMagazaAd();
                                                });
                                            </script>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="fv-row mb-7 fv-plugins-icon-container">
                                            <label for="derece" class="fs-6 fw-semibold form-label mt-3">İstek Derecesi:</label>
                                            <select name="derece" id="derece" class="form-select form-control form-control-solid" required>
                                                <option value="ACİL">ACİL</option>
                                                <option value="KRİTİK">KRİTİK</option>
                                                <option value="YÜKSEK">YÜKSEK</option>
                                                <option value="ORTA">ORTA</option>
                                                <option value="DÜŞÜK">DÜŞÜK</option>
                                                <option value="İNCELENİYOR">İNCELENİYOR</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <a href="/isemri/listesi" type="reset" class="btn btn-light me-3">Kapat</a>
                                    <button type="submit" class="btn btn-primary">
                                        <span class="indicator-label">Gönder</span>
                                        <span class="indicator-progress">Lütfen bekleyiniz...
                                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Kamera ve dosya yükleme için gelişmiş JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        const kameraAcBtn = document.getElementById('kamera_ac_btn');
        const fotoCekBtn = document.getElementById('foto_cek_btn');
        const kameraOnizleme = document.getElementById('kamera_onizleme');
        const kameraCanvas = document.getElementById('kamera_canvas');
        const dosyaYukle = document.getElementById('dosya_yukle');
        const yuklenenDosyalar = document.getElementById('yuklenen_dosyalar');
        const kameraAciklama = document.getElementById('kamera_aciklama');

        // Mağaza adını ayarlama fonksiyonu
        function setMagazaAd() {
            const magazaSelect = document.getElementById('magaza_id');
            const magazaAdInput = document.getElementById('magaza_ad');
            if (magazaSelect && magazaAdInput) {
                const selectedOption = magazaSelect.options[magazaSelect.selectedIndex];
                magazaAdInput.value = selectedOption.getAttribute('data-ad');
            }
        }

        // Kamera açma
        kameraAcBtn.addEventListener('click', async function() {
            try {
                const constraints = {
                    video: { 
                        facingMode: 'environment', // Arka kamerayı tercih et
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }
                };

                const stream = await navigator.mediaDevices.getUserMedia(constraints);
                
                if (!stream.getVideoTracks().length) {
                    throw new Error('Kamera bulunamadı');
                }

                kameraOnizleme.srcObject = stream;
                kameraOnizleme.play(); // Videoyu oynatmayı açıkça çağır
                
                // Kamera yüklenene kadar bekleme
                kameraOnizleme.onloadedmetadata = () => {
                    kameraOnizleme.style.display = 'block';
                    kameraAcBtn.style.display = 'none';
                    fotoCekBtn.style.display = 'inline-block';
                    kameraAciklama.style.display = 'block'; // Kamera açıklamayı göster
                };
            } catch (err) {
                console.error("Kamera hatası: ", err);
                
                // Detaylı hata mesajları
                if (err.name === 'NotAllowedError') {
                    alert('Kamera erişimi reddedildi. Lütfen tarayıcı ayarlarından izin verin.');
                } else if (err.name === 'NotFoundError') {
                    alert('Cihazınızda kamera bulunamadı.');
                } else {
                    alert('Kamera açılırken bir hata oluştu: ' + err.message);
                }
            }
        });

        // Fotoğraf çekme
        fotoCekBtn.addEventListener('click', function() {
            kameraCanvas.width = kameraOnizleme.videoWidth;
            kameraCanvas.height = kameraOnizleme.videoHeight;
            kameraCanvas.getContext('2d').drawImage(kameraOnizleme, 0, 0);
            
            // Canvas'ı blob'a çevirme
            kameraCanvas.toBlob(function(blob) {
                // Blob'dan dosya oluşturma
                const dosya = new File([blob], 'kamera_foto_' + new Date().getTime() + '.jpg', { type: 'image/jpeg' });
                
                // Dosyayı listeye ekleme
                const dosyaEleman = document.createElement('div');
                dosyaEleman.className = 'col-md-3 mb-3';
                dosyaEleman.innerHTML = `
                    <div class="card">
                        <img src="${URL.createObjectURL(blob)}" class="card-img-top" alt="Çekilen Fotoğraf">
                        <div class="card-body p-2">
                            <small class="text-muted">${dosya.name}</small>
                        </div>
                    </div>
                `;
                yuklenenDosyalar.appendChild(dosyaEleman);

                // Dosyayı form elemanına ekle
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(dosya);
                dosyaYukle.files = dataTransfer.files;

                // Kamera akışını durdur
                const tracks = kameraOnizleme.srcObject.getTracks();
                tracks.forEach(track => track.stop());
                kameraOnizleme.srcObject = null;
                kameraOnizleme.style.display = 'none';
                fotoCekBtn.style.display = 'none';
                kameraAcBtn.style.display = 'inline-block';
                kameraAciklama.style.display = 'none';
            }, 'image/jpeg');
        });

        // Dosya yükleme
        dosyaYukle.addEventListener('change', function(e) {
            yuklenenDosyalar.innerHTML = ''; // Önceki dosyaları temizle
            
            Array.from(this.files).forEach(dosya => {
                const dosyaEleman = document.createElement('div');
                dosyaEleman.className = 'col-md-3 mb-3';
                
                // Resim ise önizleme, değilse dosya ikonu
                const onizleme = dosya.type.startsWith('image/') 
                    ? `<img src="${URL.createObjectURL(dosya)}" class="card-img-top" alt="${dosya.name}">`
                    : `<div class="text-center p-3"><i class="ki-outline ki-document fs-2x text-muted"></i></div>`;
                
                dosyaEleman.innerHTML = `
                    <div class="card">
                        ${onizleme}
                        <div class="card-body p-2">
                            <small class="text-muted">${dosya.name}</small>
                        </div>
                    </div>
                `;
                
                yuklenenDosyalar.appendChild(dosyaEleman);
            });
        });
    });
</script>

<?php require_once __DIR__ . '/../layouts/layout/footer.php'; ?>
