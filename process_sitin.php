<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Determine the student ID (If admin types it in 'username', use that. Else use logged-in ID)
    $student_id = !empty($_POST['username']) ? $conn->real_escape_string($_POST['username']) : $_SESSION['user_id'];
    
    $lab = $conn->real_escape_string($_POST['lab_room']);
    $purpose = $conn->real_escape_string($_POST['purpose']);

    // 1. Fetch the student's current session balance from the DB (crucial for Admin manual entry)
    $checkSql = "SELECT sessions, role FROM users WHERE id = '$student_id'";
    $checkRes = $conn->query($checkSql);

    if ($checkRes && $checkRes->num_rows > 0) {
        $userData = $checkRes->fetch_assoc();
        
        if ($userData['sessions'] > 0) {
            // 2. Record the sit-in
            $sqlRecord = "INSERT INTO sitin_records (student_id, lab_room, purpose) VALUES ('$student_id', '$lab', '$purpose')";
            
            if ($conn->query($sqlRecord)) {
                // 3. Deduct session from DB
                $conn->query("UPDATE users SET sessions = sessions - 1 WHERE id = '$student_id'");

                // 4. If the person sitting in IS the logged-in user, update their session variable
                if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $student_id) {
                    $_SESSION['sessions'] -= 1;
                }

                echo "<script>alert('Sit-in Record Created for $student_id'); window.location.href='" . ($_SESSION['role'] == 1 ? "admin_sitin_manage.php" : "dashboard.php") . "';</script>";
            }
        } else {
            echo "<script>alert('Error: Student has 0 sessions left!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Error: Student ID not found in database.'); window.history.back();</script>";
    }
}
?>