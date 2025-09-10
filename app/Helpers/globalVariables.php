<?php

use App\Services\LicenseService;
use App\Models\License;



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

    //Métodos de pegar a licença
    function getLicenseCodeFile()
    {
        $path = getClientStoragePathDat();

        if (file_exists($path) && filesize($path) > 0) {
            return file_get_contents($path);
        }

        return null;
    }
    function getLicenseCodeBd()
    {
        return License::latest()->first(); // Pega o registro mais recente
    }

    // Retorna os dados descriptografados
    function getLicenseCodedecrypted()
    {
        return licenseService()->getLicenseData();
    }
    //verificação de status
    function getStatusLicense()
    {
        return licenseService()->verificationStatus();
    }

}
if (!function_exists('licenseService')) {
    function licenseService(): LicenseService
    {
        static $service;
        if (!$service) {
            $service = app(LicenseService::class);
        }
        return $service;
    }
}
