<?php 
include 'admin_check.php'; 
require_once 'db_connect.php'; 
include 'admin_header.php'; 
?>

<div class="admin-container" style="max-width: 1600px; margin: 0 auto; padding: 20px;">
    <h2 class="section-title" style="font-weight: 700; margin-bottom: 25px;"><i class="fas fa-desktop"></i> Manual Sit-in Entry</h2>

    <div class="admin-card" style="max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <form action="process_sitin.php" method="POST" id="sitinForm">
            
            <div style="margin-bottom: 15px;">
                <label style="display:block; font-size:0.85rem; font-weight:600; color:#555; margin-bottom:5px;">ID Number</label>
                <input type="text" name="username" id="student_id" placeholder="Enter ID (e.g. 2023-0001)" required 
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box;">
                <small id="status_msg" style="display:block; margin-top:5px; font-weight: 600; font-size: 0.85rem;"></small>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display:block; font-size:0.85rem; font-weight:600; color:#555; margin-bottom:5px;">Student Name</label>
                <input type="text" id="display_name" readonly placeholder="Name will appear here..." 
                       style="width: 100%; padding: 12px; background: #f8f9fa; border: 1px solid #eee; border-radius: 6px; color: #333; box-sizing: border-box; font-weight: 500;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display:block; font-size:0.85rem; font-weight:600; color:#555; margin-bottom:5px;">Remaining Sessions</label>
                <input type="text" id="display_sessions" readonly placeholder="--" 
                       style="width: 100%; padding: 12px; background: #f8f9fa; border: 1px solid #eee; border-radius: 6px; font-weight: bold; color: #27ae60; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display:block; font-size:0.85rem; font-weight:600; color:#555; margin-bottom:5px;">Purpose</label>
                <select name="purpose" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; background: white; box-sizing: border-box;">
                    <option value="C++ Programming">C++ Programming</option>
                    <option value="Java Programming">Java Programming</option>
                    <option value="Web Development">Web Development</option>
                    <option value="Research">Research / Assignment</option>
                </select>
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display:block; font-size:0.85rem; font-weight:600; color:#555; margin-bottom:5px;">Laboratory</label>
                <select name="lab_room" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; background: white; box-sizing: border-box;">
                    <option value="Lab 524">Lab 524</option>
                    <option value="Lab 526">Lab 526</option>
                    <option value="Lab 542">Lab 542</option>
                    <option value="Lab 544">Lab 544</option>
                </select>
            </div>

            <button type="submit" style="background: #27ae60; color: white; width: 100%; border: none; padding: 14px; border-radius: 6px; font-size: 1rem; font-weight: bold; cursor: pointer; box-shadow: 0 2px 5px rgba(39,174,96,0.2);">Confirm Sit-in</button>
        </form>
    </div>
</div>

<script>
document.getElementById('student_id').addEventListener('input', function() {
    let id = this.value.trim();
    let nameBox = document.getElementById('display_name');
    let sessionBox = document.getElementById('display_sessions');
    let statusMsg = document.getElementById('status_msg');

    // Searches when ID hits 3 or more characters
    if (id.length >= 3) { 
        fetch('get_student_info.php?id=' + encodeURIComponent(id))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    nameBox.value = data.name;
                    sessionBox.value = data.sessions;
                    statusMsg.innerHTML = "✅ Student Profile Verified";
                    statusMsg.style.color = "#27ae60";
                    
                    // Style session text warning color if balance hits empty threshold
                    if (data.sessions <= 0) {
                        sessionBox.style.color = "#e74c3c";
                        statusMsg.innerHTML = "⚠ Warning: Zero sessions remaining!";
                        statusMsg.style.color = "#e67e22";
                    } else {
                        sessionBox.style.color = "#27ae60";
                    }
                } else {
                    nameBox.value = "";
                    sessionBox.value = "";
                    statusMsg.innerHTML = "❌ ID Number Not Found";
                    statusMsg.style.color = "#e74c3c";
                }
            })
            .catch(err => {
                console.error("AJAX Lookup Error: ", err);
            });
    } else {
        // Clear forms cleanly if length drops back down
        nameBox.value = "";
        sessionBox.value = "";
        statusMsg.innerHTML = "";
    }
});
</script>
</body>
</html>