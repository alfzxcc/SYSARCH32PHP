<?php 
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login_page.php"); exit(); }
include 'header.php'; 
require_once 'db_connect.php'; 

$uid      = $_SESSION['user_id'];
$role     = $_SESSION['role'] ?? 'student';
$userData = $conn->query("SELECT * FROM users WHERE id_number = '$uid'")->fetch_assoc();

$successMsg = $errorMsg = '';

// Submit / update testimonial
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_testimonial'])) {
    $message = $conn->real_escape_string(trim($_POST['message']));
    $rating  = max(1, min(5, (int)$_POST['rating']));
    if (strlen($message) < 10) {
        $errorMsg = "Testimonial must be at least 10 characters.";
    } else {
        $existing = $conn->query("SELECT id FROM testimonials WHERE id_number = '$uid'");
        if ($existing && $existing->num_rows > 0) {
            $conn->query("UPDATE testimonials SET message='$message', rating=$rating, updated_at=NOW() WHERE id_number='$uid'");
            $successMsg = "Your testimonial has been updated!";
        } else {
            $conn->query("INSERT INTO testimonials (id_number, message, rating, is_approved, created_at) VALUES ('$uid','$message',$rating,0,NOW())");
            $successMsg = "Thank you! Your testimonial has been submitted for review.";
        }
    }
}

// Admin actions
if ($role == 'admin') {
    if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
        $conn->query("UPDATE testimonials SET is_approved=1 WHERE id=".(int)$_GET['approve']);
        header("Location: testimonials.php?msg=approved"); exit();
    }
    if (isset($_GET['hide']) && is_numeric($_GET['hide'])) {
        $conn->query("UPDATE testimonials SET is_approved=0 WHERE id=".(int)$_GET['hide']);
        header("Location: testimonials.php?msg=hidden"); exit();
    }
    if (isset($_GET['delete_t']) && is_numeric($_GET['delete_t'])) {
        $conn->query("DELETE FROM testimonials WHERE id=".(int)$_GET['delete_t']);
        header("Location: testimonials.php?msg=deleted"); exit();
    }
}

$myTestimonial  = $conn->query("SELECT * FROM testimonials WHERE id_number='$uid'")->fetch_assoc();
$approvedFilter = ($role != 'admin') ? "WHERE t.is_approved = 1" : "";
$testimonials   = $conn->query("
    SELECT t.*, CONCAT(u.firstname,' ',u.lastname) AS student_name, u.course, u.course_level
    FROM testimonials t LEFT JOIN users u ON u.id_number = t.id_number
    $approvedFilter ORDER BY t.created_at DESC
");
$avgResult = $conn->query("SELECT AVG(rating) AS avg_r, COUNT(*) AS total FROM testimonials WHERE is_approved=1")->fetch_assoc();
$avg = round($avgResult['avg_r'] ?? 0, 1);
?>

<style>
/* ── Dark Mode Adaptive System Overrides ── */
body.dark-mode {
    --dm-bg:      #121212;
    --dm-card:    #1e1e1e;
    --dm-border:  #333;
    --dm-text:    #e0e0e0;
    --dm-muted:   #aaa;
    --dm-input:   #2a2a2a;
    background: linear-gradient(rgba(0,0,0,0.85),rgba(0,0,0,0.85)), url('UC.jpg') no-repeat center center fixed;
    background-size: cover;
    color: var(--dm-text);
}
body.dark-mode .unified-profile-box,
body.dark-mode .unified-main-panel,
body.dark-mode .testimonial-entry-card {
    background: var(--dm-card) !important;
    color: var(--dm-text) !important;
    border-color: var(--dm-border) !important;
}
body.dark-mode .info-metric span { background: #2a2a2a; color: #ddd; border-color: #444; }
body.dark-mode .info-metric label { color: #90b4e8; }
body.dark-mode .dm-meta { color: #888; }
body.dark-mode .dm-name { color: #90b4e8; }
body.dark-mode textarea { background: #2a2a2a !important; color: #ddd !important; border-color: #444 !important; }
</style>

<div style="max-width: 1450px; padding: 0 20px; box-sizing: border-box;">
    
    <div style="background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 30px; display: flex; gap: 25px; min-height: 600px; flex-wrap: wrap; align-items: flex-start;">
        
        <aside style="flex: 1; min-width: 270px; display: flex; flex-direction: column; gap: 20px;">
            <a href="student_sitin.php" style="display: block; text-align: center; background: #003366; color: white; padding: 14px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 0.9rem; letter-spacing: 0.5px; transition: background 0.2s; box-shadow: 0 2px 5px rgba(0,51,102,0.2);">
                <i class="fas fa-plus-circle" style="margin-right:10px;"></i> REQUEST NEW SIT-IN
            </a>
            
            <div class="unified-profile-box" style="background: #f8fafc; border-radius: 10px; padding: 22px; border: 1px solid #e2e8f0;">
                <div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px;">
                    <div style="margin-bottom: 12px;">
                        <img src="<?php echo !empty($userData['profile_pic']) ? htmlspecialchars($userData['profile_pic']) : 'default_avatar.png'; ?>" 
                             style="width:90px; height:90px; border-radius:50%; object-fit:cover; border:3px solid #003366; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    </div>
                    <h3 style="color: #003366; margin: 0; font-size: 1.15rem; font-weight: 700;">Student Profile</h3>
                </div>
                
                <div class="info-metric" style="margin-bottom: 15px;">
                    <label style="display:block; font-size:0.72rem; color:#64748b; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; margin-bottom:4px;"><i class="fas fa-id-card" style="width: 18px; color:#003366;"></i> ID Number</label>
                    <span style="font-weight:600; color:#1e293b; font-size: 0.92rem; display:block; background:white; padding:8px 12px; border-radius:6px; border:1px solid #e2e8f0;"><?php echo htmlspecialchars($userData['id_number'] ?? 'N/A'); ?></span>
                </div>
                
                <div class="info-metric" style="margin-bottom: 15px;">
                    <label style="display:block; font-size:0.72rem; color:#64748b; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; margin-bottom:4px;"><i class="fas fa-user" style="width: 18px; color:#003366;"></i> Full Name</label>
                    <span style="font-weight:600; color:#1e293b; font-size: 0.92rem; display:block; background:white; padding:8px 12px; border-radius:6px; border:1px solid #e2e8f0;"><?php echo htmlspecialchars(($userData['firstname']??'').' '.($userData['lastname']??'')); ?></span>
                </div>
                
                <div class="info-metric style="background: #fffbeb; padding: 12px 15px; border-radius: 8px; border: 1px solid #fef3c7; border-left: 4px solid #b45309; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                    <label style="display:block; font-size:0.72rem; color:#b45309; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;"><i class="fas fa-hourglass-half" style="width: 18px;"></i> Remaining Sessions</label>
                    <span style="font-size: 1.25rem; font-weight: 800; color: #b45309; display: block; margin-top: 2px; text-align: center;"><?php echo htmlspecialchars($userData['remaining_sessions'] ?? '30'); ?> Sessions Left</span>
                </div>
            </div>

            <div class="unified-profile-box" style="background: #f8fafc; border-radius: 10px; padding: 20px; border: 1px solid #e2e8f0; text-align: center;">
                <div style="font-size:2.4rem; font-weight:800; color:#003366; line-height:1.1;"><?php echo $avg ?: '—'; ?></div>
                <div style="color:#FFD700; font-size:1.3rem; margin:6px 0; letter-spacing: 2px;">
                    <?php for($i=1; $i<=5; $i++) echo $i<=round($avg) ? '★' : '☆'; ?>
                </div>
                <div style="font-size:0.75rem; color:#64748b; font-weight: 500; text-transform: uppercase; letter-spacing: 0.3px;"><?php echo number_format($avgResult['total']??0); ?> Verified Testimonials</div>
            </div>
        </aside>

        <section style="flex: 2.8; min-width: 450px; display: flex; flex-direction: column; gap: 25px;">
            
            <div class="unified-main-panel" style="border: 1px solid #e2e8f0; border-radius: 10px; background: white; padding: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;">
                    <h2 style="margin: 0; font-size: 1.25rem; color: #003366; font-weight: 700;"><i class="fas fa-comment-dots" style="margin-right: 8px;"></i> <?php echo $myTestimonial ? 'Update My Testimonial' : 'Share Your Lab Experience'; ?></h2>
                    <span style="font-size: 0.78rem; background: #e0f2fe; color: #0369a1; padding: 4px 12px; border-radius: 12px; font-weight: 700; text-transform: uppercase; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-star" style="color:#FFD700;"></i> Student Feedback
                    </span>
                </div>

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

                <form method="POST">
                    <div style="margin-bottom:18px;">
                        <label style="display:block; margin-bottom:6px; font-weight:700; color:#475569; font-size:0.82rem; text-transform:uppercase; letter-spacing:0.5px;">Assign Quality Rating</label>
                        <div id="starRow" style="display:flex; gap:8px; font-size:2.2rem; cursor:pointer; width: fit-content;">
                            <?php for($i=1;$i<=5;$i++): ?>
                            <span class="star-btn" data-val="<?php echo $i; ?>" style="color:#cbd5e1; transition:color 0.15s; user-select:none;">★</span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="ratingVal" value="<?php echo $myTestimonial['rating'] ?? 5; ?>" required>
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block; margin-bottom:6px; font-weight:700; color:#475569; font-size:0.82rem; text-transform:uppercase; letter-spacing:0.5px;">Your Written Review Statement</label>
                        <textarea name="message" rows="4" required placeholder="Share your constructive notes regarding CCS lab computer units, speed, environment, or air-conditioning systems..."
                                  style="width:100%; padding:14px; border:1px solid #cbd5e1; border-radius:8px; box-sizing:border-box; font-size:0.92rem; color:#1e293b; resize:vertical; transition:border-color 0.2s;"
                                  onfocus="this.style.borderColor='#003366'" onblur="this.style.borderColor='#cbd5e1'"><?php echo htmlspecialchars($myTestimonial['message'] ?? ''); ?></textarea>
                        <div style="font-size:0.75rem; color:#94a3b8; margin-top:6px; font-weight:500; display:flex; align-items:center; gap:5px;">
                            <i class="fas fa-info-circle"></i> Minimum character length filter limit: 10 chars. 
                            <?php if($myTestimonial && !$myTestimonial['is_approved']): ?>
                                <span style="color:#d97706; font-weight:700; margin-left:auto;"><i class="fas fa-clock"></i> Intercepted: Awaiting Admin Approval Mod Verification</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="display:flex; gap:12px; flex-wrap:wrap;">
                        <button type="submit" name="submit_testimonial" style="flex:2; min-width:180px; padding:12px; background:#003366; color:white; border:none; border-radius:6px; font-weight:700; cursor:pointer; font-size:0.88rem; letter-spacing:0.3px; text-transform:uppercase; box-shadow:0 2px 4px rgba(0,51,102,0.15);">
                            <i class="fas fa-paper-plane" style="margin-right:8px;"></i> <?php echo $myTestimonial ? 'Update Testimonial Registry' : 'Publish Testimonial Statement'; ?>
                        </button>
                        <a href="dashboard.php" style="flex:1; min-width:90px; text-align:center; padding:12px; border-radius:6px; border:1px solid #cbd5e1; text-decoration:none; color:#475569; background:white; font-weight:700; font-size:0.88rem; box-sizing:border-box;">BACK</a>
                    </div>
                </form>
            </div>

            <div class="unified-main-panel" style="border: 1px solid #e2e8f0; border-radius: 10px; background: white; padding: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;">
                    <h2 style="margin: 0; font-size: 1.25rem; color: #003366; font-weight: 700;"><i class="fas fa-comments" style="margin-right: 8px;"></i> Public Testimonial Board</h2>
                    <?php if($role=='admin'): ?>
                    <span style="font-size: 0.78rem; background: #fee2e2; color: #b91c1c; padding: 4px 12px; border-radius: 12px; font-weight: 700; text-transform: uppercase; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-shield-alt"></i> Administrative Audit Active View
                    </span>
                    <?php else: ?>
                    <span style="font-size: 0.78rem; background: #f1f5f9; color: #475569; padding: 4px 12px; border-radius: 12px; font-weight: 600;">Verified Class Insights</span>
                    <?php endif; ?>
                </div>

                <?php if (isset($_GET['msg'])): ?>
                <div style="background:#e0f2fe; color:#0369a1; padding:10px 15px; font-size:0.82rem; border-radius:6px; margin-bottom:15px; font-weight:600; border-left:3px solid #0369a1;">
                    <i class="fas fa-info-circle"></i> Matrix action synced: 
                    <?php $msgs=['approved'=>'Testimonial status switched to Approved.','hidden'=>'Testimonial visibility restricted to Hidden.','deleted'=>'Testimonial object dropped from cluster database entry records.']; echo $msgs[$_GET['msg']]??'Action completed successfully.'; ?>
                </div>
                <?php endif; ?>

                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <?php if ($testimonials && $testimonials->num_rows > 0):
                        while ($t = $testimonials->fetch_assoc()):
                            $isOwn = $t['id_number'] == $uid;
                            $statusBorder = $t['is_approved'] ? '#003366' : '#d97706';
                    ?>
                    <div class="testimonial-entry-card" style="background: <?php echo $t['is_approved'] ? 'white' : '#fefaf0'; ?>; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); border-left: 5px solid <?php echo $statusBorder; ?>; position: relative;">
                        
                        <?php if ($role == 'admin'): ?>
                        <div style="position: absolute; top: 15px; right: 15px; display: flex; gap: 6px; flex-wrap: wrap;">
                            <?php if(!$t['is_approved']): ?>
                            <a href="?approve=<?php echo $t['id']; ?>" onclick="return confirm('Change status flag to approved public view?')"
                               style="background: #16a34a; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 0.72rem; font-weight:700; display:inline-flex; align-items:center; gap:4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);"><i class="fas fa-check"></i> Approve</a>
                            <?php else: ?>
                            <a href="?hide=<?php echo $t['id']; ?>" onclick="return confirm('Hide this entry row block from students log boards?')"
                               style="background: #d97706; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 0.72rem; font-weight:700; display:inline-flex; align-items:center; gap:4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);"><i class="fas fa-eye-slash"></i> Hide</a>
                            <?php endif; ?>
                            <a href="?delete_t=<?php echo $t['id']; ?>" onclick="return confirm('Permanently wipe out this testimonial row element item across records?')"
                               style="background: #ef4444; color: white; padding: 5px 8px; border-radius: 4px; text-decoration: none; font-size: 0.72rem; display:inline-flex; align-items:center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);"><i class="fas fa-trash"></i></a>
                        </div>
                        <?php endif; ?>

                        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 12px; padding-right: <?php echo ($role=='admin') ? '140px':'0px'; ?>;">
                            <div style="width: 44px; height: 44px; background: #003366; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.1rem; flex-shrink: 0; box-shadow: 0 2px 5px rgba(0,51,102,0.15);">
                                <?php echo strtoupper(substr($t['student_name'] ?? 'U', 0, 1)); ?>
                            </div>
                            <div style="flex: 1;">
                                <div class="dm-name" style="font-weight: 700; color: #003366; font-size: 0.95rem; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                    <?php echo htmlspecialchars($t['student_name'] ?? 'Anonymous Student'); ?>
                                    <?php if($isOwn): ?><span style="background: #003366; color: white; font-size: 0.62rem; padding: 2px 8px; border-radius: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px;">You</span><?php endif; ?>
                                    <?php if(!$t['is_approved'] && $role=='admin'): ?><span style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 0.62rem; padding: 1px 7px; border-radius: 10px; font-weight: 700; text-transform: uppercase; display: inline-flex; align-items: center; gap: 3px;"><i class="fas fa-clock"></i> Pending review</span><?php endif; ?>
                                </div>
                                <div class="dm-meta" style="font-size: 0.78rem; color: #64748b; font-weight: 500; margin-top: 1px;"><?php echo htmlspecialchars($t['course'] ?? 'General Course'); ?><?php echo $t['course_level'] ? ' · Year Level '.$t['course_level'] : ''; ?></div>
                            </div>
                            <div style="text-align: right; flex-shrink: 0;">
                                <div style="color: #FFD700; font-size: 1rem; letter-spacing: 1px;"><?php for($s=1; $s<=5; $s++) echo $s<=$t['rating'] ? '★' : '☆'; ?></div>
                                <div class="dm-meta" style="font-size: 0.72rem; color: #94a3b8; font-weight: 500; margin-top: 2px;"><?php echo date('M d, Y', strtotime($t['created_at'])); ?></div>
                            </div>
                        </div>
                        
                        <p style="margin: 0; color: #334155; font-size: 0.9rem; line-height: 1.6; font-weight: 400;"><?php echo nl2br(htmlspecialchars($t['message'])); ?></p>
                    </div>
                    <?php endwhile; else: ?>
                    <div style="text-align: center; padding: 50px 20px; color: #94a3b8; border: 1px dashed #cbd5e1; border-radius: 8px;">
                        <i class="fas fa-comment-slash" style="font-size: 2.5rem; display: block; margin-bottom: 12px; color: #cbd5e1;"></i>
                        No student submission feedback entries found.
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </section>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const stars    = document.querySelectorAll('.star-btn');
    const ratingIn = document.getElementById('ratingVal');
    let current    = parseInt(ratingIn.value) || 5;

    function paintStars(n) {
        stars.forEach((s, i) => {
            s.style.color = i < n ? '#FFD700' : '#cbd5e1';
        });
    }
    
    // Initial UI Setup Execution Render
    paintStars(current);

    stars.forEach(s => {
        s.addEventListener('mouseover', () => paintStars(parseInt(s.dataset.val)));
        s.addEventListener('mouseout',  () => paintStars(current));
        s.addEventListener('click', () => {
            current = parseInt(s.dataset.val);
            ratingIn.value = current;
            paintStars(current);
        });
    });
});
</script>

</body>
</html>