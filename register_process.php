<?php
require_once 'db_connect.php'; // Pulls in your sysarch32_db connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['new_user'];
    $pass = $_POST['new_pass'];

    // Clean data to prevent SQL injection
    $user = $conn->real_escape_string($user);
    $pass = $conn->real_escape_string($pass);

    // STEP 1: Check if username already exists
    $checkQuery = "SELECT * FROM users WHERE username = '$user'";
    $result = $conn->query($checkQuery);

    if ($result->num_rows > 0) {
        // Username found! Send them back with an alert
        echo "<script>
                alert('Error: Username already exists! Please choose another.');
                window.location.href='register.php';
              </script>";
    } else {
        // STEP 2: Username is unique, proceed with registration
        $sql = "INSERT INTO users (username, password) VALUES ('$user', '$pass')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>
                    alert('Registration Successful!');
                    window.location.href='index.php';
                  </script>";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
$conn->close();
?>