<?php
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['token']) || !isset($data['email']) || !isset($data['new_password'])) {
    sendResponse(false, "Token, email, and new password are required", null, 400);
}

$token = $data['token'];
$email = $data['email'];
$newPassword = $data['new_password'];

if (strlen($newPassword) < 6) {
    sendResponse(false, "Password must be at least 6 characters", null, 400);
}

$conn = (new Database())->getConnection();

// Verify token
$query = "SELECT id FROM password_resets WHERE email = :email AND token = :token AND expires_at > NOW() AND used = 0";
$stmt = $conn->prepare($query);
$stmt->execute([':email' => $email, ':token' => $token]);
$reset = $stmt->fetch();

if (!$reset) {
    sendResponse(false, "Invalid or expired reset token", null, 400);
}

// Mark token as used
$updateReset = "UPDATE password_resets SET used = 1 WHERE id = :id";
$updateStmt = $conn->prepare($updateReset);
$updateStmt->execute([':id' => $reset['id']]);

// Update user password
$passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
$updateUser = "UPDATE users SET password_hash = :password_hash WHERE email = :email";
$updateUserStmt = $conn->prepare($updateUser);
$updateUserStmt->execute([':password_hash' => $passwordHash, ':email' => $email]);

// Delete all existing sessions for this user
$deleteSessions = "DELETE FROM sessions WHERE user_id IN (SELECT id FROM users WHERE email = :email)";
$deleteStmt = $conn->prepare($deleteSessions);
$deleteStmt->execute([':email' => $email]);

sendResponse(true, "Password reset successful. Please sign in with your new password.");
