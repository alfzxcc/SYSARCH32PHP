<?php 
include 'admin_check.php'; 
require_once 'db_connect.php'; 
include 'admin_header.php'; 

// Initialize variables so they don't cause "Undefined" errors
$student = null;
$history = [];
$search_performed = false;
$error_msg = "";

// Check if the search button was clicked
if (isset($_GET['search_id']) && !empty($_GET['search_id'])) {
    $search_performed = true;
    $search_id = $conn->real_escape_string($_GET['search_id']);
    
    // 1. Get Student Data
    $userQuery = "SELECT * FROM users WHERE id = '$search_id' AND role = 0 LIMIT 1";
    $userResult = $conn->query($userQuery);
    
    if ($userResult && $userResult->num_rows > 0) {
        $student = $userResult->fetch_assoc();
        
        // 2. Get Sit-in History for this specific student
        $historyQuery = "SELECT * FROM sitin_records WHERE student_id = '$search_id' ORDER BY time_in DESC";
        $history = $conn->query($historyQuery);
    } else {
        $error_msg = "No student found with ID: " . htmlspecialchars($search_id);
    }
}
?>

<div class="admin-container">
    <h2 class="section-title"><i class="fas fa-search"></i> Student Search Portal</h2>

    <div class="admin-card" style="max-width: 700px; margin: 0 auto 40px auto; text-align: center;">
        <p style="margin-bottom: 15px; color: #666;">Enter a Student ID Number to view their profile and sit-in history.</p>
        
        <form action="admin_search.php" method="GET" style="display: flex; gap: 10px; justify-content: center;">
            <input type="text" name="search_id" 
                   placeholder="e.g., 22104532" 
                   value="<?php echo isset($_GET['search_id']) ? htmlspecialchars($_GET['search_id']) : ''; ?>" 
                   required 
                   style="flex: 1; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 16px;">
            
            <button type="submit" class="btn-post" style="width: auto; padding: 0 30px; margin-top: 0;">
                <i class="fas fa-search"></i> Search
            </button>
        </form>

        <?php if ($error_msg): ?>
            <div style="margin-top: 15px; color: #e74c3c; background: #fff5f5; padding: 10px; border-radius: 5px; border: 1px solid #feb2b2;">
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($student): ?>
        <div class="admin-grid" style="display: grid; grid-template-columns: 1fr 2fr; gap: 25px; width: 100%;">
            
            <div class="admin-card">
                <div style="text-align: center; padding: 10px 0;">
                    <i class="fas fa-user-circle" style="font-size: 5rem; color: #1e293b; margin-bottom: 10px;"></i>
                    <h3><?php echo htmlspecialchars($student['firstName'] . ' ' . $student['lastName']); ?></h3>
                    <span class="tag info" style="background: #dbeafe; color: #1e40af; padding: 5px 12px; border-radius: 15px; font-size: 12px;">
                        <?php echo htmlspecialchars($student['course']); ?>
                    </span>
                </div>
                
                <div style="margin-top: 20px; border-top: 1px solid #f1f5f9; padding-top: 20px;">
                    <p style="margin-bottom: 10px;"><strong>ID:</strong> <?php echo htmlspecialchars($student['id']); ?></p>
                    <p style="margin-bottom: 10px;"><strong>Year:</strong> <?php echo htmlspecialchars($student['course_level']); ?></p>
                    <p style="margin-bottom: 10px;"><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                    <div style="background: #f0fdf4; padding: 15px; border-radius: 8px; border: 1px solid #bbf7d0; margin-top: 15px;">
                        <p style="color: #166534; font-weight: bold; margin: 0;">Remaining Sessions:</p>
                        <h2 style="color: #15803d; margin: 5px 0 0 0; font-size: 2rem;">
                            <?php echo $student['sessions']; ?>
                        </h2>
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-history"></i> Recent Sit-in Records</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Laboratory</th>
                            <th>Purpose</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($history && $history->num_rows > 0): ?>
                            <?php while($row = $history->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($row['time_in'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['lab_room']); ?></td>
                                    <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align: center; padding: 40px; color: #94a3b8;">
                                    <i class="fas fa-folder-open" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
                                    No sit-in history found for this student.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php elseif ($search_performed && !$error_msg): ?>
        <?php endif; ?>
</div>

</body>
</html>