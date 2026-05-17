<?php 
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login_page.php"); exit(); }
include 'header.php'; 
require_once 'db_connect.php'; 

$uid      = $_SESSION['user_id'];
$userData = $conn->query("SELECT * FROM users WHERE id_number = '$uid'")->fetch_assoc();

$successMsg = $errorMsg = '';

// ── Check global reservation setting ──
$settingRow = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'reservations_enabled' LIMIT 1");
$reservationsEnabled = true;
if ($settingRow && $settingRow->num_rows > 0) {
    $reservationsEnabled = ($settingRow->fetch_assoc()['setting_value'] == '1');
}

// ── Handle submission ──
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_reservation'])) {
    if (!$reservationsEnabled) {
        $errorMsg = "Reservations are currently disabled by the administrator.";
    } else {
        $lab     = $conn->real_escape_string($_POST['lab_room']);
        $pc_num  = $conn->real_escape_string($_POST['pc_number']);
        $purpose = $conn->real_escape_string($_POST['purpose']);
        $date    = $conn->real_escape_string($_POST['reservation_date']);
        $timein  = $conn->real_escape_string($_POST['time_in']);
        $timeout = $conn->real_escape_string($_POST['time_out']);

        $conflict = $conn->query("
            SELECT id FROM reservations 
            WHERE lab_room='$lab' AND pc_number='$pc_num' AND reservation_date='$date' 
              AND status != 'Cancelled'
              AND ('$timein' < time_out AND '$timeout' > time_in)
        ");
        if ($conflict && $conflict->num_rows > 0) {
            $errorMsg = "That PC is already reserved during the selected time slot. Please choose a different time or PC.";
        } else {
            $sql = "INSERT INTO reservations (id_number,lab_room,pc_number,purpose,reservation_date,time_in,time_out,status,created_at)
                    VALUES ('$uid','$lab','$pc_num','$purpose','$date','$timein','$timeout','Pending',NOW())";
            if ($conn->query($sql)) {
                $successMsg = "Reservation submitted! Waiting for admin approval.";
            } else {
                $errorMsg = "Error: " . $conn->error;
            }
        }
    }
}

// ── Cancel a reservation ──
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $rid = (int)$_GET['cancel'];
    $conn->query("UPDATE reservations SET status='Cancelled', updated_at=NOW() WHERE id=$rid AND id_number='$uid' AND status='Pending'");
    header("Location: reservation.php?cancelled=1"); exit();
}

// ── Fetch student's reservations ──
$myReservations = $conn->query("SELECT * FROM reservations WHERE id_number='$uid' ORDER BY created_at DESC LIMIT 20");
?>

<div style="max-width: 1450px;padding: 0 20px; box-sizing: border-box;">
    
    <div style="background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 30px; display: flex; gap: 25px; min-height: 600px; flex-wrap: wrap; align-items: flex-start;">
        
        <aside style="flex: 1; min-width: 270px; display: flex; flex-direction: column; gap: 20px;">
            <a href="student_sitin.php" style="display: block; text-align: center; background: #003366; color: white; padding: 14px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 0.9rem; letter-spacing: 0.5px; transition: background 0.2s; box-shadow: 0 2px 5px rgba(0,51,102,0.2);">
                <i class="fas fa-plus-circle" style="margin-right:10px;"></i> REQUEST NEW SIT-IN
            </a>
            
            <div style="background: #f8fafc; border-radius: 10px; padding: 22px; border: 1px solid #e2e8f0;">
                <div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px;">
                    <div style="margin-bottom: 12px;">
                        <img src="<?php echo !empty($userData['profile_pic']) ? htmlspecialchars($userData['profile_pic']) : 'default_avatar.png'; ?>" 
                             style="width:90px; height:90px; border-radius:50%; object-fit:cover; border:3px solid #003366; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    </div>
                    <h3 style="color: #003366; margin: 0; font-size: 1.15rem; font-weight: 700;">Student Profile</h3>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display:block; font-size:0.72rem; color:#64748b; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; margin-bottom:4px;"><i class="fas fa-id-card" style="width: 18px; color:#003366;"></i> ID Number</label>
                    <span style="font-weight:600; color:#1e293b; font-size: 0.92rem; display:block; background:white; padding:8px 12px; border-radius:6px; border:1px solid #e2e8f0;"><?php echo htmlspecialchars($userData['id_number'] ?? 'N/A'); ?></span>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display:block; font-size:0.72rem; color:#64748b; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; margin-bottom:4px;"><i class="fas fa-user" style="width: 18px; color:#003366;"></i> Full Name</label>
                    <span style="font-weight:600; color:#1e293b; font-size: 0.92rem; display:block; background:white; padding:8px 12px; border-radius:6px; border:1px solid #e2e8f0;"><?php echo htmlspecialchars(($userData['firstname']??'').' '.($userData['lastname']??'')); ?></span>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display:block; font-size:0.72rem; color:#64748b; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; margin-bottom:4px;"><i class="fas fa-graduation-cap" style="width: 18px; color:#003366;"></i> Course</label>
                    <span style="font-weight:600; color:#1e293b; font-size: 0.92rem; display:block; background:white; padding:8px 12px; border-radius:6px; border:1px solid #e2e8f0;"><?php echo htmlspecialchars($userData['course'] ?? 'N/A'); ?></span>
                </div>
                
                <div style="background: #fffbeb; padding: 12px 15px; border-radius: 8px; border: 1px solid #fef3c7; border-left: 4px solid #b45309; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                    <label style="display:block; font-size:0.72rem; color:#b45309; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;"><i class="fas fa-hourglass-half" style="width: 18px;"></i> Remaining Sessions</label>
                    <span style="font-size: 1.25rem; font-weight: 800; color: #b45309; display: block; margin-top: 2px; text-align: center;"><?php echo htmlspecialchars($userData['remaining_sessions'] ?? '30'); ?> Sessions Left</span>
                </div>
            </div>

            <div style="background: #f8fafc; border-radius: 10px; padding: 20px; border: 1px solid #e2e8f0; text-align: center;">
                <?php if ($reservationsEnabled): ?>
                    <div style="width:48px; height:48px; background:#dcfce7; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 10px;">
                        <i class="fas fa-calendar-check" style="color:#16a34a; font-size:1.3rem;"></i>
                    </div>
                    <div style="font-weight:700; color:#16a34a; font-size:0.95rem;">Reservations Open</div>
                    <div style="font-size:0.78rem; color:#64748b; margin-top:4px;">System slots are active.</div>
                <?php else: ?>
                    <div style="width:48px; height:48px; background:#fee2e2; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 10px;">
                        <i class="fas fa-ban" style="color:#dc2626; font-size:1.3rem;"></i>
                    </div>
                    <div style="font-weight:700; color:#dc2626; font-size:0.95rem;">Reservations Locked</div>
                    <div style="font-size:0.78rem; color:#64748b; margin-top:4px;">Controlled by administrator.</div>
                <?php endif; ?>
            </div>
        </aside>

        <section style="flex: 2.8; min-width: 450px; display: flex; flex-direction: column; gap: 20px;">
            <div style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; background: white; padding: 25px;">
                
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;">
                    <h2 style="margin: 0; font-size: 1.25rem; color: #003366; font-weight: 700;"><i class="fas fa-calendar-plus" style="margin-right: 8px;"></i> Lab Reservation</h2>
                    <span style="font-size: 0.78rem; background: <?php echo $reservationsEnabled ? '#dcfce7':'#fee2e2'; ?>; color: <?php echo $reservationsEnabled ? '#15803d':'#b91c1c'; ?>; padding: 4px 12px; border-radius: 12px; font-weight: 700; text-transform: uppercase; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-circle" style="font-size:0.5rem;"></i> <?php echo $reservationsEnabled ? 'Open' : 'Disabled'; ?>
                    </span>
                </div>

                <?php if (!$reservationsEnabled): ?>
                <div style="background:#fee2e2; border:1px solid #fca5a5; border-left: 4px solid #dc2626; border-radius:8px; padding:15px; margin-bottom:20px; display:flex; align-items:center; gap:12px;">
                    <i class="fas fa-ban" style="color:#dc2626; font-size:1.3rem; flex-shrink:0;"></i>
                    <div style="font-size:0.85rem; color:#991b1b;">The administrator has temporarily closed reservations. Form updates and allocations are frozen.</div>
                </div>
                <?php endif; ?>

                <?php if ($successMsg): ?>
                <div style="background:#dcfce7; color:#16a34a; padding:12px 15px; border-radius:8px; margin-bottom:20px; border-left:4px solid #16a34a; font-size:0.88rem; font-weight:500;">
                    <i class="fas fa-check-circle" style="margin-right:6px;"></i> <?php echo $successMsg; ?>
                </div>
                <?php endif; ?>
                <?php if ($errorMsg): ?>
                <div style="background:#fee2e2; color:#dc2626; padding:12px 15px; border-radius:8px; margin-bottom:20px; border-left:4px solid #dc2626; font-size:0.88rem; font-weight:500;">
                    <i class="fas fa-exclamation-circle" style="margin-right:6px;"></i> <?php echo $errorMsg; ?>
                </div>
                <?php endif; ?>
                <?php if (isset($_GET['cancelled'])): ?>
                <div style="background:#fef3c7; color:#d97706; padding:12px 15px; border-radius:8px; margin-bottom:20px; border-left:4px solid #d97706; font-size:0.88rem; font-weight:500;">
                    <i class="fas fa-info-circle" style="margin-right:6px;"></i> Reservation system reference updated and cancelled successfully.
                </div>
                <?php endif; ?>

                <form method="POST" style="background:#f8fafc; padding:25px; border-radius:8px; border:1px solid #e2e8f0; margin-bottom:30px; <?php echo !$reservationsEnabled ? 'opacity:0.55; pointer-events:none;' : ''; ?>">
                    <h4 style="margin-top:0; color:#003366; margin-bottom:18px; font-size:1rem; font-weight:700;"><i class="fas fa-edit"></i> Create Booking Placement</h4>
                    
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">
                        <div>
                            <label style="display:block; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:6px;">Laboratory Room</label>
                            <select name="lab_room" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #cbd5e1; background:white; font-size:0.9rem; color:#1e293b;">
                                <option value="" disabled selected>-- Select Lab Room --</option>
                                <option value="Lab 524">Lab 524 (Programming)</option>
                                <option value="Lab 526">Lab 526 (Multimedia)</option>
                                <option value="Lab 542">Lab 542 (Networking)</option>
                                <option value="Lab 544">Lab 544 (General)</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:6px;">Target PC Unit</label>
                            <select name="pc_number" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #cbd5e1; background:white; font-size:0.9rem; color:#1e293b;">
                                <option value="" disabled selected>-- Select PC Station --</option>
                                <?php for($i=1; $i<=40; $i++): $station = "PC-" . str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                                <option value="<?php echo $station; ?>"><?php echo $station; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:6px;">Usage Purpose</label>
                            <select name="purpose" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #cbd5e1; background:white; font-size:0.9rem; color:#1e293b;">
                                <option value="" disabled selected>-- Select Purpose --</option>
                                <option value="Java Programming">Java Programming</option>
                                <option value="Web Development">Web Development</option>
                                <option value="CICS Project">CICS Project</option>
                                <option value="Research">Research</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:6px;">Reservation Date</label>
                            <input type="date" name="reservation_date" min="<?php echo date('Y-m-d'); ?>" required style="width:100%; padding:9px; border-radius:6px; border:1px solid #cbd5e1; background:white; font-size:0.9rem; color:#1e293b; box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:6px;">Time In</label>
                            <input type="time" name="time_in" required style="width:100%; padding:9px; border-radius:6px; border:1px solid #cbd5e1; background:white; font-size:0.9rem; color:#1e293b; box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:6px;">Time Out</label>
                            <input type="time" name="time_out" required style="width:100%; padding:9px; border-radius:6px; border:1px solid #cbd5e1; background:white; font-size:0.9rem; color:#1e293b; box-sizing:border-box;">
                        </div>
                    </div>
                    
                    <div style="margin-top:22px; display:flex; gap:12px; flex-wrap:wrap;">
                        <button type="submit" name="submit_reservation" style="flex:2; min-width:180px; padding:12px; background:#003366; color:white; border:none; border-radius:6px; font-weight:700; cursor:pointer; font-size:0.88rem; letter-spacing:0.3px; text-transform:uppercase; box-shadow:0 2px 4px rgba(0,51,102,0.15);">
                            <i class="fas fa-calendar-plus" style="margin-right:8px;"></i> Submit Assignment Request
                        </button>
                        <a href="dashboard.php" style="flex:1; min-width:90px; text-align:center; padding:12px; border-radius:6px; border:1px solid #cbd5e1; text-decoration:none; color:#475569; background:white; font-weight:700; font-size:0.88rem; box-sizing:border-box;">BACK</a>
                    </div>
                </form>

                <h3 style="color:#003366; font-size:1.05rem; font-weight:700; margin:0 0 14px 0; display:flex; align-items:center; gap:8px;"><i class="fas fa-list-alt"></i> My Booking History Logs</h3>
                <div style="overflow-x:auto; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <table style="width:100%; border-collapse:collapse; background:white; font-size:0.88rem; text-align:left; min-width:650px;">
                        <thead>
                            <tr style="background:#f8fafc; color:#003366; border-bottom:2px solid #e2e8f0;">
                                <th style="padding:14px; font-weight:700;">Date</th>
                                <th style="padding:14px; font-weight:700;">Lab Allocation</th>
                                <th style="padding:14px; font-weight:700;">Purpose context</th>
                                <th style="padding:14px; font-weight:700;">Time Window</th>
                                <th style="padding:14px; font-weight:700;">State Status</th>
                                <th style="padding:14px; font-weight:700; text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $statusColors = ['Pending'=>'#d97706','Approved'=>'#16a34a','Cancelled'=>'#64748b','Rejected'=>'#dc2626'];
                            if ($myReservations && $myReservations->num_rows > 0):
                                while ($r = $myReservations->fetch_assoc()):
                                    $currStatus = $r['status'];
                                    $dotColor = $statusColors[$currStatus] ?? '#94a3b8';
                            ?>
                            <tr style="border-bottom:1px solid #e2e8f0; color:#334155;">
                                <td style="padding:14px; font-weight:500;"><?php echo date('M d, Y', strtotime($r['reservation_date'])); ?></td>
                                <td style="padding:14px;">
                                    <span style="font-weight:700; color:#1e293b;"><?php echo htmlspecialchars($r['lab_room']); ?></span>
                                    <span style="display:block; font-size:0.78rem; color:#64748b; font-weight:500;"><?php echo htmlspecialchars($r['pc_number']); ?></span>
                                </td>
                                <td style="padding:14px; color:#475569;"><?php echo htmlspecialchars($r['purpose']); ?></td>
                                <td style="padding:14px; font-variant-numeric:tabular-nums;">
                                    <span style="font-weight:500; color:#1e293b;"><?php echo date('g:i A', strtotime($r['time_in'])); ?></span>
                                    <span style="color:#94a3b8; margin:0 2px;">➔</span>
                                    <span style="font-weight:500; color:#1e293b;"><?php echo date('g:i A', strtotime($r['time_out'])); ?></span>
                                </td>
                                <td style="padding:14px;">
                                    <span style="color:<?php echo $dotColor; ?>; font-weight:700; display:inline-flex; align-items:center; gap:6px;">
                                        ● <?php echo $currStatus; ?>
                                    </span>
                                </td>
                                <td style="padding: 14px; text-align: center; vertical-align: middle;">
                                    <?php if ($currStatus == 'Pending'): ?>
                                    <a href="reservation.php?cancel=<?php echo $r['id']; ?>"
                                    onclick="return confirm('Drop and cancel this reservation entry slot?')"
                                    style="display: inline-flex; align-items: center; justify-content: center; gap: 6px; background: #fee2e2; color: #ef4444; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.78rem; font-weight: 700; border: 1px solid #fca5a5; line-height: 1; transition: all 0.2s;">
                                        <i class="fas fa-times" style="font-size: 0.8rem; margin: 0;"></i> Cancel
                                    </a>
                                    <?php else: ?>
                                    <span style="color: #cbd5e1; font-weight: bold; display: block; line-height: 1;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center; padding:50px 20px; color:#94a3b8;">
                                    <i class="fas fa-calendar-times" style="display:block; font-size:2.2rem; margin-bottom:10px; color:#cbd5e1;"></i>
                                    No lab reservation history entries found in database context.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </section>
    </div>
</div>

</body>
</html>