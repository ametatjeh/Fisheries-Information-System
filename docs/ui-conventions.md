# UI Conventions: Field Placeholder Standard

Standardisasi antarmuka pengguna (UI) untuk penempatan petunjuk pengisian/pencarian (*field guidance*) pada seluruh halaman aplikasi Sistem Perikanan Tangkap Aceh (**IKAN KECIL V1.2**).

---

## 1. Konsep Utama: Field Placeholder Standard

Teks petunjuk yang berfungsi membimbing pengguna dalam pencarian atau pengisian field ditampilkan **di dalam field sebagai placeholder**, bukan sebagai label teks visual duplikat di luar field.

### Ringkasan Aturan:
1. **Text & Search Guidance**: Menggunakan atribut `placeholder="..."` di dalam field `<input>` atau `<textarea>`.
2. **Select & Dropdown**: Menggunakan opsi placeholder pertama bernilai kosong/null: `<option value="">Pilih ...</option>` atau `<option value="">[Nama Field]</option>`.
3. **Placeholder Bukan Data**: Nilai placeholder tidak boleh menjadi nilai filter aktif atau tersimpan ke database.
4. **Hindari Duplikasi Visual**: Jangan menampilkan teks label di luar field yang isinya hanya mengulang teks di dalam placeholder.
5. **Pertahankan Accessibility Semantics**: Label tetap disediakan untuk screen reader dan aksesibilitas HTML menggunakan class `sr-only` atau atribut `aria-label`/`id` yang terhubung.
6. **Prioritas Wording Existing**: Gunakan wording dan terminologi bisnis yang sudah ada pada aplikasi (`Cari ...`, `Pilih ...`, `Masukkan ...`).

---

## 2. Penerapan per Komponen

### A. Input Teks & Pencarian
* **Pola Benar**:
  ```html
  <label for="search_box" class="sr-only">Cari Data</label>
  <div class="relative">
      <input type="text" id="search_box" name="search" placeholder="Cari Data..." class="...">
      <span class="absolute left-3 top-2.5 text-slate-400">🔍</span>
  </div>
  ```
* **Hindari**:
  ```html
  <!-- Duplikasi visual label dan placeholder -->
  <label>Cari Data</label>
  <input type="text" placeholder="Cari Data">
  ```

### B. Select / Dropdown
* **Pola Benar**:
  ```html
  <label for="filter_select" class="sr-only">Pilih Status</label>
  <select id="filter_select" name="status" class="...">
      <option value="">Pilih Status</option>
      <option value="1">Aktif</option>
      <option value="0">Nonaktif</option>
  </select>
  ```
* **Kriteria Option Placeholder**:
  - `value=""` (string kosong atau null).
  - Tidak terhitung sebagai filter aktif dalam query backend.

### C. Input Tanggal (Date) & Angka (Number)
* **Date**: Gunakan kontrol native date dengan label tersembunyi secara visual (`sr-only`) dan `title="..."` untuk membantu browser tooltip.
* **Number**: Gunakan placeholder contoh nilai numerik (contoh: `placeholder="Contoh: 1250.50"`), pertahankan `type="number"`, satuan, dan validasi min/step.

---

## 3. Halaman yang Telah Menerapkan Standar

* `/master/species` (Halaman acuan/referensi)
* `/logbooks` (Card ke-3 toolbar pencarian & filter)
* `/efforts` (Toolbar pencarian & filter alat tangkap serta trip)
* `/catches` (Toolbar pencarian & filter status tangkapan serta kelompok ikan)
* `/landings` (Toolbar pencarian & filter pelabuhan/TPI serta tanggal)
* `/analysis/sampling` (Toolbar filter, search, batch sampel, dan rencana program)
* `/analysis/estimations` (Toolbar filter dan parameter estimasi tangkapan)
* `/analysis/statistics` (Filter engine analitik statistik perikanan)
* `/reports` (Toolbar filter parameter laporan dan rekapitulasi data)
* `/dashboard/gis` (Toolbar filter spasial dan analisis koordinat)

Semua implementasi halaman baru berikutnya wajib mematuhi standar ini.
