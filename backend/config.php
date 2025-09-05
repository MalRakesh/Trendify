<?php
/**
 * Trendify - Backend Configuration & Database Connection
 * 
 * This file handles:
 * - Database connection via MySQLi
 * - Error reporting
 * - Session start
 * - Constants definition
 * - Security headers
 * - Utility functions
 * 
 * @package Trendify
 * @author Developer Bhai
 * @version 1.0
 * @since 2025
 */

// Enable error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Define constants
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('FRONTEND_PATH', ROOT_PATH . '/frontend');
define('BACKEND_PATH', ROOT_PATH . '/backend');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('DEALER_PATH', ROOT_PATH . '/dealer');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('UPLOAD_DIR', ASSETS_PATH . '/uploads/');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'RakeshMal@12345');
define('DB_NAME', 'trendify_db');

// Create connection
class Database {
    public $conn;

    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error);
        }

        // Set charset
        $this->conn->set_charset("utf8");

        // Enable strict mode
        $this->conn->query("SET sql_mode = 'STRICT_TRANS_TABLES'");
    }

    public function getConnection() {
        return $this->conn;
    }
}

// Initialize DB
$db = new Database();
$conn = $db->getConnection();

// Utility Functions

/**
 * Sanitize input data
 */
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

/**
 * Redirect to a page
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Show error message
 */
function showError($message) {
    $_SESSION['error'] = $message;
}

/**
 * Show success message
 */
function showSuccess($message) {
    $_SESSION['success'] = $message;
}

/**
 * Get session message
 */
function getMessage() {
    if (isset($_SESSION['success'])) {
        $msg = '<div class="alert success">' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
        return $msg;
    }
    if (isset($_SESSION['error'])) {
        $msg = '<div class="alert error">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
        return $msg;
    }
    return '';
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user is dealer
 */
function isDealer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'dealer';
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate CSRF Token
 */
function generateCSRF() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF Token
 */
function validateCSRF($token) {
    if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $token) {
        showError("CSRF token invalid. Possible attack.");
        return false;
    }
    return true;
}

/**
 * Log activity (basic)
 */
function logActivity($action) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_id = isLoggedIn() ? $_SESSION['user_id'] : 0;
    global $conn;
    $stmt = $conn->prepare("INSERT INTO logs (user_id, action, ip, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $user_id, $action, $ip);
    $stmt->execute();
    $stmt->close();
}

/**
 * Upload image securely
 */
function uploadImage($file, $folder = 'products') {
    $target_dir = UPLOAD_DIR . $folder . '/';
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($imageFileType, $allowed)) {
        showError("Only JPG, JPEG, PNG & GIF files allowed.");
        return false;
    }

    if ($file["size"] > 5000000) {
        showError("File too large. Max 5MB.");
        return false;
    }

    $filename = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $filename;

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return "assets/uploads/$folder/$filename";
    } else {
        showError("Upload failed.");
        return false;
    }
}

// Set security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

// End of config.php
// Total Lines: ~350
?>