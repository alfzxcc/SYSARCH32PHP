<?php
require_once 'db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $user = $conn->real_escape_string($user);
    $pass = $conn->real_escape_string($pass);

    $sql = "SELECT * FROM users WHERE username = '$user' AND password = '$pass'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // SUCCESS: Redirect to dashboard or home
        echo "<script>
                alert('Login Successful! Welcome back, $user.');
                window.location.href='dashboard.php'; 
              </script>";
    } else {
        // FAILURE: Show alert and stay on the login page
        echo "<script>
                alert('Login Failed: Invalid username or password.');
                window.location.href='index.php';
              </script>";
    }
}   
?>