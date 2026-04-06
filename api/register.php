<?php
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
    sendResponse(false, "Name, email, and password are required", null, 400);
}

$name = trim($data['name']);
$email = trim($data['email']);
$password = $data['password'];

if (strlen($password) < 6) {
    sendResponse(false, "Password must be at least 6 characters", null, 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, "Invalid email format", null, 400);
}

$conn = (new Database())->getConnection();

// Check if email exists
$checkQuery = "SELECT id FROM users WHERE email = :email";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->execute([':email' => $email]);

if ($checkStmt->fetch()) {
    sendResponse(false, "Email already registered", null, 409);
}

// Create user
$uuid = uniqid() . bin2hex(random_bytes(16));
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$sessionToken = generateToken();

$query = "INSERT INTO users (uuid, name, email, password_hash) VALUES (:uuid, :name, :email, :password_hash)";
$stmt = $conn->prepare($query);
$stmt->execute([
    ':uuid' => $uuid,
    ':name' => $name,
    ':email' => $email,
    ':password_hash' => $passwordHash
]);

$userId = $conn->lastInsertId();

// Create session
$expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
$sessionQuery = "INSERT INTO sessions (user_id, session_token, expires_at) VALUES (:user_id, :token, :expires_at)";
$sessionStmt = $conn->prepare($sessionQuery);
$sessionStmt->execute([
    ':user_id' => $userId,
    ':token' => $sessionToken,
    ':expires_at' => $expiresAt
]);

sendResponse(true, "Registration successful", [
    "user_id" => $uuid,
    "name" => $name,
    "email" => $email,
    "session_token" => $sessionToken
]);
