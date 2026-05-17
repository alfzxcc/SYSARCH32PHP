<?php 
include 'admin_check.php'; 
require_once 'db_connect.php'; 
include 'admin_header.php';

$conn->set_charset("utf8mb4");

// Handle status update
if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $rid    = (int)$_GET['id'];
    $action = in_array($_GET['action'], ['approve','reject','cancel']) ? $_GET['action'] : '';
    $statusMap = ['approve'=>'Approved', 'reject'=>'Rejected', 'cancel'=>'Cancelled'];
    if ($action && isset($statusMap[$action])) {
        $newStatus = $statusMap[$action];
        $conn->query("UPDATE reservations SET status = '$newStatus', updated_at = NOW() WHERE id = $rid");

        // Notify student
        $resRow = $conn->query("SELECT * FROM reservations WHERE id = $rid")->fetch_assoc();
        if ($resRow) {
            $msg = $conn->real_escape_string("Your reservation for {$resRow['lab_room']} on {$resRow['reservation_date']} has been $newStatus.");
            $conn->query("INSERT INTO notifications (id_number, message, is_read, created_at) VALUES ('{$resRow['id_number']}', '$msg', 0, NOW())");
        }
        header("Location: admin_reservation.php?updated=1");
        exit();
    }
}

// Filters
$statusFilter = $conn->real_escape_string($_GET['status'] ?? '');
$dateFilter   = $conn->real_escape_string($_GET['date']   ?? '');
$labFilter    = $conn->real_escape_string($_GET['lab']    ?? '');
$search       = $conn->real_escape_string($_GET['q']      ?? '');

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset  = ($page - 1) * $perPage;

$whereArr = [];
if ($statusFilter) $whereArr[] = "r.status = '$statusFilter'";
if ($dateFilter)   $whereArr[] = "r.reservation_date = '$dateFilter'";
if ($labFilter)    $whereArr[] = "r.lab_room = '$labFilter'";
if ($search)       $whereArr[] = "(r.id_number LIKE '%$search%' OR u.firstname LIKE '%$search%' OR u.lastname LIKE '%$search%')";
$whereSQL = $whereArr ? 'WHERE ' . implode(' AND ', $whereArr) : '';

// Added COLLATE utf8mb4_general_ci here to solve error line 45
$countResult = $conn->query("SELECT COUNT(*) AS cnt FROM reservations r LEFT JOIN users u ON u.id_number COLLATE utf8mb4_general_ci = r.id_number COLLATE utf8mb4_general_ci $whereSQL");
$totalRows   = $countResult ? $countResult->fetch_assoc()['cnt'] : 0;
$totalPages  = ceil($totalRows / $perPage);

// Added COLLATE utf8mb4_general_ci here to solve error line 49
$reservations = $conn->query("
    SELECT r.*, CONCAT(u.firstname, ' ', u.lastname) AS student_name, u.course
    FROM reservations r
    LEFT JOIN users u ON u.id_number COLLATE utf8mb4_general_ci = r.id_number COLLATE utf8mb4_general_ci
    $whereSQL
    ORDER BY 
        FIELD(r.status, 'Pending', 'Approved', 'Rejected', 'Cancelled'),
        r.reservation_date ASC, r.time_in ASC
    LIMIT $perPage OFFSET $offset
");

// Summary counts
$counts = ['Pending'=>0,'Approved'=>0,'Rejected'=>0,'Cancelled'=>0];
$countRes = $conn->query("SELECT status, COUNT(*) AS cnt FROM reservations GROUP BY status");
if ($countRes) while ($c = $countRes->fetch_assoc()) $counts[$c['status']] = $c['cnt'];
?>

<div class="admin-container">
    <h2 class="section-title"><i class="fas fa-calendar-alt"></i> View Reservations</h2>

    <?php if (isset($_GET['updated'])): ?>
    <div style="background:#d4edda;color:#155724;padding:12px 20px;border-radius:8px;margin-bottom:20px;border-left:4px solid #28a745;">
        <i class="fas fa-check-circle"></i> Reservation status updated successfully.
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin-bottom:25px;">
        <?php
        $cardDef = [
            'Pending'   => ['#ffc107','#856404','fas fa-clock'],
            'Approved'  => ['#28a745','white',  'fas fa-check-circle'],
            'Rejected'  => ['#e74c3c','white',  'fas fa-times-circle'],
            'Cancelled' => ['#6c757d','white',  'fas fa-ban'],
        ];
        foreach ($cardDef as $status => [$bg, $color, $icon]):
        ?>
        <div style="background:<?php echo $bg; ?>;border-radius:10px;padding:18px;color:<?php echo $color; ?>;display:flex;align-items:center;gap:15px;box-shadow:0 2px 8px rgba(0,0,0,0.12);">
            <i class="<?php echo $icon; ?>" style="font-size:1.8rem;opacity:0.85;"></i>
            <div>
                <div style="font-size:1.6rem;font-weight:800;"><?php echo number_format($counts[$status]); ?></div>
                <div style="font-size:0.82rem;opacity:0.9;"><?php echo $status; ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="admin-card" style="margin-bottom:20px;">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
            <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search student..." style="padding:8px 13px;border:1px solid #ddd;border-radius:7px;flex:2;min-width:150px;">
            <select name="status" style="padding:8px 12px;border:1px solid #ddd;border-radius:7px;">
                <option value="">All Status</option>
                <?php foreach (array_keys($counts) as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo $statusFilter==$s?'selected':''; ?>><?php echo $s; ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="date" value="<?php echo $dateFilter; ?>" style="padding:8px 12px;border:1px solid #ddd;border-radius:7px;">
            <select name="lab" style="padding:8px 12px;border:1px solid #ddd;border-radius:7px;">
                <option value="">All Labs</option>
                <option value="Lab 524" <?php echo $labFilter=='Lab 524'?'selected':''; ?>>Lab 524</option>
                <option value="Lab 526" <?php echo $labFilter=='Lab 526'?'selected':''; ?>>Lab 526</option>
                <option value="Lab 542" <?php echo $labFilter=='Lab 542'?'selected':''; ?>>Lab 542</option>
                <option value="Lab 544" <?php echo $labFilter=='Lab 544'?'selected':''; ?>>Lab 544</option>
            </select>
            <button type="submit" class="btn-post" style="padding:9px 20px;"><i class="fas fa-search"></i> Filter</button>
            <a href="admin_reservation.php" style="padding:9px 15px;border:1px solid #ddd;border-radius:7px;text-decoration:none;color:#666;font-size:0.9rem;">Reset</a>
        </form>
    </div>

    <div class="admin-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
            <h3 style="margin:0;"><i class="fas fa-list"></i> Reservations</h3>
            <span style="font-size:0.85rem;color:#888;"><?php echo $totalRows; ?> total records</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Lab / PC</th>
                        <th>Purpose</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $statusColors = ['Pending'=>'#ffc107','Approved'=>'#27ae60','Rejected'=>'#e74c3c','Cancelled'=>'#6c757d'];
                    if ($reservations && $reservations->num_rows > 0):
                        while ($r = $reservations->fetch_assoc()):
                            $sc = $statusColors[$r['status']] ?? '#aaa';
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($r['student_name'] ?? 'Unknown'); ?></strong><br>
                            <small style="color:#888;"><?php echo htmlspecialchars($r['id_number']); ?></small><br>
                            <small style="color:#aaa;"><?php echo htmlspecialchars($r['course'] ?? ''); ?></small>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($r['lab_room']); ?></strong><br>
                            <small style="color:#666;"><?php echo htmlspecialchars($r['pc_number']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($r['purpose']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($r['reservation_date'])); ?></td>
                        <td><?php echo date('g:i A', strtotime($r['time_in'])); ?></td>
                        <td><?php echo date('g:i A', strtotime($r['time_out'])); ?></td>
                        <td><span style="color:<?php echo $sc; ?>;font-weight:bold;">● <?php echo $r['status']; ?></span></td>
                        <td>
                            <?php if ($r['status'] == 'Pending'): ?>
                            <div style="display:flex;gap:5px;flex-wrap:wrap;">
                                <a href="?action=approve&id=<?php echo $r['id']; ?>" onclick="return confirm('Approve this reservation?')"
                                   style="background:#27ae60;color:white;padding:5px 10px;border-radius:5px;text-decoration:none;font-size:0.78rem;white-space:nowrap;">
                                    <i class="fas fa-check"></i> Approve
                                </a>
                                <a href="?action=reject&id=<?php echo $r['id']; ?>" onclick="return confirm('Reject this reservation?')"
                                   style="background:#e74c3c;color:white;padding:5px 10px;border-radius:5px;text-decoration:none;font-size:0.78rem;white-space:nowrap;">
                                    <i class="fas fa-times"></i> Reject
                                </a>
                            </div>
                            <?php elseif ($r['status'] == 'Approved'): ?>
                            <a href="?action=cancel&id=<?php echo $r['id']; ?>" onclick="return confirm('Cancel this approved reservation?')"
                               style="background:#6c757d;color:white;padding:5px 10px;border-radius:5px;text-decoration:none;font-size:0.78rem;">
                                <i class="fas fa-ban"></i> Cancel
                            </a>
                            <?php else: ?>
                            <span style="color:#ccc;font-size:0.8rem;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="8" style="text-align:center;padding:40px;color:#999;">No reservations found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div style="display:flex;justify-content:center;gap:8px;margin-top:20px;flex-wrap:wrap;">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <a href="?page=<?php echo $p; ?>&status=<?php echo urlencode($statusFilter); ?>&date=<?php echo $dateFilter; ?>&lab=<?php echo urlencode($labFilter); ?>&q=<?php echo urlencode($search); ?>"
                   style="padding:7px 14px;border:1px solid <?php echo $p==$page?'#003366':'#ddd'; ?>;border-radius:6px;text-decoration:none;color:<?php echo $p==$page?'white':'#003366'; ?>;background:<?php echo $p==$page?'#003366':'white'; ?>;font-size:0.85rem;">
                    <?php echo $p; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>