<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Resmi Perikanan Tangkap - {{ ucfirst($type) }}</title>
    <link rel="icon" type="image/png" href="{{ asset('Logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #1e293b;
            background-color: #f8fafc;
            line-height: 1.5;
            font-size: 12px;
        }

        .container {
            max-width: 1080px;
            margin: 0 auto;
            padding: 24px;
            background: #ffffff;
            min-height: 100vh;
        }

        /* Top Action Bar (hidden on print) */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #0f172a;
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            margin-bottom: 24px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: 0.15s ease;
        }

        .btn-primary {
            background-color: #0284c7;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0369a1;
        }

        .btn-secondary {
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
        }

        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.25);
        }

        /* Official Header / Kop Surat */
        .kop-header {
            display: flex;
            align-items: center;
            gap: 16px;
            padding-bottom: 16px;
            border-bottom: 3px double #0f172a;
            margin-bottom: 20px;
        }

        .kop-logo {
            font-size: 38px;
            width: 64px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
        }

        .kop-text {
            flex: 1;
            text-align: center;
        }

        .kop-instansi {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #0f172a;
            text-transform: uppercase;
        }

        .kop-subinstansi {
            font-size: 16px;
            font-weight: 800;
            color: #0369a1;
            text-transform: uppercase;
        }

        .kop-app {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Document Title */
        .doc-title {
            text-align: center;
            margin-bottom: 16px;
        }

        .doc-title h2 {
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            color: #0f172a;
        }

        .doc-title p {
            font-size: 11px;
            color: #64748b;
        }

        /* Metadata & KPI */
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 16px;
            font-size: 11px;
        }

        .meta-item {
            display: flex;
            gap: 6px;
        }

        .meta-label {
            font-weight: 600;
            color: #475569;
            min-width: 110px;
        }

        .meta-value {
            color: #0f172a;
            font-weight: 500;
        }

        .kpi-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .kpi-box {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
        }

        .kpi-title {
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
        }

        .kpi-val {
            font-size: 15px;
            font-weight: 700;
            font-family: monospace;
            color: #0369a1;
            margin-top: 2px;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 24px;
        }

        th,
        td {
            padding: 7px 9px;
            border: 1px solid #cbd5e1;
        }

        th {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: 700;
            text-align: left;
            text-transform: uppercase;
            font-size: 10px;
        }

        td.text-center {
            text-align: center;
        }

        td.text-right {
            text-align: right;
        }

        td.font-mono {
            font-family: monospace;
        }

        tr:nth-child(even) {
            background: #fafafa;
        }

        /* Signatures */
        .sign-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 36px;
            padding: 0 40px;
            page-break-inside: avoid;
        }

        .sign-box {
            text-align: center;
            width: 220px;
            font-size: 11px;
        }

        .sign-space {
            height: 64px;
        }

        .sign-name {
            font-weight: 700;
            text-decoration: underline;
            color: #0f172a;
        }

        .sign-nip {
            color: #64748b;
            font-size: 10px;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: white;
                color: black;
            }

            .container {
                max-width: 100%;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            th {
                background: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .kpi-box {
                background: #f8fafc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                size: A4 portrait;
                margin: 1.5cm 1cm;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        {{-- Action bar (Hidden on Print) --}}
        <div class="action-bar no-print">
            <div>
                <strong>Mode Pratinjau Dokumen Cetak</strong>
                <span style="opacity: 0.75; font-size: 11px; margin-left: 8px;">(Gunakan opsi Save as PDF pada dialog cetak)</span>
            </div>
            <div style="display: flex; gap: 8px;">
                <button onclick="window.print()" class="btn btn-primary">
                    🖨️ Cetak / Simpan PDF
                </button>
                <a href="{{ route('reports.index', request()->query()) }}" class="btn btn-secondary">
                    ← Kembali
                </a>
            </div>
        </div>

        {{-- Kop Laporan Organisasi --}}
        <div class="kop-header">
            <div class="kop-logo">
                <img src="{{ $currentOrganization?->logo_url ?? asset('Logo.png') }}" alt="Logo" style="width: 48px; height: 48px; object-fit: contain;">
            </div>
            <div class="kop-text">
                <div class="kop-subinstansi">{{ $currentOrganization?->organization_name ?? 'Sistem Informasi & Statistik Perikanan Tangkap' }}</div>
                <div class="kop-app">Pangkalan Data Terintegrasi Hasil Tangkapan, Upaya, Pendaratan, & Biologi Ikan</div>
            </div>
        </div>

        {{-- Judul Laporan --}}
        <div class="doc-title">
            <h2>
                @if($type === 'efforts')
                Laporan Upaya Penangkapan Ikan & Trip Operasi Laut (Fishing Efforts)
                @elseif($type === 'landings')
                Laporan Pendaratan Ikan & Nilai Transaksi Pelelangan (Landings & Revenue)
                @elseif($type === 'sampling')
                Laporan Pengukuran Biologi & Morfometrik Ikan (Biological Sampling)
                @elseif($type === 'monthly')
                Laporan Rekapitulasi Statistik Produksi Bulanan Perikanan Tangkap
                @elseif($type === 'production')
                Laporan Produksi Terpadu Multi-Dimensi (Observed, Landed, & Estimated)
                @elseif($type === 'statistics')
                Laporan Statistik Perikanan Tangkap & Laju Tangkap (CPUE)
                @elseif($type === 'summary')
                Laporan Ringkasan Eksekutif & Indikator Kinerja Utama Perikanan
                @else
                Laporan Hasil Tangkapan Ikan (Fish Catches & Species Logbook)
                @endif
            </h2>
            <p>Waktu Cetak Dokumen: {{ $printedAt }} WIB</p>
        </div>

        {{-- Metadata Filter --}}
        <div class="meta-grid">
            <div class="meta-item">
                <span class="meta-label">Periode Waktu:</span>
                <span class="meta-value">
                    @if($type === 'monthly')
                    Tahun {{ $filters['year'] }} {{ $filters['month'] ? '(Bulan ke-' . $filters['month'] . ')' : '(Semua Bulan)' }}
                    @else
                    {{ $filters['start_date'] ?: 'Semua' }} s/d {{ $filters['end_date'] ?: 'Hari ini' }}
                    @endif
                </span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Pelabuhan / TPI:</span>
                <span class="meta-value">{{ $selectedLandingSite?->name ?? 'Semua Pangkalan / TPI' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Alat Tangkap:</span>
                <span class="meta-value">{{ $selectedGear?->name ?? 'Semua Alat Tangkap' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Wilayah WPP:</span>
                <span class="meta-value">{{ $selectedWpp?->name ?? 'Semua Wilayah WPP' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Komoditas Ikan:</span>
                <span class="meta-value">{{ $selectedSpecies?->local_name_id ?? $selectedSpecies?->english_name ?? $selectedSpecies?->scientific_name ?? 'Semua Jenis Ikan' }}</span>
            </div>
        </div>

        {{-- KPI Ringkasan Cetak --}}
        <div class="kpi-row">
            @if($type === 'efforts')
            <div class="kpi-box">
                <div class="kpi-title">Total Siklus Setting</div>
                <div class="kpi-val">{{ number_format($totalSettings) }} kali</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Total Durasi Operasi</div>
                <div class="kpi-val">{{ number_format($totalDurationHours, 1) }} Jam</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Rata-rata Setting</div>
                <div class="kpi-val">{{ $avgDurationHours }} Jam</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Total Rekaman</div>
                <div class="kpi-val">{{ number_format($totalRecords) }} baris</div>
            </div>
            @elseif($type === 'landings')
            <div class="kpi-box">
                <div class="kpi-title">Total Volume Pendaratan</div>
                <div class="kpi-val">{{ number_format($totalWeightTon, 2, ',', '.') }} Ton</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Total Nilai Omzet Lelang</div>
                <div class="kpi-val">Rp {{ number_format($totalValueRp, 0, ',', '.') }}</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Rata-rata Harga / Kg</div>
                <div class="kpi-val">Rp {{ number_format($avgPricePerKg, 0, ',', '.') }}</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Jumlah Pendaratan</div>
                <div class="kpi-val">{{ number_format($totalRecords) }} manifest</div>
            </div>
            @elseif($type === 'sampling')
            <div class="kpi-box">
                <div class="kpi-title">Spesimen Terukur</div>
                <div class="kpi-val">{{ number_format($totalSpecimens) }} ekor</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Rerata Panjang Total</div>
                <div class="kpi-val">{{ $avgTotalLengthCm }} cm</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Rerata Bobot Ikan</div>
                <div class="kpi-val">{{ $avgWeightGram }} gram</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Rentang Ukuran</div>
                <div class="kpi-val">{{ $minTotalLengthCm }}-{{ $maxTotalLengthCm }} cm</div>
            </div>
            @elseif($type === 'monthly')
            <div class="kpi-box">
                <div class="kpi-title">Total Volume Produksi</div>
                <div class="kpi-val">{{ number_format($totalVolumeTon, 2, ',', '.') }} Ton</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Total Nilai Produksi</div>
                <div class="kpi-val">Rp {{ number_format($totalValueRp, 0, ',', '.') }}</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Total Trip Operasi</div>
                <div class="kpi-val">{{ number_format($totalTrips) }} Trip</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">CPUE Rata-Rata</div>
                <div class="kpi-val">{{ $avgCpue }} kg/trip</div>
            </div>
            @elseif($type === 'production')
            <div class="kpi-box">
                <div class="kpi-title">Volume Pendaratan</div>
                <div class="kpi-val">{{ number_format(($totalLandedKg ?? 0) / 1000, 2, ',', '.') }} Ton</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Tangkapan Teramati</div>
                <div class="kpi-val">{{ number_format(($totalObservedKg ?? 0) / 1000, 2, ',', '.') }} Ton</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Estimasi Produksi</div>
                <div class="kpi-val">{{ number_format(($totalEstimatedKg ?? 0) / 1000, 2, ',', '.') }} Ton</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Total Produksi</div>
                <div class="kpi-val">{{ number_format(($totalProductionKg ?? 0) / 1000, 2, ',', '.') }} Ton</div>
            </div>
            @elseif($type === 'statistics')
            <div class="kpi-box">
                <div class="kpi-title">Total Upaya (Trips)</div>
                <div class="kpi-val">{{ number_format($totalTrips ?? 0) }} Trip</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Jam Operasi</div>
                <div class="kpi-val">{{ number_format($totalEffortHours ?? 0, 1) }} Jam</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Tangkapan Teramati</div>
                <div class="kpi-val">{{ number_format(($totalCatchKg ?? 0) / 1000, 2, ',', '.') }} Ton</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">CPUE Rata-rata</div>
                <div class="kpi-val">{{ number_format($cpuePerHour ?? 0, 2, ',', '.') }} kg/jam</div>
            </div>
            @elseif($type === 'summary')
            <div class="kpi-box">
                <div class="kpi-title">Total Pelayaran</div>
                <div class="kpi-val">{{ number_format($summaryKpis['total_trips'] ?? 0) }} Trip</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Total Tangkapan</div>
                <div class="kpi-val">{{ number_format(($summaryKpis['total_catch_kg'] ?? 0) / 1000, 2, ',', '.') }} Ton</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Total Pendaratan</div>
                <div class="kpi-val">{{ number_format(($summaryKpis['total_landing_kg'] ?? 0) / 1000, 2, ',', '.') }} Ton</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">CPUE Rerata</div>
                <div class="kpi-val">{{ number_format($summaryKpis['cpue_kg_hour'] ?? 0, 2, ',', '.') }} kg/jam</div>
            </div>
            @else
            <div class="kpi-box">
                <div class="kpi-title">Total Volume Tangkapan</div>
                <div class="kpi-val">{{ number_format($totalWeightTon, 2, ',', '.') }} Ton</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Total Berat (Kg)</div>
                <div class="kpi-val">{{ number_format($totalWeightKg, 0, ',', '.') }} kg</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Total Jumlah Ekor</div>
                <div class="kpi-val">{{ number_format($totalFishCount) }} ekor</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-title">Total Catatan Tangkapan</div>
                <div class="kpi-val">{{ number_format($totalRecords) }} baris</div>
            </div>
            @endif
        </div>

        {{-- Data Table --}}
        @if($type === 'efforts')
        <table>
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">No</th>
                    <th>Kode Trip</th>
                    <th>Kapal</th>
                    <th>Pelabuhan Pangkalan</th>
                    <th>Alat Tangkap</th>
                    <th style="text-align: center;">Set #</th>
                    <th>Waktu Setting</th>
                    <th style="text-align: right;">Durasi (Jam)</th>
                    <th>Koordinat</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $index => $row)
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td class="font-mono">{{ $row->fishingTrip?->trip_number ?? '-' }}</td>
                    <td>{{ $row->fishingTrip?->vessel?->name ?? '-' }}</td>
                    <td>{{ $row->fishingTrip?->landingSite?->name ?? '-' }}</td>
                    <td>{{ $row->fishingGear?->name ?? '-' }}</td>
                    <td class="text-center font-mono">{{ $row->setting_number ?? 1 }}</td>
                    <td class="font-mono">{{ $row->setting_date ? $row->setting_date->format('d/m/Y H:i') : '-' }}</td>
                    <td class="text-right font-mono">{{ number_format($row->duration_hours, 1) }}</td>
                    <td class="font-mono" style="font-size: 10px;">
                        {{ $row->latitude_setting ? "{$row->latitude_setting}, {$row->longitude_setting}" : '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px;">Tidak ada data rekaman</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @elseif($type === 'landings')
        <table>
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">No</th>
                    <th>No. Pendaratan</th>
                    <th>Tgl Pendaratan</th>
                    <th>Pelabuhan / TPI</th>
                    <th>Kapal Asal</th>
                    <th style="text-align: right;">Volume (kg)</th>
                    <th style="text-align: right;">Nilai Omzet (Rp)</th>
                    <th style="text-align: center;">Pembeli</th>
                    <th>Petugas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $index => $row)
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td class="font-mono">{{ $row->landing_number }}</td>
                    <td class="font-mono">{{ $row->landing_date ? $row->landing_date->format('d/m/Y') : '-' }}</td>
                    <td>{{ $row->landingSite?->name ?? '-' }}</td>
                    <td>{{ $row->fishingTrip?->vessel?->name ?? '-' }}</td>
                    <td class="text-right font-mono">{{ number_format($row->total_weight_kg, 1, ',', '.') }}</td>
                    <td class="text-right font-mono">Rp {{ number_format($row->total_value_rp, 0, ',', '.') }}</td>
                    <td class="text-center font-mono">{{ $row->buyer_count ?? 0 }}</td>
                    <td>{{ $row->recordedBy?->name ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px;">Tidak ada data rekaman</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @elseif($type === 'sampling')
        <table>
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">No</th>
                    <th>Kode Sampel</th>
                    <th>Tgl Sampling</th>
                    <th>Lokasi TPI</th>
                    <th>Jenis Ikan</th>
                    <th style="text-align: right;">Panjang (cm)</th>
                    <th style="text-align: right;">Bobot (g)</th>
                    <th style="text-align: center;">Kelamin</th>
                    <th style="text-align: center;">TKG</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $index => $row)
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td class="font-mono">{{ $row->sample?->sample_code ?? '-' }}</td>
                    <td class="font-mono">{{ $row->sample?->sample_date ? $row->sample->sample_date->format('d/m/Y') : '-' }}</td>
                    <td>{{ $row->sample?->landingSite?->name ?? '-' }}</td>
                    <td>{{ $row->fishSpecies?->local_name_id ?? $row->fishSpecies?->english_name ?? $row->fishSpecies?->scientific_name ?? '-' }}</td>
                    <td class="text-right font-mono">{{ number_format($row->total_length_cm, 1) }}</td>
                    <td class="text-right font-mono">{{ number_format($row->weight_gram, 1) }}</td>
                    <td class="text-center">{{ $row->sex ?? '-' }}</td>
                    <td class="text-center font-mono">{{ $row->gonad_maturity_stage ? 'TKG ' . $row->gonad_maturity_stage : '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px;">Tidak ada data rekaman</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @elseif($type === 'monthly')
        <table>
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">No</th>
                    <th>Periode</th>
                    <th>Pelabuhan / Pangkalan</th>
                    <th>Wilayah WPP</th>
                    <th>Alat Tangkap</th>
                    <th>Jenis Ikan</th>
                    <th style="text-align: right;">Volume (kg)</th>
                    <th style="text-align: right;">Nilai Produksi (Rp)</th>
                    <th style="text-align: right;">Harga Rata-Rata</th>
                    <th style="text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $index => $row)
                @php
                    $vol = (float) ($row->volume_kg ?? $row->total_volume_kg ?? 0);
                    $val = (float) ($row->value_rp ?? $row->total_value_rp ?? 0);
                    $avg = $vol > 0 ? round($val / $vol, 2) : 0;
                @endphp
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td class="font-mono">{{ $row->year }} - Bln {{ str_pad($row->month, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $row->landing_site_name ?? $row->landingSite?->name ?? '-' }}</td>
                    <td>{{ $row->wpp_name ?? '-' }}</td>
                    <td>{{ $row->gear_name ?? $row->fishingGear?->name ?? '-' }}</td>
                    <td>{{ $row->species_name ?? $row->fishSpecies?->local_name_id ?? $row->fishSpecies?->indonesian_name ?? '-' }}</td>
                    <td class="text-right font-mono">{{ number_format($vol, 1, ',', '.') }}</td>
                    <td class="text-right font-mono">Rp {{ number_format($val, 0, ',', '.') }}</td>
                    <td class="text-right font-mono">Rp {{ number_format($avg, 0, ',', '.') }}</td>
                    <td class="text-center font-mono">{{ ucfirst($row->status ?? 'validated') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 20px;">Tidak ada data rekaman</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @elseif($type === 'production')
        <table>
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">No</th>
                    <th>Periode</th>
                    <th>Pelabuhan / Pangkalan</th>
                    <th>Wilayah WPP</th>
                    <th>Alat Tangkap</th>
                    <th>Jenis Ikan</th>
                    <th style="text-align: right;">Volume (kg)</th>
                    <th style="text-align: right;">Nilai Produksi (Rp)</th>
                    <th style="text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $index => $row)
                @php
                    $vol = (float) ($row->volume_kg ?? $row->total_volume_kg ?? 0);
                    $val = (float) ($row->value_rp ?? $row->total_value_rp ?? 0);
                @endphp
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td class="font-mono">{{ $row->year }}-{{ str_pad($row->month, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $row->landing_site_name ?? $row->landingSite?->name ?? '-' }}</td>
                    <td>{{ $row->wpp_name ?? '-' }}</td>
                    <td>{{ $row->gear_name ?? $row->fishingGear?->name ?? '-' }}</td>
                    <td>{{ $row->species_name ?? $row->fishSpecies?->local_name_id ?? '-' }}</td>
                    <td class="text-right font-mono">{{ number_format($vol, 1, ',', '.') }}</td>
                    <td class="text-right font-mono">Rp {{ number_format($val, 0, ',', '.') }}</td>
                    <td class="text-center font-mono">{{ ucfirst($row->status ?? 'validated') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px;">Tidak ada data produksi terpadu</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @elseif($type === 'statistics')
        <table>
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">No</th>
                    <th>Alat Tangkap</th>
                    <th style="text-align: right;">Total Tangkapan (kg)</th>
                    <th style="text-align: right;">Upaya Tangkap (Jam)</th>
                    <th style="text-align: right;">CPUE (kg/jam)</th>
                    <th style="text-align: center;">Jumlah Trip</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $index => $row)
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td>{{ $row->gear_name ?? '-' }}</td>
                    <td class="text-right font-mono">{{ number_format($row->total_catch_kg ?? 0, 1, ',', '.') }}</td>
                    <td class="text-right font-mono">{{ number_format($row->total_effort_hours ?? 0, 1) }}</td>
                    <td class="text-right font-mono">{{ number_format($row->cpue ?? 0, 2, ',', '.') }}</td>
                    <td class="text-center font-mono">{{ number_format($row->trips_count ?? 0) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px;">Tidak ada data statistik perikanan</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @elseif($type === 'summary')
        <table>
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">No</th>
                    <th>Indikator Kinerja Utama (KPI)</th>
                    <th style="text-align: right;">Nilai Agregat</th>
                    <th style="text-align: center;">Satuan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $index => $row)
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td>{{ $row->indicator }}</td>
                    <td class="text-right font-mono font-bold">{{ $row->value }}</td>
                    <td class="text-center">{{ $row->unit }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center" style="padding: 20px;">Tidak ada ringkasan KPI</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @else
        <table>
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">No</th>
                    <th>Kode Trip</th>
                    <th>Tgl Berangkat</th>
                    <th>Nama Kapal</th>
                    <th>Nahkoda</th>
                    <th>Pelabuhan</th>
                    <th>Alat Tangkap</th>
                    <th>Jenis Ikan</th>
                    <th style="text-align: right;">Berat (kg)</th>
                    <th style="text-align: center;">Ekor</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $index => $row)
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td class="font-mono">{{ $row->fishingTrip?->trip_number ?? '-' }}</td>
                    <td class="font-mono">{{ $row->fishingTrip?->departure_date ? $row->fishingTrip->departure_date->format('d/m/Y') : '-' }}</td>
                    <td>{{ $row->fishingTrip?->vessel?->name ?? '-' }}</td>
                    <td>{{ $row->fishingTrip?->captain?->name ?? '-' }}</td>
                    <td>{{ $row->fishingTrip?->landingSite?->name ?? '-' }}</td>
                    <td>{{ $row->fishingTrip?->primaryGear?->name ?? '-' }}</td>
                    <td>{{ $row->species?->local_name_id ?? $row->species?->english_name ?? $row->species?->scientific_name ?? '-' }}</td>
                    <td class="text-right font-mono">{{ number_format($row->weight_kg, 1, ',', '.') }}</td>
                    <td class="text-center font-mono">{{ $row->fish_count ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 20px;">Tidak ada data rekaman</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @endif

        {{-- Bagian Tanda Tangan Pengesahan --}}
        <div class="sign-section">
            <div class="sign-box">
                <div>Dibuat & Diverifikasi,</div>
                <div style="font-weight: 600;">Petugas</div>
                <div class="sign-space"></div>
                <div class="sign-name">( {{ auth()->user()->name ?? 'Petugas Pendataan' }} )</div>
                <div class="sign-nip">ID Pengguna: #{{ auth()->user()->id ?? 1 }}</div>
            </div>
        </div>
    </div>
</body>

</html>