<?php
require_once '../config/database.php';

$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

if (!$token) {
    sendResponse(false, "Authentication required", null, 401);
}

$conn = (new Database())->getConnection();
$userId = validateSession($conn, $token);

if (!$userId) {
    sendResponse(false, "Invalid session", null, 401);
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['post_id'])) {
    sendResponse(false, "Post ID required", null, 400);
}

$query = "DELETE FROM posts WHERE uuid = :uuid AND user_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->execute([
    ':uuid' => $data['post_id'],
    ':user_id' => $userId
]);

sendResponse(true, "Post deleted successfully");
