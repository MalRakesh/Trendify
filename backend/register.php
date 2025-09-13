<?php
// backend/register.php - Advanced Registration

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Include config
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Only POST allowed']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$required = ['name', 'email', 'password', 'role'];
foreach ($required as $field) {
    if (!isset($data[$field]) || empty(trim($data[$field]))) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => ucfirst($field) . ' required']);
        exit();
    }
}

$name = trim($data['name']);
$email = trim($data['email']);
$password = $data['password'];
$role = strtolower(trim($data['role']));
$phone = $data['phone'] ?? null;

$valid_roles = ['customer', 'dealer', 'admin'];
if (!in_array($role, $valid_roles)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid role']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email']);
    exit();
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    http_response_code(409);
    echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
    exit();
}
$stmt->close();

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $hashed_password, $role, $phone);
    
    if (!$stmt->execute()) {
        throw new Exception("User insert failed");
    }
    
    $user_id = $stmt->insert_id;
    $stmt->close();

    switch ($role) {
        case 'customer':
            $stmt = $conn->prepare("INSERT INTO customer_profile (user_id) VALUES (?)");
            $stmt->bind_param("i", $user_id);
            break;

        case 'dealer':
            $shop_name = $data['shop_name'] ?? $name . "'s Shop";
            $gst_number = $data['gst_number'] ?? null;
            $stmt = $conn->prepare("INSERT INTO dealer_profile (user_id, shop_name, gst_number) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $shop_name, $gst_number);
            break;

        case 'admin':
            $access_level = $data['access_level'] ?? 'moderator';
            $stmt = $conn->prepare("INSERT INTO admin_profile (user_id, access_level) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $access_level);
            break;
    }

    if (!$stmt->execute()) {
        throw new Exception("Profile insert failed");
    }
    $stmt->close();

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Registration successful! Welcome to Trendify.',
        'user' => [
            'id' => $user_id,
            'name' => $name,
            'role' => $role
        ]
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Registration failed']);
}

$conn->close();
?>