<?php 
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login_page.php"); exit(); }
include 'header.php'; 
require_once 'db_connect.php'; 

// FIXED LINE 9: Changed the query to filter by 'id_number' instead of the non-existent 'user_id'
$uid      = $_SESSION['user_id'];
$userData = $conn->query("SELECT * FROM users WHERE id_number = '$uid'")->fetch_assoc();

// Fetch Latest System Announcements
$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5");
?>

<div style="max-width: 1450px; padding: 0 20px; box-sizing: border-box;">
    
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
                    <span style="font-weight:600; color:#1e293b; font-size: 0.92rem; display:block; background:white; padding:8px 12px; border-radius:6px; border:1px solid #e2e8f0;"><?php
                        $fn = $userData['firstname'] ?? '';
                        $mn = !empty($userData['midname']) ? ' ' . $userData['midname'][0] . '. ' : ' ';
                        $ln = $userData['lastname'] ?? '';
                        echo htmlspecialchars($fn . $mn . $ln);
                    ?></span>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display:block; font-size:0.72rem; color:#64748b; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; margin-bottom:4px;"><i class="fas fa-graduation-cap" style="width: 18px; color:#003366;"></i> Course</label>
                    <span style="font-weight:600; color:#1e293b; font-size: 0.92rem; display:block; background:white; padding:8px 12px; border-radius:6px; border:1px solid #e2e8f0;"><?php echo htmlspecialchars($userData['course'] ?? 'N/A'); ?></span>
                </div>
                
                <div style="background: #fffbeb; padding: 12px 15px; border-radius: 8px; border-left: 4px solid #b45309; border: 1px solid #fef3c7; border-left: 4px solid #b45309; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                    <label style="display:block; font-size:0.72rem; color:#b45309; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;"><i class="fas fa-hourglass-half" style="width: 18px;"></i> Remaining Sessions</label>
                    <span style="font-size: 1.25rem; font-weight: 800; color: #b45309; display: block; margin-top: 2px; text-align: center;"><?php echo htmlspecialchars($userData['remaining_sessions'] ?? '0'); ?> Sessions Left</span>
                </div>
            </div>
        </aside>

        <section style="flex: 2; min-width: 400px; display: flex; flex-direction: column; gap: 20px;">
            <div style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; min-height: 520px; background: white;">
                <div style="background: #f8fafc; padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="margin: 0; font-size: 1.1rem; color: #003366; font-weight: 700;"><i class="fas fa-bullhorn" style="margin-right: 8px;"></i> Announcements</h2>
                    <span style="font-size: 0.78rem; background: #e0f2fe; color: #0369a1; padding: 3px 10px; border-radius: 12px; font-weight: 600; text-transform: uppercase;">Recent Updates</span>
                </div>

                <div style="padding: 20px; display: flex; flex-direction: column; gap: 16px;">
                    <?php if ($announcements && $announcements->num_rows > 0): ?>
                        <?php while($row = $announcements->fetch_assoc()): ?>
                            <div style="background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0; border-left: 4px solid #FFD700;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; flex-wrap: wrap; gap: 5px;">
                                    <strong style="color: #1e293b; font-size: 0.95rem;"><?php echo htmlspecialchars($row['title']); ?></strong>
                                    <small style="color: #94a3b8;"><i class="far fa-clock"></i> <?php echo date('M d, Y', strtotime($row['created_at'])); ?></small>
                                </div>
                                <p style="margin: 0; color: #475569; font-size: 0.88rem; line-height: 1.5;"><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0; border-left: 4px solid #FFD700;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <strong style="color: #1e293b; font-size: 0.95rem;">System Update</strong>
                                <small style="color: #94a3b8;"><i class="far fa-clock"></i> May 16, 2026</small>
                            </div>
                            <p style="margin: 0; color: #475569; font-size: 0.88rem; line-height: 1.5;">Welcome to the structural layout interface dashboard update system logs.</p>
                        </div>
                        <div style="background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0; border-left: 4px solid #003366;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <strong style="color: #1e293b; font-size: 0.95rem;">Maintenance Notice</strong>
                                <small style="color: #94a3b8;"><i class="far fa-clock"></i> Apr 17, 2026</small>
                            </div>
                            <p style="margin: 0; color: #475569; font-size: 0.88rem; line-height: 1.5;">Routine network interface maintenance scheduled over server stacks.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <aside style="flex: 1.1; min-width: 290px;">
            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px; text-align: center; background: #f8fafc;">
                <img src="uc2.jpg" alt="UC Logo" style="height: 60px; margin-bottom: 10px; object-fit: contain;" onerror="this.src='UC.jpg';">
                
                <h4 style="margin: 5px 0 2px 0; color: #003366; font-size: 0.85rem; letter-spacing: 0.3px; font-weight: 800; line-height: 1.3;">COLLEGE OF INFORMATION & COMPUTER STUDIES</h4>
                <div style="width: 40px; height: 3px; background: #FFD700; margin: 12px auto;"></div>
                <h5 style="margin: 0 0 20px 0; color: #b91c1c; font-size: 0.78rem; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">Laboratory Rules and Regulations</h5>
                
                <ul style="list-style: none; padding: 0; margin: 0; text-align: left; display: flex; flex-direction: column; gap: 12px;">
                    <li style="display: flex; gap: 12px; font-size: 0.84rem; color: #334155; line-height: 1.4;">
                        <span style="background: #FFD700; color: #003366; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.75rem; flex-shrink: 0;">1</span>
                        <span>Maintain silence and proper decorum.</span>
                    </li>
                    <li style="display: flex; gap: 12px; font-size: 0.84rem; color: #334155; line-height: 1.4;">
                        <span style="background: #FFD700; color: #003366; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.75rem; flex-shrink: 0;">2</span>
                        <span>Games are not allowed inside the lab.</span>
                    </li>
                    <li style="display: flex; gap: 12px; font-size: 0.84rem; color: #334155; line-height: 1.4;">
                        <span style="background: #FFD700; color: #003366; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.75rem; flex-shrink: 0;">3</span>
                        <span>Surfing is only allowed with permission.</span>
                    </li>
                    <li style="display: flex; gap: 12px; font-size: 0.84rem; color: #334155; line-height: 1.4;">
                        <span style="background: #FFD700; color: #003366; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.75rem; flex-shrink: 0;">4</span>
                        <span>Eating and drinking are strictly prohibited.</span>
                    </li>
                    <li style="display: flex; gap: 12px; font-size: 0.84rem; color: #334155; line-height: 1.4;">
                        <span style="background: #FFD700; color: #003366; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.75rem; flex-shrink: 0;">5</span>
                        <span>Keep workstations clean and orderly.</span>
                    </li>
                    <li style="display: flex; gap: 12px; font-size: 0.84rem; color: #334155; line-height: 1.4;">
                        <span style="background: #FFD700; color: #003366; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.75rem; flex-shrink: 0;">6</span>
                        <span>Damage to equipment is the student's responsibility.</span>
                    </li>
                </ul>
                <p style="margin: 20px 0 0; font-size: 0.75rem; font-style: italic; color: #94a3b8; border-top: 1px dashed #cbd5e1; padding-top: 15px;">Please observe these rules accordingly.</p>
            </div>
        </aside>

    </div>
</div>

</body>
</html>