<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

class Database
{
    private $host = "localhost";
    private $db_name = "mobileapps_2026B_chidima_ugwu";
    private $username = "chidima.ugwu";
    private $password = "66071288";
    private $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => "Database connection failed: " . $e->getMessage()]);
            exit();
        }
        return $this->conn;
    }
}

function sendResponse($success, $message, $data = null, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit();
}

function generateToken($length = 64)
{
    return bin2hex(random_bytes($length));
}

function validateSession($conn, $token)
{
    $query = "SELECT user_id FROM sessions WHERE session_token = :token AND expires_at > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->execute([':token' => $token]);
    $result = $stmt->fetch();
    return $result ? $result['user_id'] : null;
}
