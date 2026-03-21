<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If not logged in OR if the role is not 1 (Admin), kick them out
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header("Location: login_page.php?error=unauthorized");
    exit();
}
?>