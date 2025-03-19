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
        $input = json_decode(file_get_contents('php://input'), true);
        $amount = $input['amount'] ?? null;

        if ($amount === null || $amount === '') {
            throw new Exception("Please enter the amount of funds");
        }
        if (!is_numeric($amount) || $amount <= 0) {
            throw new Exception("Invalid amount. Please enter a positive number");
        }

        $conn->begin_transaction();

        $stmt = $conn->prepare("SELECT balance FROM user_info WHERE username = ? FOR UPDATE");
        if (!$stmt) {
            throw new Exception("Database error: Failed to prepare statement");
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($old_balance);
        $stmt->fetch();
        $stmt->close();

        if ($old_balance === null) {
            throw new Exception("User not found: " . $username);
        }

        $new_balance = $old_balance + $amount;

        $stmt = $conn->prepare("UPDATE user_info SET balance = ? WHERE username = ?");
        if (!$stmt) {
            throw new Exception("Database error: Failed to prepare statement");
        }
        $stmt->bind_param("ds", $new_balance, $username);
        if (!$stmt->execute()) {
            throw new Exception("Database error: Failed to update balance");
        }
        $stmt->close();

        $conn->commit();

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'Deposit successful',
            'new_balance' => $new_balance
        ]);
    } catch (Exception $e) {
        if (isset($conn) && $conn->in_transaction) {
            $conn->rollback();
        }

        error_log("Error processing deposit: " . $e->getMessage());

        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST']);
}

$conn->close();
?>