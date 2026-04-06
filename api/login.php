<?php
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || !isset($data['password'])) {
    sendResponse(false, "Email and password are required", null, 400);
}

$email = trim($data['email']);
$password = $data['password'];

$conn = (new Database())->getConnection();

$query = "SELECT id, uuid, name, email, password_hash FROM users WHERE email = :email";
$stmt = $conn->prepare($query);
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    sendResponse(false, "Invalid email or password", null, 401);
}

// Create session
$sessionToken = generateToken();
$expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

$sessionQuery = "INSERT INTO sessions (user_id, session_token, expires_at) VALUES (:user_id, :token, :expires_at)";
$sessionStmt = $conn->prepare($sessionQuery);
$sessionStmt->execute([
    ':user_id' => $user['id'],
    ':token' => $sessionToken,
    ':expires_at' => $expiresAt
]);

sendResponse(true, "Login successful", [
    "user_id" => $user['uuid'],
    "name" => $user['name'],
    "email" => $user['email'],
    "session_token" => $sessionToken
]);
