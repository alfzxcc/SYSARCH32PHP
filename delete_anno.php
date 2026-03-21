<?php
session_start();
require_once 'db_connect.php';

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    exit("Unauthorized");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM announcements WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        header("Location: admin_dashboard.php?msg=deleted");
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}
?>