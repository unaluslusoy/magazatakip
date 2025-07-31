<?php
require_once 'app/Views/kullanici/layout/header.php';
require_once 'app/Views/kullanici/layout/navbar.php';
?>

    <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
        <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
            <?php if (isset($_SESSION['message']) && isset($_SESSION['message_type'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                    <?php
                    echo $_SESSION['message'];
                    unset($_SESSION['message'], $_SESSION['message_type']);
                    ?>
                </div>
            <?php endif; ?>

            <div id="kt_app_content_container" class="app-container container-fluid">
                <div class="card mb-5 mb-xl-10">
                    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_signin_method">
                        <div class="card-title m-0">
                            <h3 class="fw-bold m-0">Yeni Mağaza Cirosu Ekleme Formu</h3>
                        </div>
                    </div>

                    <div class="card-body border-top p-9">
                        <form class="form" method="post" action="/ciro/ekle">
                            <div class="d-flex flex-column mb-5 fv-row fv-plugins-icon-container">
                                <div class="col-md-12 fv-row fv-plugins-icon-container">
                                    <label for="magaza_id" class="d-flex align-items-center fs-5 fw-semibold mb-2">
                                        <span class="required">Mağaza</span>
                                    </label>
                                    <select name="magaza_id" id="magaza_id" class="form-select form-select-solid" onchange="setMagazaAd()" required>
                                        <option value="">Seçim Yapınız</option>
                                        <?php foreach ($magazalar as $magaza): ?>
                                            <option value="<?= $magaza['id'] ?>" data-ad="<?= htmlspecialchars($magaza['ad']) ?>"><?= htmlspecialchars($magaza['ad']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="magaza_ad" id="magaza_ad">
                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="col-md-4 fv-row fv-plugins-icon-container">
                                <label for="ekleme_tarihi" class="required fs-5 fw-semibold mb-2">Ekleme Tarihi:</label>
                                <input type="text" name="ekleme_tarihi" id="ekleme_tarihi" class="form-control form-control-solid" disabled>
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                            </div>

                            <div class="row mb-5">
                                <div class="col-md-2 fv-row fv-plugins-icon-container">
                                    <label for="gun" class="required fs-5 fw-semibold mb-2">Gün</label>
                                    <input type="date" name="gun" id="gun" class="form-control form-control-solid" required>
                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                                </div>
                                <div class="col-md-2 fv-row fv-plugins-icon-container">
                                    <label for="nakit" class="required fs-5 fw-semibold mb-2">Nakit</label>
                                    <input type="text" name="nakit" id="nakit" class="form-control form-control-solid" value="0,00 TL" oninput="calculateTotal()" required>
                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                                </div>
                                <div class="col-md-2 fv-row fv-plugins-icon-container">
                                    <label for="kredi_karti" class="required fs-5 fw-semibold mb-2">Kredi Kartı</label>
                                    <input type="text" name="kredi_karti" id="kredi_karti" class="form-control form-control-solid" value="0,00 TL" oninput="calculateTotal()" required>
                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="col-md-2 fv-row fv-plugins-icon-container">
                                    <label for="carliston" class="required fs-5 fw-semibold mb-2">Çarliston</label>
                                    <input type="text" name="carliston" id="carliston" class="form-control form-control-solid" value="0,00 TL" oninput="calculateTotal()">
                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                                </div>
                                <div class="col-md-2 fv-row fv-plugins-icon-container">
                                    <label for="getir_carsi" class="required fs-5 fw-semibold mb-2">Getir Çarşı</label>
                                    <input type="text" name="getir_carsi" id="getir_carsi" class="form-control form-control-solid" value="0,00 TL" oninput="calculateTotal()">
                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                                </div>
                                <div class="col-md-2 fv-row fv-plugins-icon-container">
                                    <label for="trendyolgo" class="required fs-5 fw-semibold mb-2">TrendyolGO</label>
                                    <input type="text" name="trendyolgo" id="trendyolgo" class="form-control form-control-solid" value="0,00 TL" oninput="calculateTotal()">
                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="col-md-2 fv-row fv-plugins-icon-container">
                                    <label for="multinet" class="required fs-5 fw-semibold mb-2">MultiNet</label>
                                    <input type="text" name="multinet" id="multinet" class="form-control form-control-solid" value="0,00 TL" oninput="calculateTotal()">
                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                                </div>
                                <div class="col-md-2 fv-row fv-plugins-icon-container">
                                    <label for="sodexo" class="required fs-5 fw-semibold mb-2">Sodexo</label>
                                    <input type="text" name="sodexo" id="sodexo" class="form-control form-control-solid" value="0,00 TL" oninput="calculateTotal()">
                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                                </div>
                                <div class="col-md-2 fv-row fv-plugins-icon-container">
                                    <label for="edenred" class="required fs-5 fw-semibold mb-2">Edenred</label>
                                    <input type="text" name="edenred" id="edenred" class="form-control form-control-solid" value="0,00 TL" oninput="calculateTotal()">
                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                                </div>
                                <div class="col-md-2 fv-row fv-plugins-icon-container">
                                    <label for="setcard" class="required fs-5 fw-semibold mb-2">Setcard</label>
                                    <input type="text" name="setcard" id="setcard" class="form-control form-control-solid" value="0,00 TL" oninput="calculateTotal()">
                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                                </div>
                                <div class="col-md-2 fv-row fv-plugins-icon-container">
                                    <label for="tokenflex" class="required fs-5 fw-semibold mb-2">Token Flex</label>
                                    <input type="text" name="tokenflex" id="tokenflex" class="form-control form-control-solid" value="0,00 TL" oninput="calculateTotal()">
                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                                </div>
                                <div class="col-md-2 fv-row fv-plugins-icon-container">
                                    <label for="iwallet" class="required fs-5 fw-semibold mb-2">iWallet</label>
                                    <input type="text" name="iwallet" id="iwallet" class="form-control form-control-solid" value="0,00 TL" oninput="calculateTotal()">
                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                                </div>
                                <div class="col-md-2 fv-row fv-plugins-icon-container">
                                    <label for="metropol" class="required fs-5 fw-semibold mb-2">Metropol</label>
                                    <input type="text" name="metropol" id="metropol" class="form-control form-control-solid" value="0,00 TL" oninput="calculateTotal()">
                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="col-md-2 fv-row fv-plugins-icon-container">
                                <label for="gider" class=" fs-5 fw-semibold mb-2">Mağaza Masraf/Gider</label>
                                <input type="text" name="gider" id="gider" class="form-control form-control-solid" value="0,00 TL" oninput="calculateTotal()">
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                            </div>
                            <div class="col-md-2 fv-row fv-plugins-icon-container">
                                <label for="toplam" class="fs-5 fw-semibold mb-2">Toplam Ciro</label>
                                <input type="text" name="toplam" id="toplam" class="form-control form-control-solid" readonly>
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                            </div>
                            <div class="d-flex flex-column mb-5 fv-row fv-plugins-icon-container">
                                <label for="aciklama" class="fs-5 fw-semibold mb-2">Mağaza Masraf/Gider açıklamasını </label>
                                <textarea name="aciklama" id="aciklama" class="form-control form-control-solid" placeholder="Bu alana yazınız"></textarea>
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                            </div>

                            <div class="modal-footer flex-lg-end">
                                <a href="/ciro/listele" id="kt_modal_new_address_cancel" class="btn btn-light me-3">Geri</a>
                                <button type="submit" class="btn btn-primary">
                                    <span class="indicator-label">Gönder</span>
                                    <span class="indicator-progress">Lütfen bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function calculateTotal() {
            const nakit         = parseFloat(document.getElementById('nakit').value.replace(/[^0-9,-]+/g, '')) || 0;
            const krediKarti    = parseFloat(document.getElementById('kredi_karti').value.replace(/[^0-9,-]+/g, '')) || 0;
            const getirCarsi    = parseFloat(document.getElementById('getir_carsi').value.replace(/[^0-9,-]+/g, '')) || 0;
            const trendyolgo    = parseFloat(document.getElementById('trendyolgo').value.replace(/[^0-9,-]+/g, '')) || 0;
            const carliston     = parseFloat(document.getElementById('carliston').value.replace(/[^0-9,-]+/g, '')) || 0;
            const tokenflex     = parseFloat(document.getElementById('tokenflex').value.replace(/[^0-9,-]+/g, '')) || 0;
            const setcard       = parseFloat(document.getElementById('setcard').value.replace(/[^0-9,-]+/g, '')) || 0;
            const edenred       = parseFloat(document.getElementById('edenred').value.replace(/[^0-9,-]+/g, '')) || 0;
            const sodexo        = parseFloat(document.getElementById('sodexo').value.replace(/[^0-9,-]+/g, '')) || 0;
            const multinet      = parseFloat(document.getElementById('multinet').value.replace(/[^0-9,-]+/g, '')) || 0;
            const iwallet       = parseFloat(document.getElementById('iwallet').value.replace(/[^0-9,-]+/g, '')) || 0;
            const metropol      = parseFloat(document.getElementById('metropol').value.replace(/[^0-9,-]+/g, '')) || 0;
            const gider         = parseFloat(document.getElementById('gider').value.replace(/[^0-9,-]+/g, '')) || 0;

            const toplam = nakit + krediKarti + carliston + getirCarsi + iwallet + tokenflex + trendyolgo + sodexo + edenred + setcard + multinet +metropol - gider;
            document.getElementById('toplam').value = toplam.toFixed(2) + ' TL';
        }
        document.querySelectorAll('input[type="text"]').forEach((input) => {
            input.addEventListener('input', function (e) {
                let value = this.value.replace(/[^0-9,-]+/g, ''); // Sadece rakam ve virgül bırak
                let parts = value.split(',');
                let integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // Binlik gruplamayı yap
                let decimalPart = parts[1] ? ',' + parts[1].substring(0, 2) : ''; // En fazla iki ondalık hane ekle
                this.value = integerPart + decimalPart;
            });

            input.addEventListener('focus', function () {
                // Input'a odaklanıldığında, eğer varsayılan değer varsa sil
                if (this.value === '0,00 TL') {
                    this.value = '';
                }
                this.value = this.value.replace(' TL', '');
            });

            input.addEventListener('blur', function () {
                // Input'tan çıkıldığında, boşsa varsayılan değeri ekle
                if (this.value === '') {
                    this.value = '0,00 TL';
                } else {
                    this.value += ' TL'; // Para birimi ekle
                }
            });
        });


        document.addEventListener('DOMContentLoaded', () => {
            const dateInput = document.getElementById('ekleme_tarihi');
            if (dateInput) {
                const today = new Date();
                const day = today.getDate().toString().padStart(2, '0');
                const month = (today.getMonth() + 1).toString().padStart(2, '0');
                const year = today.getFullYear();
                const formattedDate = `${day}.${month}.${year}`;
                dateInput.value = formattedDate;
            } else {
                console.error("Element with ID 'ekleme_tarihi' not found.");
            }
        });

        function setMagazaAd() {
            const magazaSelect = document.getElementById('magaza_id');
            const magazaAdInput = document.getElementById('magaza_ad');
            if (magazaSelect && magazaAdInput) {
                const selectedOption = magazaSelect.options[magazaSelect.selectedIndex];
                magazaAdInput.value = selectedOption.getAttribute('data-ad');
            } else {
                console.error("Magaza ID or Magaza Ad element not found.");
            }
        }
    </script>

<?php
require_once 'app/Views/kullanici/layout/footer.php';
