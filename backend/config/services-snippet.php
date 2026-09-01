<?php

// Tambahkan blok 'google' ini ke dalam array yang dikembalikan config/services.php

return [

    // ...konfigurasi service lain yang sudah ada...

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'), // contoh: http://localhost:8000/auth/google/callback
    ],

];
