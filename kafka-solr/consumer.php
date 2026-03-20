<?php
 
require 'vendor/autoload.php';
 
use Kafka\Consumer;
use Kafka\ConsumerConfig;

$partition   = isset($argv[1]) ? (int)$argv[1] : 0;
$kafkaBroker = 'localhost:9092';
$kafkaTopic  = 'kafka-solr-csvdata';
$solrUrl     = 'http://localhost:8983/solr/kafka-solr-csvdata';
// $groupId     = 'solr-indexer-group';
// $batchSize   = 200;
$idleTimeout = 300;
 
$config = ConsumerConfig::getInstance();
$config->setMetadataBrokerList('127.0.0.1:9092');
$config->setGroupId('solr-group');
$config->setTopics(['kafka-solr-csvdata']);
$config->setOffsetReset('earliest');
 
define('BATCH_SIZE', 1000);
 
function getSolrSuffix($value): string {
    if (is_int($value))   return '_i';
    if (is_float($value)) return '_f';
    if (is_bool($value))  return '_b';
    return '_s';
}
 
function sendBatchToSolr(array $batch): void {
 
    if (empty($batch)) return;
 
    $solrUrl = "http://localhost:8983/solr/kafka-solr-csvdata/update/json/docs?commitWithin=5000";
 
    $ch = curl_init();
 
    curl_setopt($ch, CURLOPT_URL, $solrUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($batch));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 
    $response = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
 
    curl_close($ch);
 
    if ($httpCode === 200) {
        echo "✅ Batch indexed: ".count($batch)." docs\n";
    } else {
        echo "❌ Solr error ($httpCode): $response\n";
    }
}
 
$batch = [];
$totalIndexed = 0;
 
$consumer = new Consumer();
 
$consumer->start(function ($topic, $part, $message) use (&$batch, &$totalIndexed) {
 
    $jsonData = $message['message']['value'];
    $data = json_decode($jsonData, true);
 
    if (!$data) return;
 
    $doc = [];
 
    foreach ($data as $key => $value) {
 
        $cleanKey = preg_replace('/[^a-zA-Z0-9_]/', '_', $key);
        $cleanKey = preg_replace('/_+/', '_', $cleanKey);
        $cleanKey = trim($cleanKey, '_');
 
        $cleanValue = ($value === null) ? '' : $value;
 
        if ($cleanKey === '_source_file' || $cleanKey === 'source_file') {
            $doc['source_file_s'] = $cleanValue;
            continue;
        }
 
        if ($cleanKey === 'id') {
            $doc['id'] = $cleanValue;
            continue;
        }
 
        $suffix = getSolrSuffix($value);
 
        $doc[$cleanKey.$suffix] = $cleanValue;
    }
 
    $doc['id'] = 'product_' . ($doc['product_id_i'] ?? '') . '_' . ($doc['source_file_s'] ?? '') . '_' . uniqid('', true);
 
    $batch[] = $doc;
    $totalIndexed++;
 
    if (count($batch) >= BATCH_SIZE) {
 
        sendBatchToSolr($batch);
 
        echo "📦 Total indexed so far: $totalIndexed\n";
 
        $batch = [];
    }
 
});
 
if (!empty($batch)) {
 
    sendBatchToSolr($batch);
 
    echo "📦 Final indexed count: $totalIndexed\n";
 
}
 
echo "🎉 All records indexed to Solr\n";