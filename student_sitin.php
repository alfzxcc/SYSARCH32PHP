<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login_page.php");
    exit();
}
include 'header.php'; 
require_once 'db_connect.php'; 

$uid = $_SESSION['user_id'];
$userData = $conn->query("SELECT * FROM users WHERE id_number = '$uid'")->fetch_assoc();

$errorDbMsg = "";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // DOUBLE CHECK: Ensure your table matches what your Admin queries!
    // If Admin looks for "lab_room", change lab_name below to lab_room.
    $lab = $conn->real_escape_string($_POST['lab_name']);
    $purpose = $conn->real_escape_string($_POST['purpose']);
    
    // Explicitly inserting NOW() for 'created_at' ensures it appears at the top of Admin records
    $sql = "INSERT INTO sitin_history (id_number, lab_name, purpose, status, created_at) 
            VALUES ('$uid', '$lab', '$purpose', 'Pending', NOW())";
            
    if ($conn->query($sql)) {
        echo "<script>alert('Sit-in request submitted successfully!'); window.location.href='student_history.php';</script>";
        exit();
    } else {
        // Captures structural table name issues safely if it crashes
        $errorDbMsg = "Database Error: " . $conn->error;
    }
}
?>

<div class="dashboard-container" style="margin-top: 100px; padding: 0 20px;">
    <section class="dash-column center-column" style="max-width: 600px; margin: 0 auto;">
        
        <?php if (!empty($errorDbMsg)): ?>
            <div style="background:#f8d7da; color:#721c24; padding:15px; border-radius:8px; margin-bottom:20px; border-left:5px solid #e74c3c; font-weight:600;">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $errorDbMsg; ?>
            </div>
        <?php endif; ?>

        <div class="announcement-card" style="background: white; border-radius: 12px; padding: 35px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-top: 4px solid #003366;">
            <div class="card-header-main" style="margin-bottom: 25px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                <h2 style="margin:0; color:#003366; font-size:1.4rem;"><i class="fas fa-plus-circle" style="color:#FFD700; margin-right:8px;"></i> Request New Sit-in</h2>
            </div>

            <form method="POST" action="">
                <div class="student-input-group" style="margin-bottom: 20px;">
                    <label style="display:block; font-size:0.85rem; color:#555; font-weight:600; margin-bottom:8px;">Select Laboratory</label>
                    <select name="lab_name" required style="width:100%; padding:12px; border-radius:6px; border:1px solid #ccc; background:#f9f9f9; font-size:0.95rem; font-weight:500;">
                        <option value="" disabled selected>-- Choose Lab --</option>
                        <option value="Lab 524">Lab 524 (Programming)</option>
                        <option value="Lab 526">Lab 526 (Multimedia)</option>
                        <option value="Lab 542">Lab 542 (Networking)</option>
                        <option value="Lab 544">Lab 544 (General)</option>
                    </select>
                </div>

                <div class="student-input-group" style="margin-bottom: 25px;">
                    <label style="display:block; font-size:0.85rem; color:#555; font-weight:600; margin-bottom:8px;">Purpose of Sit-in</label>
                    <select name="purpose" required style="width:100%; padding:12px; border-radius:6px; border:1px solid #ccc; background:#f9f9f9; font-size:0.95rem; font-weight:500;">
                        <option value="" disabled selected>-- Select Purpose --</option>
                        <option value="Java Programming">Java Programming</option>
                        <option value="Web Development">Web Development</option>
                        <option value="CICS Project">CICS Project</option>
                        <option value="Research">Research</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div style="background: #fffbeb; padding: 15px; border-radius: 8px; margin-bottom: 25px; border-left: 5px solid #FFD700; border: 1px solid #fef3c7; border-left: 5px solid #FFD700;">
                    <p style="margin: 0; font-size: 0.9rem; color: #b45309; font-weight: 600;">
                        <strong>Current Balance:</strong> 
                        <span style="color: #003366; font-weight: 800; margin-left: 5px;">
                            <?php echo $userData['remaining_sessions'] ?? '30'; ?> Sessions Left
                        </span>
                    </p>
                </div>

                <div style="display: flex; gap: 15px; align-items: center;">
                    <button type="submit" class="btn-login" style="flex: 2; background: #003366; border: none; padding: 14px; border-radius: 8px; color: white; font-weight: bold; cursor: pointer; font-size:0.95rem; transition: background 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                        SUBMIT REQUEST
                    </button>
                    <a href="dashboard.php" style="flex: 1; text-align: center; padding: 13px; border-radius: 8px; border: 1px solid #ccc; text-decoration: none; color: #666; font-weight: bold; font-size:0.95rem; background: #fff;">
                        CANCEL
                    </a>
                </div>
            </form>
        </div>
    </section>
</div>
</body>
</html>