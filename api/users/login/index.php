<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../../../includes/db_connect.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON input.");
        }

        error_log("Input data: " . print_r($input, true));

        $username = $input['username'] ?? null;
        $password = $input['password'] ?? null;

        if (!$username || !$password) {
            throw new Exception("Username and password are required.");
        }

        $query = "SELECT * FROM login WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            error_log("User found: " . print_r($user, true));

            if ($password !== $user['password']) {
                error_log("Password mismatch for user: " . $username);
                throw new Exception("Invalid username or password.");
            }

            if ($user['banned'] == 1) {
                throw new Exception("Your account has been banned.");
            }

            $query = "SELECT * FROM roles WHERE username = ? AND role = 'moderator'";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            $payload = [
                'username' => $username,
                'role' => 'user',
                'exp' => time() + 3600
            ];
            
            $envPath = __DIR__ . '/../../../config/.env';
            if (file_exists($envPath)) {
                $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos(trim($line), '#') === 0) {
                        continue;
                    }
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[$key] = $value;
                }
            }
            
            $secretKey = $_ENV['JWT_SECRET_KEY'];

            $token = base64_encode(json_encode($payload)) . '.' . hash_hmac('sha256', json_encode($payload), $secretKey);

            $redirect = $result->num_rows > 0 ? "/login/pages/home_mod.html" : "/login/pages/home.html";

            echo json_encode([
                "status" => "success",
                "message" => "Login successful",
                "data" => [
                    "token" => $token,
                    "redirect" => $redirect
                ]
            ]);
        } else {
            error_log("No user found for username: " . $username);
            throw new Exception("Invalid username or password.");
        }
    } elseif ($method === 'DELETE') {
        echo json_encode([
            "status" => "success",
            "message" => "Logged out successfully"
        ]);
    } else {
        throw new Exception("Method not allowed.");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}