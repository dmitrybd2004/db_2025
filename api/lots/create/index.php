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

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'POST') {
        $conn->begin_transaction();

        $sellerName = $payload['username'];

        if (empty($_POST['item_name'])) {
            throw new Exception("Item name is required");
        }
        if (empty($_FILES['item_image'])) {
            throw new Exception("Item image is required");
        }

        $imagesDir = __DIR__ . '/../../../image/';

        if (!is_dir($imagesDir)) {
            if (!mkdir($imagesDir, 0755, true)) {
                throw new Exception("Failed to create images directory");
            }
        }

        $file_name = basename($_FILES['item_image']['name']);
        $target_file = $imagesDir . $file_name;

        if (!move_uploaded_file($_FILES['item_image']['tmp_name'], $target_file)) {
            throw new Exception("Failed to upload image");
        }

        $item_name = $_POST['item_name'];
        $price = (float) $_POST['price'];

        $stmt = $conn->prepare("INSERT INTO lot (seller_name, item_name, price, image_name) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Database error: Failed to prepare statement");
        }
        $stmt->bind_param("ssds", $sellerName, $item_name, $price, $file_name);
        if (!$stmt->execute()) {
            throw new Exception("Database error: Failed to execute statement");
        }

        $conn->commit();

        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "data" => [
                "seller_name" => $sellerName,
                "item_name" => $item_name,
                "price" => $price,
                "image_url" => "/login/images/$file_name",
            ]
        ]);
    } else {
        throw new Exception("Method not allowed");
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