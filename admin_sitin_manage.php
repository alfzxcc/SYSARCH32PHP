<?php 
include 'admin_check.php'; 
require_once 'db_connect.php'; 
include 'admin_header.php'; 
?>

<div class="admin-container">
    <h2 class="section-title"><i class="fas fa-desktop"></i> Manual Sit-in Entry</h2>

    <div class="admin-card" style="max-width: 600px; margin: 0 auto;">
        <form action="process_sitin.php" method="POST" id="sitinForm">
            
            <div style="margin-bottom: 15px;">
                <label>ID Number</label>
                <input type="text" name="username" id="student_id" placeholder="Enter ID (e.g. 2210345)" required 
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
                <small id="status_msg" style="display:block; margin-top:5px;"></small>
            </div>

            <div style="margin-bottom: 15px;">
                <label>Student Name</label>
                <input type="text" id="display_name" readonly placeholder="Name will appear here..." 
                       style="width: 100%; padding: 12px; background: #f9f9f9; border: 1px solid #eee; border-radius: 5px; color: #555;">
            </div>

            <div style="margin-bottom: 15px;">
                <label>Remaining Sessions</label>
                <input type="text" id="display_sessions" readonly placeholder="--" 
                       style="width: 100%; padding: 12px; background: #f9f9f9; border: 1px solid #eee; border-radius: 5px; font-weight: bold; color: #27ae60;">
            </div>

            <div style="margin-bottom: 15px;">
                <label>Purpose</label>
                <select name="purpose" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="C++ Programming">C++ Programming</option>
                    <option value="Java Programming">Java Programming</option>
                    <option value="Web Development">Web Development</option>
                    <option value="Research">Research / Assignment</option>
                </select>
            </div>

            <div style="margin-bottom: 25px;">
                <label>Laboratory</label>
                <select name="lab_room" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="Lab 524">Lab 524</option>
                    <option value="Lab 526">Lab 526</option>
                    <option value="Lab 542">Lab 542</option>
                    <option value="Lab 544">Lab 544</option>
                </select>
            </div>

            <button type="submit" class="btn-login" style="background: #27ae60; width: 100%;">Confirm Sit-in</button>
        </form>
    </div>
</div>

<script>
document.getElementById('student_id').addEventListener('input', function() {
    let id = this.value;
    let nameBox = document.getElementById('display_name');
    let sessionBox = document.getElementById('display_sessions');
    let statusMsg = document.getElementById('status_msg');

    if (id.length >= 4) { // Start searching after 4 characters
        fetch('get_student_info.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    nameBox.value = data.name;
                    sessionBox.value = data.sessions;
                    statusMsg.innerHTML = "✅ Student Found";
                    statusMsg.style.color = "green";
                } else {
                    nameBox.value = "";
                    sessionBox.value = "";
                    statusMsg.innerHTML = "❌ ID Not Found";
                    statusMsg.style.color = "red";
                }
            });
    }
});
</script>

</body>
</html>