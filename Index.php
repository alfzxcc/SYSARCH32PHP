<?php 
include 'header.php'; 
require_once 'db_connect.php'; // Needed to talk to the database
?>

<div class="container-wrapper">
    <div class="announcement-container">
        <div class="announcement-header">
            <i class="fas fa-bullhorn"></i>
            <h3>Announcements</h3>
        </div>
        
        <div class="announcement-body">
            <?php
            // FIX: Changed date_posted to created_at in the ORDER BY clause
            $sql = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // This line is already correct in your snippet
                    $formattedDate = date("M d, Y", strtotime($row['created_at']));
                    
                    echo '
                    <div class="announcement-item">
                        <span class="announcement-date">' . $formattedDate . '</span>
                        <p><strong>' . htmlspecialchars($row['title']) . ':</strong> ' . htmlspecialchars($row['content']) . '</p>
                    </div>';
                }
            } else {
                // If the table is empty, show this default message
                echo '
                <div class="announcement-item">
                    <span class="announcement-date">' . date("M d, Y") . '</span>
                    <p>Welcome to the CCS Sit-in System. Currently, there are no new announcements. Stay tuned!</p>
                </div>';
            }
            ?>
        </div>
    </div>

    </div>

</main>
</body>
</html>