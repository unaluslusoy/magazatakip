# PALMİYE Web Servis Dokümantasyonu (Detaylı)
Bu doküman, **tamsoftintegration.camlica.com.tr** üzerinde yayımlanan PALMİYE entegrasyon servislerini ve 185.124.86.45:8899 üzerinde verilen alternatif erişim uçlarını bir araya getirir. **Cursor** içinde referans olarak kullanılabilir. İstek/yanıt örnekleri, enum değerleri ve veri modelleri özetlenmiştir. 【9†web_servis_döküman 1 - Kopya.pdf】
---
## 1) Ortam ve Temel Ayarlar
### Base URL
- Birincil: `http://tamsoftintegration.camlica.com.tr` 【9†web_servis_döküman 1 - Kopya.pdf】  
- Alternatif/IP: `http://185.124.86.45:8899` (aynı uç noktaların IP ve port ile sunulan hali) 【9†web_servis_döküman 1 - Kopya.pdf】
> **Öneri:** Cursor/Projede `.env` içine `BASE_URL` olarak tanımlayın ve çağrılarda `${BASE_URL}` kullanın.
### Kimlik Doğrulama (Token)
Web servislere erişmeden önce bir **Bearer Token** alınmalıdır. Token süresi **1 saattir**; süre dolduğunda yeni token üretin. 【9†web_servis_döküman 1 - Kopya.pdf】
- URL: `POST {BASE_URL}/token` (PDF’de parametreler verilmiştir) 【9†web_servis_döküman 1 - Kopya.pdf】
- Postman koleksiyonunda `/token` isteği **GET** olarak kaydedilmiştir; pratikte **POST kullanmanız önerilir.** 【8†PALMIYE.postman_collection.json】【9†web_servis_döküman 1 - Kopya.pdf】
- Gönderilecek alanlar: `grant_type=password`, `username=<KULLANICI_ADI>`, `password=<SIFRE>` 【9†web_servis_döküman 1 - Kopya.pdf】
- Postman koleksiyonu, `access_token`’ı **wg_token** değişkenine otomatik yazan bir test script’i içerir. 【8†PALMIYE.postman_collection.json】
**cURL Örneği (Token Alma):**
```bash
curl -X POST "${BASE_URL}/token" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data-urlencode "grant_type=password" \
  --data-urlencode "username=<KULLANICI_ADI>" \
  --data-urlencode "password=<SIFRE>"
```
**Yanıt (ör.):**
```json
{ "access_token": "<JWT_OR_BEARER_TOKEN>", "expires_in": 3600 }
```
**Tüm isteklerde:**
```
Authorization: Bearer <access_token>
```
---
## 2) Uç Noktalar Özeti (Quick Reference)
| Metod | Yol (Path) | Açıklama |
|---|---|---|
| POST | `/token` | Bearer token üretir (1 saat geçerli). 【9†web_servis_döküman 1 - Kopya.pdf】 |
| GET | `/api/Integration/BankaKrediKartlari` | Tahsilat evrakları işlenmeden önce bağlı kredi kartlarını listeler. 【9†web_servis_döküman 1 - Kopya.pdf】 |
| GET | `/api/Integration/DepoListesi` | Depoları listeler. 【8†PALMIYE.postman_collection.json】【9†web_servis_döküman 1 - Kopya.pdf】 |
| GET | `/api/Integration/StokListesi` | Tarih & depo bazlı stoklar. Filtre parametreleri mevcut. 【9†web_servis_döküman 1 - Kopya.pdf】 |
| GET | `/api/Integration/EticaretStokListesi` | E-ticaret görünür stoklar (tarih & depo bazlı). 【8†PALMIYE.postman_collection.json】【9†web_servis_döküman 1 - Kopya.pdf】 |
| GET | `/api/Integration/MusteriKartlari` | Müşteri listesini getirir. 【9†web_servis_döküman 1 - Kopya.pdf】 |
| GET | `/api/Integration/MusteriSorgulama` | VKN/TCKN, Telefon veya Ünvan ile müşteri arama. 【9†web_servis_döküman 1 - Kopya.pdf】 |
| GET | `/api/Integration/EvrakMevcutMu` | Belirli sipariş numarası için evrak mevcut mu? 【9†web_servis_döküman 1 - Kopya.pdf】 |
| POST | `/api/Integration/SiparisKaydet` | Sipariş kayıt (List<EvrakBilgisi>). 【9†web_servis_döküman 1 - Kopya.pdf】 |
| POST | `/api/Integration/FaturaKaydet` | Fatura kayıt (List<EvrakBilgisi>). 【9†web_servis_döküman 1 - Kopya.pdf】 |
| POST | `/api/Integration/FinansEvrakKaydet` | Finans evrak kayıt (List<FinansEvrak>). 【9†web_servis_döküman 1 - Kopya.pdf】 |
> Alternatif/IP ortamında aynı yollar `http://185.124.86.45:8899` tabanıyla da mevcuttur. 【9†web_servis_döküman 1 - Kopya.pdf】
---
## 3) Ayrıntılı Uç Noktalar
### 3.1) BankaKrediKartlari (GET)
```
GET ${BASE_URL}/api/Integration/BankaKrediKartlari
Authorization: Bearer <token>
```
**Açıklama:** Tahsilat evrakları işlenmeden önce bağlı kredi kartlarını çekmek için kullanılır. 【9†web_servis_döküman 1 - Kopya.pdf】
**Örnek Yanıt (örnek şema):**
```json
[
  { "ID": 1, "KartNo": "**** **** **** 1234", "KartSahibi": "ABC LTD" }
]
```
---
### 3.2) DepoListesi (GET)
```
GET ${BASE_URL}/api/Integration/DepoListesi
Authorization: Bearer <token>
```
**Açıklama:** Depoları listeler. 【8†PALMIYE.postman_collection.json】【9†web_servis_döküman 1 - Kopya.pdf】
**Örnek Yanıt (örnek şema):**
```json
[
  { "Depoid": 1, "Kod": "MERKEZ", "Adi": "Merkez Depo" },
  { "Depoid": 2, "Kod": "IKITELLI", "Adi": "İkitelli Depo" }
]
```
---
### 3.3) StokListesi (GET)
```
GET ${BASE_URL}/api/Integration/StokListesi?tarih=2024-01-01&depoid=1&urununsonbarkodulistelensin=false&miktarsifirdanbuyukstoklarlistelensin=false&sadeceeticaretstoklarigetir=false
Authorization: Bearer <token>
```
**Parametreler:**  
- `tarih` (yyyy-MM-dd): Son çekim tarihi. İlk kullanımda **1900-01-01** gönderilirse tüm stoklar gelir.  
- `depoid` (int): Miktar/fiyat hangi depoya göre gelmeli ise o depo ID’si.  
- `urununsonbarkodulistelensin` (bool): Ürüne ait tek bir barkod listelensin mi? (True/False)  
- `miktarsifirdanbuyukstoklarlistelensin` (bool): Sadece miktarı olan stoklar mı listelensin? (True/False)  
- `sadeceeticaretstoklarigetir` (bool): Sadece e-ticarette listelenecek stoklar mı? (True/False)  
【9†web_servis_döküman 1 - Kopya.pdf】
**Örnek Yanıt (örnek şema):**
```json
[
  {
    "ID": 1001,
    "UrunKodu": "SKU-001",
    "UrunAdi": "Ürün A",
    "KDVOrani": 20,
    "Tutar": 150.0,
    "IndirimliTutar": 140.0,
    "Envanter": 25,
    "Resimler": ["https://.../sku-001-1.jpg"],
    "Barkodlar": [{ "Barkodu": "8691234567890", "Birim": "Adet", "Fiyat": 150.0 }],
    "Gruplar": [{ "ID": 10, "Tanim": "Kategori X" }],
    "Kategori": "Kategori X",
    "GuncellemeTarihi": "2024-05-01T12:00:00"
  }
]
```
---
### 3.4) EticaretStokListesi (GET)
```
GET ${BASE_URL}/api/Integration/EticaretStokListesi?tarih=2024-01-01&depoid=1&miktarsifirdanbuyukstoklarlistelensin=false
Authorization: Bearer <token>
```
**Parametreler:**  
- `tarih` (yyyy-MM-dd)  
- `depoid` (int)  
- `miktarsifirdanbuyukstoklarlistelensin` (bool)  
【8†PALMIYE.postman_collection.json】【9†web_servis_döküman 1 - Kopya.pdf】
**Örnek Yanıt:** StokListesi ile benzer şema (e-ticaret görünür ürünler). 【9†web_servis_döküman 1 - Kopya.pdf】
---
### 3.5) MusteriKartlari (GET)
```
GET ${BASE_URL}/api/Integration/MusteriKartlari
Authorization: Bearer <token>
```
**Açıklama:** Müşteri listesini getirir. 【9†web_servis_döküman 1 - Kopya.pdf】
**Örnek Yanıt (örnek şema):**
```json
[
  {
    "Id": 5001,
    "Kod": "CARI-001",
    "Adi": "Müşteri AŞ",
    "Email": "info@musteri.com",
    "Tc": null,
    "VergiDairesi": "KARTAL",
    "VergiNumarasi": "1234567890",
    "WebSitesi": "https://musteri.com",
    "EFatura": 1,
    "Not": "",
    "Fiyattipid": 1,
    "Adresler": [
      {
        "Id": 1, "address":"Adres satırı",
        "city":"İstanbul", "city_code":"34",
        "district":"Kartal", "neighborhood":"Atalar",
        "countryCode":"TR", "full_address":"...",
        "AdresSecim": 0
      }
    ]
  }
]
```
---
### 3.6) MusteriSorgulama (GET)
```
GET ${BASE_URL}/api/Integration/MusteriSorgulama?filtre_tip=0&filtre=12345678901
Authorization: Bearer <token>
```
**Parametreler:**  
- `filtre_tip`: `0` VKN/TCKN, `1` Telefon Numarası, `2` Müşteri Ünvanı  
- `filtre`: Filtre tipine göre aranan değer  
【9†web_servis_döküman 1 - Kopya.pdf】
**Örnek Yanıt:** İlgili müşteri detaylarını döndürür. 【9†web_servis_döküman 1 - Kopya.pdf】
---
### 3.7) EvrakMevcutMu (GET)
```
GET ${BASE_URL}/api/Integration/EvrakMevcutMu?SiparisNumarasi=TY123456
Authorization: Bearer <token>
```
**Açıklama:** Evrak aktarılmış mı kontrol eder; mevcut ise `1`, değilse `0` döner. 【9†web_servis_döküman 1 - Kopya.pdf】
**Örnek Yanıt:**
```json
1
```
---
### 3.8) SiparisKaydet (POST)
```
POST ${BASE_URL}/api/Integration/SiparisKaydet
Authorization: Bearer <token>
Content-Type: application/json
```
**Body:** `siparislerjson` alanında `List<EvrakBilgisi>` bekler. 【9†web_servis_döküman 1 - Kopya.pdf】
**Örnek İstek Gövdesi:**
```json
{
  "siparislerjson": [
    {
      "Tip": 1,
      "HareketDurum": 2,
      "Durum": 0,
      "EvrakCins": 1,
      "FaturaTip": 0,
      "IslemTip": 1,
      "SiparisFaturayaCevrilsin": false,
      "SiparisSeri": "SP",
      "SiparisSira": 10001,
      "DepoNo": "1",
      "SiparisNumarasi": "TY123456",
      "SiparisTarihi": "2025-08-01T10:30:00",
      "currency_code": "TRY",
      "AraToplam": 300.0,
      "ToplamFiyat": 354.0,
      "ToplamKdvMatrah": 300.0,
      "ToplamKdvTutar": 54.0,
      "MusteriBilgi": {
        "Id": 5001,
        "Kod": "CARI-001",
        "Adi": "Müşteri AŞ",
        "VergiDairesi": "KARTAL",
        "VergiNumarasi": "1234567890",
        "EFatura": 1,
        "Adresler": [
          {
            "Id": 1,
            "full_address": "Adres...",
            "city": "İstanbul",
            "city_code": "34",
            "district": "Kartal",
            "neighborhood": "Atalar",
            "AdresSecim": 1
          }
        ]
      },
      "SiparisUrunBilgisileri": [
        {
          "DepoNo": "1",
          "Barkod": "8691234567890",
          "UrunAdi": "Ürün A",
          "UrunKodu": "SKU-001",
          "BirimTanim": "Adet",
          "Miktar": 2,
          "BirimFiyat": 150.0,
          "KdvOran": 18,
          "KdvMatrah": 300.0,
          "KdvTutar": 54.0,
          "ToplamFiyat": 354.0,
          "DovizKodu": "TRY",
          "KurCarpani": 1.0,
          "sku": "SKU-001"
        }
      ]
    }
  ]
}
```
---
### 3.9) FaturaKaydet (POST)
```
POST ${BASE_URL}/api/Integration/FaturaKaydet
Authorization: Bearer <token>
Content-Type: application/json
```
**Body:** `faturajson` alanında `List<EvrakBilgisi>` bekler. 【9†web_servis_döküman 1 - Kopya.pdf】
**Örnek İstek Gövdesi:** (Sipariş örneği ile aynı şemayı kullanır; alan adı `faturajson`’dur.)
```json
{
  "faturajson": [
    {
      "Tip": 1,
      "HareketDurum": 4,
      "Durum": 0,
      "EvrakCins": 1,
      "FaturaTip": 1,
      "IslemTip": 1,
      "FaturaSeri": "FA",
      "Faturasira": 20001,
      "DepoNo": "1",
      "SiparisNumarasi": "TY123456",
      "SiparisTarihi": "2025-08-01T10:30:00",
      "currency_code": "TRY",
      "ToplamFiyat": 354.0,
      "MusteriBilgi": { "Id": 5001, "Kod": "CARI-001", "Adi": "Müşteri AŞ" },
      "SiparisUrunBilgisileri": [
        { "UrunKodu": "SKU-001", "Miktar": 2, "BirimFiyat": 150.0, "KdvOran": 18, "ToplamFiyat": 354.0 }
      ]
    }
  ]
}
```
---
### 3.10) FinansEvrakKaydet (POST)
```
POST ${BASE_URL}/api/Integration/FinansEvrakKaydet
Authorization: Bearer <token>
Content-Type: application/json
```
**Body:** `finansEvraklar` alanında `List<FinansEvrak>` bekler. 【9†web_servis_döküman 1 - Kopya.pdf】
**Örnek İstek Gövdesi:**
```json
{
  "finansEvraklar": [
    {
      "EvrakTip": 0,
      "Seri": "THS",
      "Sira": 1,
      "Belgeno": "THS-0001",
      "EvrakTarihi": "2025-08-01T12:00:00",
      "Hareketler": [
        {
          "Sira": 1,
          "IslemTip": 4,
          "GirisTip": 2,
          "GirisID": 5001,
          "HareketTip": 5,
          "CikisTip": 4,
          "CikisID": 1,
          "DovizKodu": "TRY",
          "KurCarpan": 1.0,
          "Tutar": 354.0,
          "DepoNo": "1",
          "ReferansNo": "TY123456",
          "Aciklama": "E-ticaret tahsilat",
          "IslemTarihi": "2025-08-01T12:00:00"
        }
      ]
    }
  ]
}
```
---
## 4) Enum Değerleri (C# Özet)
- **BelgeTip (byte):** `0` SatinAlma, `1` Satis, `2` OnaylanmamisEFatura, `3` DepoSayimGiris, `4` DepoSayimCikis 【9†web_servis_döküman 1 - Kopya.pdf】  
- **BelgeHareketDurum (byte):** `1` Teklif, `2` Siparis, `3` Irsaliye, `4` Fatura 【9†web_servis_döküman 1 - Kopya.pdf】  
- **BelgeDurum (byte):** `0` Hicbiri, `1` Donusturulmus, `2` KismenDonusturulmus, `3` AcikFatura, `4` KapaliFatura 【9†web_servis_döküman 1 - Kopya.pdf】  
- **BelgeEvrakCinsi (byte):** `1` Normal, `2` Iade, `3` Masraf, `4` FiyatFarki 【9†web_servis_döküman 1 - Kopya.pdf】  
- **BelgeFaturaTipi (byte):** `0` HicBiri, `1` Acik, `2` Kapali 【9†web_servis_döküman 1 - Kopya.pdf】  
- **BelgeIslemTipi (byte):** `1` Toptan, `2` Perakende 【9†web_servis_döküman 1 - Kopya.pdf】  
- **AdresTipi (byte):** `0` Fatura, `1` Siparis 【9†web_servis_döküman 1 - Kopya.pdf】  
- **FinansEvrakTipi (byte):** `0` Tahsilat, `1` Tediye 【9†web_servis_döküman 1 - Kopya.pdf】  
- **FinansIslemTipi (byte):** `1` Nakit, `4` KrediKarti 【9†web_servis_döküman 1 - Kopya.pdf】  
- **GirisTipi (byte):** `1` Banka, `2` Cari 【9†web_servis_döküman 1 - Kopya.pdf】  
- **FinansHareketTipi (byte):** `5` Tahsilat, `6` Tediye 【9†web_servis_döküman 1 - Kopya.pdf】  
- **FinansCikisTipi (byte):** `0` Nakit, `4` KrediKarti 【9†web_servis_döküman 1 - Kopya.pdf】  
---
## 5) Veri Modelleri (Özet Şema)
**Depo**: `Depoid`, `Kod`, `Adi` 【9†web_servis_döküman 1 - Kopya.pdf】  
**Musteri**: `Id`, `Kod`, `Adi`, `Email`, `Tc`, `VergiDairesi`, `VergiNumarasi`, `WebSitesi`, `EFatura`, `Not`, `Fiyattipid`, `Adresler: MusteriAddress[]` 【9†web_servis_döküman 1 - Kopya.pdf】  
**MusteriAddress**: `Id`, `address`, `city`, `city_code`, `district`, `neighborhood`, `countryCode`, `full_address`, `AdresSecim` 【9†web_servis_döküman 1 - Kopya.pdf】  
**BankaKrediKarti**: `ID`, `KartNo`, `KartSahibi` 【9†web_servis_döküman 1 - Kopya.pdf】  
**UrunBilgisi**: `ID`, `UrunKodu`, `UrunAdi`, `KDVOrani`, `IndirimliTutar`, `Tutar`, `Envanter`, `UreticiFirmaAdi`, kanal fiyatları (`n11`, `ggidiyor`, `hepsiburada`, `trendyol`, `amazon`), `GuncellemeTarihi`, `UrunAciklamasi`, `Resimler:string[]`, `Barkodlar:BarkodBilgisi[]`, `Gruplar:GrupBilgisi[]`, `Kategori` 【9†web_servis_döküman 1 - Kopya.pdf】  
**BarkodBilgisi**: `Barkodu`, `Birim`, `Fiyat` 【9†web_servis_döküman 1 - Kopya.pdf】  
**GrupBilgisi**: `ID`, `Tanim` 【9†web_servis_döküman 1 - Kopya.pdf】  
**FinansEvrak**: `EvrakTip`, `Seri`, `Sira`, `Belgeno`, `EvrakTarihi`, `Hareketler:FinansEvrakHareket[]` 【9†web_servis_döküman 1 - Kopya.pdf】  
**FinansEvrakHareket**: `Sira`, `IslemTip`, `GirisTip`, `GirisID`, `HareketTip`, `CikisTip`, `CikisID`, `DovizKodu`, `KurCarpan`, `Tutar`, `DepoNo`, `ReferansNo`, `Aciklama`, `IslemTarihi` 【9†web_servis_döküman 1 - Kopya.pdf】  
**EvrakBilgisi**: Belge başlığı + toplamlar + müşteri + satırlar (`SiparisUrunBilgisileri:EvrakUrunBilgisi[]`) 【9†web_servis_döküman 1 - Kopya.pdf】  
**EvrakUrunBilgisi**: `DepoNo`, `Barkod`, `UrunAdi`, `UrunKodu`, `BirimTanim`, `Miktar`, `BirimFiyat`, `KdvOran`, `KdvMatrah`, `KdvTutar`, `OtvMatrah`, `OivMatrah`, `Iskonto1..10`, `BedelsizIskonto`, `IskontoToplam`, `ToplamFiyat`, `AnaDovizToplamFiyat`, `DovizKodu`, `KurCarpani`, `sku`, `merchant_sku`, `product_size`, `order_status`, `order_number`, `SiparisRecID`, `FaturaRecID` 【9†web_servis_döküman 1 - Kopya.pdf】
---
## 6) Postman Koleksiyonu Notları
- Koleksiyon adı **PALMIYE** ve içinde *Workgroup Copy* klasörü ile bazı örnek istekler bulunur (Token, DepoListesi, EticaretStokListesi). 【8†PALMIYE.postman_collection.json】
- `Token` isteğinin **Tests** sekmesinde `pm.response.json().access_token` değeri **collection variable** olarak `wg_token` içine yazılır; diğer isteklerde **Bearer** olarak `{{wg_token}}` kullanılır. 【8†PALMIYE.postman_collection.json】
- EticaretStokListesi isteğinde örnek sorgu: `tarih=2024-01-01&depoid=1&miktarsifirdanbuyukstoklarlistelensin=False` şeklindedir. 【8†PALMIYE.postman_collection.json】
---
## 7) İstemci Kod Örnekleri
**JavaScript (fetch) – Token + Depo Listesi:**
```js
const BASE_URL = process.env.BASE_URL;
async function getToken() {
  const body = new URLSearchParams({
    grant_type: "password",
    username: process.env.API_USER,
    password: process.env.API_PASS
  });
  const res = await fetch(`${BASE_URL}/token`, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body
  });
  if (!res.ok) throw new Error("Token alınamadı");
  return res.json(); // { access_token, expires_in }
}
async function getDepolar(access_token) {
  const res = await fetch(`${BASE_URL}/api/Integration/DepoListesi`, {
    headers: { Authorization: `Bearer ${access_token}` }
  });
  if (!res.ok) throw new Error("Depo listesi alınamadı");
  return res.json();
}
(async () => {
  const { access_token } = await getToken();
  const depolar = await getDepolar(access_token);
  console.log(depolar);
})();
```
**cURL – EticaretStokListesi:**
```bash
curl "${BASE_URL}/api/Integration/EticaretStokListesi?tarih=2024-01-01&depoid=1&miktarsifirdanbuyukstoklarlistelensin=false" \
  -H "Authorization: Bearer <access_token>"
```
---
## 8) En İyi Uygulamalar
- Token’ı süresi içinde yeniden üretin (1 saat) ve isteklerde `Authorization: Bearer` başlığı ile gönderin. 【9†web_servis_döküman 1 - Kopya.pdf】
- İlk stok çekiminde `tarih=1900-01-01` vererek tam liste alın, sonrasında değişiklik tarihine göre incremental çekin. 【9†web_servis_döküman 1 - Kopya.pdf】
- Depo bazlı fiyat/miktar için her istekte doğru `depoid` gönderildiğinden emin olun. 【9†web_servis_döküman 1 - Kopya.pdf】
- E-ticaret operasyonu için önce **Evrak mevcut mu** kontrolü yapıp (1/0), ardından kayıt servislerini çağırın. 【9†web_servis_döküman 1 - Kopya.pdf】
---
## 9) Alternatif/IP Ortam Uçları (Aynı Şema)
- `http://185.124.86.45:8899/token`  
- `http://185.124.86.45:8899/api/Integration/DepoListesi`  
- `http://185.124.86.45:8899/api/Integration/StokListesi`  
- `http://185.124.86.45:8899/api/Integration/BankaKrediKartlari`  
- `http://185.124.86.45:8899/api/Integration/MusteriKartlari`  
- `http://185.124.86.45:8899/api/Integration/MusteriSorgulama`  
- `http://185.124.86.45:8899/api/Integration/EvrakMevcutMu`  
- `http://185.124.86.45:8899/api/Integration/SiparisKaydet`  
- `http://185.124.86.45:8899/api/Integration/FaturaKaydet`  
- `http://185.124.86.45:8899/api/Integration/FinansEvrakKaydet`  
【9†web_servis_döküman 1 - Kopya.pdf】
---
### Notlar
- Postman koleksiyonundaki **GET /token** isteği pratikte **POST** olarak kullanılmalıdır; örnekler bu doğrultudadır. 【8†PALMIYE.postman_collection.json】【9†web_servis_döküman 1 - Kopya.pdf】
- Örnek JSON’lar şema gösterimi içindir; gerçek yanıt alanları sürüme göre değişebilir.
