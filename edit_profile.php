<?php 
session_start();
include 'header.php'; 
require_once 'db_connect.php';

$uid = $_SESSION['user_id'];
$userData = $conn->query("SELECT * FROM users WHERE id_number = '$uid'")->fetch_assoc();
$current_pic = !empty($userData['profile_pic']) ? $userData['profile_pic'] : 'default_avatar.png';
?>

<div class="dashboard-container" style="max-width: 100%; padding: 10px;">
    <div class="student-profile-container" style="max-width: 1300px; margin: 0 auto; padding: 25px; border-radius: 12px;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
            <h2 style="color: #003366; font-size: 1.5rem; margin: 0;"><i class="fas fa-user-cog"></i> Student Account Settings</h2>
            <span style="background: #FFD700; color: #003366; padding: 5px 12px; border-radius: 20px; font-weight: bold; font-size: 0.8rem;">ID: <?php echo $_SESSION['user_id']; ?></span>
        </div>

        <form action="edit_profile_process.php" method="POST" enctype="multipart/form-data" onsubmit="return validatePasswords()">
            
            <div style="display: grid; grid-template-columns: 180px 1.2fr 1.2fr 1fr; gap: 20px; align-items: start;">
                
                <div class="student-form-section" style="text-align: center; padding: 15px;">
                    <div style="position: relative; display: inline-block;">
                        <img src="<?php echo !empty($userData['profile_pic']) ? $userData['profile_pic'] : 'uc2.jpg'; ?>" 
                             style="width: 140px; height: 140px; border-radius: 10px; object-fit: cover; border: 3px solid #003366;">
                        <label for="photo" style="position: absolute; bottom: -5px; right: -5px; background: #003366; color: #fff; width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                            <i class="fas fa-camera"></i>
                        </label>
                    </div>
                    <input type="file" name="profile_photo" id="photo" style="display: none;" accept="image/*">
                    <p style="font-size: 0.75rem; margin-top: 10px; color: #666;">Profile Image</p>
                </div>

                <div class="student-form-section" style="padding: 15px;">
                    <div class="student-input-group">
                        <label>First Name</label>
                        <input type="text" name="firstname" value="<?php echo htmlspecialchars($userData['firstname'] ?? ''); ?>" required>
                    </div>
                    <div class="student-input-group">
                        <label>Last Name</label>
                        <input type="text" name="lastname" value="<?php echo htmlspecialchars($userData['lastname'] ?? ''); ?>" required>
                    </div>
                    <div class="student-input-group">
                        <label>Middle Name</label>
                        <input type="text" name="midname" value="<?php echo htmlspecialchars($userData['midname'] ?? ''); ?>">
                    </div>
                </div>

                <div class="student-form-section" style="padding: 15px;">
                    <div class="student-input-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" required>
                    </div>
                    <div class="student-input-group">
                        <label>Course</label>
                        <input type="text" name="course" value="<?php echo htmlspecialchars($userData['course'] ?? ''); ?>" required>
                    </div>
                    <div class="student-input-group">
                        <label>Year Level</label>
                        <select name="course_level" required>
                            <option value="1" <?php echo ($userData['course_level'] == '1') ? 'selected' : ''; ?>>Year 1</option>
                            <option value="2" <?php echo ($userData['course_level'] == '2') ? 'selected' : ''; ?>>Year 2</option>
                            <option value="3" <?php echo ($userData['course_level'] == '3') ? 'selected' : ''; ?>>Year 3</option>
                            <option value="4" <?php echo ($userData['course_level'] == '4') ? 'selected' : ''; ?>>Year 4</option>
                        </select>
                    </div>
                </div>

                <div class="student-form-section" style="padding: 15px; background: #fff9e6; border: 1px solid #ffeeba;">
                    <h3 style="font-size: 1rem; margin-top: 0;"><i class="fas fa-shield-alt"></i> Security</h3>
                    <div class="student-input-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" id="new_pass" placeholder="Enter New">
                    </div>
                    <div class="student-input-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirm_pass" placeholder="Repeat New">
                    </div>
                    <small style="display: block; color: #856404; font-size: 0.7rem;">Leave both blank to keep current.</small>
                </div>
            </div>

            <div style="display: flex; gap: 20px; margin-top: 25px; align-items: flex-end;">
                <div class="student-input-group" style="flex-grow: 1; margin-bottom: 0;">
                    <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #444;">HOME ADDRESS</label>
                    <input type="text" name="address" 
                        style="margin-bottom: 0; width: 100%; padding: 14px; border-radius: 8px; border: 2px solid #e1e5ee; font-size: 1rem;" 
                        value="<?php echo htmlspecialchars($userData['address'] ?? ''); ?>">
                </div>
                <div style="margin-bottom: 0;">
                    <a href="dashboard.php" 
                    style="background: #fff5f5; color: #dc3545; padding: 0 30px; border-radius: 8px; font-weight: bold; min-width: 120px; height: 52px; display: flex; align-items: center; justify-content: center; border: 1px solid #ddd; text-decoration: none; transition: 0.3s;">
                        CANCEL
                    </a>
                </div>
                <div style="margin-bottom: 0;">
                    <button type="submit" class="btn-login" 
                            style="background: #003366; color: white; padding: 0 40px; border-radius: 8px; font-weight: bold; min-width: 220px; height: 52px; display: flex; align-items: center; justify-content: center; border: none; cursor: pointer; transition: 0.3s;">
                        <i class="fas fa-check-circle" style="margin-right: 10px;"></i> SAVE UPDATES
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function validatePasswords() {
    var pass = document.getElementById("new_pass").value;
    var confirm = document.getElementById("confirm_pass").value;

    if (pass !== "" || confirm !== "") {
        if (pass !== confirm) {
            alert("Error: New passwords do not match!");
            return false;
        }
    }
    return true;
}
</script>