<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Global Fishing Watch (GFW) API Base URL
    |--------------------------------------------------------------------------
    |
    | Base URL for accessing the Global Fishing Watch API v3 gateway.
    |
    */
    'base_url' => env('GFW_API_BASE_URL', 'https://gateway.api.globalfishingwatch.org/v3'),

    /*
    |--------------------------------------------------------------------------
    | Global Fishing Watch API Token (Bearer Token)
    |--------------------------------------------------------------------------
    |
    | Personal Access Token obtained from the Global Fishing Watch API portal.
    | Sent as a Bearer token in the Authorization header.
    |
    */
    'api_token' => env('GFW_API_TOKEN', env('GFW_API_KEY')),
    'api_key' => env('GFW_API_TOKEN', env('GFW_API_KEY')),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum execution timeout in seconds for requests made to GFW API.
    |
    */
    'timeout' => (int) env('GFW_API_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Connect Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum connection establishment timeout in seconds.
    |
    */
    'connect_timeout' => (int) env('GFW_API_CONNECT_TIMEOUT', 5),

    /*
    |--------------------------------------------------------------------------
    | Default Vessel Identity Dataset
    |--------------------------------------------------------------------------
    |
    | The default dataset alias for querying vessel identity.
    |
    */
    'vessel_dataset' => env('GFW_VESSEL_DATASET', 'public-global-vessel-identity:latest'),

    /*
    |--------------------------------------------------------------------------
    | Vessel Identity Cache TTL
    |--------------------------------------------------------------------------
    |
    | Cache duration in seconds for vessel identity and search results.
    | Default: 86400 seconds (24 hours).
    |
    */
    'cache_ttl' => (int) env('GFW_CACHE_TTL', 86400),
    'vessel_cache_ttl' => (int) env('GFW_VESSEL_CACHE_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Vessel Activity / Presence Dataset & Cache TTL
    |--------------------------------------------------------------------------
    |
    | Activity / presence datasets have frequent updates and periodic latency.
    | Activity cache TTL is shorter than identity TTL (Default: 3600 seconds / 1 hour).
    |
    */
    'activity_dataset' => env('GFW_ACTIVITY_DATASET', 'public-global-vessel-tracks:latest'),
    'activity_cache_ttl' => (int) env('GFW_ACTIVITY_CACHE_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | GFW Event Datasets (Apparent Fishing, Encounters, Loitering, Port Visits)
    |--------------------------------------------------------------------------
    |
    | Datasets for algorithmic event detection in GFW API v3.
    |
    */
    'fishing_events_dataset' => env('GFW_FISHING_EVENTS_DATASET', 'public-global-fishing-events:latest'),
    'encounters_dataset' => env('GFW_ENCOUNTERS_DATASET', 'public-global-encounters:latest'),
    'loitering_dataset' => env('GFW_LOITERING_DATASET', 'public-global-loitering-events:latest'),
    'port_visits_dataset' => env('GFW_PORT_VISITS_DATASET', 'public-global-port-visits-c2:latest'),
    'event_cache_ttl' => (int) env('GFW_EVENT_CACHE_TTL', 3600),

];
