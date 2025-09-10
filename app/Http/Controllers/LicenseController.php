<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use App\Models\License;
use App\Helpers\LicenseHelper;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Services\LicenseService;



class LicenseController extends Controller
{
    private LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    public function formKeyPublic()
    {
        $keysPath = getClientStoragePathKeyPublic();
        $chaveExiste = File::exists($keysPath);

        return view("client.uploader_key", compact('chaveExiste'));
    }

    public function uploadKey(Request $request)
    {
        $request->validate([
            'public_key' => 'required|file|mimes:txt,pem|max:1024',
            'overwrite' => 'nullable|boolean',
        ]);

        $file = $request->file('public_key');
        $overwrite = $request->has('overwrite');

        $result = $this->licenseService->uploadPublicKey($file, $overwrite);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('warning', $result['message']);
    }
    public function activateForm()
    {

        return view('license.activate');
    }


    /* public function activate(Request $request)
     {
         $dados = null;
         if (!File::exists(getStorageKeys())) {
             return redirect()->back()->with('error', 'Chave pública não existe. Faça o upload primeiro, para activar a sua licença.');
         }

         if (!empty($request->license_code)) {
             try {
                 $dados = json_decode(decryptLicenseCode($request->license_code), true);
             } catch (DecryptException $e) {
                 return back()->with('error', 'Código de licença inválido!');
             }
         }
         if (!$dados) {
             return back()->with('error', 'Código de licença inválido!');
         }


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
             $publicKeyPath = getClientStoragePathKeyPublic();
             $publicKey = openssl_pkey_get_public(file_get_contents($publicKeyPath));
             $assinaturaRSA = base64_decode($dados['rsa']);

             if (
                 openssl_verify(json_encode([
                     'client_name' => $dados['client_name'],
                     'hardware_id' => $dados['hardware_id'],
                     'expire_in' => $dados['expire_in'],
                 ]), $assinaturaRSA, $publicKey, OPENSSL_ALGO_SHA256) !== 1
             ) {
                 return back()->with('error', 'Licença adulterada (Chave pública inválida), contate o suporte para adquirir uma nova chave!');
             }
         } else {
             return back()->with('error', 'Licença inválida (sem assinatura RSA)!');
         }

         // ⏳ Valida expiração
         if (Carbon::now()->gt(Carbon::parse($dados['expire_in']))) {
             return back()->with('error', 'Licença expirada!');
         }

         // 💻 Valida hardware
         $hardwareId = getHardwareFingerprint();
         if ($dados['hardware_id'] !== $hardwareId) {
             return back()->with('error', 'Licença não corresponde a esta máquina!');
         }

         // ✅ Salva no banco e arquivo
         License::updateOrCreate(
             ['id' => 1],
             [
                 'license_code' => $request->license_code,
                 'valid_until' => $dados['expire_in'],
             ]
         );

         file_put_contents(getClientStoragePathDat(), $request->license_code);

         return redirect()->route('index')->with('success', 'Licença ativada com sucesso! Válida até ' . $dados['expire_in']);
     }
 */
    public function activate(Request $request, LicenseService $licenseService)
    {
        $request->validate([
            'license_code' => 'required|string',
        ]);

        $result = $licenseService->activateLicense($request->license_code);

        if ($result['success']) {
            return redirect()->route('index')->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }



    //Método para gerar request code
    public function requestCode()
    {
        $dados = [
            'client_name' => clientName(),
            'hardware_id' => getHardwareFingerprint(),
            'data' => now()->toDateString(),
        ];
        $requestCode = generateRequestCode($dados);

        return view('license.request', compact('requestCode'));
    }



    public function index()
    {
        $licenseInfo = $this->licenseService->checkLicense();

        if ($licenseInfo['days_left'] <= 5) {
            session()->flash('warning', 'Sua licença vai expirar em ' . $licenseInfo['days_left'] . ' dias!');
        }

        return view('index_teste');
    }
}
