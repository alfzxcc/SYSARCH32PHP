<?php 
include 'admin_check.php'; 
require_once 'db_connect.php'; 

// 1. Fetch Real Statistics from Database
// Count Total Students (where role is 0)
$studentQuery = "SELECT COUNT(*) as total FROM users WHERE role = 0";
$sResult = $conn->query($studentQuery);
$sData = $sResult->fetch_assoc();
$totalStudents = $sData['total'];

// Fetch Recent Announcements to display in the table
$announcementQuery = "SELECT * FROM announcements ORDER BY date_posted DESC LIMIT 5";
$announcements = $conn->query($announcementQuery);

include 'admin_header.php'; 
?>

<div class="admin-container">
    <h2 class="section-title"><i class="fas fa-chart-line"></i> System Overview</h2>
    <section class="stats-overview">
        <div class="stat-box blue">
            <i class="fas fa-users"></i>
            <div class="stat-text">
                <h3><?php echo $totalStudents; ?></h3>
                <p>Registered Students</p>
            </div>
        </div>
        
        <div class="stat-box green">
            <i class="fas fa-desktop"></i>
            <div class="stat-text">
                <h3>0</h3> <p>Active Sit-ins</p>
            </div>
        </div>

        <div class="stat-box gold">
            <i class="fas fa-clock"></i>
            <div class="stat-text">
                <h3>30</h3>
                <p>Avg. Hours Left</p>
            </div>
        </div>
    </section>

    <div class="admin-grid" style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 20px;">
        
        <div class="admin-card">
            <h3><i class="fas fa-plus-circle"></i> Create Announcement</h3>
            <form action="post_announcement.php" method="POST" class="announcement-form">
                <label>Title</label>
                <input type="text" name="title" placeholder="e.g. Lab Maintenance" required>
                
                <label>Category</label>
                <select name="category">
                    <option value="System Update">System Update</option>
                    <option value="Maintenance">Maintenance</option>
                    <option value="Event">Event</option>
                </select>
                
                <label>Details</label>
                <textarea name="content" rows="4" placeholder="Enter message for students..." required></textarea>
                
                <button type="submit" class="btn-post">Publish Now</button>
            </form>
        </div>

        <div class="admin-card">
            <h3><i class="fas fa-history"></i> Recent Posts</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($announcements->num_rows > 0): ?>
                        <?php while($row = $announcements->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d', strtotime($row['date_posted'])); ?></td>
                                <td><?php echo $row['title']; ?></td>
                                <td><span class="tag info"><?php echo $row['category']; ?></span></td>
                                <td>
                                    <a href="delete_announcement.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Delete this post?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center;">No announcements found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>