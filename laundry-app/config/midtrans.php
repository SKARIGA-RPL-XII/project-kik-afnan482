<?php

return [
    'merchant_id' => env('MIDTRANS_MERCHANT_ID', 'G325156151'),
    
    // PENTING: Pastikan tidak ada spasi atau karakter tersembunyi
    'client_key' => env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-mBgJz3k5SXAWjt8J'),
    'server_key' => env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-OF3nCp6q1q0nJEZ2cbMWeBGF'),
    
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds' => env('MIDTRANS_IS_3DS', true),
];