<?php
session_start(); // Starts the session to track the user
require_once 'db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect ID and Password from the login form
    // Note: ensure your login.php uses name="id_num" and name="pass"
    $id_input = $_POST['username']; // Re-using your variable but it will hold the ID
    $pass_input = $_POST['password'];

    // Clean data
    $id_input = $conn->real_escape_string($id_input);
    $pass_input = $conn->real_escape_string($pass_input);

    // Search for the ID and Password in your 'users' table
    $sql = "SELECT * FROM users WHERE id = '$id_input' AND pass = '$pass_input'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();

        // Store user info in session variables for use on the dashboard
        $_SESSION['user_id'] = $user_data['id'];
        $_SESSION['firstname'] = $user_data['firstname'];
        $_SESSION['lastname'] = $user_data['lastname'];
        $_SESSION['course'] = $user_data['course'];

        // SUCCESS
        echo "<script>
                alert('Login Successful! Welcome back, " . $user_data['firstname'] . ".');
                window.location.href='dashboard.php'; 
              </script>";
    } else {
        // FAILURE
        echo "<script>
                alert('Login Failed: Invalid ID Number or Password.');
                window.location.href='login_page.php';
              </script>";
    }
}
$conn->close();
?>