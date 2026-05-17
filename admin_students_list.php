<?php
include 'admin_check.php';
require_once 'db_connect.php';
include 'admin_header.php';
$conn->set_charset("utf8mb4");

// Reset sessions for one student
if (isset($_GET['reset_id'])) {
    $rid = $conn->real_escape_string($_GET['reset_id']);
    // Verified: targeting users table by id_number
    $conn->query("UPDATE users SET remaining_sessions=30 WHERE id_number='$rid' AND role='student'");
    header("Location: admin_students_list.php?msg=reset"); exit();
}

// Reset all students
if (isset($_GET['reset_all'])) {
    $conn->query("UPDATE users SET remaining_sessions=30 WHERE role='student'");
    header("Location: admin_students_list.php?msg=reset_all"); exit();
}

$search  = $conn->real_escape_string($_GET['q'] ?? '');
$course  = $conn->real_escape_string($_GET['course'] ?? '');
$page    = max(1,(int)($_GET['page']??1));
$perPage = 20;
$offset  = ($page-1)*$perPage;

$where = "WHERE role='student'";
if ($search) $where .= " AND (id_number LIKE '%$search%' OR firstname LIKE '%$search%' OR lastname LIKE '%$search%' OR email LIKE '%$search%')";
if ($course) $where .= " AND course='$course'";

$total = $conn->query("SELECT COUNT(*) AS c FROM users $where")->fetch_assoc()['c']??0;
$pages = ceil($total/$perPage);
$students = $conn->query("SELECT * FROM users $where ORDER BY lastname ASC LIMIT $perPage OFFSET $offset");
$courses  = $conn->query("SELECT DISTINCT course FROM users WHERE role='student' AND course IS NOT NULL AND course != '' ORDER BY course");
?>
<div class="admin-container">
    <h2 class="section-title"><i class="fas fa-users"></i> Student List</h2>

    <?php if (isset($_GET['msg'])): ?>
    <div style="background:#d4edda;color:#155724;padding:12px 20px;border-radius:8px;margin-bottom:20px;border-left:4px solid #28a745;">
        <i class="fas fa-check-circle"></i>
        <?php echo $_GET['msg']=='reset_all' ? 'All students reset to 30 sessions.' : 'Student sessions reset to 30.'; ?>
    </div>
    <?php endif; ?>

    <div class="admin-card" style="margin-bottom:20px;">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
            <input type="text" name="q" value="<?php echo htmlspecialchars($_GET['q']??''); ?>"
                   placeholder="Search ID, name, email..."
                   style="flex:2;min-width:180px;padding:9px 12px;border:1px solid #ddd;border-radius:7px;">
            <select name="course" style="padding:9px 12px;border:1px solid #ddd;border-radius:7px; margin-bottom: 9px;">
                <option value="">All Courses</option>
                <?php if ($courses) while ($c=$courses->fetch_assoc()): ?>
                <option value="<?php echo $c['course']; ?>" <?php echo ($course==$c['course'])?'selected':''; ?>>
                    <?php echo htmlspecialchars($c['course']); ?>
                </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn-post" style="padding:10px 17px;margin-bottom: 9px;"><i class="fas fa-search"></i> Filter</button>
            <a href="admin_students_list.php" style="padding:7px 14px;margin-bottom: 9px;border:1px solid #ddd;border-radius:7px;text-decoration:none;color:#666;font-size:0.9rem;">Reset</a>
            <a href="?reset_all=1" onclick="return confirm('Reset ALL students to 30 sessions?')"
               style="background:#e67e22;color:white;padding:9px 16px;border-radius:7px;text-decoration:none; margin-bottom: 9px;font-size:0.88rem;font-weight:600;margin-left:auto;display:inline-flex;align-items:center;gap:5px;">
               <i class="fas fa-redo"></i> Reset All Sessions
            </a>
        </form>
    </div>

    <div class="admin-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
            <h3 style="margin:0;"><i class="fas fa-list"></i> All Students</h3>
            <span style="font-size:0.85rem;color:#888;"><?php echo $total; ?> total</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ID Number</th>
                        <th>Full Name</th>
                        <th>Course / Year</th>
                        <th>Email</th>
                        <th>Sessions Left</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students && $students->num_rows > 0): 
                        $n=$offset+1; 
                        while ($s=$students->fetch_assoc()):
                            $low = ($s['remaining_sessions'] ?? 0) <= 5;
                    ?>
                    <tr>
                        <td style="color:#aaa;font-size:0.82rem;"><?php echo $n++; ?></td>
                        <td><strong><?php echo htmlspecialchars($s['id_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($s['firstname'].' '.$s['lastname']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($s['course'] ?? '—'); ?><br>
                            <small style="color:#aaa;">Year <?php echo htmlspecialchars($s['course_level'] ?? '—'); ?></small>
                        </td>
                        <td style="font-size:0.83rem;color:#555;"><?php echo htmlspecialchars($s['email'] ?? '—'); ?></td>
                        <td>
                            <span style="font-weight:700;color:<?php echo $low?'#e74c3c':'#27ae60'; ?>;background:<?php echo $low?'#fdecea':'#e8f5e9'; ?>;padding:4px 10px;border-radius:10px;font-size:0.85rem;display:inline-flex;align-items:center;gap:5px;">
                                <?php echo $s['remaining_sessions'] ?? 0; ?>
                                <?php if ($low): ?> <i class="fas fa-exclamation-circle"></i><?php endif; ?>
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;gap:5px;">
                                <a href="admin_search.php?search_id=<?php echo urlencode($s['id_number']); ?>"
                                   title="View Details"
                                   style="background:#003366;color:white;padding:6px 10px;border-radius:5px;text-decoration:none;font-size:0.75rem;display:inline-block;">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="?reset_id=<?php echo urlencode($s['id_number']); ?>" 
                                   onclick="return confirm('Reset sessions to 30 for this student?')"
                                   title="Reset Sessions"
                                   style="background:#27ae60;color:white;padding:6px 10px;border-radius:5px;text-decoration:none;font-size:0.75rem;display:inline-block;">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="7" style="text-align:center;padding:40px;color:#aaa;">No students found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($pages > 1): ?>
        <div style="display:flex;justify-content:center;gap:8px;margin-top:20px;flex-wrap:wrap;">
            <?php for ($p=1;$p<=$pages;$p++): ?>
            <a href="?page=<?php echo $p; ?>&q=<?php echo urlencode($search); ?>&course=<?php echo urlencode($course); ?>"
               style="padding:7px 13px;border:1px solid <?php echo $p==$page?'#003366':'#ddd'; ?>;border-radius:6px;text-decoration:none;color:<?php echo $p==$page?'white':'#003366'; ?>;background:<?php echo $p==$page?'#003366':'white'; ?>;font-size:0.85rem;">
                <?php echo $p; ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>