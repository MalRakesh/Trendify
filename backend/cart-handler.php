<?php
// backend/cart-handler.php - Add/Remove/Get Cart Items

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");

include 'config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Login required']);
    exit();
}

$user_id = $_SESSION['user_id'];

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get user's cart
        $sql = "SELECT c.quantity, p.id, p.name, p.price, p.image 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $cart = [];
        while ($row = $result->fetch_assoc()) {
            $cart[] = $row;
        }

        echo json_encode(['status' => 'success', 'cart' => $cart]);
        break;

    case 'POST':
        // Add to cart
        $data = json_decode(file_get_contents("php://input"), true);
        $product_id = $data['product_id'];
        $qty = $data['quantity'] ?? 1;

        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + ?");
        $stmt->bind_param("iiii", $user_id, $product_id, $qty, $qty);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Added to cart']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to add']);
        }
        break;

    case 'PUT':
        // Update quantity
        $data = json_decode(file_get_contents("php://input"), true);
        $product_id = $data['product_id'];
        $qty = $data['quantity'];

        if ($qty < 1) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid quantity']);
            exit();
        }

        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = AND product_id = ?");
        $stmt->bind_param("iii", $qty, $user_id, $product_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Updated']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Update failed']);
        }
        break;

    case 'DELETE':
        // Remove from cart
        parse_str(file_get_contents("php://input"), $data);
        $product_id = $data['product_id'];

        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Removed']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Remove failed']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
$conn->close();
?>