<?php
require_once 'db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_num  = $_POST['id_num'];
    $fname   = $_POST['firstname'];
    $lname   = $_POST['lastname'];
    $mname   = $_POST['midname'];

    // Custom course logic
    $course = $_POST['course'];
    if ($course === 'Other' && !empty($_POST['custom_course'])) {
        $course = $_POST['custom_course'];
    }

    $level   = $_POST['course_level'];
    $email   = $_POST['email'];
    $address = $_POST['address'];
    $pass    = $_POST['pass'];
    $confirm = $_POST['confirm_pass'];

    // Check passwords match
    if ($pass !== $confirm) {
        echo "<script>alert('Error: Passwords do not match!'); window.history.back();</script>";
        exit();
    }

    // Sanitize inputs
    $id_num  = $conn->real_escape_string(trim($id_num));
    $fname   = $conn->real_escape_string(trim($fname));
    $lname   = $conn->real_escape_string(trim($lname));
    $mname   = $conn->real_escape_string(trim($mname));
    $course  = $conn->real_escape_string(trim($course));
    $level   = $conn->real_escape_string($level);
    $email   = $conn->real_escape_string(trim(strtolower($email)));
    $address = $conn->real_escape_string(trim($address));
    $pass    = $conn->real_escape_string($pass);

    // STEP 1: Check if ID Number already exists
    $checkID = $conn->query("SELECT id_number FROM users WHERE id_number = '$id_num'");
    if ($checkID && $checkID->num_rows > 0) {
        echo "<script>alert('Error: ID Number is already registered!'); window.location.href='register.php';</script>";
        exit();
    }

    // STEP 2: Check if Email already exists
    $checkEmail = $conn->query("SELECT id_number FROM users WHERE email = '$email'");
    if ($checkEmail && $checkEmail->num_rows > 0) {
        echo "<script>alert('Error: Email address is already registered!\\nPlease use a different email or login to your existing account.'); window.location.href='register.php';</script>";
        exit();
    }

    // STEP 3: Insert new student
    $sql = "INSERT INTO users (id_number, lastname, firstname, midname, course_level, pass, email, course, address, role, remaining_sessions)
            VALUES ('$id_num', '$lname', '$fname', '$mname', '$level', '$pass', '$email', '$course', '$address', 'student', 30)";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Registration Successful! You can now login.'); window.location.href='login_page.php';</script>";
    } else {
        echo "<script>alert('Registration failed. Please try again.'); window.location.href='register.php';</script>";
    }
}

$conn->close();
?>
