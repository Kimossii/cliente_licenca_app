<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use App\Models\License;

class LicenseController extends Controller
{

    public function activateForm()
    {
        if (!\App\Http\Controllers\LicenseController::isValid()) {

        } else {
            return redirect()->route('index');
        }
        return view('license.activate');
    }


    public function activate(Request $request)
    {
        try {

            $dados = json_decode(Crypt::decryptString($request->license_code), true);


            if (Carbon::now()->gt(Carbon::parse($dados['expira_em']))) {
                return back()->with('error', 'Licença expirada!');
            }


            $hardwareId = php_uname('n');
            if ($dados['hardware_id'] !== $hardwareId) {
                return back()->with('error', 'Licença não corresponde a esta máquina!');
            }

            License::updateOrCreate(
                ['id' => 1],
                [
                    'license_code' => $request->license_code,
                    'valid_until' => $dados['expira_em'],
                ]
            );


            $path = storage_path('app/license.dat');

            if (!file_exists($path)) {
                file_put_contents($path, '');
            }

            file_put_contents($path, $request->license_code);

            return redirect()->route('index')->with('success', 'Licença ativada com sucesso! Válida até ' . $dados['expira_em']);
            ;


        } catch (\Exception $e) {
            return back()->with('error', 'Licença inválida!');
        }
    }


    public function requestCode()
    {
        $hardwareId = php_uname('n');
        $dados = [
            'cliente' => 'Cliente X',
            'hardware_id' => $hardwareId,
            'data' => now()->toDateString(),
        ];


        $requestCode = base64_encode(json_encode($dados));

        return view('license.request', compact('requestCode'));
    }

    /* public static function isValid()
     {
         $license = License::latest()->first();
         if (!$license)

             return false;

         try {
             $dados = json_decode(Crypt::decryptString($license->license_code), true);

             // valida data de expiração
             if (Carbon::now()->gt(Carbon::parse($dados['expira_em'])))
                 return false;

             // valida hardware (opcional)
             $hardwareId = php_uname('n');
             if ($dados['hardware_id'] !== $hardwareId)
                 return false;

             return true;
         } catch (\Exception $e) {
             return false;
         }
     }*/


    public static function isValid()
    {
        $path = storage_path('app/license.dat');
        $hardwareId = php_uname('n');

        try {
            if (file_exists($path)) {

                $licenseCode = file_get_contents($path);
                $dados = json_decode(\Illuminate\Support\Facades\Crypt::decryptString($licenseCode), true);
            } else {

                $license = License::latest()->first();
                if (!$license) {
                    return false;
                }
                $dados = json_decode(\Illuminate\Support\Facades\Crypt::decryptString($license->license_code), true);
            }


            if (\Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($dados['expira_em']))) {
                return false;
            }


            if ($dados['hardware_id'] !== $hardwareId) {
                return false;
            }

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    public static function checkLicense()
    {
        $hardwareId = php_uname('n');

        try {
            if (file_exists($path = storage_path('app/license.dat'))) {

                $licenseCode = file_get_contents($path);
                $dados = json_decode(\Illuminate\Support\Facades\Crypt::decryptString($licenseCode), true);
            } else {

                $license = License::latest()->first();
                if (!$license) {
                    return ['valid' => false, 'days_left' => 0];
                }
                $dados = json_decode(\Illuminate\Support\Facades\Crypt::decryptString($license->license_code), true);
            }

            $now = \Carbon\Carbon::now();
            $expire = \Carbon\Carbon::parse($dados['expira_em']);
            $daysLeft = ceil($now->diffInDays($expire, false));

            // valida hardware
            if ($dados['hardware_id'] !== $hardwareId) {
                return ['valid' => false, 'days_left' => 0];
            }

            return [
                'valid' => $daysLeft >= 0,
                'days_left' => $daysLeft
            ];

        } catch (\Exception $e) {
            return ['valid' => false, 'days_left' => 0];
        }
    }



    public function index()
    {
        $licenseInfo = \App\Http\Controllers\LicenseController::checkLicense();
        if (!\App\Http\Controllers\LicenseController::isValid()) {
            return redirect()->route('license.activate.form')
                ->with('error', 'Licença inválida ou expirada! Por favor, solicite uma nova licença.');
        }
        if ($licenseInfo['days_left'] <= 5) {

            session()->flash('warning', 'Sua licença vai expirar em ' . $licenseInfo['days_left'] . ' dias!');
        }

        return view('index_teste');
    }
}
