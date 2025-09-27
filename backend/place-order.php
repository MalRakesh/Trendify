 <?php
// backend/place-order.php - Place Order Handler

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

$required = ['user_name', 'email', 'phone', 'shipping_address', 'total_amount', 'payment_method', 'items'];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => ucfirst($field) . ' is required']);
        exit();
    }
}

$user_name = trim($data['user_name']);
$email = trim($data['email']);
$phone = trim($data['phone']);
$shipping_address = trim($data['shipping_address']);
$total_amount = floatval($data['total_amount']);
$payment_method = $data['payment_method'];
$payment_status = $data['payment_status'];
$status = $data['status'];
$items = $data['items'];

// Generate order number
$order_number = 'ORD-' . strtoupper(substr(md5(time()), 0, 6));

// Start transaction
$conn->begin_transaction();

try {
    // Insert order
    $stmt = $conn->prepare("INSERT INTO orders (order_number, user_id, total_amount, status, shipping_address, contact_phone, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $user_id = null; // Guest order
    $stmt->bind_param("sidsssss", $order_number, $user_id, $total_amount, $status, $shipping_address, $phone, $payment_method, $payment_status);
    
    if (!$stmt->execute()) {
        throw new Exception("Order insert failed");
    }
    
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Insert order items
    foreach ($items as $item) {
        $product_id = intval($item['product_id']);
        $quantity = intval($item['quantity']);
        $price_per_unit = floatval($item['price_per_unit']);
        $total_price = $quantity * $price_per_unit;

        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_per_unit, total_price) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiidd", $order_id, $product_id, $quantity, $price_per_unit, $total_price);
        
        if (!$stmt->execute()) {
            throw new Exception("Order item insert failed");
        }
        $stmt->close();
    }

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Order placed successfully!',
        'order_number' => $order_number
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Order failed: ' . $e->getMessage()]);
}

$conn->close();
?>