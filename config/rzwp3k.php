<?php

return [

    /*
    |--------------------------------------------------------------------------
    | RZWP3K Spatial Data Sources Registry
    |--------------------------------------------------------------------------
    |
    | Registry of spatial data sources for RZWP3K Provinsi Aceh (Qanun No. 1/2020).
    | STRICT RULE: No fictitious URLs or layer names. If direct endpoint is
    | unverified, set values to null and mark verified as false.
    |
    | Supported source types:
    | geojson, geojson_url, wfs, arcgis_feature_service, arcgis_rest, shapefile,
    | wms, legal_document, unknown
    |
    */

    'target_crs' => 'EPSG:4326', // WGS84 [longitude, latitude]

    'storage_path' => 'rzwp3k', // storage/app/rzwp3k

    'sources' => [
        'official_qanun' => [
            'name' => 'RZWP3K Aceh 2020-2040 (Qanun Aceh No. 1/2020)',
            'authority' => 'Pemerintah Aceh / DPRA',
            'legal_basis' => 'Qanun Aceh Nomor 1 Tahun 2020',
            'source_type' => 'legal_document',
            'dataset_url' => 'https://jdih.acehprov.go.id/produk-hukum/detail-peraturan/1897',
            'service_url' => null,
            'layer_name' => null,
            'service_type' => null,
            'format' => 'PDF / Matrix Annex',
            'crs' => 'EPSG:4326',
            'verified' => true,
            'verified_at' => '2026-09-21',
            'verification_notes' => 'Official legislative text verified via JDIH Aceh (Qanun Aceh No. 1 Tahun 2020).',
        ],

        'dkp_aceh_geoportal' => [
            'name' => 'Geoportal DKP Aceh / Satu Data Aceh RZWP3K Vector Layer',
            'authority' => 'Dinas Kelautan dan Perikanan Aceh',
            'legal_basis' => 'Qanun Aceh Nomor 1 Tahun 2020',
            'source_type' => 'geojson_url',
            'dataset_url' => null,
            'service_url' => null,
            'layer_name' => null,
            'service_type' => null,
            'format' => 'GeoJSON / Shapefile',
            'crs' => 'EPSG:4326',
            'verified' => false,
            'verified_at' => null,
            'verification_notes' => 'Machine-readable vector data pending official direct attachment or PPID data access.',
        ],

        'kkp_sigap_wms' => [
            'name' => 'Kementerian Kelautan dan Perikanan - SIGAP Integrasi Ruang Laut',
            'authority' => 'Kementerian Kelautan dan Perikanan RI',
            'legal_basis' => 'Qanun Aceh Nomor 1 Tahun 2020 / Permen KP 23/2016',
            'source_type' => 'wms',
            'dataset_url' => null,
            'service_url' => null,
            'layer_name' => null,
            'service_type' => 'WMS',
            'format' => 'image/png',
            'crs' => 'EPSG:4326',
            'verified' => false,
            'verified_at' => null,
            'verification_notes' => 'Reference raster map layer only. Not to be parsed as master polygon geometry.',
        ],
    ],
];
