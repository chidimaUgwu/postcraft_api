<?php
require_once '../config/database.php';

$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

if (!$token) {
    sendResponse(false, "No session token provided", null, 401);
}

$conn = (new Database())->getConnection();
$userId = validateSession($conn, $token);

if (!$userId) {
    sendResponse(false, "Invalid or expired session", null, 401);
}

// Get user details
$query = "SELECT uuid, name, email FROM users WHERE id = :id";
$stmt = $conn->prepare($query);
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

sendResponse(true, "Session valid", [
    "user_id" => $user['uuid'],
    "name" => $user['name'],
    "email" => $user['email']
]);
