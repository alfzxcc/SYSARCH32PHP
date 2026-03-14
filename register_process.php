<?php
// Ensure this matches your connection filename (you used db_connect.php or db_connection.php)
require_once 'db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect data from the form
    $id_num   = $_POST['id_num'];
    $fname    = $_POST['firstname'];
    $lname    = $_POST['lastname'];
    $mname    = $_POST['midname'];
    $course   = $_POST['course'];
    $level    = $_POST['course_level'];
    $email    = $_POST['email'];
    $address  = $_POST['address'];
    $pass     = $_POST['pass'];
    $confirm  = $_POST['confirm_pass'];

    // Check if passwords match before doing anything else
    if ($pass !== $confirm) {
        echo "<script>
                alert('Error: Passwords do not match!');
                window.history.back();
              </script>";
        exit();
    }

    // Clean data to prevent SQL injection
    $id_num  = $conn->real_escape_string($id_num);
    $fname   = $conn->real_escape_string($fname);
    $lname   = $conn->real_escape_string($lname);
    $mname   = $conn->real_escape_string($mname);
    $course  = $conn->real_escape_string($course);
    $level   = $conn->real_escape_string($level);
    $email   = $conn->real_escape_string($email);
    $address = $conn->real_escape_string($address);
    $pass    = $conn->real_escape_string($pass);

    // STEP 1: Check if ID Number already exists (using your 'id' column)
    $checkQuery = "SELECT * FROM users WHERE id = '$id_num'";
    $result = $conn->query($checkQuery);

    if ($result->num_rows > 0) {
        echo "<script>
                alert('Error: ID Number already registered!');
                window.location.href='register.php';
              </script>";
    } else {
        // STEP 2: Insert all student details into the database
        // Columns: id, lastname, firstname, midname, course_level, pass, email, course, address
        $sql = "INSERT INTO users (id, lastname, firstname, midname, course_level, pass, email, course, address) 
                VALUES ('$id_num', '$lname', '$fname', '$mname', '$level', '$pass', '$email', '$course', '$address')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>
                    alert('Registration Successful! You can now login.');
                    window.location.href='login_page.php';
                  </script>";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
$conn->close();
?>