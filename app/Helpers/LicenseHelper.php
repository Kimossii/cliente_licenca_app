<?php

namespace App\Helpers;

class LicenseHelper
{
    public static function getSegredoExtra()
    {
        $p1 = 'TUVV';
        $p2 = 'LVNB';
        $p3 = 'TFRP';
        $p4 = 'LVNF';
        $p5 = 'Q1JF';
        $p6 = 'VE8t';
        $p7 = 'MjAyNQ==';

        $base64 = $p1 . $p2 . $p3 . $p4 . $p5 . $p6 . $p7;
        $segredo = base64_decode($base64);

        return strrev($segredo);
    }
    
}
