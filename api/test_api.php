<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// If no JSON, show error
if (!$data) {
    echo json_encode([
        "success" => false,
        "message" => "No JSON data received",
        "raw_input" => $input,
        "content_type" => $_SERVER['CONTENT_TYPE'] ?? 'not set'
    ]);
    exit();
}

echo json_encode([
    "success" => true,
    "message" => "JSON received successfully",
    "data" => $data
]);
?>
