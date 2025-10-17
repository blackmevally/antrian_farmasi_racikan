<?php
header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
header("Access-Control-Allow-Origin: *");

// Lokasi file log panggilan
$logFile = __DIR__ . "/../display/log_panggilan.txt";

$lastData = "";
$pingInterval = 1;

function sendSSE($event, $data) {
    echo "event: $event\n";
    echo "data: " . json_encode($data) . "\n\n";
    @ob_flush();
    @flush();
}

while (true) {
    clearstatcache();

    if (file_exists($logFile)) {
        $content = trim(file_get_contents($logFile));
        if ($content && $content !== $lastData) {
            $lastData = $content;

            $d = json_decode($content, true);
            if (!$d || !isset($d['no'])) {
                $d = ["no" => "---", "loket" => "?", "time" => time()];
            }

            // Deteksi jenis otomatis dari prefix
            $jenis = $d['jenis'] ?? '';
            if (empty($jenis)) {
                $no = strtoupper($d['no']);
                if (strpos($no, 'R') === 0) $jenis = 'racikan';
                elseif (strpos($no, 'O') === 0) $jenis = 'obat';
                else $jenis = 'obat';
            }
            $d['jenis'] = $jenis;

            // Normalisasi nilai loket (hapus kata "Loket" supaya tidak dobel)
            if (!empty($d['loket'])) {
                $d['loket'] = trim(str_ireplace("loket", "", $d['loket']));
            }

            sendSSE("update", $d);
        }
    }

    sendSSE("ping", ["t" => time()]);
    sleep($pingInterval);
}
?>
