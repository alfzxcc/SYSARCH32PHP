<?php
include 'admin_check.php';
require_once 'db_connect.php';
include 'admin_header.php';

// Ensure settings table exists
$conn->query("CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value VARCHAR(255) NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB");

// Seed default if not exists
$conn->query("INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES ('reservations_enabled','1')");

// Handle toggle
if (isset($_GET['toggle_reservations'])) {
    $cur = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key='reservations_enabled'")->fetch_assoc();
    $newVal = ($cur['setting_value'] == '1') ? '0' : '1';
    $conn->query("UPDATE system_settings SET setting_value='$newVal' WHERE setting_key='reservations_enabled'");
    header("Location: admin_settings.php?updated=1"); exit();
}

$resSetting = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key='reservations_enabled'")->fetch_assoc();
$resEnabled = ($resSetting['setting_value'] ?? '1') == '1';
?>

<div class="admin-container">
    <h2 class="section-title"><i class="fas fa-cogs"></i> System Settings</h2>

    <?php if (isset($_GET['updated'])): ?>
    <div style="background:#d4edda;color:#155724;padding:12px 20px;border-radius:8px;margin-bottom:20px;border-left:4px solid #28a745;">
        <i class="fas fa-check-circle"></i> Setting updated successfully.
    </div>
    <?php endif; ?>

    <div class="admin-card">
        <h3><i class="fas fa-calendar-alt"></i> Reservation System</h3>
        <p style="color:#666;margin-top:5px;font-size:0.9rem;">Control whether students can submit new lab reservations. Existing approved reservations are not affected.</p>

        <div style="display:flex;align-items:center;justify-content:space-between;background:<?php echo $resEnabled?'#e8f5e9':'#fdecea'; ?>;border-radius:10px;padding:20px 25px;margin-top:15px;border:1px solid <?php echo $resEnabled?'#a5d6a7':'#f5c6cb'; ?>;">
            <div style="display:flex;align-items:center;gap:15px;">
                <div style="width:50px;height:50px;background:<?php echo $resEnabled?'#27ae60':'#e74c3c'; ?>;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-<?php echo $resEnabled?'calendar-check':'ban'; ?>" style="color:white;font-size:1.3rem;"></i>
                </div>
                <div>
                    <div style="font-weight:700;font-size:1rem;color:<?php echo $resEnabled?'#1b5e20':'#7f0000'; ?>;">
                        Reservations are currently <strong><?php echo $resEnabled?'ENABLED':'DISABLED'; ?></strong>
                    </div>
                    <div style="font-size:0.83rem;color:#777;margin-top:3px;">
                        <?php echo $resEnabled
                            ? 'Students can book lab PCs in advance.'
                            : 'Students cannot submit new reservation requests.'; ?>
                    </div>
                </div>
            </div>
            <a href="admin_settings.php?toggle_reservations=1"
               onclick="return confirm('<?php echo $resEnabled?'Disable':'Enable'; ?> reservations for all students?')"
               style="background:<?php echo $resEnabled?'#e74c3c':'#27ae60'; ?>;color:white;padding:12px 22px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:0.9rem;white-space:nowrap;flex-shrink:0;">
                <i class="fas fa-<?php echo $resEnabled?'ban':'check-circle'; ?>" style="margin-right:6px;"></i>
                <?php echo $resEnabled?'Disable Reservations':'Enable Reservations'; ?>
            </a>
        </div>
    </div>

    <!-- Future settings placeholder -->
    <div class="admin-card" style="opacity:0.6;">
        <h3><i class="fas fa-sliders-h"></i> More Settings</h3>
        <p style="color:#aaa;font-size:0.9rem;">Additional system settings (sit-in limits, lab hours, etc.) can be added here.</p>
        <div style="background:#f8f9fa;border-radius:8px;padding:15px;text-align:center;color:#aaa;font-size:0.85rem;">
            <i class="fas fa-tools" style="font-size:1.5rem;display:block;margin-bottom:8px;"></i>
            Coming soon
        </div>
    </div>
</div>
</body>
</html>
