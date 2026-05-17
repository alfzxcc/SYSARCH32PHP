<?php
session_start();
require_once 'db_connect.php';
$conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Determine the student's ID number (If admin types it in 'username', use that. Else use logged-in student's id_number)
    $student_id = !empty($_POST['username']) ? $conn->real_escape_string(trim($_POST['username'])) : ($_SESSION['id_number'] ?? '');
    
    if (empty($student_id)) {
        echo "<script>alert('Error: Invalid Student Identifier.'); window.history.back();</script>";
        exit();
    }

    $lab = $conn->real_escape_string($_POST['lab_room']);
    $purpose = $conn->real_escape_string($_POST['purpose']);

    // 1. FIXED: Changed 'sessions' to 'remaining_sessions' and targeted 'id_number'
    $checkSql = "SELECT remaining_sessions, role FROM users WHERE id_number = '$student_id'";
    $checkRes = $conn->query($checkSql);

    if ($checkRes && $checkRes->num_rows > 0) {
        $userData = $checkRes->fetch_assoc();
        
        // Check structural limits using the correct column name
        if ($userData['remaining_sessions'] > 0) {
            
            // 2. FIXED: Inserting directly into sitin_history using its structural schema layout
            $sqlRecord = "INSERT INTO sitin_history (id_number, lab_name, purpose, status, login_time) 
                          VALUES ('$student_id', '$lab', '$purpose', 'Active', NOW())";
            
            if ($conn->query($sqlRecord)) {
                
                // 3. FIXED: Deduct from remaining_sessions column using the student's id_number
                $conn->query("UPDATE users SET remaining_sessions = remaining_sessions - 1 WHERE id_number = '$student_id'");

                // 4. Update active local session tokens if the student is logging themselves in
                if (isset($_SESSION['id_number']) && $_SESSION['id_number'] == $student_id) {
                    if (isset($_SESSION['remaining_sessions'])) {
                        $_SESSION['remaining_sessions'] -= 1;
                    }
                }

                // Redirect dynamically based on the user's role
                $redirectPage = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? "admin_current_sitin.php" : "dashboard.php";
                
                echo "<script>
                    alert('Sit-in Session successfully activated for ID: $student_id'); 
                    window.location.href='$redirectPage';
                </script>";
                exit();
            } else {
                echo "<script>alert('Database Error: Unable to record session.'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('Access Denied: Student has 0 remaining sessions left!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Error: Student ID ($student_id) not found in database.'); window.history.back();</script>";
    }
}
?>