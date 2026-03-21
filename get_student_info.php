<?php
require_once 'db_connect.php';

$id = $_GET['id'] ?? '';
$response = ['success' => false];

if ($id) {
    $stmt = $conn->prepare("SELECT firstName, lastName, sessions FROM users WHERE id = ? AND role = 0");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        $response = [
            'success' => true,
            'name' => $user['firstName'] . " " . $user['lastName'],
            'sessions' => $user['sessions']
        ];
    }
}

echo json_encode($response);
?>