<?php

namespace Database\Seeders;

use App\Models\FishingTrip;
use App\Models\User;
use App\Models\ValidationLog;
use Illuminate\Database\Seeder;

class ValidationLogSeeder extends Seeder
{
    /**
     * Seed master data audit trail validasi data trip operasional kapal.
     * Idempoten: aman dijalankan berulang kali.
     */
    public function run(): void
    {
        $admin = User::first();

        if (! $admin) {
            return;
        }

        $auditLogs = [
            // TRIP 1: KM Inka Mina 704
            [
                'trip_number' => 'TRIP-202609-0001',
                'from_status' => 'draft',
                'to_status' => 'submitted',
                'notes' => 'Enumerator lapangan melengkapi data logbook harian kapal, rincian 2 setting pukat cincin, dan estimasi hasil tangkapan tuna cakalang.',
                'created_at' => '2026-08-30 17:00:00',
            ],
            [
                'trip_number' => 'TRIP-202609-0001',
                'from_status' => 'submitted',
                'to_status' => 'validated',
                'notes' => 'Data diverifikasi pengawas perikanan: Dokumen logbook lengkap (7 entri), titik GPS di WPPNRI 572 valid, rekonsiliasi berat tangkapan (4.970 kg) sesuai 100% dengan timbangan manifest pendaratan PPS Lampulo.',
                'created_at' => '2026-08-30 18:30:00',
            ],

            // TRIP 2: KM Lampulo Jaya Mandiri
            [
                'trip_number' => 'TRIP-202609-0002',
                'from_status' => 'draft',
                'to_status' => 'submitted',
                'notes' => 'Pengajuan data trip pelagis kecil Pulo Aceh setelah selesai pembongkaran di TPI Lampulo.',
                'created_at' => '2026-09-03 18:30:00',
            ],
            [
                'trip_number' => 'TRIP-202609-0002',
                'from_status' => 'submitted',
                'to_status' => 'validated',
                'notes' => 'Verifikasi data disetujui: Komposisi jenis ikan (tongkol krai, kembung, selar, cumi) akurat, BBM 850L dan es 2 ton wajar untuk 3 hari melaut.',
                'created_at' => '2026-09-03 19:15:00',
            ],

            // TRIP 3: KM Malaka Rayeuk
            [
                'trip_number' => 'TRIP-202609-0003',
                'from_status' => 'draft',
                'to_status' => 'submitted',
                'notes' => 'Data trip drift gillnet Lhokseumawe diajukan untuk verifikasi mutu.',
                'created_at' => '2026-09-04 16:30:00',
            ],
            [
                'trip_number' => 'TRIP-202609-0003',
                'from_status' => 'submitted',
                'to_status' => 'validated',
                'notes' => 'Disetujui: Spesimen tenggiri batang dan tongkol komo tercatat 1.020 kg sesuai nota lelang PPI Pusong.',
                'created_at' => '2026-09-04 17:00:00',
            ],

            // TRIP 4: KM Selat Malaka 02
            [
                'trip_number' => 'TRIP-202609-0004',
                'from_status' => 'draft',
                'to_status' => 'submitted',
                'notes' => 'Pengajuan verifikasi trip penangkapan teri dan layang di PPN Idi.',
                'created_at' => '2026-09-04 18:00:00',
            ],
            [
                'trip_number' => 'TRIP-202609-0004',
                'from_status' => 'submitted',
                'to_status' => 'validated',
                'notes' => 'Validasi tuntas: Alokasi 12 ABK dan konsumsi logistik telah dikonfirmasi nahkoda.',
                'created_at' => '2026-09-04 18:45:00',
            ],

            // TRIP 5: KM Meulaboh Bahari
            [
                'trip_number' => 'TRIP-202609-0005',
                'from_status' => 'draft',
                'to_status' => 'submitted',
                'notes' => 'Data trip pantai barat Aceh selesai dibongkar di PPI Ujong Baroh.',
                'created_at' => '2026-09-06 16:30:00',
            ],
            [
                'trip_number' => 'TRIP-202609-0005',
                'from_status' => 'submitted',
                'to_status' => 'validated',
                'notes' => 'Tervalidasi: Tangkapan kakap merah dan kerapu batu 860 kg terverifikasi, catatan logbook badai ombak 2.8m telah diperiksa.',
                'created_at' => '2026-09-06 17:15:00',
            ],

            // TRIP 6: KM Pulo Breueh Mandiri (Status: submitted)
            [
                'trip_number' => 'TRIP-202609-0006',
                'from_status' => 'draft',
                'to_status' => 'submitted',
                'notes' => 'Menunggu verifikasi pengawas perikanan PPI Ulee Lheue untuk hasil pancing tonda 670 kg.',
                'created_at' => '2026-09-06 09:00:00',
            ],

            // TRIP 7: PMT Ulee Lheue Samudera (Status: submitted)
            [
                'trip_number' => 'TRIP-202609-0007',
                'from_status' => 'draft',
                'to_status' => 'submitted',
                'notes' => 'Pengajuan verifikasi one-day fishing nelayan perahu motor tempel.',
                'created_at' => '2026-09-06 18:00:00',
            ],
        ];

        foreach ($auditLogs as $log) {
            $trip = FishingTrip::where('trip_number', $log['trip_number'])->first();

            if (! $trip) {
                continue;
            }

            ValidationLog::firstOrCreate(
                [
                    'fishing_trip_id' => $trip->id,
                    'from_status' => $log['from_status'],
                    'to_status' => $log['to_status'],
                    'created_at' => $log['created_at'],
                ],
                [
                    'validator_id' => $admin->id,
                    'notes' => $log['notes'],
                    'updated_at' => $log['created_at'],
                ]
            );
        }
    }
}
