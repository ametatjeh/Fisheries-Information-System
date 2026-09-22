# Tanya Jawab Terantisipasi (Frequently Asked Questions)
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F11 — FAQ Document
Target Audiences: Decision Makers, Enumerators, IT Developers
Date: 21 September 2026
```

---

## 1. Pertanyaan Umum & Manajerial (Pimpinan / Decision Makers)

### Q1: Apa tujuan utama dibangunnya Sistem Informasi Perikanan Tangkap Aceh?
**Jawaban:**
Tujuan utamanya adalah mengintegrasikan seluruh tata kelola data perikanan tangkap di 23 Kabupaten/Kota Aceh secara terpadu—mulai dari pendaftaran nelayan dan kapal, pelayaran penangkapan di laut, transaksi pendaratan di pelabuhan/TPI, perhitungan statistik CPUE, hingga visualisasi geospasial WebGIS guna mendukung perumusan kebijakan perikanan berbasis bukti nyata (*evidence-based policy*).

### Q2: Mengapa sistem ini menggunakan visualisasi GIS?
**Jawaban:**
Aktivitas perikanan tangkap memiliki dimensi ruang dan wilayah yang sangat kental. Visualisasi WebGIS membantu pimpinan melihat secara langsung sebaran pelabuhan pendaratan, titik operasi alat tangkap, serta intensitas penangkapan antara wilayah perairan WPP 571 (Selat Malaka) dan WPP 572 (Samudera Hindia).

### Q3: Apakah sistem ini sudah bisa menentukan stok ikan atau kuota tangkapan?
**Jawaban:**
Sistem ini menyediakan **Statistik Produksi & Indikator Laju Tangkap (CPUE)**, bukan model prediksi biomassa stok ikan laut lepas penuh (*full stock assessment*). Data CPUE yang dihasilkan sistem menjadi salah satu input penting bagi peneliti dan balai riset perikanan untuk melakukan kajian stok ikan formal.

### Q4: Apakah data laporan dapat diekspor untuk keperluan presentasi dan audit?
**Jawaban:**
Ya, seluruh laporan dapat diekspor secara instan ke dalam format spreadsheet Excel (`.xlsx`) yang aman atau dicetak langsung menjadi dokumen resmi bertanda tangan (PDF).

---

## 2. Pertanyaan Operasional & Lapangan (Petugas / Enumerators)

### Q5: Dari mana data fishing effort dan hasil tangkapan berasal?
**Jawaban:**
Data berasal dari input formulir operasional oleh petugas pencatat pelabuhan (enumerator) berdasarkan buku harian kapal (*logbook*) dan wawancara pendaratan nelayan saat kapal tiba di dermaga.

### Q6: Mengapa terdapat data fishing effort yang tidak memiliki koordinat di peta?
**Jawaban:**
Banyak kapal nelayan skala kecil di Aceh beroperasi secara konvensional tanpa perangkat GPS. Sesuai prinsip kejujuran data, sistem **TIDAK membuat koordinat palsu di laut**. Data non-GPS tetap tercatat lengkap durasi dan berat tangkapannya pada tabel statistik.

### Q7: Bagaimana cara menghitung CPUE pada sistem?
**Jawaban:**
Sistem menyediakan dua formula terstandar:
1. **CPUE per Jam:** Total Tangkapan (kg) dibagi Total Durasi Penarikan Jaring (jam).
2. **CPUE per Trip:** Total Tangkapan (kg) dibagi Total Pelayaran (trip).
Perhitungan aman dari pembagian nol jika durasi bernilai 0.

---

## 3. Pertanyaan Teknis & Arsitektur (IT / Developers)

### Q8: Teknologi apa yang digunakan untuk membangun sistem ini?
**Jawaban:**
* **Backend:** Laravel 13.x dan PHP 8.4.x (Clean MVC dengan Service Layer).
* **Basis Data:** MariaDB / MySQL dengan foreign key integrity `ON DELETE RESTRICT`.
* **Frontend & Map:** Laravel Blade, Tailwind CSS, dan MapLibre GL JS 4.7.1.

### Q9: Apakah posisi kapal pada peta merupakan pelacakan langsung (live tracking)?
**Jawaban:**
Bukan. Layer *Homeport Kapal* menunjukkan lokasi pelabuhan registrasi resmi kapal, dan layer *Logbook Historis* menyajikan rekaman perjalanan masa lalu. Keduanya bukan transmiter radar AIS satelit *real-time*.

### Q10: Bagaimana keamanan data dan proteksi hak akses pengguna?
**Jawaban:**
Sistem menerapkan Spatie Laravel Permission (RBAC) dengan 5 tingkatan peran, proteksi mutasi CSRF token, sanitasi Blade escaping untuk mencegah XSS, dan isolasi kredensial `.env`.

### Q11: Apakah sistem sudah siap untuk deployment produksi?
**Jawaban:**
Ya, seluruh pengujian otomatis (273 tests) dan audit kode Laravel Pint telah lulus 100%. Sistem siap masuk tahap F12 (Deployment Preparation).
