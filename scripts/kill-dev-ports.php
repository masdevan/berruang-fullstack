<?php

$isWin = str_starts_with(PHP_OS_FAMILY, 'Windows');

if ($isWin) {
    $json = shell_exec('powershell -NoProfile -Command "Get-CimInstance Win32_Process | Where-Object { $_.Name -in @(\'php.exe\',\'node.exe\') } | Select-Object ProcessId,CommandLine | ConvertTo-Json -Compress"');
    $procs = json_decode($json ?: '[]', true) ?: [];
    if (isset($procs['ProcessId'])) {
        $procs = [$procs];
    }
} else {
    $out = shell_exec('ps axo pid=,args=') ?: '';
    $procs = [];
    foreach (explode("\n", $out) as $line) {
        if (preg_match('/^\s*(\d+)\s+(.+)$/', $line, $m)) {
            $procs[] = ['ProcessId' => (int) $m[1], 'CommandLine' => $m[2]];
        }
    }
}

$pattern = '/(artisan (serve|queue:listen|queue:work|reverb:start)|server\.php|vite)/';

foreach ($procs as $p) {
    $cmd = $p['CommandLine'] ?? '';
    if (! preg_match($pattern, $cmd)) {
        continue;
    }

    $pid = (int) $p['ProcessId'];
    echo "Killed dev process PID $pid: ".basename(explode(' ', trim($cmd))[0] ?? $cmd).PHP_EOL;
    $isWin ? exec('taskkill /F /PID '.$pid.' 2>nul') : exec('kill -9 '.$pid);
}
