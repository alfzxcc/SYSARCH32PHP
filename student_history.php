<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login_page.php");
    exit();
}
include 'header.php'; 
require_once 'db_connect.php'; 

$uid = $_SESSION['user_id'];

// Handle Feedback Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback'])) {
    $hid = $conn->real_escape_string($_POST['history_id']);
    $msg = $conn->real_escape_string($_POST['feedback_text']);
    
    $updateSql = "UPDATE sitin_history SET feedback = '$msg' WHERE history_id = '$hid' AND id_number = '$uid'";
    $conn->query($updateSql);
    echo "<script>alert('Feedback submitted!'); window.location.href='history.php';</script>";
}
?>

<div class="dashboard-container">
    <section class="dash-column center-column" style="flex: 3;">
        <div class="announcement-card">
            <div class="card-header-main">
                <h2><i class="fas fa-history"></i> My Sit-in History</h2>
                <span class="view-all">Standardized ID: <?php echo htmlspecialchars($uid); ?></span>
            </div>

            <div style="padding: 20px; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px;">
                    <thead>
                        <tr style="background: #003366; color: white; text-align: left;">
                            <th style="padding: 15px;">Lab / Station</th>
                            <th style="padding: 15px;">Purpose</th>
                            <th style="padding: 15px;">Date & Time</th>
                            <th style="padding: 15px;">Status</th>
                            <th style="padding: 15px;">Feedback</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM sitin_history WHERE id_number = '$uid' ORDER BY login_time DESC";
                        $result = $conn->query($sql);

                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $statusColor = ($row['status'] == 'Completed') ? '#28a745' : '#ffc107';
                                echo "<tr style='border-bottom: 1px solid #eee;'>
                                    <td style='padding: 15px; font-weight: bold;'>".htmlspecialchars($row['lab_name'])."</td>
                                    <td style='padding: 15px;'>".htmlspecialchars($row['purpose'])."</td>
                                    <td style='padding: 15px; font-size: 0.85rem;'>".date("M d, g:i A", strtotime($row['login_time']))."</td>
                                    <td style='padding: 15px;'><span style='color: $statusColor;'>● ".$row['status']."</span></td>
                                    <td style='padding: 15px;'>";
                                
                                if (!empty($row['feedback'])) {
                                    echo "<i style='color: #666;'>\"" . htmlspecialchars($row['feedback']) . "\"</i>";
                                } else {
                                    echo "<button onclick='openFeedback(".$row['history_id'].")' style='background: #FFD700; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 0.75rem; font-weight: bold;'>+ Add Feedback</button>";
                                }
                                
                                echo "</td></tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align:center; padding: 40px; color: #777;'>No history records found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<div id="feedbackModal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 25px; border-radius: 12px; width: 400px; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
        <h3 style="margin-top: 0; color: #003366;">Session Feedback</h3>
        <form method="POST">
            <input type="hidden" name="history_id" id="modal_hid">
            <textarea name="feedback_text" placeholder="Tell us about the equipment or your experience..." required style="width: 100%; height: 100px; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px;"></textarea>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeFeedback()" style="padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer;">Cancel</button>
                <button type="submit" name="submit_feedback" style="background: #003366; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer;">Submit</button>
            </div>
        </form>
    </div>
</div>

<script>
function openFeedback(id) {
    document.getElementById('modal_hid').value = id;
    document.getElementById('feedbackModal').style.display = 'flex';
}
function closeFeedback() {
    document.getElementById('feedbackModal').style.display = 'none';
}
</script>