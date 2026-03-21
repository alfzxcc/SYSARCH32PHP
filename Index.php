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
            // Fetch the 3 most recent announcements from the database
            $sql = "SELECT * FROM announcements ORDER BY date_posted DESC LIMIT 3";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                // Loop through each row found in the database
                while($row = $result->fetch_assoc()) {
                    // Convert the database timestamp into a readable date (e.g., Oct 24, 2025)
                    $formattedDate = date("M d, Y", strtotime($row['date_posted']));
                    
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