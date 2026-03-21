<?php
session_start();
require_once 'db_connect.php';

// Check if the user is actually an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: login_page.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Get and clean the data
    $title    = $conn->real_escape_string($_POST['title']);
    $category = $conn->real_escape_string($_POST['category']);
    $content  = $conn->real_escape_string($_POST['content']);

    // 2. Insert into database
    $sql = "INSERT INTO announcements (title, category, content) 
            VALUES ('$title', '$category', '$content')";

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