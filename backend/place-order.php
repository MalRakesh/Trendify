<?php
// backend/place-order.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Only POST allowed']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

// Auth check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$cart = $data['cart'] ?? [];
$shipping_address = trim($data['address'] ?? '');
$contact_phone = trim($data['phone'] ?? '');
$payment_method = $data['payment_method'] ?? 'cod'; // cod | online

if (empty($cart)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
    exit();
}

if (empty($shipping_address) || empty($contact_phone)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Shipping address and phone required']);
    exit();
}

$total_amount = 0;
$order_items = [];

$conn->begin_transaction();

try {
    // Calculate total & validate products
    foreach ($cart as $item) {
        $stmt = $conn->prepare("SELECT id, name, price FROM products WHERE id = ? AND is_active = TRUE");
        $stmt->bind_param("i", $item['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("Product not available: " . $item['id']);
        }
        $product = $result->fetch_assoc();
        $total_amount += $product['price'] * $item['qty'];

        $order_items[] = [
            'product_id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'qty' => $item['qty']
        ];
    }

    // Generate order number
    $order_number = "ORD-" . date("Ymd") . rand(1000, 9999);

    // Insert order
    $stmt = $conn->prepare("INSERT INTO orders (order_number, user_id, total_amount, shipping_address, contact_phone, payment_method) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sidsss", $order_number, $user_id, $total_amount, $shipping_address, $contact_phone, $payment_method);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // Insert order items
    $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_per_unit, total_price) VALUES (?, ?, ?, ?, ?)");
    foreach ($order_items as $item) {
        $total_price = $item['price'] * $item['qty'];
        $stmt_item->bind_param("iiidd", $order_id, $item['product_id'], $item['qty'], $item['price'], $total_price);
        $stmt_item->execute();
    }

    $conn->commit();

    // Clear cart after order
    // Note: frontend will clear it

    echo json_encode([
        'status' => 'success',
        'message' => 'Order placed successfully!',
        'order' => [
            'id' => $order_number,
            'date' => date('Y-m-d H:i:s'),
            'total' => $total_amount,
            'payment_method' => $payment_method,
            'items' => $order_items
        ]
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Order failed: ' . $e->getMessage()]);
}

$conn->close();
?>