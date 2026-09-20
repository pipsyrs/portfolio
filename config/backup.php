<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Kunci enkripsi backup
    |--------------------------------------------------------------------------
    |
    | Sengaja TERPISAH dari APP_KEY. Dump database berisi kredensial integrasi
    | yang dienkripsi dengan APP_KEY; bila backup memakai kunci yang sama, satu
    | kebocoran membuka keduanya sekaligus.
    |
    | Hasilkan dengan: php artisan backup:key
    |
    | PERINGATAN: kehilangan kunci ini berarti kehilangan seluruh backup secara
    | permanen. Simpan salinannya di luar server.
    |
    */

    'encryption_key' => env('BACKUP_ENCRYPTION_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Batas waktu mysqldump (detik)
    |--------------------------------------------------------------------------
    */

    'timeout' => (int) env('BACKUP_TIMEOUT', 600),

];
