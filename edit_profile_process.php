<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    
    // Sanitize all inputs
    $fname   = $conn->real_escape_string($_POST['firstname']);
    $lname   = $conn->real_escape_string($_POST['lastname']);
    $mname   = $conn->real_escape_string($_POST['midname']);
    $address = $conn->real_escape_string($_POST['address']);
    $email   = $conn->real_escape_string($_POST['email']);
    $course  = $conn->real_escape_string($_POST['course']);
    $level   = $conn->real_escape_string($_POST['course_level']);
    
    // Inside edit_profile_process.php
    $pass = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $pass_query = "";
    if (!empty($pass)) {
        if ($pass === $confirm) {
            $clean_pass = $conn->real_escape_string($pass);
            $pass_query = ", pass = '$clean_pass'";
        } else {
            echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
            exit();
        }
    }

    // Handle Photo Upload
    $photo_query = "";
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $ext = pathinfo($_FILES["profile_photo"]["name"], PATHINFO_EXTENSION);
        $new_name = $uid . "_" . time() . "." . $ext;
        $target_file = $target_dir . $new_name;

        if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
            $photo_query = ", profile_pic = '$target_file'";
        }
    }

    // The Master Update Query
    if ($conn->query($sql) === TRUE) {
        // REFRESH SESSION DATA SO IT REFLECTS IMMEDIATELY
        $_SESSION['firstName'] = $fname;
        $_SESSION['lastName'] = $lname;
        $_SESSION['midName'] = $mname;
        $_SESSION['course'] = $course;
        $_SESSION['course_level'] = $level;
        
        // Only update the session profile pic if a new one was actually uploaded
        if (!empty($photo_query)) {
            $_SESSION['profile_pic'] = $target_file;
        }

        echo "<script>alert('Your profile has been fully updated!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>