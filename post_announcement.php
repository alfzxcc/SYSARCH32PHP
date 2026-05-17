<?php
session_start();
require_once 'db_connect.php';

// 1. FIX: Security Check (Update to match the 'admin' string)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_page.php?error=unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 2. Get and clean the data
    $title    = $conn->real_escape_string($_POST['title']);
    $category = $conn->real_escape_string($_POST['category']);
    $content  = $conn->real_escape_string($_POST['content']);

    // 3. FIX: Column Name Alignment
    // Even if MySQL auto-fills the date, being explicit prevents issues if the DB defaults change.
    $sql = "INSERT INTO announcements (title, category, content, created_at) 
            VALUES ('$title', '$category', '$content', NOW())";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('Announcement Published Successfully!');
                window.location.href='admin_dashboard.php';
              </script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>