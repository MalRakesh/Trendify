<?php
// backend/db.php - Database Connection

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials (WAMP default)
$host = "localhost";
// username
$username = "root";
$password = "RakeshMal@12345"; // ✅ WAMP ke liye blank
$database = "trendify_db";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

// Set charset
$conn->set_charset("utf8");
?>