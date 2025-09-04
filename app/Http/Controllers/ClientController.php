<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class ClientController extends Controller
{
    // Mostra o formulário para gerar fingerprint
    public function showForm()
    {
        return view('client.gerar_codigo');
    }

    // Gera o fingerprint da máquina
    public function generateFingerprint()
    {
        $fingerprint = $this->getHardwareFingerprint();
        return view('client.gerar_codigo', compact('fingerprint'));
    }

    // Mostra formulário para validar a licença
    public function showFormValidar()
    {
        return view('client.index');
    }

    // Valida o código de licença colado no input
    public function validateLicense(Request $request)
    {

        $request->validate([
            'license_key' => 'required|string',
        ]);
        dd($request)->all();
        try {
            // Descriptografar
            $data = Crypt::decryptString($request->license_key);

            // Converter JSON em array
            $licenseData = json_decode($data, true);

            // Verificar data de expiração
            $expiresAt = Carbon::parse($licenseData['expires']);

            if ($expiresAt->isPast()) {
                return back()->withErrors(['license_key' => 'Licença expirada!']);
            }
            //dd($request);
            // Se chegou aqui → licença válida
            return back()->with('success', 'Licença válida até: ' . $expiresAt->toDateString());

        } catch (\Exception $e) {
            return back()->withErrors(['license_key' => 'Licença inválida ou corrompida!']);
        }
    }

    // Função para gerar fingerprint (Windows, Linux, Mac)
    private function getHardwareFingerprint()
    {
        $os = PHP_OS_FAMILY;
        $diskSerial = '';
        $mac = '';

        if ($os === 'Windows') {
            $diskSerial = trim(shell_exec("wmic diskdrive get serialnumber 2>&1"));
            $diskSerial = preg_replace('/\s+/', '', explode("\n", $diskSerial)[1] ?? '');
            $mac = trim(shell_exec("getmac"));
            $mac = preg_replace('/\s+/', '', explode(" ", $mac)[0] ?? '');
        } elseif ($os === 'Linux') {
            $diskSerial = trim(shell_exec("lsblk -o SERIAL | sed -n 2p"));
            $mac = trim(shell_exec("cat /sys/class/net/$(ip route show default | awk '/default/ {print $5}')/address"));
        } elseif ($os === 'Darwin') {
            $diskSerial = trim(shell_exec("system_profiler SPSerialATADataType | awk '/Serial Number/ {print $4; exit}'"));
            $mac = trim(shell_exec("ifconfig en0 | awk '/ether/ {print $2}'"));
        }

        return hash('sha256', $diskSerial . $mac);
    }
}
