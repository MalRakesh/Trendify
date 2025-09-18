<?php
// backend/logout.php - User Logout

session_start();
session_unset();
session_destroy();

// Redirect to login or home
header("Location: ../frontend/login.html");
exit();
?>