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

$query = "SELECT role FROM roles WHERE username = '$username'";
$result = $conn->query($query);

if (!$result) {
    http_response_code(500);
    die(json_encode(["status" => "error", "message" => "Database query failed: " . $conn->error]));
}

if ($result->num_rows === 0) {
    http_response_code(404);
    die(json_encode(["status" => "error", "message" => "User not found"]));
}

$userData = $result->fetch_assoc();
if ($userData['role'] !== 'moderator') {
    http_response_code(403);
    die(json_encode(["status" => "error", "message" => "Access denied. You are not a moderator."]));
}

$request_id = $_GET['request_id'] ?? null;
if ($request_id) {
    $query = "SELECT lot.item_id, lot.buyer_name, lot.seller_name, lot.item_name, lot.price, lot.image_name, refunds.request_id, refunds.report_type, refunds.description, user_info.rating
              FROM lot 
              JOIN refunds ON lot.item_id = refunds.item_id
              JOIN user_info ON user_info.username = lot.seller_name
              WHERE refunds.processed = 0 AND refunds.request_id = $request_id";
    error_log("SQL Query: " . $query);

    $result = $conn->query($query);
    if (!$result) {
        error_log("Query Failed: " . $conn->error);
        http_response_code(500);
        die(json_encode(["status" => "error", "message" => "Database query failed: " . $conn->error]));
    }

    if ($result->num_rows === 0) {
        error_log("Request Not Found: request_id = $request_id");
        http_response_code(404);
        die(json_encode([
            "status" => "error",
            "message" => "Request not found or already processed.",
            "details" => [
                "request_id" => $request_id,
                "processed" => "Ensure the request exists and is unprocessed."
            ]
        ]));
    }

    $request = $result->fetch_assoc();
    $request['price'] = (float) $request['price'];
    $request['report_type'] = (int) $request['report_type'];
    echo json_encode([
        "status" => "success",
        "data" => $request
    ]);
} else {
    $query = "SELECT lot.item_id, lot.buyer_name, lot.item_name, lot.price, lot.image_name, refunds.request_id, refunds.report_type 
              FROM lot 
              JOIN refunds ON lot.item_id = refunds.item_id 
              WHERE refunds.processed = 0";
    $result = $conn->query($query);

    if (!$result) {
        http_response_code(500);
        die(json_encode(["status" => "error", "message" => "Database query failed: " . $conn->error]));
    }

    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $row['price'] = (float) $row['price'];
        $row['report_type'] = (int) $row['report_type'];
        $requests[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "data" => $requests
    ]);
}

$conn->close();
?>