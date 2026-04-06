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

$status = isset($_GET['status']) ? $_GET['status'] : null;
$favoriteOnly = isset($_GET['favorites']) && $_GET['favorites'] === 'true';
$search = isset($_GET['search']) ? $_GET['search'] : null;

$query = "SELECT * FROM posts WHERE user_id = :user_id";
$params = [':user_id' => $userId];

if ($status && in_array($status, ['draft', 'saved'])) {
    $query .= " AND status = :status";
    $params[':status'] = $status;
}

if ($favoriteOnly) {
    $query .= " AND is_favorite = 1";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Apply search filter (PHP side for JSON fields)
if ($search) {
    $posts = array_filter($posts, function ($post) use ($search) {
        return stripos($post['description'], $search) !== false ||
            stripos($post['location'], $search) !== false ||
            stripos($post['price'], $search) !== false;
    });
    $posts = array_values($posts);
}

sendResponse(true, "Posts retrieved", ["posts" => $posts]);
