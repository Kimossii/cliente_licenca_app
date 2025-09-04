<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class CheckLicense
{
    public function handle($request, Closure $next)
    {
        $licensePath = storage_path('app/license.lic');

        if (!File::exists($licensePath)) {
            abort(403, 'Licença não encontrada!');
        }

        try {
            $licenseData = json_decode(Crypt::decryptString(File::get($licensePath)), true);
        } catch (\Exception $e) {
            abort(403, 'Licença inválida!');
        }

        // Valida expiração
        if (Carbon::now()->greaterThan(Carbon::parse($licenseData['expires']))) {
            abort(403, 'Licença expirada!');
        }

        // Valida fingerprint (opcional)
        if (!empty($licenseData['fingerprint'])) {
            if ($licenseData['fingerprint'] !== $this->getHardwareFingerprint()) {
                abort(403, 'Licença inválida para este hardware!');
            }
        }

        return $next($request);
    }

    // Função fingerprint dentro do middleware
    private function getHardwareFingerprint()
    {
        $os = PHP_OS_FAMILY;
        $diskSerial = '';
        $mac = '';

        if ($os === 'Windows') {
            $diskSerial = trim(shell_exec("wmic diskdrive get serialnumber"));
            $diskSerial = preg_replace('/\s+/', '', $diskSerial);

            $mac = trim(shell_exec("getmac"));
            $mac = preg_replace('/\s+/', '', explode(" ", $mac)[0]);

        } elseif ($os === 'Linux') {
            $diskSerial = trim(shell_exec("lsblk -o SERIAL | sed -n 2p"));
            $diskSerial = preg_replace('/\s+/', '', $diskSerial);

            $mac = trim(shell_exec("cat /sys/class/net/$(ip route show default | awk '/default/ {print $5}')/address"));
            $mac = preg_replace('/\s+/', '', $mac);

        } elseif ($os === 'Darwin') {
            $diskSerial = trim(shell_exec("system_profiler SPSerialATADataType | awk '/Serial Number/ {print $4; exit}'"));
            $diskSerial = preg_replace('/\s+/', '', $diskSerial);

            $mac = trim(shell_exec("ifconfig en0 | awk '/ether/ {print $2}'"));
            $mac = preg_replace('/\s+/', '', $mac);
        }

        return hash('sha256', $diskSerial . $mac);
    }
}
