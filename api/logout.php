<?php
require_once '../config/database.php';

$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

if ($token) {
    $conn = (new Database())->getConnection();
    $query = "DELETE FROM sessions WHERE session_token = :token";
    $stmt = $conn->prepare($query);
    $stmt->execute([':token' => $token]);
}

sendResponse(true, "Logged out successfully");
