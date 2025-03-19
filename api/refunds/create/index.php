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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['item_id']) || !isset($data['user_review']) || !isset($data['report_type'])) {
            throw new Exception("Missing required fields");
        }

        $item_id = $data['item_id'];
        $user_review = $data['user_review'];
        $report_type = $data['report_type'];

        $query = "SELECT seller_name, buyer_name, item_name FROM lot WHERE item_id = ? FOR UPDATE";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Database error: Failed to prepare statement");
        }
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();

        if (!$item) {
            throw new Exception("Item not found");
        }

        $seller = $item['seller_name'];
        $buyer = $item['buyer_name'];
        $item_name = $item['item_name'];

        $insertquery = "INSERT INTO refunds (item_id, item_name, description, report_type, sent_by) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertquery);
        if (!$stmt) {
            throw new Exception("Database error: Failed to prepare statement");
        }
        $stmt->bind_param("issis", $item_id, $item_name, $user_review, $report_type, $buyer);
        $stmt->execute();

        $conn->commit();

        http_response_code(201);
        echo json_encode(['message' => 'Refund request submitted successfully']);
    } catch (Exception $e) {
        if (isset($conn) && $conn->in_transaction) {
            $conn->rollback();
        }

        error_log("Error processing refund request: " . $e->getMessage());

        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST']);
}

$conn->close();
?>