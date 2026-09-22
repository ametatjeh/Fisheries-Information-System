# Disaster Recovery Plan
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F12 — Disaster Recovery Plan
Document Type: Business Continuity & Data Protection
Date: 21 September 2026
```

---

## 1. Kebijakan Pencadangan & Retensi (Backup Policy)

* **Frekuensi Pencadangan:** Setiap hari pukul 02:00 WIB (Otomatis via Cron).
* **Format Cadangan:** SQL dump terkompresi GZIP (`sistem_perikanan_prod_YYYYMMDD_HHMMSS.sql.gz`).
* **Retensi Lokal:** Berkas backup disimpan selama **30 hari** di direktori `/backups/database/`.
* **Pencadangan Luar Lokasi (Off-site Backup):** Disarankan melakukan replikasi harian ke cloud storage (AWS S3, Google Cloud Storage, atau server backup Diskominfo Aceh) terenkripsi.

---

## 2. Tingkat Prioritas Pemulihan Layanan (Recovery Priority)

Apabila terjadi kegagalan server total atau bencana infrastruktur:

```text
PRIORITAS 1: PEMULIHAN BASIS DATA (DATABASE RESTORE)
- Mengembalikan tabel master, nelayan, kapal, trip, effort, catch, dan pendaratan.

PRIORITAS 2: PENYIMPANAN BERKAS (FILESYSTEM RESTORE)
- Mengembalikan direktori storage/app/public (foto armada dan berkas unggahan).

PRIORITAS 3: KODE SUMBER APLIKASI (APPLICATION RE-DEPLOY)
- Mengunduh tag rilis stabil dari repositori Git dan instalasi dependensi Composer/NPM.

PRIORITAS 4: CACHE & OPTIMASI LINGKUNGAN
- Membangun ulang konfigurasi cache, route, view, dan menghubungkan symbolic link storage.
```

---

## 3. Matriks Kontak Darurat (Emergency Roles)

| Peran Tanggap Darurat | Penanggung Jawab Instansi | Tugas & Tanggung Jawab |
| :--- | :--- | :--- |
| **Koordinator Pemulihan Data** | Administrator Database DKP Aceh | Eksekusi restore database & verifikasi integritas data |
| **Koordinator Infrastruktur** | Tim Teknis Jaringan Diskominfo Aceh | Penyediaan server baru, konfigurasi domain & SSL |
| **Pengawas Mutu Layanan** | Kepala Bidang Perikanan Tangkap DKP | Verifikasi operasional sebelum layanan dibuka ke publik |
