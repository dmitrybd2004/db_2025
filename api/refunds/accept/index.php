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

$input = json_decode(file_get_contents('php://input'), true);
$request_id = $input['request_id'] ?? null;

if (!$request_id) {
    http_response_code(400);
    die(json_encode(["status" => "error", "message" => "Request ID is required"]));
}

$query = "SELECT * FROM refunds WHERE request_id = $request_id";
$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    http_response_code(404);
    die(json_encode(["status" => "error", "message" => "Request not found"]));
}

$request = $result->fetch_assoc();
$type = $request['report_type'];
$item_id = $request['item_id'];

$query = "SELECT * FROM lot WHERE item_id = $item_id";
$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    http_response_code(404);
    die(json_encode(["status" => "error", "message" => "Item not found"]));
}

$item = $result->fetch_assoc();
$price = $item['price'];
$buyer = $item['buyer_name'];
$seller = $item['seller_name'];

error_log("Price: " . $price);
error_log("Buyer: " . $buyer);
error_log("Seller: " . $seller);

$conn->begin_transaction();

try {
    if ($type == 0) {
        $queries = [
            "UPDATE user_info SET balance = balance + ? WHERE username = ?",
            "UPDATE user_info SET balance = balance - ? WHERE username = ?",
            "UPDATE user_info SET products_sold = products_sold - 1 WHERE username = ?",
            "UPDATE user_info SET products_bought = products_bought - 1 WHERE username = ?",
            "UPDATE lot SET buyer_name = NULL WHERE buyer_name = ? AND item_id = ?",
            "UPDATE refunds SET processed = 1, accepted = 1 WHERE request_id = ?"
        ];

        foreach ($queries as $query) {
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Database error: Failed to prepare statement");
            }
            if ($query === $queries[0]) {
                $stmt->bind_param("ds", $price, $buyer);
            } elseif ($query === $queries[1]) {
                $stmt->bind_param("ds", $price, $seller);
            } elseif ($query === $queries[2]) {
                $stmt->bind_param("s", $seller);
            } elseif ($query === $queries[3]) {
                $stmt->bind_param("s", $buyer);
            } elseif ($query === $queries[4]) {
                $stmt->bind_param("si", $buyer, $item_id);
            } else {
                $stmt->bind_param("i", $request_id);
            }
            $stmt->execute();
        }
    } else if ($type == 1) {
        $queries = [
            "UPDATE user_info SET balance = balance + ? WHERE username = ?",
            "UPDATE user_info SET balance = balance - ? WHERE username = ?",
            "UPDATE user_info SET products_sold = products_sold - 1 WHERE username = ?",
            "UPDATE user_info SET products_bought = products_bought - 1 WHERE username = ?",
            "UPDATE lot SET buyer_name = NULL WHERE buyer_name = ? AND item_id = ?",
            "UPDATE refunds SET processed = 1, accepted = 1 WHERE request_id = ?",
            "UPDATE login SET banned = 1 WHERE username = ?"
        ];

        foreach ($queries as $query) {
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Database error: Failed to prepare statement");
            }
            if ($query === $queries[0]) {
                $stmt->bind_param("ds", $price, $buyer);
            } elseif ($query === $queries[1]) {
                $stmt->bind_param("ds", $price, $seller);
            } elseif ($query === $queries[2]) {
                $stmt->bind_param("s", $seller);
            } elseif ($query === $queries[3]) {
                $stmt->bind_param("s", $buyer);
            } elseif ($query === $queries[4]) {
                $stmt->bind_param("si", $buyer, $item_id);
            } elseif ($query === $queries[5]) {
                $stmt->bind_param("i", $request_id);
            } else {
                $stmt->bind_param("s", $seller);
            }
            $stmt->execute();
        }

        $query = "SELECT image_name FROM lot WHERE buyer_name IS NULL AND seller_name = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Database error: Failed to prepare statement");
        }
        $stmt->bind_param("s", $seller);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $file_name = htmlspecialchars($row['image_name']);
                $target_file = __DIR__ . "/../../../image/" . $file_name;
                if (file_exists($target_file)) {
                    unlink($target_file);
                }
            }
        }

        $query = "DELETE FROM lot WHERE buyer_name IS NULL AND seller_name = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Database error: Failed to prepare statement");
        }
        $stmt->bind_param("s", $seller);
        $stmt->execute();
    }

    $conn->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Refund request accepted."
    ]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    die(json_encode(["status" => "error", "message" => "Failed to process refund request: " . $e->getMessage()]));
}

$conn->close();
?>