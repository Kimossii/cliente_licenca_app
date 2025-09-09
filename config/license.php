<?php
return [

    // Define o modo de uso (client, server ou both)
    'mode' => env('LICENSE_MODE', 'client'),

    // Configs do CLIENTE
    'client' => [
        'storage_path_dat' => storage_path('app/license.dat'),
        'storage_path_key_public' => storage_path('keys/public.pem'),
        'check_interval' => 60, // minutos entre verificações automáticas
    ],

    // Configs do SERVIDOR-Gerador de Licenças
    'server' => [
        'keys' => [
            'public' => storage_path('keys/public.pem'),
            'private' => storage_path('keys/private.pem'),
        ],
        'default_expiration_days' => 30,
    ],

];
