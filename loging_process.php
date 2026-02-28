<?php
// Check if the form was actually submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Capture the data from the input names
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // For now, let's use a "Hardcoded" check (we will use a database later)
    if ($user == "admin" && $pass == "12345") {
        echo "<h1>Login Successful!</h1>";
        echo "<p>Welcome back, " . htmlspecialchars($user) . ".</p>";
        echo "<a href='index.php'>Go Back</a>";
    } else {
        echo "<h1>Login Failed!</h1>";
        echo "<p>Invalid username or password.</p>";
        echo "<a href='index.php'>Try Again</a>";
    }
} else {
    // If someone tries to access this file directly without posting, send them back
    header("Location: index.php");
}
?>