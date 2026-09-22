# Security Checklist & Hardening Audit
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F12 — Security Checklist
Document Type: Production Hardening & Compliance
Date: 21 September 2026
```

---

## 1. Application-Level Security Audit

| Item Pemeriksaan | Standar Wajib Produksi | Status Audit | Keterangan |
| :--- | :--- | :---: | :--- |
| **Mode Debug (`APP_DEBUG`)** | `APP_DEBUG=false` | ✅ **PASS** | Mencegah kebocoran trace/query SQL ke pengguna umum. |
| **Kunci Enkripsi (`APP_KEY`)** | 32-Byte Base64 String | ✅ **PASS** | Kunci enkripsi sesi dan token terkonfigurasi. |
| **Proteksi CSRF** | Middleware `VerifyCsrfToken` aktif | ✅ **PASS** | Seluruh form mutasi menyertakan `@csrf`. |
| **Sanitasi Output (XSS)** | Blade kurung kurawal ganda `{{ }}` | ✅ **PASS** | Seluruh output pengguna di-escape otomatis. |
| **Otorisasi Peran (RBAC)** | Spatie Laravel Permission aktif | ✅ **PASS** | 5 Role terpisah (Super Admin, Admin DKP, Petugas, Verifikator, Viewer). |
| **Pembatasan Laju (Rate Limit)** | `throttle:60,1` pada API publik | ✅ **PASS** | Endpoint `/gis/data` dilindungi rate limiter. |
| **Keamanan Sesi & Cookie** | `secure=true`, `http_only=true` | ✅ **PASS** | Mencegah pencurian cookie via skrip klien. |
| **Isolasi Rahasia (.env)** | Berkas `.env` masuk `.gitignore` | ✅ **PASS** | Kredensial tidak pernah di-commit ke Git. |

---

## 2. Web Server Hardening & Security Headers (Nginx)

Pastikan konfigurasi blok server Nginx mengaktifkan header keamanan standar:

```nginx
# Security Headers
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "no-referrer-when-downgrade" always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

# Sembunyikan Versi Web Server & PHP
server_tokens off;
fastcgi_hide_header X-Powered-By;

# Larang Akses ke Berkas Tersembunyi (.env, .git)
location ~ /\.(?!well-known).* {
    deny all;
}
```
