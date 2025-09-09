<?php
namespace App\Services;

class LicenseService
{
    public function getClientStoragePathKeyPublic()
    {
        return config('license.client.storage_path_key_public');
    }
      public function getClientStoragePathKeyDat()
    {
        return config('license.client.storage_path_dat');
    }

}
