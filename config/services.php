<?php

return [

    'liqpay' => [
        'public_key'  => env('LIQPAY_PUBLIC_KEY', ''),
        'private_key' => env('LIQPAY_PRIVATE_KEY', ''),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
    ],

];