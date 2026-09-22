# Production Deployment & Readiness Hub
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F12 — Deployment Preparation & Production Readiness
Version: 1.0.0
Date: 21 September 2026
Status: Verified & Production Ready with Conditions
```

Selamat datang di repositori dokumentasi **Kesiapan Deployment Produksi (Production Readiness)** Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh.

Direktori ini memuat seluruh panduan operasional, daftar periksa lingkungan (*environment checklist*), audit kesiapan basis data, prosedur pencadangan dan pemulihan (*backup & restore*), manajemen penyimpanan berkas, *deployment runbook*, rencana pemulihan kegagalan (*rollback plan*), pemantauan sistem (*monitoring*), pemulihan bencana (*disaster recovery*), daftar periksa keamanan (*security checklist*), matriks uji asap (*smoke test matrix*), serta laporan resmi F12.

---

## 🧭 Navigasi Dokumen Deployment

| Berkas Dokumen | Deskripsi & Isi Utama |
| :--- | :--- |
| 📋 [**environment-checklist.md**](./environment-checklist.md) | Daftar periksa konfigurasi `.env`, requirement PHP 8.4, ekstensi wajib, dan isolasi rahasia server. |
| 🗄️ [**database-readiness.md**](./database-readiness.md) | Audit skema 47 migrasi, integritas foreign key `RESTRICT`, strategi indexing, dan isolasi transaksi. |
| 💾 [**backup-restore.md**](./backup-restore.md) | Prosedur formal pencadangan harian terkompresi, verifikasi backup, dan simulasi pemulihan di staging. |
| 📁 [**storage.md**](./storage.md) | Konfigurasi direktori `storage/app/public`, permissions `www-data` (775), dan pembuatan symbolic link. |
| 🚀 [**deployment-runbook.md**](./deployment-runbook.md) | Panduan operasional langkah-demi-langkah pra-deploy, deploy, optimasi cache, dan pasca-deploy. |
| 🔄 [**rollback-plan.md**](./rollback-plan.md) | Rencana pemulihan darurat jika deployment gagal, rollback rilis aplikasi, dan isolasi basis data. |
| 📊 [**monitoring.md**](./monitoring.md) | Panduan pemantauan log Laravel, PHP-FPM, web server Nginx, metrik database, dan kesehatan API. |
| 🛡️ [**disaster-recovery.md**](./disaster-recovery.md) | Kebijakan penanganan bencana, retensi backup berkala, pemulihan darurat, dan prioritas layanan. |
| 🔒 [**security-checklist.md**](./security-checklist.md) | Audit keamanan akhir: proteksi CSRF, XSS, RBAC Spatie, rate limiting, dan nonaktifkan debug mode. |
| 🧪 [**smoke-test.md**](./smoke-test.md) | Matriks pengujian verifikasi cepat 15 fitur kunci sistem pasca-deployment di server target. |
| 📑 [**F12-report.md**](./F12-report.md) | Laporan komprehensif status kesiapan deployment sistem menuju lingkungan produksi. |

---

## ⚠️ Aturan Keselamatan Operasional (Safety Protocol)

Seluruh aktivitas deployment dan pemeliharaan server produksi wajib mematuhi standar keselamatan:
1. **Zero Database Destructive Action:** Dilarang menjalankan `migrate:fresh`, `migrate:refresh`, `db:wipe`, atau perintah drop/truncate pada database produksi.
2. **Backup Before Mutation:** Sebelum mengeksekusi script update kode atau migrasi skema baru, backup basis data wajib dibuat dan diverifikasi terlebih dahulu.
3. **Database Rollback as Last Resort:** Pemulihan database hanya dilakukan jika terjadi kerusakan fatal struktur data, guna melindungi integritas catatan transaksi nelayan yang telah berjalan.
4. **Zero Plaintext Secrets:** Kredensial database, enkripsi `APP_KEY`, dan token API tidak boleh disimpan di repositori Git atau dicetak dalam laporan publik.
