<?php
session_start();
include '../includes/db_connect.php';

// Language Handling
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$curr_lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
include "../lang/$curr_lang.php";

// Filter Handling
$district_filter = isset($_GET['district']) ? $_GET['district'] : '';
$ds_filter = isset($_GET['ds_division']) ? $_GET['ds_division'] : '';
$office_filter = isset($_GET['office']) ? $_GET['office'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build Query
$sql = "SELECT p.*, t.type_name 
        FROM projects p 
        LEFT JOIN project_types t ON p.project_type_id = t.id 
        WHERE p.approval_status = 'approved' AND p.is_active = 1";

if ($district_filter) {
    $sql .= " AND p.district = '$district_filter'";
}
if ($ds_filter) {
    $sql .= " AND p.ds_division = '$ds_filter'";
}
if ($office_filter) {
    $sql .= " AND p.office_name = '$office_filter'";
}
if ($status_filter) {
    if ($status_filter == 'Delayed') {
        $sql .= " AND p.delay_status = 'Delayed'";
    } elseif ($status_filter == 'Completed') {
        $sql .= " AND p.physical_progress = 100";
    } elseif ($status_filter == 'Ongoing') {
        $sql .= " AND p.physical_progress < 100 AND p.delay_status != 'Delayed'";
    }
}
if ($search) {
    $sql .= " AND (p.project_name LIKE '%$search%' OR p.contractor_name LIKE '%$search%')";
}
$sql .= " ORDER BY p.id DESC";

$result = $conn->query($sql);

// Check if query failed
if (!$result) {
    die("SQL Error: " . $conn->error);
}

// Get Data for Filters
$districts = $conn->query("SELECT DISTINCT district FROM projects WHERE approval_status = 'approved' AND district IS NOT NULL");
$offices = $conn->query("SELECT DISTINCT office_name FROM projects WHERE approval_status = 'approved' AND office_name IS NOT NULL");
?>

<!DOCTYPE html>
<html lang="<?php echo $curr_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['title']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Sinhala:wght@400;700&family=Noto+Sans+Tamil:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', 'Noto Sans Sinhala', 'Noto Sans Tamil', sans-serif; background: #f0f2f5; min-height: 100vh; display: flex; flex-direction: column; }
        .project-card { transition: transform 0.2s; border: none; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .project-card:hover { transform: translateY(-5px); }
        .navbar-brand { font-size: 1.5rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><?php echo $lang['title']; ?></a>
        <div class="d-flex">
            <a href="?lang=en" class="btn btn-sm btn-outline-light me-2 <?php echo $curr_lang=='en'?'active':''; ?>">English</a>
            <a href="?lang=si" class="btn btn-sm btn-outline-light me-2 <?php echo $curr_lang=='si'?'active':''; ?>">සිංහල</a>
            <a href="?lang=ta" class="btn btn-sm btn-outline-light <?php echo $curr_lang=='ta'?'active':''; ?>">தமிழ்</a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="card p-4 mb-4 shadow-sm border-0">
        <form class="row g-3" id="filterForm" method="GET">
            <input type="hidden" name="lang" value="<?php echo $curr_lang; ?>">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted"><?php echo $lang['search']; ?></label>
                <input type="text" name="search" class="form-control" placeholder="Project or Contractor" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">Office</label>
                <select name="office" class="form-select">
                    <option value="">All Offices</option>
                    <?php while($o = $offices->fetch_assoc()): ?>
                        <option value="<?php echo $o['office_name']; ?>" <?php echo ($office_filter == $o['office_name']) ? 'selected' : ''; ?>>
                            <?php echo $o['office_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">District</label>
                <select name="district" id="district" class="form-select">
                    <option value="">All Districts</option>
                    <?php while($d = $districts->fetch_assoc()): ?>
                        <option value="<?php echo $d['district']; ?>" <?php echo ($district_filter == $d['district']) ? 'selected' : ''; ?>>
                            <?php echo $d['district']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">DS Division</label>
                <select name="ds_division" id="ds_division" class="form-select">
                    <option value="">All Divisions</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Ongoing" <?php echo ($status_filter == 'Ongoing') ? 'selected' : ''; ?>>Ongoing</option>
                    <option value="Completed" <?php echo ($status_filter == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                    <option value="Delayed" <?php echo ($status_filter == 'Delayed') ? 'selected' : ''; ?>>Delayed</option>
                </select>
            </div>
            <div class="col-12 text-end">
                <a href="index.php" class="btn btn-outline-secondary me-2">Reset</a>
                <button type="submit" class="btn btn-primary px-4">Filter Projects</button>
            </div>
        </form>
    </div>

    <input type="hidden" id="selected_ds" value="<?php echo $ds_filter; ?>">

    <div class="row">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card project-card h-100">
                        <?php 
                        $thumb = "https://via.placeholder.com/400x200?text=No+Image";
                        $pid = $row['id'];
                        $p_res = $conn->query("SELECT photo_path FROM project_photos WHERE project_id=$pid LIMIT 1");
                        if ($p_res && $p_res->num_rows > 0) {
                            $p_row = $p_res->fetch_assoc();
                            $thumb = str_replace("../../", "../", $p_row['photo_path']); 
                        }
                        ?>
                        <img src="<?php echo $thumb; ?>" class="card-img-top" alt="Project Image" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <span class="badge bg-secondary mb-2"><?php echo $row['type_name']; ?></span>
                            <?php if($row['delay_status'] == 'Delayed'): ?>
                                <span class="badge bg-danger mb-2"><?php echo $lang['delayed']; ?></span>
                            <?php else: ?>
                                <span class="badge bg-success mb-2"><?php echo $lang['on_track']; ?></span>
                            <?php endif; ?>
                            
                            <h5 class="card-title"><?php echo $row['project_name']; ?></h5>
                            <p class="card-text text-muted small">
                                <i class="bi bi-geo-alt"></i> <?php echo $row['district']; ?> | <?php echo $row['ds_division']; ?>
                            </p>
                            
                            <div class="mt-3">
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span><?php echo $lang['phy_progress']; ?></span>
                                        <span><?php echo $row['physical_progress']; ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $row['physical_progress']; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 p-3">
                             <button class="btn btn-outline-primary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#details-<?php echo $pid; ?>">
                                <?php echo $lang['view_details']; ?>
                            </button>
                             <div class="collapse mt-2" id="details-<?php echo $pid; ?>">
                                <ul class="list-group list-group-flush small border rounded">
                                    <li class="list-group-item"><strong>Contractor:</strong> <?php echo $row['contractor_name']; ?></li>
                                    <li class="list-group-item"><strong>Amount:</strong> Rs. <?php echo number_format($row['contract_amount'], 2); ?></li>
                                    <li class="list-group-item"><strong>Office:</strong> <?php echo $row['office_name']; ?></li>
                                    <li class="list-group-item"><strong>Start:</strong> <?php echo $row['start_date']; ?></li>
                                    <li class="list-group-item"><strong>End:</strong> <?php echo $row['completion_date']; ?></li>
                                    <?php if($row['delay_reason']): ?>
                                        <li class="list-group-item text-danger"><strong>Delay:</strong> <?php echo $row['delay_reason']; ?></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-search" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">No projects found matching your criteria.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer class="bg-white text-center text-muted py-4 mt-auto border-top">
    <div class="container">
        <small>Developed by Digital Division | CS Office (NWP)</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const districtSelect = document.getElementById('district');
    const dsSelect = document.getElementById('ds_division');
    const selectedDS = document.getElementById('selected_ds').value;

    function loadDSDivisions(district, selected = '') {
        dsSelect.innerHTML = '<option value="">Loading...</option>';
        if(!district) {
            dsSelect.innerHTML = '<option value="">All Divisions</option>';
            return;
        }

        fetch('../api/get_locations.php?type=ds&district=' + district)
            .then(response => response.json())
            .then(data => {
                dsSelect.innerHTML = '<option value="">All Divisions</option>';
                data.forEach(d => {
                    const option = document.createElement('option');
                    option.value = d;
                    option.textContent = d;
                    if (d === selected) option.selected = true;
                    dsSelect.appendChild(option);
                });
            });
    }

    districtSelect.addEventListener('change', function() {
        loadDSDivisions(this.value);
    });

    if (districtSelect.value) {
        loadDSDivisions(districtSelect.value, selectedDS);
    }
</script>
</body>
</html>
