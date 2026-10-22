<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google reCAPTCHA v3 Master Switch
    |--------------------------------------------------------------------------
    |
    | When enabled, all incoming login requests must present and verify a valid
    | Google reCAPTCHA v3 token server-side.
    |
    */

    'enabled' => (bool) env('RECAPTCHA_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA Keys
    |--------------------------------------------------------------------------
    |
    | Site key is public and rendered in frontend Blade templates.
    | Secret key is private and used strictly on the server-side verification.
    | NEVER expose the secret key to frontend, logs, or git.
    |
    */

    'site_key' => env('RECAPTCHA_SITE_KEY', ''),

    'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA Action & Score Threshold
    |--------------------------------------------------------------------------
    |
    | Google reCAPTCHA v3 actions prevent token reuse across different flows.
    | Minimum score threshold (0.0 to 1.0) required to consider request human.
    |
    */

    'login_action' => env('RECAPTCHA_LOGIN_ACTION', 'login'),

    'login_score' => (float) env('RECAPTCHA_LOGIN_SCORE', 0.5),

    /*
    |--------------------------------------------------------------------------
    | Verification Endpoint & Timeout
    |--------------------------------------------------------------------------
    */

    'verify_url' => env('RECAPTCHA_VERIFY_URL', 'https://www.google.com/recaptcha/api/siteverify'),

    'timeout' => (int) env('RECAPTCHA_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Allowed Hostnames
    |--------------------------------------------------------------------------
    |
    | Strict hostname validation to reject tokens solved on unauthorized domains.
    | In production, defaults strictly to barbossa.nagakecil.site.
    |
    */

    'allowed_hostnames' => env('RECAPTCHA_ALLOWED_HOSTNAMES')
        ? array_filter(array_map('trim', explode(',', (string) env('RECAPTCHA_ALLOWED_HOSTNAMES'))))
        : (env('APP_ENV') === 'production'
            ? ['barbossa.nagakecil.site']
            : ['barbossa.nagakecil.site', 'localhost', '127.0.0.1']),

];
