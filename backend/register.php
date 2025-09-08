<?php
// backend/register.php

// Allow CORS (for local testing)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Only POST allowed']);
    exit();
}

// Database Connection
$host = "localhost";
$username = "root";
$password = "";
$database = "trendify_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

// Get JSON data
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['name'], $data['email'], $data['password'], $data['role'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit();
}

$name = trim($data['name']);
$email = trim($data['email']);
$password = $data['password']; // Will hash below
$role = $data['role'];
$phone = $data['phone'] ?? null;
$address = $data['address'] ?? null;
$city = $data['city'] ?? null;
$state = $data['state'] ?? null;
$pincode = $data['pincode'] ?? null;

// Validate role
if (!in_array($role, ['customer', 'dealer', 'admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid role']);
    exit();
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email']);
    exit();
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email already registered']);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

// Insert user
$stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone, address, city, state, pincode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssss", $name, $email, $hashed_password, $role, $phone, $address, $city, $state, $pincode);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Registration successful',
        'user' => [
            'name' => $name,
            'email' => $email,
            'role' => $role
        ]
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>