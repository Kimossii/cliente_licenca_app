<?php

use App\Services\LicenseService;

if (!function_exists('getClientStoragePath')) {
    function getClientStoragePathKeyPublic()
    {
        return app(LicenseService::class)->getClientStoragePathKeyPublic();
    }

    function getClientStoragePathDat()
    {
        return app(LicenseService::class)->getClientStoragePathKeyDat();
    }
    function getStorageKeys()
    {

        return storage_path('keys');
    }
    
}
