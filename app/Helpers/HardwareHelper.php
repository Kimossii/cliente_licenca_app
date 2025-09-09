<?php

if (!function_exists('getHardwareFingerprint')) {
    function getHardwareFingerprint()
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
    function clientName()
    {
        // Pega o hostname do servidor
        $hostname = php_uname('n') ?: 'unknown'; // fallback se php_uname falhar

        // Usa regex para pegar a parte antes do primeiro hífen
        if (preg_match('/^[^-]+/', $hostname, $matches)) {
            $name = $matches[0];
        } else {
            $name = $hostname; // fallback para hostname inteiro
        }

        // Remove espaços extras e caracteres indesejados
        $name = preg_replace('/[^A-Za-z0-9_\-]/', '', $name);

        // Se ainda estiver vazio, usa um valor padrão
        return $name ?: 'client';
    }
    //Método para gerar request code
    function generateRequestCode(array $dados)
    {
        return base64_encode(json_encode($dados));
    }

    function decryptLicenseCode(string $licenseCode)
    {
        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($licenseCode);
        } catch (\Exception $e) {
            return null;
        }
    }
}

