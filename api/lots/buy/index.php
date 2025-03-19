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
        echo json_encode(["status" => "error", "message" => "Authorization header missing"]);
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
    echo json_encode(["status" => "error", "message" => "Invalid or expired token"]);
    exit;
}

$buyerName = $payload['username'];

if (!$buyerName) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Username not found in token"]);
    exit;
}
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'POST') {
        $conn->begin_transaction();

        $itemId = $_GET['id'] ?? null;

        if (!$itemId) {
            throw new Exception("Item ID is required.");
        }

        error_log("Item ID: " . $itemId);

        $input = json_decode(file_get_contents('php://input'), true);
        $buyerName = $input['buyer_name'] ?? null;

        if (!$buyerName) {
            throw new Exception("Buyer name is required.");
        }

        error_log("Buyer Name: " . $buyerName);

        $query = "SELECT seller_name, price FROM lot WHERE item_id = ? AND buyer_name IS NULL FOR UPDATE";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Database error: Failed to prepare statement");
        }
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            throw new Exception("Item not available for purchase.");
        }
        $item = $result->fetch_assoc();

        error_log("Item Details: " . print_r($item, true));

        $query = "SELECT balance FROM user_info WHERE username = ? FOR UPDATE";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Database error: Failed to prepare statement");
        }
        $stmt->bind_param("s", $buyerName);
        $stmt->execute();
        $result = $stmt->get_result();
        $buyer = $result->fetch_assoc();

        if ($buyer['balance'] < $item['price']) {
            throw new Exception("Insufficient balance.");
        }

        error_log("Buyer Balance: " . $buyer['balance']);

        $query = "UPDATE user_info SET balance = balance + ?, products_sold = products_sold + 1 WHERE username = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Database error: Failed to prepare statement");
        }
        $stmt->bind_param("ds", $item['price'], $item['seller_name']);
        $stmt->execute();

        error_log("Seller balance updated.");

        $query = "UPDATE user_info SET balance = balance - ?, products_bought = products_bought + 1 WHERE username = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Database error: Failed to prepare statement");
        }
        $stmt->bind_param("ds", $item['price'], $buyerName);
        $stmt->execute();

        error_log("Buyer balance updated.");

        $query = "UPDATE lot SET buyer_name = ? WHERE item_id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Database error: Failed to prepare statement");
        }
        $stmt->bind_param("si", $buyerName, $itemId);
        $stmt->execute();

        error_log("Item marked as sold.");

        $conn->commit();

        echo json_encode([
            "status" => "success",
            "message" => "Item purchased successfully."
        ]);
    } else {
        throw new Exception("Method not allowed.");
    }
} catch (Exception $e) {
    if (isset($conn) && $conn->in_transaction) {
        $conn->rollback();
    }

    error_log("Error: " . $e->getMessage());

    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

$conn->close();
?>