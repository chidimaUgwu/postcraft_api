<?php
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email'])) {
    sendResponse(false, "Email is required", null, 400);
}

$email = trim($data['email']);

$conn = (new Database())->getConnection();

// Check if user exists
$checkQuery = "SELECT id FROM users WHERE email = :email";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->execute([':email' => $email]);
$user = $checkStmt->fetch();

if (!$user) {
    // Don't reveal that email doesn't exist for security
    sendResponse(true, "If your email is registered, you will receive a reset link");
}

// Create reset token
$resetToken = generateToken(32);
$expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

$query = "INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)";
$stmt = $conn->prepare($query);
$stmt->execute([
    ':email' => $email,
    ':token' => $resetToken,
    ':expires_at' => $expiresAt
]);

// Send email (using your server's mail function)
$resetLink = "http://169.239.251.102:280/~chidima.ugwu/reset_password.html?token=$resetToken&email=" . urlencode($email);
$subject = "Reset Your PostCraft AI Password";
$message = "Hello,\n\nClick the link below to reset your password:\n\n$resetLink\n\nThis link expires in 1 hour.\n\nIf you didn't request this, ignore this email.\n\n- PrimeNest Realty";
$headers = "From: noreply@primenext.com";

mail($email, $subject, $message, $headers);

sendResponse(true, "If your email is registered, you will receive a reset link");
