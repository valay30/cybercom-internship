<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

/**
 * Advanced Solr Query Builder (Backend implementation)
 * Translates abstract filter JSON into safe Solr filter queries.
 */
function buildSolrFq(array $filters): ?string {
    $clauses = [];

    foreach ($filters as $f) {
        if (empty($f['field'])) continue;

        $field    = $f['field'];
        $op       = $f['operator'] ?? 'exact';
        $val      = $f['value']    ?? '';
        $from     = $f['from']     ?? '*';
        $to       = $f['to']       ?? '*';
        $type     = $f['type']     ?? 'string';
        $logic    = $f['logic']    ?? 'AND';

        $clause = null;

        // Clean numeric values
        $cleanVal = ($type === 'integer' || $type === 'float') ? str_replace(',', '', (string)$val) : $val;

        // 1. Range support
        if ($op === 'range') {
            $cFrom = ($type === 'integer' || $type === 'float') ? str_replace(',', '', (string)$from) : $from;
            $cTo   = ($type === 'integer' || $type === 'float') ? str_replace(',', '', (string)$to)   : $to;
            if ($cFrom === '') $cFrom = '*';
            if ($cTo === '')   $cTo   = '*';
            $clause = "{$field}:[{$cFrom} TO {$cTo}]";
        }
        // 2. Text matching (escaped)
        elseif ($op === 'contains' && !empty($val)) {
            $escaped = preg_quote($val, '/'); // Simplified escaping
            $escaped = str_replace([' ', ':'], ['\\ ', '\\:'], $val); // Basic Solr escapes
            $clause = "{$field}:*{$escaped}*";
        }
        elseif ($op === 'starts' && !empty($val)) {
            $escaped = str_replace([' ', ':'], ['\\ ', '\\:'], $val);
            $clause = "{$field}:{$escaped}*";
        }
        // 3. Comparison
        elseif ($op === 'gt' && !empty($cleanVal)) {
            $clause = "{$field}:[{$cleanVal} TO *]";
        }
        elseif ($op === 'lt' && !empty($cleanVal)) {
            $clause = "{$field}:[* TO {$cleanVal}]";
        }
        // 4. Exact match
        elseif ($op === 'exact' && $val !== '') {
            $safeVal = ($type === 'string' || $type === 'boolean') ? "\"{$cleanVal}\"" : $cleanVal;
            $clause = "{$field}:{$safeVal}";
        }

        if ($clause) {
            $clauses[] = ['clause' => $clause, 'logic' => $logic];
        }
    }

    if (empty($clauses)) return null;

    // Fold logic: (A AND B) OR (C AND D)
    $orGroups = [];
    $andGroup = [$clauses[0]['clause']];

    for ($i = 1; $i < count($clauses); $i++) {
        if ($clauses[$i]['logic'] === 'OR') {
            $orGroups[] = '(' . implode(' AND ', $andGroup) . ')';
            $andGroup = [$clauses[$i]['clause']];
        } else {
            $andGroup[] = $clauses[$i]['clause'];
        }
    }
    $orGroups[] = '(' . implode(' AND ', $andGroup) . ')';

    return implode(' OR ', $orGroups);
}

/**
 * Handle Date Range (Specific)
 */
function buildDateRangeFq(array $dr): ?string {
    if (empty($dr['field']) || empty($dr['from'])) return null;

    $field = $dr['field'];
    $from  = $dr['from'];
    $to    = !empty($dr['to']) ? $dr['to'] : '*';

    // Solr Date formats (_dt) require ISO string [FROM TO TO]
    if (str_ends_with($field, '_dt')) {
        $isoFrom = date('Y-m-d\TH:i:s\Z', strtotime($from));
        $isoTo   = ($to === '*') ? '*' : date('Y-m-d\TH:i:s\Z', strtotime($to . ' 23:59:59'));
        return "{$field}:[{$isoFrom} TO {$isoTo}]";
    } 
    
    // String dates (_s) fallback to pattern match or wildcard range
    return "{$field}:[{$from} TO {$to}]";
}

// ── Main Controller ───────────────────────────────────────────────────────────

$solrBase = getenv('SOLR_URL') ?: 'http://solr:8983/solr/csvcore';

// Get params from either GET or POST JSON body
$input = $_SERVER['REQUEST_METHOD'] === 'POST' ? json_decode(file_get_contents('php://input'), true) : $_GET;

$q            = $input['q']            ?? '*:*';
$start        = max(0, (int)($input['start'] ?? 0));
$rows         = min(1000, max(1, (int)($input['rows'] ?? 20)));
$sort         = $input['sort']         ?? '';
$fl           = $input['fl']           ?? '*';
$facet        = $input['facet']        ?? '';
$facetFields  = $input['facet_field']  ?? [];
$selectedFile = $input['selectedFile'] ?? '';

// Build FQ array
$fq = [];

// 1. Existing FQ strings (backwards compatibility)
if (!empty($input['fq'])) {
    $existingFq = is_array($input['fq']) ? $input['fq'] : [$input['fq']];
    $fq = array_merge($fq, $existingFq);
}

// 2. Structured modern filters (NEW)
if (!empty($input['filters']) && is_array($input['filters'])) {
    $builtFq = buildSolrFq($input['filters']);
    if ($builtFq) $fq[] = $builtFq;
}

// 3. Structured date range (NEW)
if (!empty($input['dateRange']) && is_array($input['dateRange'])) {
    $builtDr = buildDateRangeFq($input['dateRange']);
    if ($builtDr) $fq[] = $builtDr;
}

// 4. Selected file
if ($selectedFile) {
    $fq[] = "source_file_s:\"{$selectedFile}\"";
}

$params = [
    'q'     => $q,
    'start' => $start,
    'rows'  => $rows,
    'wt'    => 'json',
    'fl'    => $fl,
];

if ($sort)  $params['sort']  = $sort;
if ($facet) {
    $params['facet']          = 'true';
    $params['facet.mincount'] = 1;
    $params['facet.limit']    = 200;
    $params['facet.sort']     = 'index';
}

$url = $solrBase . '/select?' . http_build_query($params);

// Add multiple FQs
foreach ($fq as $f) {
    if ($f) $url .= '&fq=' . urlencode($f);
}

// Add facet fields
if (is_string($facetFields)) $facetFields = [$facetFields];
foreach ($facetFields as $ff) {
    if ($ff) $url .= '&facet.field=' . urlencode($ff);
}

$context  = stream_context_create(['http' => ['timeout' => 30, 'method' => 'GET']]);
$response = @file_get_contents($url, false, $context);

if (!$response) {
    http_response_code(500);
    echo json_encode(['error' => 'Cannot connect to Solr', 'url' => $url, 'fq_built' => $fq]);
    exit();
}

echo $response;