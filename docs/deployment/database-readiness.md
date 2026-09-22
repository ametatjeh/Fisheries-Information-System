# Database Production Readiness & Schema Audit
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F12 — Database Readiness
Document Type: Database Architecture & Migration Safety
Date: 21 September 2026
```

---

## 1. Database Schema & Migration Inventory

Sistem Informasi Perikanan Aceh memiliki **47 file migrasi** yang terdaftar dan telah tereksekusi secara konsisten tanpa konflik skema.

```text
[Framework & Infrastructure]
1.  0001_01_01_000000_create_users_table.php
2.  0001_01_01_000001_create_cache_table.php
3.  0001_01_01_000002_create_jobs_table.php
4.  2026_09_08_043506_create_permission_tables.php (Spatie RBAC)
5.  2026_09_17_095342_add_google_id_to_users_table.php

[Master Data & Geografis Aceh]
6.  2026_09_08_100001_create_provinces_table.php
7.  2026_09_08_100002_create_regencies_table.php
8.  2026_09_08_100003_create_districts_table.php
9.  2026_09_08_100004_create_villages_table.php
10. 2026_09_08_100005_create_fish_species_table.php
11. 2026_09_08_100006_create_fishing_gears_table.php
12. 2026_09_08_100007_create_landing_sites_table.php
13. 2026_09_08_100008_create_fisher_groups_table.php
14. 2026_09_08_100009_create_fishers_table.php
15. 2026_09_08_100010_create_vessels_table.php
16. 2026_09_08_110001_create_vessel_types_table.php
17. 2026_09_20_071543_create_fao_fishing_areas_table.php
18. 2026_09_20_071550_create_wppnri_table.php
19. 2026_09_20_071555_create_fishing_grounds_table.php

[Transaksi Operasional Lapangan]
20. 2026_09_08_100011_create_fishing_trips_table.php
21. 2026_09_08_100012_create_logbooks_table.php
22. 2026_09_08_100013_create_fishing_efforts_table.php
23. 2026_09_08_100014_create_catches_table.php
24. 2026_09_08_100015_create_landings_table.php
25. 2026_09_08_100016_create_landing_items_table.php
26. 2026_09_20_071602_add_fishing_ground_id_to_fishing_trips_table.php
27. 2026_09_20_072236_update_fishing_activity_fks_to_restrict.php

[Analisis Statistik & Biologi]
28. 2026_09_08_100017_create_validation_logs_table.php
29. 2026_09_08_100018_create_sampling_plans_table.php
30. 2026_09_08_100019_create_samples_table.php
31. 2026_09_08_100020_create_biological_measurements_table.php
32. 2026_09_08_100021_create_catch_estimations_table.php
33. 2026_09_08_100022_create_monthly_production_statistics_table.php

[Referensi Standar Internasional FAO ASFIS & ISSCFG]
34. 2026_09_17_115806_upgrade_fish_species_to_asfis_species_table.php
35. 2026_09_17_125731_make_local_name_id_nullable_on_species_table.php
36. 2026_09_18_154500_upgrade_fishing_gears_to_isscfg_annex_m.php
37. 2026_09_20_063607_create_reference_imports_table.php
38. 2026_09_20_063612_create_fao_asfis_species_table.php
39. 2026_09_20_063616_add_fao_asfis_species_id_to_species_table.php
40. 2026_09_20_065625_create_fao_isscfg_gears_table.php
41. 2026_09_20_065641_add_fao_isscfg_gear_id_to_fishing_gears_table.php
42. 2026_09_20_065703_change_category_to_varchar_on_fishing_gears_table.php
43. 2026_09_20_070921_create_fao_isscaap_groups_table.php
44. 2026_09_20_071044_add_fao_isscaap_foreign_to_asfis.php

[Satelit Eksternal GFW]
45. 2026_09_20_154337_create_gfw_vessels_table.php
46. 2026_09_20_160000_create_gfw_vessel_activities_table.php
47. 2026_09_20_170000_create_gfw_events_table.php
```

---

## 2. Foreign Key & Data Integrity Strategy

Sistem menerapkan kebijakan relasi foreign key yang aman:
* **`ON DELETE RESTRICT` pada Rantai Operasional Utama:**
  * Penghapusan record Nelayan (`fishers`) tidak dapat dilakukan jika nelayan tersebut masih memiliki riwayat Kapal atau Pelayaran (*Fishing Trip*).
  * Penghapusan Kapal (`vessels`) dicegah jika terdapat histori transaksi pelayaran aktif.
  * Menjamin seluruh data historis statistik perikanan terlindungi dari penghapusan massal atau ketidaksengajaan operator.
* **`ON DELETE CASCADE` Hanya pada Item Turunan Langsung:**
  * Transaksi pendaratan (`landings`) menghapus item rincian timbang (`landing_items`) yang menjadi bagian internal transaksi tersebut.
  * Rencana sampling (`sampling_plans`) menghapus sampel spesimen terkait.

---

## 3. Database Migration Deployment Procedure

Saat melakukan rilis atau pembaruan kode pada server produksi:

```bash
# 1. Pastikan backup database telah selesai dibuat
mysqldump -u sistem_user -p sistem_perikanan_prod > /backups/db/pre_deploy_backup.sql

# 2. Jalankan migrasi tambahan dengan flag non-interaktif
php artisan migrate --force --no-interaction
```

> [!WARNING]
> **Larangan Mutlak Perintah Destruktif:**
> Dilarang keras mengeksekusi `php artisan migrate:fresh`, `php artisan migrate:refresh`, atau `php artisan db:wipe` di server produksi.
