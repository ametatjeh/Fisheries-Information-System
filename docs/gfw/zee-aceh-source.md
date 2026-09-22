# ZEE Aceh AOI — Source Audit

## Dataset
Batas Maritim Pengamatan Perikanan Aceh (Aceh Maritime Observation AOI) / Integrasi Batas Yurisdiksi Laut Teritorial Aceh (0–12 Mil Laut) & ZEE Indonesia (WPPNRI 571 & 572).

## Publisher
- **Nasional/Regional:** Pemerintah Aceh / Dinas Kelautan dan Perikanan (DKP) Aceh, Badan Informasi Geospasial (BIG), Kementerian Kelautan dan Perikanan (KKP) Republik Indonesia.
- **Internasional:** Flanders Marine Institute (VLIZ) — Marine Regions (MRGID: 8492 / Indonesia Exclusive Economic Zone) & Global Fishing Watch (GFW).

## Source URL
- https://geoportal.big.go.id (Badan Informasi Geospasial)
- https://jdih.kkp.go.id (Kementerian Kelautan dan Perikanan RI)
- https://jdih.acehprov.go.id/produk-hukum/detail-peraturan/1897 (Qanun Aceh No. 1 Tahun 2020)
- https://www.marineregions.org/eezdetails.php?mrgid=8492&zone=eez (Marine Regions Indonesia EEZ)

## Dataset URL
- https://gateway.api.globalfishingwatch.org/v3/datasets/public-eez-areas (GFW EEZ Reference)
- `storage/app/private/gfw/zee-aceh.geojson` (Internal Canonical Storage)

## Version / Date
- **Qanun Aceh No. 1/2020:** 13 Januari 2020 (RZWP-3-K Aceh 2020–2040)
- **Kepmen KP No. 20/2024 & Permen KP No. 18/2014:** WPPNRI 571 & 572
- **Marine Regions World EEZ:** v12 (2023-10-25) / MRGID 8492
- **Audit Execution Date:** 21 September 2026

## CRS
EPSG:4326 (WGS84 Geodetic Decimal Degrees `[longitude, latitude]`)

## Geometry Type
Polygon / MultiPolygon

## Geographic Scope
Perairan laut di sekeliling daratan dan kepulauan Provinsi Aceh, mencakup koridor Selat Malaka bagian utara, Laut Andaman, dan perairan Samudera Hindia di sebelah barat Pulau Sumatera hingga batas yurisdiksi maritim terluar yang relevan dengan pengamatan armada perikanan tangkap Aceh.

Rentang Koordinat Geografis (Bounding Box):
- Min Longitude: 94.5° E
- Min Latitude: 1.8° N
- Max Longitude: 98.3° E
- Max Latitude: 6.2° N

## Authority / Provenance
1. **Dasar Hukum Internasional & Nasional ZEE:**
   - Berdasarkan *United Nations Convention on the Law of the Sea* (UNCLOS 1982) dan Undang-Undang Republik Indonesia Nomor 5 Tahun 1983 tentang Zona Ekonomi Eksklusif Indonesia, ZEE merupakan hak berdaulat (*sovereign rights*) **Negara Republik Indonesia**, bukan unit administratif pemerintah daerah tingkat provinsi.
   - Tidak ada penetapan hukum internasional maupun nasional yang mendefinisikan batas administratif terpisah berstatus "ZEE Provinsi Aceh" secara independen dari ZEE Indonesia.

2. **Kewenangan Pengelolaan Wilayah Laut Daerah (Aceh):**
   - Berdasarkan Undang-Undang Nomor 11 Tahun 2006 tentang Pemerintahan Aceh (UUPA) Pasal 166 dan Undang-Undang Nomor 23 Tahun 2014 tentang Pemerintahan Daerah Pasal 27, kewenangan pengelolaan sumber daya kelautan Provinsi Aceh meliputi wilayah laut paling jauh **12 mil laut** diukur dari garis pantai ke arah laut lepas dan/atau ke arah perairan kepulauan (Laut Teritorial dan Perairan Pedalaman/Kepulauan), sebagaimana tertuang dalam Qanun Aceh Nomor 1 Tahun 2020 tentang RZWP-3-K Aceh.

3. **Pengelolaan Perikanan Tangkap ZEE (WPPNRI):**
   - Wilayah perairan laut di luar 12 mil laut hingga 200 mil laut ZEE dikelola oleh Kementerian Kelautan dan Perikanan (KKP) melalui Wilayah Pengelolaan Perikanan Negara Republik Indonesia:
     - **WPPNRI 571:** Selat Malaka dan Laut Andaman (wilayah utara dan timur Aceh).
     - **WPPNRI 572:** Samudera Hindia sebelah Barat Sumatera (wilayah barat dan barat daya Aceh).

4. **Konsep AOI (Area of Interest) pada Global Fishing Watch:**
   - Geometri yang disimpan pada `storage/app/private/gfw/zee-aceh.geojson` difungsikan sebagai **Area of Interest (AOI) Spasial** untuk memfilter pergerakan kapal AIS/VMS pada Global Fishing Watch API v3.
   - AOI ini merepresentasikan wilayah pengamatan maritim perikanan Aceh (*Aceh Maritime Observation AOI*), dan **bukan** merupakan deklarasi batas batas kedaulatan baru di luar ketentuan perundang-undangan Republik Indonesia.

## License / Usage
- **Data Nasional (BIG/KKP/Pemerintah Aceh):** Kebijakan Satu Data Indonesia & Open Government Data RI untuk keperluan dinas, riset, dan sistem informasi perikanan tangkap.
- **Marine Regions / GFW:** Creative Commons Attribution 4.0 International (CC BY 4.0).

## Processing
1. Ekstraksi batas koordinat referensi maritim perairan Aceh dari persimpangan WPPNRI 571, WPPNRI 572, dan batas luar perairan teritorial/ZEE barat-utara Sumatera.
2. Validasi format koordinat ke standar RFC 7946 GeoJSON dengan urutan `[longitude, latitude]`.
3. Pemeriksaan ketelitian topologi: ring tertutup (*closed ring*), orientasi searah jarum jam / counter-clockwise untuk outer ring, dan tidak ada *self-intersection*.
4. Penyimpanan ke direktori privat terlindungi: `storage/app/private/gfw/zee-aceh.geojson`.
5. Isolasi kredensial: Dokumen GeoJSON sama sekali tidak memuat API token, API key, atau header otorisasi.

## Validation
- **Format:** GeoJSON RFC 7946 valid (FeatureCollection).
- **CRS:** EPSG:4326 (WGS84).
- **Coordinate Order:** `[longitude, latitude]` terverifikasi.
- **Ring Closure:** Titik awal `[94.5, 1.8]` identik dengan titik penutup `[94.5, 1.8]`.
- **Bounding Box Terhitung:** `[94.5, 1.8, 98.3, 6.2]`.
- **Geodesic Topology Check:** Valid, tanpa self-intersection atau titik singularitas antimeridian.

## Important Limitation
1. **Bukan Batas Hukum ZEE Provinsi:** Dokumen dan dataset ini **tidak boleh diklaim** sebagai "batas hukum ZEE independen Provinsi Aceh" karena ZEE secara hukum laut internasional adalah hak berdaulat NKRI.
2. **Kewenangan Hukum Daerah:** Batas kewenangan administratif daerah otonom Aceh adalah wilayah laut sampai dengan 12 mil laut (Qanun 1/2020).
3. **Fungsi Khusus AOI:** Geometri GeoJSON ini murni berfungsi sebagai filter geospasial query bagi integrasi analitik satelit Global Fishing Watch (GFW) API.
