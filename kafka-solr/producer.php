<?php
ini_set('memory_limit', '512M');
require 'vendor/autoload.php';

use longlang\phpkafka\Producer\Producer;
use longlang\phpkafka\Producer\ProducerConfig;

$folder     = __DIR__ . '/csvfiles/';
$topic      = 'kafka-solr-csvdata';
$batchSize  = 1000;
$partitions = 4;
$broker     = 'localhost:9092';

echo "========================================\n";
echo "  KAFKA PRODUCER - CSV INDEXER\n";
echo "========================================\n\n";

$config = new ProducerConfig();
$config->setBootstrapServer($broker);
$config->setAcks(-1);
$producer = new Producer($config);

$files = glob($folder . '*.csv');
if (!$files) {
    echo "No CSV files found in $folder\n";
    exit(0);
}

$fileCount = count($files);
echo "Found $fileCount CSV files\n\n";

$total     = 0;
$fileIndex = 0;

foreach ($files as $file) {
    $fileIndex++;
    $fileName = basename($file);
    echo "[$fileIndex/$fileCount] Processing: $fileName\n";

    $handle = fopen($file, 'r');
    if (!$handle) {
        echo "  Cannot open — skipping\n";
        continue;
    }

    $headers = fgetcsv($handle);
    if (!$headers) {
        echo "  Empty header — skipping\n";
        fclose($handle);
        continue;
    }

    $headers   = array_map('trim', $headers);
    $rowNumber = 0;
    $batch     = [];

    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) !== count($headers)) continue;

        $raw  = array_combine($headers, $row);
        $data = [];

        foreach ($raw as $key => $value) {
            $data[trim($key)] = autocast((string)$value);
        }

        $data['_source_file'] = $fileName;

        $batch[] = ['data' => $data, 'row' => $rowNumber];
        $rowNumber++;
        $total++;

        if (count($batch) >= $batchSize) {
            sendBatch($producer, $topic, $batch, $fileName);
            $batch = [];
        }
    }

    if (!empty($batch)) {
        sendBatch($producer, $topic, $batch, $fileName);
    }

    fclose($handle);
    echo "  Finished — $rowNumber rows\n\n";
}

// Send sentinels — one per partition
echo "Sending sentinel signals to $partitions partitions...\n";
for ($p = 0; $p < $partitions; $p++) {
    $producer->send(
        $topic,
        json_encode(['__sentinel__' => true]),
        '__sentinel_' . $p . '__'
    );
}

$producer->close();

echo "\n========================================\n";
echo "  DONE — TOTAL ROWS SENT: $total\n";
echo "========================================\n";


// ─── Functions ───────────────────────────────────────────────

function autocast(string $value) {
    $value = trim($value);
    if ($value === '') return null;
    $clean = str_replace(',', '', $value);
    if (ctype_digit($clean)) return (int)$clean;
    if (preg_match('/^-\d+$/', $clean)) return (int)$clean;
    if (is_numeric($clean) && strpos($clean, '.') !== false) return (float)$clean;
    if (strtolower($value) === 'true') return true;
    if (strtolower($value) === 'false') return false;
    return $value;
}

function sendBatch(Producer $producer, string $topic, array $batch, string $fileName): void
{
    foreach ($batch as $item) {
        $key = md5($fileName . ':' . $item['row'] . ':' . uniqid('', true));
        $producer->send($topic, json_encode($item['data'], JSON_UNESCAPED_UNICODE), $key);
    }
    echo "  Sent batch of " . count($batch) . "\n";
}