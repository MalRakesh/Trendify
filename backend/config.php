<?php
// backend/config.php - Database Configuration

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Database credentials
$host = "localhost";
$username = "root";
$password = "RakeshMal@12345"; // XAMPP mein default blank hota hai
$database = "trendify_db";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");

// Utility Functions
function showError($message) {
    $_SESSION['error'] = $message;
}

function showSuccess($message) {
    $_SESSION['success'] = $message;
}

function getMessage() {
    if (isset($_SESSION['success'])) {
        $msg = '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
        return $msg;
    }
    if (isset($_SESSION['error'])) {
        $msg = '<div class="alert alert-error">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
        return $msg;
    }
    return '';
}

// No need to include db.php anymore
?>