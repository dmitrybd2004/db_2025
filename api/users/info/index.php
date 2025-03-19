<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../../../includes/db_connect.php';

error_log("Incoming Headers: " . print_r(getallheaders(), true));

if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
    if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } elseif (isset($_SERVER['Authorization'])) {
        $_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['Authorization'];
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Authorization header missing']);
        exit;
    }
}

$authHeader = $_SERVER['HTTP_AUTHORIZATION'];
$token = str_replace('Bearer ', '', $authHeader);

error_log("Token received: " . $token);

function validateToken($token) {
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

    $parts = explode('.', $token);
    if (count($parts) !== 2) {
        error_log("Invalid token format");
        return false;
    }

    $payload = json_decode(base64_decode($parts[0]), true);
    if (!$payload || !isset($payload['username'])) {
        error_log("Invalid token payload");
        return false;
    }

    error_log("Decoded payload: " . print_r($payload, true));

    if (isset($payload['exp']) && $payload['exp'] < time()) {
        error_log("Token expired");
        return false;
    }

    $expectedSignature = hash_hmac('sha256', json_encode($payload), $secretKey);
    if ($parts[1] !== $expectedSignature) {
        error_log("Invalid token signature");
        return false;
    }

    return $payload;
}

$payload = validateToken($token);
if (!$payload) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or expired token']);
    exit;
}

$username = $payload['username'];

error_log("Username from token: " . $username);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        global $conn;
        $stmt = $conn->prepare("SELECT balance, rating, products_bought, products_sold FROM user_info WHERE username = ?");
        if (!$stmt) {
            throw new Exception("Database error: Failed to prepare statement");
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($balance, $rating, $products_bought, $products_sold);
        $stmt->fetch();

        if ($balance === null) {
            throw new Exception("User not found: " . $username);
        }

        error_log("Balance: $balance, Rating: $rating, Products Bought: $products_bought, Products Sold: $products_sold");

        http_response_code(200);
        echo json_encode([
            'balance' => $balance,
            'username' => $username,
            'rating' => $rating,
            'products_bought' => $products_bought,
            'products_sold' => $products_sold
        ]);
    } catch (Exception $e) {
        error_log("Error fetching user data: " . $e->getMessage());

        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use GET']);
}