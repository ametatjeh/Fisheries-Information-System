# Security Architecture & Policies

**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Version: 1.0.0
Documentation Version: 1.0.0
Date: 21 September 2026
Status: Final Approved (F9 Audit Compliant)
```

---

## 1. Authentication & Session Management

* **Guard & Provider:** Aplikasi menggunakan autentikasi standar Laravel berbasis sesi web (`web` guard) dengan `EloquentUserProvider`.
* **Password Hashing:** Menggunakan algoritma **Bcrypt** dengan cost factor 12 atau **Argon2id**. Password dalam plaintext tidak pernah disimpan di database atau dicatat ke log.
* **Single Sign-On (SSO):** Mendukung Google OAuth2 (`Laravel Socialite`) untuk akun terverifikasi pegawai Dinas Kelautan dan Perikanan (DKP) Aceh.
* **Session Security:**
  * `SESSION_SECURE_COOKIE=true` (Production HTTPS)
  * `SESSION_HTTP_ONLY=true` (Mencegah pencurian cookie via skrip JavaScript)
  * `SESSION_SAME_SITE=lax` (Proteksi terhadap serangan CSRF)

---

## 2. Role-Based Access Control (RBAC)

Otorisasi dikelola menggunakan package **Spatie Laravel Permission** dengan pemisahan peran (*Role*) dan izin (*Permission*) yang ketat:

### Daftar Peran Pengguna (User Roles)

| Peran (Role) | Deskripsi | Hak Akses Utama |
| :--- | :--- | :--- |
| **Super Admin** | Administrator Sistem Utama | Akses penuh ke seluruh modul, manajemen pengguna, konfigurasi sistem, dan audit log. |
| **Admin DKP** | Pejabat Teknis Dinas Kelautan & Perikanan | Manajemen Master Data, Statistik Perikanan, Estimasi Tangkapan, Laporan, dan GIS. |
| **Petugas Lapangan** | Enumerator / Pencatat Pelabuhan (TPI/PPN/PPS) | Input & Edit Data Operasional: Nelayan, Kapal, Fishing Trips, Efforts, Catches, Landings, dan Logbook. |
| **Verifikator** | Petugas Validasi & Mutu Data | Validasi transaksi pelayaran dan pendaratan sebelum masuk perhitungan statistik resmi. |
| **Viewer / Peneliti** | Pengamat / Akademisi / Auditor | Hak akses baca (*read-only*) ke dashboard, statistik, laporan, dan GIS. |

---

## 3. Input Validation & Mass Assignment

* **Form Request Isolation:** Setiap mutasi data (`POST`, `PUT`, `PATCH`) divalidasi secara terisolasi pada kelas Form Request khusus di `app/Http/Requests/`.
* **Cross-Field Validation:** Validasi integritas relasi antar data (e.g., `return_date >= departure_date`, `hauling_date >= setting_date`).
* **Coordinate Range Validation:**
  * Latitude: $-90.0 \le \text{lat} \le 90.0$
  * Longitude: $-180.0 \le \text{lng} \le 180.0$
* **Mass Assignment Protection:** Seluruh Model Eloquent mengonfigurasi `$fillable` secara eksplisit untuk mencegah injeksi kolom terlarang (seperti `is_admin` atau `id`).

---

## 4. Cross-Site Scripting (XSS) & Output Sanitization

* **Blade Escaping:** Seluruh data teks pengguna dirender menggunakan kurung kurawal ganda `{{ $variable }}` yang otomatis menjalankan fungsi `htmlspecialchars()`.
* **Raw HTML Prevention:** Penulisan `{!! $variable !!}` dibatasi ketat dan dilarang untuk data yang berasal dari input pengguna (nama nelayan, catatan trip, nama kapal).
* **GIS Popup Safety:** Seluruh konten popup MapLibre GL JS dibuat menggunakan template DOM ter-escape atau pemetaan properti aman untuk mencegah injeksi skrip berbahaya pada peta.

---

## 5. Cross-Site Request Forgery (CSRF)

* Seluruh form mutasi data dilindungi directive `@csrf` yang menghasilkan token CSRF terenkripsi berbasis sesi.
* Request AJAX internal menyertakan header `X-CSRF-TOKEN` yang diverifikasi oleh middleware `VerifyCsrfToken`.l

---

## 6. API Security & Rate Limiting

* **Internal API Rate Limiting:**
  * Public GIS API (`GET /gis/data`): Dibatasi 60 request per menit per IP (`throttle:60,1`).
  * GFW Gateway API (`/api/gfw/*`): Dibatasi melalui `throttle:gfw-api`.
* **Sanitized JSON Responses:** Endpoint publik `GET /gis/data` secara eksplisit membatasi kolom yang dikembalikan, mengisolasi data pribadi nelayan (NIK, nomor telepon pribadi, alamat rumah).

---

## 7. Environment & Secrets Management

* Kredensial database, `APP_KEY`, dan token API eksternal (Global Fishing Watch API Key) hanya dikonfigurasi melalui berkas `.env` di server.
* Berkas `.env` dimasukkan ke dalam `.gitignore` dan **DILARANG** di-commit ke repositori Git.
* Pada lingkungan production:
  * `APP_DEBUG=false` untuk mencegah kebocoran *stack trace* dan query SQL ke pengguna.
