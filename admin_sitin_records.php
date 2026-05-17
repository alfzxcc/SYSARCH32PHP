<?php
session_start();
// Add your admin authentication check here if needed
include 'admin_header.php'; // Assumes admin header is handled or shared
require_once 'db_connect.php';

// Handle Actions: Approve/Clock-In or Reject
if (isset($_GET['action']) && isset($_GET['id'])) {
    $record_id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'approve') {
        // Updated to use history_id and login_time based on your database schema
        $conn->query("UPDATE sitin_history SET status = 'Active', login_time = NOW() WHERE history_id = $record_id");
        echo "<script>alert('Student clocked in successfully!'); window.location.href='admin_sitin_records.php';</script>";
        exit();
    } elseif ($action === 'reject') {
        // Updated to use history_id
        $conn->query("UPDATE sitin_history SET status = 'Rejected' WHERE history_id = $record_id");
        echo "<script>alert('Request rejected.'); window.location.href='admin_sitin_records.php';</script>";
        exit();
    }
}

// Handle Search Filters
$search = isset($_POST['search_ident']) ? $conn->real_escape_string($_POST['search_ident']) : '';
$filter_lab = isset($_POST['filter_lab']) ? $conn->real_escape_string($_POST['filter_lab']) : '';
$filter_status = isset($_POST['filter_status']) ? $conn->real_escape_string($_POST['filter_status']) : '';

// Base Query: JOIN sitin_history with users to get Student Name and Course
$queryStr = "SELECT sh.*, u.firstname, u.lastname, u.course 
             FROM sitin_history sh 
             JOIN users u ON sh.id_number = u.id_number 
             WHERE 1=1";

if (!empty($search)) {
    $queryStr .= " AND (sh.id_number LIKE '%$search%' OR u.firstname LIKE '%$search%' OR u.lastname LIKE '%$search%')";
}
if (!empty($filter_lab)) {
    $queryStr .= " AND sh.lab_name = '$filter_lab'";
}
if (!empty($filter_status)) {
    $queryStr .= " AND sh.status = '$filter_status'";
} else {
    // Default view shows Pending and Active sessions first
    $queryStr .= " ORDER BY CASE WHEN sh.status = 'Pending' THEN 1 WHEN sh.status = 'Active' THEN 2 ELSE 3 END, sh.created_at DESC";
}

$results = $conn->query($queryStr);
$entry_count = $results ? $results->num_rows : 0;
?>

<div class="dashboard-container" style=" padding: 0 20px;">
    <div class="announcement-card" style="background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; max-width: 1200px; margin: 0 auto;">
        
        <div style="background: linear-gradient(135deg, #003366, #002244); padding: 20px 24px; display: flex; align-items: center; gap: 12px; border-bottom: 4px solid #FFD700;">
            <div style="background: #FFD700; color: #003366; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <h2 style="margin: 0; color: white; font-size: 1.4rem; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">Sit-in History & Logs</h2>
        </div>

        <div style="padding: 24px;">
            <form method="POST" style="background: #f8f9fa; border: 1px solid #eef0f2; border-radius: 10px; padding: 20px; margin-bottom: 25px;">
                <h5 style="margin: 0 0 15px 0; color: #555; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-sliders-h" style="color: #003366;"></i> Search Filters
                </h5>
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 15px; align-items: flex-end;">
                    <div>
                        <label style="display: block; font-size: 0.78rem; color: #666; font-weight: 600; margin-bottom: 6px;">Student Identifier / Name</label>
                        <input type="text" name="search_ident" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search ID or name..." style="width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
                    </div>
                    <div style = "margin-bottom: 10px;">
                        <label style="display: block; font-size: 0.78rem; color: #666; font-weight: 600; margin-bottom: 10px;">Laboratory</label>
                        <select name="filter_lab" style="width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; background: white; font-size: 0.9rem;">
                            <option value="">All Labs</option>
                            <option value="Lab 524" <?php if($filter_lab=='Lab 524') echo 'selected'; ?>>Lab 524</option>
                            <option value="Lab 526" <?php if($filter_lab=='Lab 526') echo 'selected'; ?>>Lab 526</option>
                            <option value="Lab 542" <?php if($filter_lab=='Lab 542') echo 'selected'; ?>>Lab 542</option>
                            <option value="Lab 544" <?php if($filter_lab=='Lab 544') echo 'selected'; ?>>Lab 544</option>
                        </select>
                    </div>
                    <div style = "margin-bottom: 10px;">
                        <label style="display: block; font-size: 0.78rem; color: #666; font-weight: 600; margin-bottom: 10px;">Status</label>
                        <select name="filter_status" style="width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; background: white; font-size: 0.9rem;">
                            <option value="">All Status</option>
                            <option value="Pending" <?php if($filter_status=='Pending') echo 'selected'; ?>>Pending</option>
                            <option value="Active" <?php if($filter_status=='Active') echo 'selected'; ?>>Active (Clocked In)</option>
                            <option value="Completed" <?php if($filter_status=='Completed') echo 'selected'; ?>>Completed</option>
                            <option value="Rejected" <?php if($filter_status=='Rejected') echo 'selected'; ?>>Rejected</option>
                        </select>
                    </div>
                    <div style="display: flex; gap: 8px; margin-bottom: 10px;">
                        <button type="submit" style="background: #003366; color: white; border: none; padding: 11px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.9rem;"><i class="fas fa-search"></i> Filter</button>
                        <a href="admin_sitin_records.php" style="background: white; color: #666; border: 1px solid #ccc; padding: 10px 15px; border-radius: 6px; font-weight: 600; text-decoration: none; font-size: 0.9rem; text-align: center;">Reset</a>
                    </div>
                </div>
            </form>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h4 style="margin: 0; color: #333; font-weight: 700;"><i class="fas fa-database" style="color:#003366; margin-right: 6px;"></i> Database Logs Output <span style="font-weight:normal; color:#888; font-size:0.9rem;">(<?php echo $entry_count; ?> entries found)</span></h4>
                <div style="display: flex; gap: 10px;">
                    <button style="background: #27ae60; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; font-size: 0.85rem; cursor: pointer;"><i class="fas fa-file-excel"></i> Export Spreadsheets</button>
                    <button style="background: #34495e; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; font-size: 0.85rem; cursor: pointer;"><i class="fas fa-print"></i> Print View</button>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem; background: white; border: 1px solid #eef0f2;">
                    <thead>
                        <tr style="background: #f4f6f9; color: #444; font-weight: bold; border-bottom: 2px solid #eaeaea;">
                            <th style="padding: 12px 10px;">#</th>
                            <th style="padding: 12px 10px;">ID Number</th>
                            <th style="padding: 12px 10px;">Student Name</th>
                            <th style="padding: 12px 10px;">Course</th>
                            <th style="padding: 12px 10px;">Laboratory</th>
                            <th style="padding: 12px 10px;">Purpose</th>
                            <th style="padding: 12px 10px;">Time In</th>
                            <th style="padding: 12px 10px;">Time Out</th>
                            <th style="padding: 12px 10px;">Duration</th>
                            <th style="padding: 12px 10px; text-align: center;">Status</th>
                            <th style="padding: 12px 10px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($entry_count > 0): 
                            $counter = 1;
                            $status_colors = ['Pending'=>'#d97706', 'Active'=>'#27ae60', 'Completed'=>'#2980b9', 'Rejected'=>'#7f8c8d'];
                            while ($row = $results->fetch_assoc()): 
                                $st = $row['status'];
                                $badge_color = $status_colors[$st] ?? '#666';
                                
                                // Fixed: Mapping to correct schema columns login_time and logout_time
                                $t_in = $row['login_time'] ?? null;
                                $t_out = $row['logout_time'] ?? null;

                                $time_in = !empty($t_in) && $t_in !== '0000-00-00 00:00:00' ? date('g:i A', strtotime($t_in)) : '—';
                                $time_out = !empty($t_out) && $t_out !== '0000-00-00 00:00:00' ? date('g:i A', strtotime($t_out)) : '—';
                                
                                // Calculate Duration dynamically if session is completed
                                $duration = '—';
                                if (!empty($t_in) && !empty($t_out) && $t_out !== '0000-00-00 00:00:00') {
                                    $start = strtotime($t_in);
                                    $end = strtotime($t_out);
                                    $diff = $end - $start;
                                    $mins = round($diff / 60);
                                    $duration = $mins . " mins";
                                } elseif ($st === 'Active') {
                                    $duration = 'Ongoing';
                                }
                        ?>
                            <tr style="border-bottom: 1px solid #f2f4f7; transition: background 0.15s;" onmouseover="this.style.background='#fdfefe'" onmouseout="this.style.background='none'">
                                <td style="padding: 14px 10px; color: #888;"><?php echo $counter++; ?></td>
                                <td style="padding: 14px 10px; font-weight: 600; color: #003366;"><?php echo htmlspecialchars($row['id_number']); ?></td>
                                <td style="padding: 14px 10px; color: #333;"><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                                <td style="padding: 14px 10px; color: #555;"><?php echo htmlspecialchars($row['course']); ?></td>
                                <td style="padding: 14px 10px; font-weight: 500;"><?php echo htmlspecialchars($row['lab_name']); ?></td>
                                <td style="padding: 14px 10px; color: #666;"><?php echo htmlspecialchars($row['purpose']); ?></td>
                                <td style="padding: 14px 10px; color: #333; font-weight: 500;"><?php echo $time_in; ?></td>
                                <td style="padding: 14px 10px; color: #333; font-weight: 500;"><?php echo $time_out; ?></td>
                                <td style="padding: 14px 10px; color: #666; font-weight: 500;"><?php echo $duration; ?></td>
                                <td style="padding: 14px 10px; text-align: center;">
                                    <span style="color: <?php echo $badge_color; ?>; font-weight: bold; font-size: 0.82rem; background: <?php echo $badge_color; ?>15; padding: 4px 10px; border-radius: 12px; border: 1px solid <?php echo $badge_color; ?>30; display: inline-block;">
                                        ● <?php echo $st; ?>
                                    </span>
                                </td>
                                <td style="padding: 14px 10px; text-align: center;">
                                    <?php 
                                    // Fixed: Get record identifier via primary key 'history_id'
                                    $record_id = $row['history_id'];

                                    if ($st === 'Pending') {
                                        echo '<div style="display: flex; gap: 6px; justify-content: center; align-items: center;">';
                                        
                                        // Check-In Link
                                        echo '<a href="admin_sitin_records.php?action=approve&id=' . $record_id . '" ' .
                                             'onclick="return confirm(\'Clock-In this student?\')" ' .
                                             'style="background: #27ae60; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.78rem; font-weight: bold; display: inline-flex; align-items: center; gap: 4px;">' .
                                             '<i class="fas fa-sign-in-alt"></i> Check-In' .
                                             '</a>';
                                        
                                        // Dismiss Link
                                        echo '<a href="admin_sitin_records.php?action=reject&id=' . $record_id . '" ' .
                                             'onclick="return confirm(\'Reject this sit-in request?\')" ' .
                                             'style="background: #e74c3c; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.78rem; font-weight: bold; display: inline-flex; align-items: center; gap: 4px;">' .
                                             '<i class="fas fa-times"></i> Dismiss' .
                                             '</a>';
                                             
                                        echo '</div>';
                                    } elseif ($st === 'Active') {
                                        // Clock Out Link
                                        echo '<a href="admin_clockout.php?id=' . $record_id . '" ' .
                                             'style="background: #e67e22; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.78rem; font-weight: bold; display: inline-block;">' .
                                             '<i class="fas fa-sign-out-alt"></i> Clock Out' .
                                             '</a>';
                                    } else {
                                        echo '<span style="color: #bbb; font-size: 0.85rem; font-weight: 600;">Completed</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php 
                            endwhile; 
                        else: 
                        ?>
                            <tr>
                                <td colspan="11" style="text-align: center; padding: 50px; color: #999; background: #fafafa;">
                                    <i class="fas fa-search" style="font-size: 2rem; color: #ddd; display: block; margin-bottom: 10px;"></i>
                                    No data logs found matching your criteria.
                                </td>
                            </tr>
                        <?php 
                        endif; 
                        ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
</body>
</html>