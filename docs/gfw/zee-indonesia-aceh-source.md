# ZEE Indonesia – Kawasan Aceh

## Purpose

AOI untuk analisis Global Fishing Watch.

## Legal Reference

UNCLOS 1982 Article 57 (Lebar Zona Ekonomi Eksklusif tidak boleh melebihi 200 mil laut dari garis pangkal laut teritorial diukur / ~370.4 km).
Perjanjian Batas Maritim Bilateral:
- Indonesia–India (Perjanjian Garis Batas Landas Kontinen & ZEE Laut Andaman/Samudera Hindia 1974 & 1977).
- Indonesia–Thailand (Perjanjian Batas Landas Kontinen & Garis Batas Laut Andaman 1971 & 1975).
- Indonesia–Malaysia (Perjanjian Garis Batas Laut Wilayah & Landas Kontinen Selat Malaka 1969 & 1970).

## Indonesian Reference

- Deklarasi Pemerintah Indonesia tentang Zona Ekonomi Eksklusif Indonesia tanggal 21 Maret 1980.
- Undang-Undang Republik Indonesia Nomor 5 Tahun 1983 tentang Zona Ekonomi Eksklusif Indonesia.
- Undang-Undang Republik Indonesia Nomor 32 Tahun 2014 tentang Kelautan.
- Peraturan Pemerintah Nomor 38 Tahun 2002 jo. PP Nomor 37 Tahun 2008 tentang Daftar Koordinat Geografis Titik-Titik Garis Pangkal Kepulauan Indonesia.
- Peraturan Menteri Kelautan dan Perikanan No. 18/PERMEN-KP/2014 jo. Kepmen KP No. 20/2024 tentang Wilayah Pengelolaan Perikanan Negara Republik Indonesia (WPPNRI 571 & 572).

## Geospatial Dataset

- **Dataset:** Marine Regions World EEZ v12 (Indonesia Exclusive Economic Zone / MRGID: 8492) & Batas Wilayah Pengelolaan Perikanan NKRI (WPPNRI 571 & 572).
- **Publisher:** Flanders Marine Institute (VLIZ) / Global Fishing Watch (GFW Dataset: `public-eez-areas`) & Badan Informasi Geospasial (BIG) / Kementerian Kelautan dan Perikanan (KKP) Republik Indonesia.
- **URL:** https://www.marineregions.org/eezdetails.php?mrgid=8492&zone=eez | https://geoportal.big.go.id
- **Version:** Maritime Boundaries Geodatabase v12
- **Date:** 25 Oktober 2023 / Audit: 21 September 2026
- **CRS:** EPSG:4326 (WGS84 Geodetic Decimal Degrees `[longitude, latitude]`)
- **Geometry type:** Polygon / MultiPolygon

## Method

Geometry ZEE Indonesia di kawasan Aceh dibentuk melalui subset spasial (*geographical bounding envelope and maritime intersection*) dari Zona Ekonomi Eksklusif Indonesia (EEZ Indonesia MRGID 8492) pada koridor yurisdiksi maritim perairan barat-utara Sumatera (Laut Andaman, Selat Malaka utara, dan Samudera Hindia barat Aceh).

Wilayah daratan dan kepulauan Provinsi Aceh (termasuk Pulau Weh, Pulau Breueh, Pulau Nasi, dan Kepulauan Simeulue) digunakan sebagai titik jangkar referensi geografis untuk mendefinisikan batas pengamatan analitik perikanan tangkap ZEE Indonesia di perairan Aceh, yang beririsan dengan WPPNRI 571 dan WPPNRI 572.

## Important Limitation

AOI ini adalah wilayah analisis geografis ZEE Indonesia yang terkait dengan kawasan Aceh, bukan batas administratif ZEE Provinsi Aceh. ZEE adalah hak berdaulat (*sovereign rights*) nasional Republik Indonesia berdasarkan UNCLOS 1982 dan UU No. 5/1983, sedangkan kewenangan pengelolaan laut daerah otonom Provinsi Aceh secara administratif dibatasi hingga 12 mil laut sesuai UU No. 11/2006 (UUPA) Pasal 166 dan Qanun Aceh No. 1/2020.

## Validation

- **Geometry:** Valid Polygon/MultiPolygon (RFC 7946 compliant).
- **CRS:** EPSG:4326 (WGS84 [longitude, latitude]).
- **Topology:** Ring tertutup sempurna (koordinat awal = akhir), tidak ada self-intersection, tidak ada poligon kosong atau tumpang tindih ke daratan pedalaman.
- **Source provenance:** Terverifikasi bersumber dari dataset resmi EEZ Indonesia (Marine Regions MRGID: 8492 / GFW EEZ Reference) dan batas yurisdiksi maritim Indonesia–India–Thailand–Malaysia di sekitar Aceh.
