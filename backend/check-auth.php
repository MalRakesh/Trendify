<?php
// backend/check-auth.php - Check Login Status

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Start session
session_start();

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'success',
        'user' => [
            'name' => $_SESSION['name'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role']
        ]
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
}
?>