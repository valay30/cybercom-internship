<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

$file = __DIR__ . '/saved_views.json';

function loadViews(string $file): array {
    if (!file_exists($file)) return [];
    return json_decode(file_get_contents($file), true) ?? [];
}

function saveViews(string $file, array $views): void {
    file_put_contents($file, json_encode($views, JSON_PRETTY_PRINT));
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode(loadViews($file));

} elseif ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body || empty($body['name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'name required']);
        exit();
    }
    $views = loadViews($file);
    $view  = [
        'id'         => uniqid(),
        'name'       => $body['name'],
        'columns'    => $body['columns']    ?? [],
        'filters'    => $body['filters']    ?? [],
        'sort'       => $body['sort']       ?? '',
        'colWidths'  => $body['colWidths']  ?? (object)[],
        'created_at' => date('Y-m-d H:i:s'),
    ];
    $views[] = $view;
    saveViews($file, $views);
    echo json_encode($view);

} elseif ($method === 'DELETE') {
    $id    = $_GET['id'] ?? '';
    $views = loadViews($file);
    $views = array_values(array_filter($views, fn($v) => $v['id'] !== $id));
    saveViews($file, $views);
    echo json_encode(['success' => true]);

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}