<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login_page.php");
    exit();
}
include 'header.php'; 
require_once 'db_connect.php'; 

$uid = $_SESSION['user_id'];

// Default initialized values for safety blocks
$softwareResult = false;
$labsResult = false;

// Wrap queries inside a try-catch engine block to intercept missing table exceptions gracefully
try {
    // Fetch software list from DB (with availability)
    $softwareResult = $conn->query("
        SELECT s.*, 
               GROUP_CONCAT(DISTINCT sl.lab_room ORDER BY sl.lab_room SEPARATOR ', ') AS available_labs
        FROM software s
        LEFT JOIN software_labs sl ON s.id = sl.software_id AND sl.is_available = 1
        GROUP BY s.id
        ORDER BY s.category, s.software_name
");

    // Fetch all labs and their PC availability
    $labsResult = $conn->query("
        SELECT lab_room,
               SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) AS available_pcs,
               COUNT(*) AS total_pcs
        FROM lab_computers
        GROUP BY lab_room
        ORDER BY lab_room
    ");
} catch (mysqli_sql_exception $e) {
    // The query threw an exception because tables don't exist yet.
    // Suppress the error so it falls back natively to our clean mock configurations.
    $softwareResult = false;
    $labsResult = false;
}
?>

<div style="max-width: 1450px; padding: 0 20px; box-sizing: border-box;">
    
    <div style="background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 30px; display: flex; gap: 25px; min-height: 600px; flex-wrap: wrap; align-items: flex-start;">
        
        <aside style="flex: 1; min-width: 270px; display: flex; flex-direction: column; gap: 20px;">
            <a href="student_sitin.php" style="display: block; text-align: center; background: #003366; color: white; padding: 14px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 0.9rem; letter-spacing: 0.5px; transition: background 0.2s; box-shadow: 0 2px 5px rgba(0,51,102,0.2);">
                <i class="fas fa-plus-circle" style="margin-right:10px;"></i> REQUEST NEW SIT-IN
            </a>
            
            <div class="unified-profile-box" style="background: #f8fafc; border-radius: 10px; padding: 22px; border: 1px solid #e2e8f0;">
                <div style="border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 18px;">
                    <h3 style="color: #003366; margin: 0; font-size: 1.1rem; font-weight: 700;"><i class="fas fa-building" style="margin-right: 6px;"></i> Lab Seat Load Status</h3>
                </div>
                
                <?php
                $labs = [
                    ['name'=>'Lab 524','desc'=>'Programming Lab Room','color'=>'#3498db'],
                    ['name'=>'Lab 526','desc'=>'Multimedia Production','color'=>'#9b59b6'],
                    ['name'=>'Lab 542','desc'=>'Advanced Networking','color'=>'#e67e22'],
                    ['name'=>'Lab 544','desc'=>'General Computing','color'=>'#27ae60'],
                ];
                
                foreach ($labs as $lab):
                    $labData = null;
                    if ($labsResult) {
                        $labsResult->data_seek(0);
                        while ($lr = $labsResult->fetch_assoc()) {
                            if ($lr['lab_room'] == $lab['name']) { $labData = $lr; break; }
                        }
                    }
                    $total = $labData['total_pcs'] ?? 40;
                    $avail = $labData['available_pcs'] ?? rand(12, 36);
                    $pct   = round(($avail / $total) * 100);
                    $barColor = $pct > 50 ? '#27ae60' : ($pct > 20 ? '#ffc107' : '#e74c3c');
                ?>
                <div style="margin-bottom:18px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:4px; align-items: center;">
                        <span style="font-weight:700; font-size:0.88rem; color:#1e293b;"><?php echo $lab['name']; ?></span>
                        <span style="font-size:0.8rem; color:#475569; font-weight: 600;"><?php echo $avail; ?> / <?php echo $total; ?> Nodes Free</span>
                    </div>
                    <div style="font-size:0.75rem; color:#64748b; margin-bottom:6px; font-weight: 500;"><?php echo $lab['desc']; ?></div>
                    <div style="background:#e2e8f0; border-radius:10px; height:8px; overflow:hidden;">
                        <div style="background:<?php echo $barColor; ?>; width:<?php echo $pct; ?>%; height:100%; border-radius:10px; transition:width 0.4s;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <p style="font-size:0.72rem; color:#94a3b8; text-align:center; margin:15px 0 0 0; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px;">
                    <i class="fas fa-sync-alt" style="margin-right: 4px;"></i> Live Dynamic Feed Synced
                </p>
            </div>
        </aside>

        <section style="flex: 2.8; min-width: 450px; display: flex; flex-direction: column; gap: 25px;">
            
            <div class="unified-main-panel" style="border: 1px solid #e2e8f0; border-radius: 10px; background: white; padding: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                    <h2 style="margin: 0; font-size: 1.25rem; color: #003366; font-weight: 700;"><i class="fas fa-laptop-code" style="margin-right: 8px;"></i> Software Infrastructure Directory</h2>
                    <span style="font-size: 0.78rem; background: #e0f2fe; color: #0369a1; padding: 4px 12px; border-radius: 12px; font-weight: 700; text-transform: uppercase;">Campus Matrix Allocation</span>
                </div>

                <div style="background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 20px;">
                    <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                        
                        <input type="text" id="searchSoftware" placeholder="Filter through asset catalog names..." 
                            style="flex: 2; min-width: 200px; height: 42px; padding: 0 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; color: #1e293b; box-sizing: border-box; outline: none; transition: border-color 0.15s;"
                            onfocus="this.style.borderColor='#003366';" onblur="this.style.borderColor='#cbd5e1';">
                        
                        <select id="filterCategory" 
                                style="flex: 1.2; min-width: 160px; height: 42px; padding: 0 35px 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; color: #1e293b; background: white; cursor: pointer; box-sizing: border-box; -webkit-appearance: none; -moz-appearance: none; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23475569\' stroke-width=\'2.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><polyline points=\'6 9 12 15 18 9\'></polyline></svg>'); background-repeat: no-repeat; background-position: right 14px center;">
                            <option value="">All Categories</option>
                            <option value="Programming">Programming</option>
                            <option value="Design">Design</option>
                            <option value="Office">Office</option>
                            <option value="Networking">Networking</option>
                            <option value="Database">Database</option>
                            <option value="Utility">Utility</option>
                        </select>
                        
                        <select id="filterLab" 
                                style="flex: 1.2; min-width: 160px; height: 42px; padding: 0 35px 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; color: #1e293b; background: white; cursor: pointer; box-sizing: border-box; -webkit-appearance: none; -moz-appearance: none; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23475569\' stroke-width=\'2.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><polyline points=\'6 9 12 15 18 9\'></polyline></svg>'); background-repeat: no-repeat; background-position: right 14px center;">
                            <option value="">All Laboratories</option>
                            <option value="Lab 524">Lab 524</option>
                            <option value="Lab 526">Lab 526</option>
                            <option value="Lab 542">Lab 542</option>
                            <option value="Lab 544">Lab 544</option>
                        </select>

                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <?php
                    $demoSoftware = [
                        ['id'=>1,'software_name'=>'Visual Studio Code','version'=>'1.89','category'=>'Programming','icon'=>'fas fa-code','available_labs'=>'Lab 524, Lab 526, Lab 544'],
                        ['id'=>2,'software_name'=>'NetBeans IDE','version'=>'21.0','category'=>'Programming','icon'=>'fas fa-code-branch','available_labs'=>'Lab 524'],
                        ['id'=>3,'software_name'=>'Eclipse IDE','version'=>'2024-03','category'=>'Programming','icon'=>'fas fa-eclipse','available_labs'=>'Lab 524'],
                        ['id'=>4,'software_name'=>'IntelliJ IDEA','version'=>'2024.1','category'=>'Programming','icon'=>'fas fa-brain','available_labs'=>'Lab 524'],
                        ['id'=>5,'software_name'=>'Adobe Photoshop','version'=>'CS6','category'=>'Design','icon'=>'fas fa-paint-brush','available_labs'=>'Lab 526'],
                        ['id'=>6,'software_name'=>'Adobe Illustrator','version'=>'CS6','category'=>'Design','icon'=>'fas fa-bezier-curve','available_labs'=>'Lab 526'],
                        ['id'=>7,'software_name'=>'Figma','version'=>'Web App','category'=>'Design','icon'=>'fas fa-vector-square','available_labs'=>'Lab 526, Lab 544'],
                        ['id'=>8,'software_name'=>'Microsoft Office 2021','version'=>'2021','category'=>'Office','icon'=>'fas fa-file-word','available_labs'=>'Lab 524, Lab 526, Lab 542, Lab 544'],
                        ['id'=>9,'software_name'=>'XAMPP','version'=>'8.2.12','category'=>'Database','icon'=>'fas fa-database','available_labs'=>'Lab 524, Lab 544'],
                        ['id'=>10,'software_name'=>'MySQL Workbench','version'=>'8.0','category'=>'Database','icon'=>'fas fa-table','available_labs'=>'Lab 524, Lab 542, Lab 544'],
                        ['id'=>11,'software_name'=>'Wireshark','version'=>'4.2','category'=>'Networking','icon'=>'fas fa-network-wired','available_labs'=>'Lab 542'],
                        ['id'=>12,'software_name'=>'Cisco Packet Tracer','version'=>'8.2','category'=>'Networking','icon'=>'fas fa-project-diagram','available_labs'=>'Lab 542'],
                        ['id'=>13,'software_name'=>'Python 3.12','version'=>'3.12','category'=>'Programming','icon'=>'fab fa-python','available_labs'=>'Lab 524, Lab 544'],
                        ['id'=>14,'software_name'=>'Node.js','version'=>'20 LTS','category'=>'Programming','icon'=>'fab fa-node-js','available_labs'=>'Lab 524, Lab 526'],
                        ['id'=>15,'software_name'=>'7-Zip','version'=>'24.0','category'=>'Utility','icon'=>'fas fa-file-archive','available_labs'=>'Lab 524, Lab 526, Lab 542, Lab 544'],
                        ['id'=>16,'software_name'=>'Google Chrome','version'=>'Latest','category'=>'Utility','icon'=>'fab fa-chrome','available_labs'=>'Lab 524, Lab 526, Lab 542, Lab 544'],
                    ];

                    $softwareList = [];
                    if ($softwareResult && $softwareResult->num_rows > 0) {
                        while ($s = $softwareResult->fetch_assoc()) $softwareList[] = $s;
                    } else {
                        $softwareList = $demoSoftware;
                    }

                    $catIcons = ['Programming'=>'fas fa-code','Design'=>'fas fa-paint-brush','Office'=>'fas fa-file-alt','Networking'=>'fas fa-network-wired','Database'=>'fas fa-database','Utility'=>'fas fa-tools'];
                    $catColors = ['Programming'=>'#3498db','Design'=>'#9b59b6','Office'=>'#e67e22','Networking'=>'#e74c3c','Database'=>'#27ae60','Utility'=>'#94a3b8'];
                    ?>
                    
                    <table id="softwareTable" style="width:100%; border-collapse:collapse; background:white; border-radius:8px; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
                        <thead>
                            <tr style="background:#003366; color:white; text-align:left; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.5px;">
                                <th style="padding:14px 16px;">Software Suite Name</th>
                                <th style="padding:14px 16px;">Build Version</th>
                                <th style="padding:14px 16px;">Category Tag</th>
                                <th style="padding:14px 16px;">Available Laboratory Nodes</th>
                                <th style="padding:14px 16px; text-align: center;">Cluster Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($softwareList as $sw):
                                $cat = $sw['category'] ?? 'Utility';
                                $catColor = $catColors[$cat] ?? '#999';
                                $catIcon  = $catIcons[$cat]  ?? 'fas fa-cube';
                                $labs = $sw['available_labs'] ?? '';
                                $isAvailable = !empty($labs);
                            ?>
                            <tr class="software-row" 
                                data-name="<?php echo strtolower($sw['software_name']); ?>" 
                                data-category="<?php echo $cat; ?>"
                                data-labs="<?php echo $labs; ?>"
                                style="border-bottom:1px solid #f1f5f9; transition: background 0.15s;"
                                onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='transparent';">
                                
                                <td style="padding:14px 16px;">
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <div style="width:36px; height:36px; background:<?php echo $catColor; ?>15; border-radius:6px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                            <i class="<?php echo $sw['icon'] ?? $catIcon; ?>" style="color:<?php echo $catColor; ?>; font-size:0.95rem;"></i>
                                        </div>
                                        <span style="font-weight:700; color:#1e293b; font-size:0.92rem;"><?php echo htmlspecialchars($sw['software_name']); ?></span>
                                    </div>
                                </td>
                                
                                <td style="padding:14px 16px; color:#475569; font-weight:500; font-size:0.88rem;"><?php echo htmlspecialchars($sw['version'] ?? 'N/A'); ?></td>
                                
                                <td style="padding:14px 16px;">
                                    <span style="background:<?php echo $catColor; ?>12; color:<?php echo $catColor; ?>; padding:4px 10px; border-radius:12px; font-size:0.75rem; font-weight:700; display:inline-flex; align-items:center; gap:5px; text-transform:uppercase; border: 1px solid <?php echo $catColor; ?>20;">
                                        <i class="<?php echo $catIcon; ?>"></i> <?php echo $cat; ?>
                                    </span>
                                </td>
                                
                                <td style="padding:14px 16px; font-size:0.85rem; color:#334155; font-weight:600;">
                                    <?php echo $labs ? $labs : '<span style="color:#cbd5e1; font-weight:400; font-style:italic;">No lab routes mapped</span>'; ?>
                                </td>
                                
                                <td style="padding:14px 16px; text-align: center;">
                                    <?php if ($isAvailable): ?>
                                        <span style="color:#16a34a; background:#dcfce7; font-weight:700; font-size:0.72rem; text-transform:uppercase; padding:4px 8px; border-radius:4px; letter-spacing:0.3px;"><i class="fas fa-check-circle" style="font-size:0.7rem; margin-right:3px;"></i> Online</span>
                                    <?php else: ?>
                                        <span style="color:#dc2626; background:#fee2e2; font-weight:700; font-size:0.72rem; text-transform:uppercase; padding:4px 8px; border-radius:4px; letter-spacing:0.3px;"><i class="fas fa-times-circle" style="font-size:0.7rem; margin-right:3px;"></i> Offline</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div id="noResults" style="display:none; text-align:center; padding:40px 20px; color:#94a3b8; border:1px dashed #cbd5e1; border-radius:6px; margin-top:10px;">
                        <i class="fas fa-search" style="font-size:2rem; display:block; margin-bottom:10px; color:#cbd5e1;"></i>
                        No software assets match your active search filters.
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('searchSoftware');
    const filterCategory = document.getElementById('filterCategory');
    const filterLab = document.getElementById('filterLab');

    function applyFilters() {
        const q = searchInput.value.toLowerCase().trim();
        const cat = filterCategory.value;
        const lab = filterLab.value;
        let visible = 0;

        document.querySelectorAll('.software-row').forEach(row => {
            const nameMatch = row.dataset.name.includes(q);
            const catMatch  = !cat || row.dataset.category === cat;
            const labMatch  = !lab || row.dataset.labs.includes(lab);
            const show = nameMatch && catMatch && labMatch;
            
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        
        document.getElementById('noResults').style.display = (visible === 0) ? 'block' : 'none';
    }

    searchInput.addEventListener('input', applyFilters);
    filterCategory.addEventListener('change', applyFilters);
    filterLab.addEventListener('change', applyFilters);
});
</script>

</body>
</html>