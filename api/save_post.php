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

if (!isset($data['post'])) {
    sendResponse(false, "Post data required", null, 400);
}

$post = $data['post'];
$postId = isset($post['id']) ? $post['id'] : null;

// Prepare data
$uuid = $postId ?? (uniqid() . bin2hex(random_bytes(16)));
$imagePaths = json_encode($post['imagePaths']);
$specialFeatures = json_encode($post['specialFeatures']);
$platforms = json_encode($post['platforms']);
$captionsHistory = json_encode($post['captionsHistory']);

if ($postId) {
    // Update existing post
    $query = "UPDATE posts SET 
        image_paths = :image_paths,
        description = :description,
        price = :price,
        bedrooms = :bedrooms,
        bathrooms = :bathrooms,
        property_type = :property_type,
        units = :units,
        water_available = :water_available,
        light_available = :light_available,
        location = :location,
        special_features = :special_features,
        mood = :mood,
        platforms = :platforms,
        captions_history = :captions_history,
        selected_version = :selected_version,
        status = :status,
        is_favorite = :is_favorite
    WHERE uuid = :uuid AND user_id = :user_id";

    $stmt = $conn->prepare($query);
    $stmt->execute([
        ':image_paths' => $imagePaths,
        ':description' => $post['description'],
        ':price' => $post['price'],
        ':bedrooms' => $post['bedrooms'],
        ':bathrooms' => $post['bathrooms'],
        ':property_type' => $post['propertyType'],
        ':units' => $post['units'],
        ':water_available' => $post['waterAvailable'] ? 1 : 0,
        ':light_available' => $post['lightAvailable'] ? 1 : 0,
        ':location' => $post['location'],
        ':special_features' => $specialFeatures,
        ':mood' => $post['mood'],
        ':platforms' => $platforms,
        ':captions_history' => $captionsHistory,
        ':selected_version' => $post['selectedVersion'],
        ':status' => $post['status'],
        ':is_favorite' => $post['isFavorite'] ? 1 : 0,
        ':uuid' => $uuid,
        ':user_id' => $userId
    ]);
} else {
    // Create new post
    $query = "INSERT INTO posts (
        uuid, user_id, image_paths, description, price, bedrooms, bathrooms,
        property_type, units, water_available, light_available, location,
        special_features, mood, platforms, captions_history, selected_version,
        status, is_favorite
    ) VALUES (
        :uuid, :user_id, :image_paths, :description, :price, :bedrooms, :bathrooms,
        :property_type, :units, :water_available, :light_available, :location,
        :special_features, :mood, :platforms, :captions_history, :selected_version,
        :status, :is_favorite
    )";

    $stmt = $conn->prepare($query);
    $stmt->execute([
        ':uuid' => $uuid,
        ':user_id' => $userId,
        ':image_paths' => $imagePaths,
        ':description' => $post['description'],
        ':price' => $post['price'],
        ':bedrooms' => $post['bedrooms'],
        ':bathrooms' => $post['bathrooms'],
        ':property_type' => $post['propertyType'],
        ':units' => $post['units'],
        ':water_available' => $post['waterAvailable'] ? 1 : 0,
        ':light_available' => $post['lightAvailable'] ? 1 : 0,
        ':location' => $post['location'],
        ':special_features' => $specialFeatures,
        ':mood' => $post['mood'],
        ':platforms' => $platforms,
        ':captions_history' => $captionsHistory,
        ':selected_version' => $post['selectedVersion'],
        ':status' => $post['status'],
        ':is_favorite' => $post['isFavorite'] ? 1 : 0
    ]);
}

sendResponse(true, "Post saved successfully", ["post_id" => $uuid]);
