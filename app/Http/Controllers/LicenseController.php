<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use App\Models\License;
use App\Helpers\LicenseHelper;
use Illuminate\Support\Facades\File;


class LicenseController extends Controller
{

    public function formKeyPublic()
    {
        $keysPath = storage_path('keys/public.pem');
        $chaveExiste = File::exists($keysPath);

        return view("client.uploader_key",compact('chaveExiste'));
    }
    public function uploadKey(Request $request)
    {
        //dd($request->file('public_key'));
        $request->validate([
            'public_key' => 'required|file|mimes:txt,pem|max:1024',
        ]);

        $keysPath = storage_path('keys');
        if (File::exists($keysPath) && !$request->has('overwrite')) {
            return redirect()->back()->with('warning', 'Chave pública já existe. Marque para substituir.');
        }

        if (!File::exists($keysPath)) {
            File::makeDirectory($keysPath, 0755, true);
        }

        $file = $request->file('public_key');
        $file->move($keysPath, 'public.pem');

        return back()->with('success', 'Chave pública enviada com sucesso!');
    }

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
        $dados = json_decode(Crypt::decryptString($request->license_code), true);

        // 🔐 Segredo protegido
        $segredoExtra = LicenseHelper::getSegredoExtra();

        // Recalcula HMAC (mantendo RSA, removendo apenas HMAC)
        $dadosParaAssinatura = $dados;
        unset($dadosParaAssinatura['hmac']);

        $assinaturaCorreta = hash_hmac('sha256', json_encode($dadosParaAssinatura), $segredoExtra);
        if (!hash_equals($assinaturaCorreta, $dados['hmac'])) {
            return back()->with('error', 'Licença adulterada (HMAC inválido)!');
        }

        // ✅ Verificação RSA
        if (!empty($dados['rsa'])) {
            $publicKeyPath = storage_path('keys/public.pem');
            $publicKey = openssl_pkey_get_public(file_get_contents($publicKeyPath));
            $assinaturaRSA = base64_decode($dados['rsa']);

            if (
                openssl_verify(json_encode([
                    'cliente' => $dados['cliente'],
                    'hardware_id' => $dados['hardware_id'],
                    'expira_em' => $dados['expira_em'],
                ]), $assinaturaRSA, $publicKey, OPENSSL_ALGO_SHA256) !== 1
            ) {
                return back()->with('error', 'Licença adulterada (assinatura RSA inválida)!');
            }
        } else {
            return back()->with('error', 'Licença inválida (sem assinatura RSA)!');
        }

        // ⏳ Valida expiração
        if (Carbon::now()->gt(Carbon::parse($dados['expira_em']))) {
            return back()->with('error', 'Licença expirada!');
        }

        // 💻 Valida hardware
        $hardwareId = php_uname('n');
        if ($dados['hardware_id'] !== $hardwareId) {
            return back()->with('error', 'Licença não corresponde a esta máquina!');
        }

        // ✅ Salva no banco e arquivo
        License::updateOrCreate(
            ['id' => 1],
            [
                'license_code' => $request->license_code,
                'valid_until' => $dados['expira_em'],
            ]
        );

        file_put_contents(storage_path('app/license.dat'), $request->license_code);

        return redirect()->route('index')->with('success', 'Licença ativada com sucesso! Válida até ' . $dados['expira_em']);
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


    public static function isValid()
    {
        $path = storage_path('app/license.dat');
        $hardwareId = php_uname('n');

        try {
            // Pega a licença do arquivo ou do banco
            $licenseCode = file_exists($path)
                ? file_get_contents($path)
                : (License::latest()->first()->license_code ?? null);

            if (!$licenseCode)
                return false;

            $dados = json_decode(Crypt::decryptString($licenseCode), true);

            // ⏳ Verifica expiração
            if (now()->gt(Carbon::parse($dados['expira_em'])))
                return false;

            // 💻 Verifica hardware
            if ($dados['hardware_id'] !== $hardwareId)
                return false;

            // 🔐 1️⃣ Verifica HMAC (remove apenas o HMAC antes de recalcular)
            $segredoExtra = LicenseHelper::getSegredoExtra();
            $dadosParaHMAC = $dados;
            unset($dadosParaHMAC['hmac']);

            $assinaturaCorreta = hash_hmac('sha256', json_encode($dadosParaHMAC), $segredoExtra);
            if (!hash_equals($assinaturaCorreta, $dados['hmac']))
                return false;

            // 🔐 2️⃣ Verifica RSA
            if (!empty($dados['rsa'])) {
                $publicKeyPath = storage_path('keys/public.pem');
                $publicKey = openssl_pkey_get_public(file_get_contents($publicKeyPath));
                $assinaturaRSA = base64_decode($dados['rsa']);

                if (
                    openssl_verify(json_encode([
                        'cliente' => $dados['cliente'],
                        'hardware_id' => $dados['hardware_id'],
                        'expira_em' => $dados['expira_em'],
                    ]), $assinaturaRSA, $publicKey, OPENSSL_ALGO_SHA256) !== 1
                ) {
                    //return back()->with('error', 'Licença adulterada (assinatura RSA inválida)!');
                    return false;
                }
            } else {
                //return back()->with('error', 'Licença inválida (sem assinatura RSA)!');
                return false;
            }

            return true; // Licença válida
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
        $dias = 3;

        if (!\App\Http\Controllers\LicenseController::isValid()) {
            return redirect()->route('license.activate.form')
                ->with('error', 'Licença inválida ou expirada! Por favor, solicite uma nova licenças.');
        }
        if ($licenseInfo['days_left'] <= 5) {
            //if ($dias <= 5) {

            session()->flash('warning', 'Sua licença vai expirar em ' . $licenseInfo['days_left'] . ' dias!');
            //session()->flash('warning', 'Sua licença vai expirar em ' . $dias . ' dias!');

        }

        return view('index_teste');
    }
}
