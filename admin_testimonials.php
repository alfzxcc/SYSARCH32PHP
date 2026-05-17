<?php 
include 'admin_check.php'; 
require_once 'db_connect.php'; 
include 'admin_header.php';

$conn->set_charset("utf8mb4");

// Dynamically get the current filename so redirects never break due to a typo
$current_page = basename(__FILE__);

// Handle testimonial state changes
if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $tid    = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'approve') {
        $conn->query("UPDATE testimonials SET is_approved = 1 WHERE id = $tid");
        header("Location: " . $current_page . "?msg=approved");
        exit();
    } elseif ($action == 'hide') {
        $conn->query("UPDATE testimonials SET is_approved = 0 WHERE id = $tid");
        header("Location: " . $current_page . "?msg=hidden");
        exit();
    } elseif ($action == 'delete') {
        $conn->query("DELETE FROM testimonials WHERE id = $tid");
        header("Location: " . $current_page . "?msg=deleted");
        exit();
    }
}

// Summary Metrics Counts using 'is_approved' values
$counts = ['Pending' => 0, 'Approved' => 0];
$countRes = $conn->query("SELECT is_approved, COUNT(*) AS cnt FROM testimonials GROUP BY is_approved");
if ($countRes) {
    while ($c = $countRes->fetch_assoc()) {
        $key = ($c['is_approved'] == 1) ? 'Approved' : 'Pending';
        $counts[$key] = $c['cnt'];
    }
}

// Filter configurations based on request variables
$filter = $_GET['status'] ?? '';
$whereSQL = "";
if ($filter === 'Approved') {
    $whereSQL = "WHERE t.is_approved = 1";
} elseif ($filter === 'Pending') {
    $whereSQL = "WHERE t.is_approved = 0";
}

// Fetch testimonials matched with your existing user database
$testimonials = $conn->query("
    SELECT t.*, CONCAT(u.firstname, ' ', u.lastname) AS student_name, u.course, u.course_level
    FROM testimonials t
    LEFT JOIN users u ON u.id_number COLLATE utf8mb4_general_ci = t.id_number COLLATE utf8mb4_general_ci
    $whereSQL
    ORDER BY t.created_at DESC
");
?>

<div class="admin-container">
    <h2 class="section-title"><i class="fas fa-comment-dots"></i> Student Testimonials Management</h2>

    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] == 'approved'): ?>
            <div style="background:#d4edda;color:#155724;padding:12px 20px;border-radius:8px;margin-bottom:20px;border-left:4px solid #28a745;">
                <i class="fas fa-check-circle"></i> Testimonial successfully approved and visible on public feeds.
            </div>
        <?php elseif ($_GET['msg'] == 'hidden'): ?>
            <div style="background:#fff3cd;color:#856404;padding:12px 20px;border-radius:8px;margin-bottom:20px;border-left:4px solid #ffc107;">
                <i class="fas fa-eye-slash"></i> Testimonial hidden from students.
            </div>
        <?php elseif ($_GET['msg'] == 'deleted'): ?>
            <div style="background:#f8d7da;color:#721c24;padding:12px 20px;border-radius:8px;margin-bottom:20px;border-left:4px solid #e74c3c;">
                <i class="fas fa-trash"></i> Testimonial entry permanently deleted from the logs.
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:25px;">
        <div style="background:#ffc107;border-radius:10px;padding:20px;color:#856404;display:flex;align-items:center;gap:15px;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
            <i class="fas fa-clock" style="font-size:2rem;opacity:0.8;"></i>
            <div>
                <div style="font-size:1.8rem;font-weight:800;"><?php echo $counts['Pending']; ?></div>
                <div style="font-size:0.85rem;font-weight:600;opacity:0.9;">Pending Review Queries</div>
            </div>
        </div>
        <div style="background:#003366;border-radius:10px;padding:20px;color:white;display:flex;align-items:center;gap:15px;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
            <i class="fas fa-check-circle" style="font-size:2rem;opacity:0.8;"></i>
            <div>
                <div style="font-size:1.8rem;font-weight:800;"><?php echo $counts['Approved']; ?></div>
                <div style="font-size:0.85rem;font-weight:600;opacity:0.9;">Approved Active Testimonials</div>
            </div>
        </div>
    </div>

    <div class="admin-card" style="margin-bottom:20px;">
        <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
            <label style="font-size:0.9rem;font-weight:bold;color:#333;">Filter Table Status:</label>
            <select name="status" style="padding:8px 12px;border:1px solid #ddd;border-radius:7px;min-width:180px;">
                <option value="">Show All Feedback Entries</option>
                <option value="Pending" <?php echo $filter=='Pending'?'selected':''; ?>>Pending Approvals</option>
                <option value="Approved" <?php echo $filter=='Approved'?'selected':''; ?>>Publicly Approved</option>
            </select>
            <button type="submit" class="btn-post" style="padding:8px 20px; background:#003366;"><i class="fas fa-filter"></i> Apply</button>
            <?php if ($filter): ?>
                <a href="<?php echo $current_page; ?>" style="padding:8px 15px;border:1px solid #ddd;border-radius:7px;text-decoration:none;color:#666;font-size:0.9rem;background:#fff;">Clear Filters</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="admin-card">
        <h3 style="margin-bottom:15px;"><i class="fas fa-list"></i> Managed Testimonial Logs</h3>
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:220px;">Student Context</th>
                        <th>Message Content</th>
                        <th style="width:120px; text-align:center;">Stars Given</th>
                        <th style="width:120px;">System Status</th>
                        <th style="width:180px; text-align:center;">Action Controls</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($testimonials && $testimonials->num_rows > 0): ?>
                        <?php while ($t = $testimonials->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($t['student_name'] ?? 'Unknown Student Account'); ?></strong><br>
                                <small style="color:#666; font-family:monospace;"><?php echo htmlspecialchars($t['id_number']); ?></small><br>
                                <small style="color:#999; font-size:0.78rem;"><?php echo htmlspecialchars($t['course'] ?? ''); ?><?php echo $t['course_level'] ? ' - Yr '.$t['course_level'] : ''; ?></small>
                            </td>
                            <td>
                                <div style="font-style:italic; color:#333; background:#f9f9f9; padding:10px 12px; border-radius:6px; border-left:3px solid #003366; line-height:1.5; font-size:0.88rem;">
                                    "<?php echo nl2br(htmlspecialchars($t['message'])); ?>"
                                </div>
                                <small style="color:#aaa; display:block; margin-top:5px;">Submitted: <?php echo date('M d, Y @ g:i A', strtotime($t['created_at'])); ?></small>
                            </td>
                            <td style="text-align:center;">
                                <div style="color:#FFD700; font-size:1rem; letter-spacing:1px;">
                                    <?php echo str_repeat('★', $t['rating']) . str_repeat('☆', 5 - $t['rating']); ?>
                                </div>
                                <small style="color:#999;">(<?php echo $t['rating']; ?>/5)</small>
                            </td>
                            <td>
                                <?php if ($t['is_approved'] == 1): ?>
                                    <span style="color:#27ae60; font-weight:bold;"><i class="fas fa-check-circle"></i> Approved</span>
                                <?php else: ?>
                                    <span style="color:#e67e22; font-weight:bold;"><i class="fas fa-clock"></i> Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display:flex; gap:6px; justify-content:center;">
                                    <?php if ($t['is_approved'] == 0): ?>
                                        <a href="?action=approve&id=<?php echo $t['id']; ?>" onclick="return confirm('Display this entry on the public testimonial page?')"
                                           style="background:#27ae60;color:white;padding:6px 10px;border-radius:5px;text-decoration:none;font-size:0.75rem;font-weight:bold;">
                                            <i class="fas fa-check"></i> Approve
                                        </a>
                                    <?php else: ?>
                                        <a href="?action=hide&id=<?php echo $t['id']; ?>" onclick="return confirm('Revoke public viewing access for this testimonial?')"
                                           style="background:#ffc107;color:#333;padding:6px 10px;border-radius:5px;text-decoration:none;font-size:0.75rem;font-weight:bold;">
                                            <i class="fas fa-eye-slash"></i> Hide
                                        </a>
                                    <?php endif; ?>

                                    <a href="?action=delete&id=<?php echo $t['id']; ?>" onclick="return confirm('Permanently drop this testimonial review record? This action cannot be reverted.')"
                                       style="background:#e74c3c;color:white;padding:6px 10px;border-radius:5px;text-decoration:none;font-size:0.75rem;">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center;padding:40px;color:#999;">
                                No database feedback records found matching this configuration.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>