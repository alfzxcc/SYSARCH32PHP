<?php
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
?>