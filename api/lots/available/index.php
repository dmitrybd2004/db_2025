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
    if ($method === 'GET') {
        $query = "SELECT lot.item_id, lot.seller_name, lot.item_name, lot.price, lot.image_name, user_info.rating 
                  FROM lot 
                  JOIN user_info ON user_info.username = lot.seller_name
                  WHERE buyer_name IS NULL AND seller_name <> '$buyerName'";

        if (!empty($_GET['search'])) {
            $search = htmlspecialchars($_GET['search']);
            $query .= " AND item_name LIKE '%{$search}%'";
        }
        if (!empty($_GET['min_price'])) {
            $min_price = (float) $_GET['min_price'];
            $query .= " AND price >= $min_price";
        }
        if (!empty($_GET['max_price'])) {
            $max_price = (float) $_GET['max_price'];
            $query .= " AND price <= $max_price";
        }
        if (!empty($_GET['min_rating'])) {
            $min_rating = (int) $_GET['min_rating'];
            $query .= " AND user_info.rating >= $min_rating";
        }
        if (!empty($_GET['sort'])) {
            $sort_type = htmlspecialchars($_GET['sort']);
            if ($sort_type === "price_asc") {
                $query .= " ORDER BY price";
            } elseif ($sort_type === "price_desc") {
                $query .= " ORDER BY price DESC";
            } elseif ($sort_type === "rating") {
                $query .= " ORDER BY rating DESC";
            } else {
                $query .= " ORDER BY item_id DESC";
            }
        }
        if (!empty($_GET['limit'])) {
            $limit = (int) $_GET['limit'];
            $query .= " LIMIT $limit";
        }

        $result = $conn->query($query);
        if (!$result) {
            throw new Exception("Database error: " . $conn->error);
        }

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $row['price'] = (float) $row['price'];
            $items[] = $row;
        }

        echo json_encode([
            "status" => "success",
            "data" => $items
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

$conn->close();
?>