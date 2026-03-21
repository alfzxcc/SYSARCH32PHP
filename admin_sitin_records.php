<?php 
include 'admin_check.php'; 
require_once 'db_connect.php'; 
include 'admin_header.php'; 

// Fetch all records, joining with users to get the student's name
$sql = "SELECT sr.*, u.firstName, u.lastName, u.course 
        FROM sitin_records sr 
        JOIN users u ON sr.student_id = u.id 
        ORDER BY sr.time_in DESC";
$result = $conn->query($sql);
?>

<div class="admin-container">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1100px; margin-bottom: 20px;">
        <h2 class="section-title" style="margin: 0;"><i class="fas fa-clipboard-list"></i> All Sit-in Logs</h2>
        
        <div style="display: flex; gap: 10px;">
            <input type="text" id="logSearch" placeholder="Search logs (ID, Name, Lab...)" 
                   style="padding: 10px; border: 1px solid #ddd; border-radius: 5px; width: 250px;">
            <button onclick="window.print()" class="btn-post" style="background: #34495e; width: auto; padding: 0 15px;">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <div class="admin-card" style="width: 100%; max-width: 1100px;">
        <table class="admin-table" id="recordsTable">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>ID Number</th>
                    <th>Student Name</th>
                    <th>Course</th>
                    <th>Laboratory</th>
                    <th>Purpose</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td style="font-size: 13px; color: #666;">
                                <?php echo date('M d, Y', strtotime($row['time_in'])); ?><br>
                                <small><?php echo date('h:i A', strtotime($row['time_in'])); ?></small>
                            </td>
                            <td><strong><?php echo htmlspecialchars($row['student_id']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['firstName'] . " " . $row['lastName']); ?></td>
                            <td><span class="tag info"><?php echo htmlspecialchars($row['course']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['lab_room']); ?></td>
                            <td style="font-style: italic; color: #555;"><?php echo htmlspecialchars($row['purpose']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; padding: 40px; color: #999;">No records found in the database.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Instant search for the logs
document.getElementById('logSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#recordsTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>

<style>
@media print {
    .admin-header, .admin-nav, #logSearch, .btn-post { display: none !important; }
    .admin-container { padding: 0; margin: 0; width: 100%; }
    .admin-card { border: none; box-shadow: none; }
    .admin-table th { background: #eee !important; color: black !important; }
}
</style>

</body>
</html>