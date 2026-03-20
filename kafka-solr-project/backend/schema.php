<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

$solrBase = getenv('SOLR_URL') ?: 'http://solr:8983/solr/csvcore';
$context  = stream_context_create(['http' => ['timeout' => 15, 'method' => 'GET']]);

// ✅ Get ALL fields that actually have data using Solr Luke handler
$url      = $solrBase . '/admin/luke?numTerms=0&wt=json';
$response = @file_get_contents($url, false, $context);

if (!$response) {
    // Fallback to sampling docs
    $url      = $solrBase . '/select?q=*:*&rows=200&wt=json';
    $response = @file_get_contents($url, false, $context);

    if (!$response) {
        http_response_code(500);
        echo json_encode(['error' => 'Cannot connect to Solr']);
        exit();
    }

    $data  = json_decode($response, true);
    $docs  = $data['response']['docs'] ?? [];
    $total = $data['response']['numFound'] ?? 0;

    $allFields = [];
    foreach ($docs as $doc) {
        foreach (array_keys($doc) as $field) {
            $allFields[$field] = true;
        }
    }

    $skip = ['id', '_version_', '_root_', 'score'];
    $categorized = [];
    foreach (array_keys($allFields) as $field) {
        if (in_array($field, $skip)) continue;
        $type = 'string';
        if (str_ends_with($field, '_i'))  $type = 'integer';
        elseif (str_ends_with($field, '_f'))  $type = 'float';
        elseif (str_ends_with($field, '_dt')) $type = 'date';
        elseif (str_ends_with($field, '_b'))  $type = 'boolean';
        $categorized[] = ['name' => $field, 'type' => $type];
    }
    usort($categorized, fn($a, $b) => strcmp($a['name'], $b['name']));
    echo json_encode(['fields' => $categorized, 'total' => $total]);
    exit();
}

// ✅ Parse Luke response — gets every field that has at least 1 doc
$data   = json_decode($response, true);
$fields = $data['fields'] ?? [];
$total  = $data['index']['numDocs'] ?? 0;

$skip = ['id', '_version_', '_root_', 'score', '_text_'];

$categorized = [];
foreach ($fields as $fieldName => $fieldInfo) {
    if (in_array($fieldName, $skip)) continue;
    if (str_starts_with($fieldName, '_')) continue;

    // Only include fields that actually have data
    $docs = $fieldInfo['docs'] ?? 0;
    if ($docs === 0) continue;

    $type = 'string';
    if (str_ends_with($fieldName, '_i'))  $type = 'integer';
    elseif (str_ends_with($fieldName, '_f'))  $type = 'float';
    elseif (str_ends_with($fieldName, '_dt')) $type = 'date';
    elseif (str_ends_with($fieldName, '_b'))  $type = 'boolean';

    $categorized[] = [
        'name' => $fieldName,
        'type' => $type,
        'docs' => $docs,   // how many docs have this field
    ];
}

// Sort by number of docs descending — most populated fields first
usort($categorized, fn($a, $b) => $b['docs'] - $a['docs']);

echo json_encode([
    'fields' => $categorized,
    'total'  => $total,
]);