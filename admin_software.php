<?php 
include 'admin_check.php'; 
require_once 'db_connect.php'; 
include 'admin_header.php';

$conn->set_charset("utf8mb4");

$successMsg = '';
$errorMsg   = '';

// Handle Add Software
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] == 'add') {
        $name    = $conn->real_escape_string(trim($_POST['software_name']));
        $version = $conn->real_escape_string(trim($_POST['version']));
        $cat     = $conn->real_escape_string($_POST['category']);
        $desc    = $conn->real_escape_string(trim($_POST['description'] ?? ''));
        $labs    = isset($_POST['labs']) ? array_map(fn($l) => $conn->real_escape_string($l), $_POST['labs']) : [];

        if ($name) {
            $check = $conn->query("SELECT id FROM software WHERE software_name = '$name' AND version = '$version'");
            if ($check && $check->num_rows > 0) {
                $errorMsg = "Software '$name $version' already exists.";
            } else {
                $conn->query("INSERT INTO software (software_name, version, category, description, created_at) VALUES ('$name','$version','$cat','$desc',NOW())");
                $swId = $conn->insert_id;
                foreach ($labs as $lab) {
                    $conn->query("INSERT INTO software_labs (software_id, lab_room, is_available) VALUES ($swId, '$lab', 1)");
                }
                $successMsg = "Software '$name' added successfully!";
            }
        }
    }

    if ($_POST['action'] == 'toggle') {
        $swId = (int)$_POST['sw_id'];
        $lab  = $conn->real_escape_string($_POST['lab_room']);
        $cur  = $conn->query("SELECT id, is_available FROM software_labs WHERE software_id=$swId AND lab_room='$lab'")->fetch_assoc();
        if ($cur) {
            $newVal = $cur['is_available'] ? 0 : 1;
            $conn->query("UPDATE software_labs SET is_available=$newVal WHERE id={$cur['id']}");
        } else {
            $conn->query("INSERT INTO software_labs (software_id, lab_room, is_available) VALUES ($swId,'$lab',1)");
        }
        header("Location: admin_software.php?updated=1");
        exit();
    }

    if ($_POST['action'] == 'delete') {
        $swId = (int)$_POST['sw_id'];
        $conn->query("DELETE FROM software_labs WHERE software_id=$swId");
        $conn->query("DELETE FROM software WHERE id=$swId");
        header("Location: admin_software.php?deleted=1");
        exit();
    }

    // CSV bulk import
    if ($_POST['action'] == 'import_csv' && isset($_FILES['csv_file'])) {
        $file = $_FILES['csv_file'];
        if ($file['error'] == 0 && pathinfo($file['name'], PATHINFO_EXTENSION) == 'csv') {
            $handle = fopen($file['tmp_name'], 'r');
            $header = fgetcsv($handle); // skip header
            $imported = 0; $skipped = 0;
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) >= 3) {
                    $n = $conn->real_escape_string(trim($row[0]));
                    $v = $conn->real_escape_string(trim($row[1]));
                    $c = $conn->real_escape_string(trim($row[2]));
                    $check = $conn->query("SELECT id FROM software WHERE software_name='$n' AND version='$v'");
                    if ($check && $check->num_rows == 0) {
                        $conn->query("INSERT INTO software (software_name, version, category, created_at) VALUES ('$n','$v','$c',NOW())");
                        $imported++;
                    } else { $skipped++; }
                }
            }
            fclose($handle);
            $successMsg = "CSV imported: $imported added, $skipped skipped (duplicates).";
        } else {
            $errorMsg = "Please upload a valid CSV file.";
        }
    }
}

// Handle delete via GET
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $swId = (int)$_GET['delete'];
    $conn->query("DELETE FROM software_labs WHERE software_id=$swId");
    $conn->query("DELETE FROM software WHERE id=$swId");
    header("Location: admin_software.php?deleted=1");
    exit();
}

// Fetch all software
$allLabs = ['Lab 524','Lab 526','Lab 542','Lab 544'];
$softwareResult = $conn->query("
    SELECT s.*, 
           GROUP_CONCAT(DISTINCT CASE WHEN sl.is_available=1 THEN sl.lab_room END ORDER BY sl.lab_room SEPARATOR ', ') AS available_labs
    FROM software s
    LEFT JOIN software_labs sl ON s.id = sl.software_id
    GROUP BY s.id
    ORDER BY s.category, s.software_name
");
?>

<div class="admin-container">
    <h2 class="section-title"><i class="fas fa-upload"></i> Software Management</h2>

    <?php if (isset($_GET['updated'])): ?>
    <div style="background:#d4edda;color:#155724;padding:12px 20px;border-radius:8px;margin-bottom:20px;border-left:4px solid #28a745;">
        <i class="fas fa-check-circle"></i> Availability updated.
    </div>
    <?php elseif (isset($_GET['deleted'])): ?>
    <div style="background:#f8d7da;color:#721c24;padding:12px 20px;border-radius:8px;margin-bottom:20px;border-left:4px solid #e74c3c;">
        <i class="fas fa-trash"></i> Software deleted.
    </div>
    <?php endif; ?>

    <?php if ($successMsg): ?>
    <div style="background:#d4edda;color:#155724;padding:12px 20px;border-radius:8px;margin-bottom:20px;border-left:4px solid #28a745;">
        <i class="fas fa-check-circle"></i> <?php echo $successMsg; ?>
    </div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
    <div style="background:#f8d7da;color:#721c24;padding:12px 20px;border-radius:8px;margin-bottom:20px;border-left:4px solid #e74c3c;">
        <i class="fas fa-exclamation-circle"></i> <?php echo $errorMsg; ?>
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:25px;">

        <!-- Add Software Form -->
        <div class="admin-card">
            <h3><i class="fas fa-plus-circle"></i> Add New Software</h3>
            <form method="POST" style="margin-top:15px;">
                <input type="hidden" name="action" value="add">
                <div style="margin-bottom:13px;">
                    <label style="font-size:0.85rem;color:#555;display:block;margin-bottom:4px;">Software Name *</label>
                    <input type="text" name="software_name" required placeholder="e.g. Visual Studio Code" style="width:100%;padding:9px 12px;border:1px solid #ddd;border-radius:7px;box-sizing:border-box;">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:13px;">
                    <div>
                        <label style="font-size:0.85rem;color:#555;display:block;margin-bottom:4px;">Version</label>
                        <input type="text" name="version" placeholder="e.g. 1.89" style="width:100%;padding:9px 12px;border:1px solid #ddd;border-radius:7px;box-sizing:border-box;">
                    </div>
                    <div style ="margin-top: 10px;">
                        <label style="font-size:0.85rem;color:#555;display:block;margin-bottom:4px;">Category</label>
                        <select name="category" style="width:100%;padding:9px;border:1px solid #ddd;border-radius:7px;">
                            <option value="Programming">Programming</option>
                            <option value="Design">Design</option>
                            <option value="Office">Office</option>
                            <option value="Database">Database</option>
                            <option value="Networking">Networking</option>
                            <option value="Utility">Utility</option>
                        </select>
                    </div>
                </div>
                <div style="margin-bottom:13px;">
                    <label style="font-size:0.85rem;color:#555;display:block;margin-bottom:4px;">Description</label>
                    <input type="text" name="description" placeholder="Brief description (optional)" style="width:100%;padding:9px 12px;border:1px solid #ddd;border-radius:7px;box-sizing:border-box;">
                </div>
                <div style="margin-bottom:15px;">
                    <label style="font-size:0.85rem;color:#555;display:block;margin-bottom:6px;">Available in Labs</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                        <?php foreach ($allLabs as $lab): ?>
                        <label style="display:flex;align-items:center;gap:7px;font-size:0.88rem;cursor:pointer;">
                            <input type="checkbox" name="labs[]" value="<?php echo $lab; ?>" style="width:auto;margin:0;"> <?php echo $lab; ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="submit" class="btn-post" style="width:100%;background:#003366;">
                    <i class="fas fa-plus"></i> Add Software
                </button>
            </form>
        </div>

        <!-- CSV Import -->
        <div class="admin-card">
            <h3><i class="fas fa-file-csv"></i> Bulk Import via CSV</h3>
            <p style="color:#666;font-size:0.88rem;margin-top:5px;">Upload a CSV file to add multiple software entries at once.</p>
            
            <div style="background:#f8f9fa;border:2px dashed #ddd;border-radius:10px;padding:25px;text-align:center;margin:15px 0;" 
                 id="dropZone" ondrop="handleDrop(event)" ondragover="event.preventDefault()">
                <i class="fas fa-file-upload" style="font-size:2.5rem;color:#ccc;display:block;margin-bottom:10px;"></i>
                <p style="color:#888;margin:0 0 12px;">Drag & drop CSV here or</p>
                <label style="background:#003366;color:white;padding:8px 18px;border-radius:7px;cursor:pointer;font-size:0.88rem;">
                    <i class="fas fa-folder-open"></i> Browse File
                    <input type="file" id="csvFile" accept=".csv" style="display:none;" onchange="previewCSV(this)">
                </label>
                <p id="fileName" style="color:#888;font-size:0.8rem;margin-top:10px;"></p>
            </div>

            <form method="POST" enctype="multipart/form-data" id="csvForm">
                <input type="hidden" name="action" value="import_csv">
                <input type="file" name="csv_file" id="hiddenCSV" accept=".csv" style="display:none;">
                <div id="csvPreview" style="display:none;max-height:150px;overflow-y:auto;border:1px solid #eee;border-radius:7px;margin-bottom:10px;font-size:0.8rem;"></div>
                <button type="submit" id="importBtn" class="btn-post" style="width:100%;background:#27ae60;display:none;">
                    <i class="fas fa-upload"></i> Import CSV
                </button>
            </form>

            <div style="margin-top:20px;padding:15px;background:#f0f4ff;border-radius:8px;font-size:0.82rem;color:#555;">
                <strong>CSV Format:</strong><br>
                <code style="font-size:0.78rem;">software_name, version, category</code><br><br>
                <strong>Example:</strong><br>
                <code style="font-size:0.78rem;">
                    Visual Studio Code, 1.89, Programming<br>
                    MySQL Workbench, 8.0, Database
                </code>
                <br><br>
                <a href="#" onclick="downloadTemplate()" style="color:#003366;font-size:0.82rem;">
                    <i class="fas fa-download"></i> Download Template
                </a>
            </div>
        </div>
    </div>

    <!-- Software Table -->
    <div class="admin-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;flex-wrap:wrap;gap:10px;">
            <h3 style="margin:0;"><i class="fas fa-list"></i> Installed Software</h3>
            <input type="text" id="searchSW" placeholder="Search software..." 
                   style="padding:8px 13px;border:1px solid #ddd;border-radius:7px;font-size:0.88rem;width:220px;">
        </div>
        <div style="overflow-x:auto;">
            <table class="admin-table" id="swTable">
                <thead>
                    <tr>
                        <th>Software Name</th>
                        <th>Version</th>
                        <th>Category</th>
                        <th>Lab 524</th>
                        <th>Lab 526</th>
                        <th>Lab 542</th>
                        <th>Lab 544</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $catColors = ['Programming'=>'#3498db','Design'=>'#9b59b6','Office'=>'#e67e22','Networking'=>'#e74c3c','Database'=>'#27ae60','Utility'=>'#95a5a6'];
                    if ($softwareResult && $softwareResult->num_rows > 0):
                        while ($sw = $softwareResult->fetch_assoc()):
                            $cc = $catColors[$sw['category']] ?? '#aaa';
                            // Get per-lab availability
                            $labAvail = [];
                            $labRes = $conn->query("SELECT lab_room, is_available FROM software_labs WHERE software_id={$sw['id']}");
                            if ($labRes) while ($la = $labRes->fetch_assoc()) $labAvail[$la['lab_room']] = $la['is_available'];
                    ?>
                    <tr class="sw-row" data-name="<?php echo strtolower($sw['software_name']); ?>">
                        <td><strong><?php echo htmlspecialchars($sw['software_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($sw['version'] ?? '—'); ?></td>
                        <td>
                            <span style="background:<?php echo $cc; ?>22;color:<?php echo $cc; ?>;padding:3px 9px;border-radius:10px;font-size:0.78rem;font-weight:600;">
                                <?php echo htmlspecialchars($sw['category']); ?>
                            </span>
                        </td>
                        <?php foreach ($allLabs as $lab):
                            $avail = $labAvail[$lab] ?? 0; ?>
                        <td style="text-align:center;">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="sw_id" value="<?php echo $sw['id']; ?>">
                                <input type="hidden" name="lab_room" value="<?php echo $lab; ?>">
                                <button type="submit" title="Toggle availability in <?php echo $lab; ?>" 
                                        style="background:none;border:none;cursor:pointer;font-size:1.1rem;padding:2px 5px;">
                                    <?php echo $avail ? '✅' : '❌'; ?>
                                </button>
                            </form>
                        </td>
                        <?php endforeach; ?>
                        <td>
                            <a href="?delete=<?php echo $sw['id']; ?>" onclick="return confirm('Delete <?php echo htmlspecialchars($sw['software_name']); ?>?')"
                               style="background:#e74c3c;color:white;padding:5px 10px;border-radius:5px;text-decoration:none;font-size:0.78rem;">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="8" style="text-align:center;padding:40px;color:#999;">
                        No software added yet. Use the form above to add software.
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Search filter
document.getElementById('searchSW').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.sw-row').forEach(row => {
        row.style.display = row.dataset.name.includes(q) ? '' : 'none';
    });
});

// CSV preview
function previewCSV(input) {
    if (!input.files.length) return;
    const file = input.files[0];
    document.getElementById('fileName').textContent = file.name;
    
    // Transfer to hidden form input
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById('hiddenCSV').files = dt.files;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const lines = e.target.result.split('\n').slice(0, 6);
        const preview = document.getElementById('csvPreview');
        preview.innerHTML = '<div style="padding:10px;"><strong style="color:#555;">Preview (first 5 rows):</strong><br><br>' +
            lines.map(l => '<div style="padding:3px 0;border-bottom:1px solid #f0f0f0;font-family:monospace;">' + l + '</div>').join('') +
            '</div>';
        preview.style.display = 'block';
        document.getElementById('importBtn').style.display = 'block';
    };
    reader.readAsText(file);
}

function handleDrop(e) {
    e.preventDefault();
    const file = e.dataTransfer.files[0];
    if (file && file.name.endsWith('.csv')) {
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('csvFile').files = dt.files;
        previewCSV(document.getElementById('csvFile'));
    }
}

function downloadTemplate() {
    const csv = 'software_name,version,category\nVisual Studio Code,1.89,Programming\nMySQL Workbench,8.0,Database\n';
    const blob = new Blob([csv], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'software_template.csv';
    a.click();
}
</script>
</body>
</html>
