# Sistem Perikanan

Sistem Perikanan adalah aplikasi manajemen data dan statistik perikanan komprehensif yang dikembangkan dengan Laravel 13. Sistem ini mematuhi standar internasional FAO ASFIS 2026.1 untuk pendataan spesies.

## 🌊 App Flow & Arsitektur Modul

Aplikasi ini dirancang untuk mencatat seluruh siklus perikanan dari hulu (penangkapan) hingga hilir (estimasi dan pelaporan statistik).

```mermaid
graph TD
    %% Define Styling
    classDef master fill:#f3f4f6,stroke:#4b5563,stroke-width:2px,color:#1f2937;
    classDef trans fill:#e0f2fe,stroke:#0284c7,stroke-width:2px,color:#0c4a6e;
    classDef stat fill:#dcfce7,stroke:#16a34a,stroke-width:2px,color:#14532d;
    classDef report fill:#fef3c7,stroke:#d97706,stroke-width:2px,color:#78350f;

    %% Master Data
    subgraph MasterData ["Master Data & Referensi"]
        S[Species<br>FAO ASFIS 2026.1]:::master
        F[Fishing Gear<br>Alat Tangkap]:::master
        V[Vessel<br>Armada Kapal]:::master
        P[Fishing Port<br>Pelabuhan/TPI]:::master
    end

    %% Transaksi Hulu
    subgraph DataCollection ["Data Collection (Hulu)"]
        LC[Logbook Catch<br>Hasil Tangkapan]:::trans
        LI[Landing Item<br>Data Pendaratan]:::trans
        SP[Sampling Plan<br>Rencana Sampling]:::trans
    end

    %% Transaksi Lanjutan & Analisis
    subgraph Analysis ["Data Analysis & Bio (Tengah)"]
        BM[Biological Measurement<br>Pengukuran Biologi]:::trans
        CE[Catch Estimation<br>Estimasi Tangkapan]:::stat
        MS[Monthly Statistics<br>Produksi Bulanan]:::stat
    end

    %% Pelaporan (Hilir)
    subgraph Output ["Reporting & Dashboard (Hilir)"]
        R1[Laporan Produksi]:::report
        R2[Analisis Tren Spesies]:::report
        R3[Distribusi Alat Tangkap]:::report
    end

    %% Relasi
    S --> LC
    S --> LI
    S --> SP
    F --> LC
    V --> LC
    P --> LI

    LC --> CE
    LI --> CE
    SP --> BM
    BM --> CE

    CE --> MS
    MS --> R1
    MS --> R2
    MS --> R3
```

### Penjelasan Modul:

1. **Master Data**: Pusat referensi data (Species, Gear, Vessel, Port). Spesies menggunakan standar global FAO ASFIS yang dapat di-import secara otomatis via console.
2. **Data Collection**: Pencatatan data primer di lapangan seperti Logbook (tangkapan nelayan), Data Pendaratan (di pelabuhan), dan Rencana Sampling.
3. **Analysis & Biologi**: Pengolahan data primer menjadi ukuran biologis ikan dan perhitungan estimasi tangkapan yang lebih representatif.
4. **Output & Laporan**: Agregasi data menjadi statistik bulanan dan pembuatan berbagai laporan manajerial.

## 🚀 Fitur Utama

- **Integrasi ASFIS**: Import otomatis data 13,000+ spesies dunia dengan standar ASFIS 2026.1.
- **SSO Login**: Dukungan login cepat melalui integrasi Google Workspace / Gmail (OAuth2).
- **Responsive UI/UX**: Antarmuka Tailwind CSS yang modern, mendukung dark/light mode dan mudah digunakan via perangkat mobile di lapangan.
- **Sistem Export/Import (Excel)**: Mendukung pengolahan massal (batch upload) via file `.xlsx` dan `.csv`.

## 🛠️ Instalasi & Persiapan Lingkungan

Sistem ini membutuhkan:
- **PHP** 8.4+
- **Laravel** 13+
- **MySQL** / MariaDB
- **Node.js** 22+

### Langkah-langkah:
1. Clone repositori ini.
2. Salin `.env.example` ke `.env` dan konfigurasikan koneksi database Anda.
3. Jalankan `composer install` dan `npm install`.
4. Generate app key: `php artisan key:generate`.
5. Jalankan migrasi: `php artisan migrate --seed`.
6. Import Master Data ASFIS: `php artisan asfis:import`.
7. Compile aset frontend: `npm run build`.
8. Mulai server: `php artisan serve`.

## 🤝 Lisensi

Aplikasi ini merupakan perangkat lunak hak milik tertutup (Proprietary). Hubungi Administrator Sistem untuk akses.
