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
}
