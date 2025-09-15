<?php
// backend/config.php

// Start session
session_start();

// Include DB connection
include 'db.php'; // ✅ Ab db.php se connection milega

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
?>