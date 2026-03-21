<?php
<<<<<<< HEAD
require_once 'db_connect.php'; // This pulls in your database bridge

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Protect against basic SQL injection using real_escape_string
    $user = $conn->real_escape_string($user);
    $pass = $conn->real_escape_string($pass);

    // Query the database
    $sql = "SELECT * FROM users WHERE username = '$user' AND password = '$pass'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<h1>Login Successful!</h1>";
        echo "<p>Welcome back to SYSARCH32, " . htmlspecialchars($user) . ".</p>";
    } else {
        echo "<h1>Login Failed!</h1>";
        echo "<p>Invalid credentials in database.</p>";
        echo "<a href='index.php'>Try Again</a>";
    }
}
=======
session_start(); 
require_once 'db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collecting ID and Password from the login form
    $id_input = $_POST['username']; 
    $pass_input = $_POST['password'];

    // Clean data to prevent SQL Injection
    $id_input = $conn->real_escape_string($id_input);
    $pass_input = $conn->real_escape_string($pass_input);

    // Search for the ID and Password in your 'users' table
    $sql = "SELECT * FROM users WHERE id = '$id_input' AND pass = '$pass_input'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();

        // 1. Store ALL user info in session (Matches your phpMyAdmin column casing)
        $_SESSION['user_id']      = $user_data['id'];
        $_SESSION['sessions'] = $user_data['sessions']; // Add this line!
        $_SESSION['lastName']     = $user_data['lastName'];   
        $_SESSION['firstName']    = $user_data['firstName'];  
        $_SESSION['midName']      = $user_data['midName'];    
        $_SESSION['course_level'] = $user_data['course_level'];
        $_SESSION['email']        = $user_data['email'];
        $_SESSION['course']       = $user_data['course'];
        $_SESSION['address']      = $user_data['address'];
        
        // 2. STORE THE ROLE (Critical for Admin access)
        $_SESSION['role']         = $user_data['role']; // 0 for Student, 1 for Admin

        // 3. SUCCESS REDIRECTION LOGIC
        if ($_SESSION['role'] == 1) {
            // Logged in as ADMIN
            echo "<script>
                    alert('Admin Login Successful! Welcome, " . $user_data['firstName'] . ".');
                    window.location.href='admin_dashboard.php'; 
                  </script>";
        } else {
            // Logged in as STUDENT
            echo "<script>
                    alert('Login Successful! Welcome back, " . $user_data['firstName'] . ".');
                    window.location.href='dashboard.php'; 
                  </script>";
        }
    } else {
        // FAILURE
        echo "<script>
                alert('Login Failed: Invalid ID Number or Password.');
                window.location.href='login_page.php';
              </script>";
    }
}
$conn->close();
>>>>>>> master
?>