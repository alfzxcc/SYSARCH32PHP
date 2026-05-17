<?php
// 1. Start output buffering to prevent random spacing or warnings from breaking JSON
ob_start();
session_start();
require_once 'db_connect.php';

// 2. Force the browser/JavaScript to read this strictly as JSON data
header('Content-Type: application/json');
ob_clean(); 

// Default structural layout reply payload
$response = ['success' => false, 'name' => '', 'sessions' => 0];

if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $sid = $conn->real_escape_string(trim($_GET['id']));
    
    // 3. Query your live table using your exact structural updates (id_number and remaining_sessions)
    $query = "SELECT firstname, lastname, remaining_sessions FROM users WHERE id_number = '$sid' AND role = 'student' LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response = [
            'success' => true,
            'name' => htmlspecialchars($row['firstname'] . ' ' . $row['lastname']),
            'sessions' => (int)($row['remaining_sessions'] ?? 0)
        ];
    }
}

// 4. Send back clean JSON text that the JavaScript fetch listener can parse
echo json_encode($response);
exit();
?>