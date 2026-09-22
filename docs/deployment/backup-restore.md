# Database Backup & Restore Plan
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F12 — Backup & Restore Plan
Document Type: Operational Continuity & Disaster Recovery
Date: 21 September 2026
```

---

## 1. Automated Daily Backup Procedure

Pencadangan basis data produksi dijalankan secara otomatis setiap hari menggunakan cron job server pada jam aktivitas terendah (pukul 02:00 WIB).

### Skrip Pencadangan Otomatis (`/usr/local/bin/backup_perikanan.sh`)

```bash
#!/bin/bash
set -e

# Konfigurasi Direktori & Waktu
BACKUP_DIR="/backups/database"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
DB_NAME="sistem_perikanan_prod"
DB_USER="sistem_user"
BACKUP_FILE="${BACKUP_DIR}/${DB_NAME}_${TIMESTAMP}.sql.gz"

mkdir -p ${BACKUP_DIR}

# Eksekusi mysqldump dengan kompresi gzip
# --single-transaction mencegah penguncian tabel InnoDB selama backup
mysqldump --user="${DB_USER}" \
          --single-transaction \
          --quick \
          --routines \
          --triggers \
          "${DB_NAME}" | gzip -9 > "${BACKUP_FILE}"

# Set permission aman (hanya root / backup user)
chmod 600 "${BACKUP_FILE}"

# Rotasi: Hapus backup yang lebih tua dari 30 hari
find ${BACKUP_DIR} -name "${DB_NAME}_*.sql.gz" -type f -mtime +30 -delete

echo "[$(date)] Backup completed successfully: ${BACKUP_FILE}" >> /var/log/db_backup.log
```

---

## 2. Backup Integrity Verification Protocol

File backup tidak boleh hanya dibuat tanpa diuji keutuhannya:

```bash
# 1. Verifikasi integritas kompresi gzip (memastikan berkas tidak korup/terpotong)
gzip -t /backups/database/sistem_perikanan_prod_YYYYMMDD_HHMMSS.sql.gz

# 2. Periksa ukuran berkas (tidak boleh 0 bytes)
ls -lh /backups/database/sistem_perikanan_prod_YYYYMMDD_HHMMSS.sql.gz
```

---

## 3. Safe Restore Test Procedure (Staging Verification)

> [!CRITICAL]
> **Aturan Keselamatan Pemulihan:**
> Uji coba pemulihan data (*restore test*) **DILARANG** dijalankan langsung pada database produksi aktif. Selalu lakukan pemulihan ke basis data pengujian/staging terisolasi (`sistem_perikanan_restore_test`).

### Langkah Uji Pemulihan pada Database Staging

```bash
# 1. Buat database pengujian sementara
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS sistem_perikanan_restore_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Dekompresi dan masukkan data cadangan ke database uji
gunzip < /backups/database/sistem_perikanan_prod_YYYYMMDD_HHMMSS.sql.gz | mysql -u root -p sistem_perikanan_restore_test

# 3. Verifikasi jumlah tabel dan integritas record
mysql -u root -p sistem_perikanan_restore_test -e "
    SELECT 'fishers' AS entity, COUNT(*) AS count FROM fishers
    UNION ALL
    SELECT 'vessels', COUNT(*) FROM vessels
    UNION ALL
    SELECT 'fishing_trips', COUNT(*) FROM fishing_trips
    UNION ALL
    SELECT 'fishing_efforts', COUNT(*) FROM fishing_efforts
    UNION ALL
    SELECT 'catches', COUNT(*) FROM catches
    UNION ALL
    SELECT 'landings', COUNT(*) FROM landings;
"

# 4. Hapus database uji setelah verifikasi selesai
mysql -u root -p -e "DROP DATABASE sistem_perikanan_restore_test;"
```
