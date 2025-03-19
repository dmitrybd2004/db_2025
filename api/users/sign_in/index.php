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

        $username = $input['username'] ?? null;
        $password = $input['password'] ?? null;

        if (!$username || !$password) {
            throw new Exception("Username and password are required.");
        }

        $conn->begin_transaction();

        $query = "SELECT * FROM login WHERE username = ? OR password = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception("Username or password already taken.");
        }

        $query = "INSERT INTO user_info (username, balance, rating, products_bought, products_sold) VALUES (?, 0, 0, 0, 0)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $query = "INSERT INTO login (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        
        $query = "INSERT INTO roles (username) VALUES (?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $conn->commit();

        echo json_encode([
            "status" => "success",
            "message" => "User created successfully",
            "data" => [
                "redirect" => "/login/pages/home.html"
            ]
        ]);
    } else {
        throw new Exception("Method not allowed.");
    }
} catch (Exception $e) {
    if (isset($conn) && $conn->in_transaction) {
        $conn->rollback();
    }

    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>