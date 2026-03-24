<?php
/**
 * CSV Upload → Kafka Endpoint
 * Accepts a multipart CSV file upload, parses it, and produces every row
 * to the Kafka topic using the same logic as producer.php.
 *
 * POST /upload.php  (multipart/form-data, field: "csvfile")
 * Returns: { success, filename, rows_sent, errors, message }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST required']);
    exit();
}

// ── Config ────────────────────────────────────────────────────────────────────
$kafkaBroker = getenv('KAFKA_BROKER') ?: 'kafka:9092';
$topic       = getenv('KAFKA_TOPIC')  ?: 'csvdata';
$batchSize   = 500;

// ── Validate upload ───────────────────────────────────────────────────────────
if (empty($_FILES['csvfile']) || $_FILES['csvfile']['error'] !== UPLOAD_ERR_OK) {
    $uploadError = $_FILES['csvfile']['error'] ?? -1;
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded or upload error: ' . $uploadError]);
    exit();
}

$file     = $_FILES['csvfile'];
$fileName = basename($file['name']);
$tmpPath  = $file['tmp_name'];

// Only accept CSV
$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
if ($ext !== 'csv') {
    http_response_code(400);
    echo json_encode(['error' => 'Only CSV files are accepted']);
    exit();
}

// ── Helpers (same as producer.php) ───────────────────────────────────────────
function autocast(string $value): mixed
{
    $value = trim($value);
    if ($value === '') return null;

    $cleanValue = str_replace(',', '', $value);

    if (ctype_digit($cleanValue))          return (float)$cleanValue;
    if (preg_match('/^-\d+$/', $cleanValue)) return (float)$cleanValue;
    if (is_numeric($cleanValue))           return (float)$cleanValue;
    if (strtolower($value) === 'true')     return true;
    if (strtolower($value) === 'false')    return false;

    // ISO date detection
    if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
        $ts = strtotime($value);
        if ($ts !== false) return date('Y-m-d\TH:i:s\Z', $ts);
    }

    return $value;
}

// ── Open CSV ──────────────────────────────────────────────────────────────────
$handle = @fopen($tmpPath, 'r');
if (!$handle) {
    http_response_code(500);
    echo json_encode(['error' => 'Cannot read uploaded file']);
    exit();
}

$rawHeader = fgetcsv($handle);
if (!$rawHeader) {
    fclose($handle);
    http_response_code(400);
    echo json_encode(['error' => 'CSV file is empty or has no header row']);
    exit();
}

$headers = array_map('trim', $rawHeader);
$validHeaders = array_filter($headers, fn($h) => !empty($h));
if (empty($validHeaders)) {
    fclose($handle);
    http_response_code(400);
    echo json_encode(['error' => 'CSV has no valid column headers']);
    exit();
}

// ── Connect to Kafka ──────────────────────────────────────────────────────────
try {
    $conf = new RdKafka\Conf();
    $conf->set('metadata.broker.list',         $kafkaBroker);
    $conf->set('allow.auto.create.topics',     'true');
    $conf->set('socket.timeout.ms',            '30000');
    $conf->set('queue.buffering.max.ms',       '200');
    $conf->set('queue.buffering.max.messages', '100000');
    $conf->set('batch.num.messages',           '1000');
    $conf->set('compression.codec',            'lz4');
    $conf->set('message.send.max.retries',     '3');
    $conf->set('retry.backoff.ms',             '500');
    $conf->set('acks',                         '1');

    $producer   = new RdKafka\Producer($conf);
    $kafkaTopic = $producer->newTopic($topic);
} catch (Exception $e) {
    fclose($handle);
    http_response_code(500);
    echo json_encode(['error' => 'Kafka connection failed: ' . $e->getMessage()]);
    exit();
}

// ── Stream rows → Kafka ───────────────────────────────────────────────────────
$rowsSent  = 0;
$errors    = 0;
$pending   = 0;
$rowNumber = 1;
$ingested  = date('Y-m-d\TH:i:s\Z');

while (($row = fgetcsv($handle)) !== false) {
    $rowNumber++;

    // Skip rows with wrong column count
    if (count($row) !== count($headers)) {
        $errors++;
        continue;
    }

    $raw  = array_combine($headers, $row);
    $data = [];

    foreach ($raw as $key => $value) {
        $data[$key] = autocast((string)($value ?? ''));
    }

    // Metadata fields
    $data['_source_file'] = $fileName;
    $data['_ingested_at'] = $ingested;

    $msgKey  = (string)($data['product_id'] ?? $data['id'] ?? $rowsSent);
    $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    try {
        $kafkaTopic->produce(RD_KAFKA_PARTITION_UA, 0, $payload, $msgKey);
        $pending++;
        $rowsSent++;
    } catch (Exception $e) {
        $errors++;
    }

    // Flush every batch
    if ($pending >= $batchSize) {
        $producer->flush(15000);
        $pending = 0;
    }
}

fclose($handle);

// Final flush
if ($pending > 0) {
    $producer->flush(30000);
}

// ── Return result ─────────────────────────────────────────────────────────────
echo json_encode([
    'success'   => true,
    'filename'  => $fileName,
    'rows_sent' => $rowsSent,
    'errors'    => $errors,
    'message'   => "✅ {$rowsSent} rows from '{$fileName}' sent to Kafka topic '{$topic}'. The consumer will index them to Solr shortly.",
]);
