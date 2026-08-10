<?php
session_start();
include '../includes/db_connect.php';

// Language Handling
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$curr_lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';

$lang_file = "../lang/$curr_lang.php";
if (file_exists($lang_file)) {
    include $lang_file;
} else {
    $lang = [
        'title' => 'BOQ System',
        'search' => 'Search',
        'delayed' => 'Delayed',
        'on_track' => 'On Track',
        'phy_progress' => 'Physical Progress',
        'view_details' => 'View Details',
        'contractor' => 'Contractor'
    ];
}

// Filter Handling
$district_filter = isset($_GET['district']) ? trim($_GET['district']) : '';
$ds_filter = isset($_GET['ds_division']) ? trim($_GET['ds_division']) : '';
$office_filter = isset($_GET['office']) ? trim($_GET['office']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination setup
$limit = 9;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Base WHERE conditions (Using Prepared Statements logic or safe escaping)
$where_conditions = "p.approval_status = 'approved'";

if ($district_filter !== '') {
    $district_Esc = $conn->real_escape_string($district_filter);
    $where_conditions .= " AND p.district = '$district_Esc'";
}
if ($ds_filter !== '') {
    $ds_esc = $conn->real_escape_string($ds_filter);
    $where_conditions .= " AND p.ds_division = '$ds_esc'";
}
if ($office_filter !== '') {
    $office_esc = $conn->real_escape_string($office_filter);
    $where_conditions .= " AND p.office_name = '$office_esc'";
}
if ($status_filter !== '') {
    if ($status_filter == 'Delayed') {
        $where_conditions .= " AND p.delay_status = 'Delayed'";
    } elseif ($status_filter == 'Completed') {
        $where_conditions .= " AND p.physical_progress = 100";
    } elseif ($status_filter == 'Ongoing') {
        $where_conditions .= " AND p.physical_progress < 100 AND p.delay_status != 'Delayed'";
    }
}
if ($search !== '') {
    $search_esc = $conn->real_escape_string($search);
    $where_conditions .= " AND (p.project_name LIKE '%$search_esc%' OR p.contractor_name LIKE '%$search_esc%')";
}

// Get total rows for pagination safely
$total_rows = 0;
$count_sql = "SELECT COUNT(p.id) as total FROM projects p WHERE $where_conditions";
$count_result = $conn->query($count_sql);
if ($count_result && $row_cnt = $count_result->fetch_assoc()) {
    $total_rows = (int)$row_cnt['total'];
}
$total_pages = ($total_rows > 0) ? ceil($total_rows / $limit) : 1;
if ($page > $total_pages) $page = $total_pages;

// Build Query with LIMIT and OFFSET

// Build Query safely without complex subquery joins that might hide projects
$sql = "SELECT p.*, 
        (SELECT type_name FROM project_types WHERE id = p.project_type_id LIMIT 1) as type_name,
        (SELECT photo_path FROM project_photos WHERE project_id = p.id LIMIT 1) as photo_path
        FROM projects p 
        WHERE $where_conditions
        ORDER BY p.id DESC
        LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);




// Get Districts and Offices for Filters safely
$districts = $conn->query("SELECT DISTINCT district FROM projects WHERE approval_status = 'approved'");
$offices = $conn->query("SELECT DISTINCT office_name FROM projects WHERE approval_status = 'approved'");
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($curr_lang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($lang['title']) ? $lang['title'] : 'BOQ System'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Sinhala:wght@400;700&family=Noto+Sans+Tamil:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', 'Noto Sans Sinhala', 'Noto Sans Tamil', sans-serif; background: #f0f2f5; min-height: 100vh; display: flex; flex-direction: column; }
        .project-card { transition: transform 0.2s; border: none; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .project-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><?php echo isset($lang['title']) ? $lang['title'] : 'BOQ System'; ?></a>
        <div class="d-flex">
            <a href="?lang=en" class="btn btn-sm btn-outline-light me-2 <?php echo $curr_lang=='en'?'active':''; ?>">English</a>
            <a href="?lang=si" class="btn btn-sm btn-outline-light me-2 <?php echo $curr_lang=='si'?'active':''; ?>">සිංහල</a>
            <a href="?lang=ta" class="btn btn-sm btn-outline-light <?php echo $curr_lang=='ta'?'active':''; ?>">தமிழ்</a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <!-- Search & Filter -->
    <div class="card p-4 mb-4">
        <form class="row g-3" id="filterForm" method="GET" action="index.php">
            <input type="hidden" name="lang" value="<?php echo htmlspecialchars($curr_lang); ?>">
            
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted"><?php echo isset($lang['search']) ? $lang['search'] : 'Search'; ?></label>
                <input type="text" name="search" class="form-control" placeholder="Project or Contractor" value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">Office</label>
                <select name="office" class="form-select">
                    <option value="">All Offices</option>
                    <?php if ($offices && $offices->num_rows > 0): ?>
                        <?php while($o = $offices->fetch_assoc()): ?>
                            <?php $sel = ($office_filter == $o['office_name']) ? 'selected' : ''; ?>
                            <option value="<?php echo htmlspecialchars($o['office_name']); ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($o['office_name']); ?></option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">District</label>
                <select name="district" id="district" class="form-select">
                    <option value="">All Districts</option>
                    <?php if ($districts && $districts->num_rows > 0): ?>
                        <?php while($d = $districts->fetch_assoc()): ?>
                            <?php $sel = ($district_filter == $d['district']) ? 'selected' : ''; ?>
                            <option value="<?php echo htmlspecialchars($d['district']); ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($d['district']); ?></option>
                        <?php endwhile; ?>
                    <?php endif; ?>
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
                    <option value="Ongoing" <?php if($status_filter == 'Ongoing') echo 'selected'; ?>>Ongoing</option>
                    <option value="Completed" <?php if($status_filter == 'Completed') echo 'selected'; ?>>Completed</option>
                    <option value="Delayed" <?php if($status_filter == 'Delayed') echo 'selected'; ?>>Delayed</option>
                </select>
            </div>

            <div class="col-12 text-end">
                <a href="index.php" class="btn btn-outline-secondary me-2">Reset</a>
                <button type="submit" class="btn btn-primary px-4">Filter Projects</button>
            </div>
        </form>
    </div>

    <input type="hidden" id="selected_ds" value="<?php echo htmlspecialchars($ds_filter); ?>">

    <!-- Project Grid -->
    <div class="row">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card project-card h-100">
                        <?php 
                        $thumb = "https://via.placeholder.com/400x200?text=No+Image";
                        $pid = (int)$row['id'];
                        $p_res = $conn->query("SELECT photo_path FROM project_photos WHERE project_id=$pid LIMIT 1");
                        if ($p_res && $p_res->num_rows > 0) {
                            $p_row = $p_res->fetch_assoc();
                            $thumb = str_replace("../../", "../", $p_row['photo_path']); 
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($thumb); ?>" class="card-img-top" alt="Project Image" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <span class="badge bg-secondary mb-2"><?php echo htmlspecialchars($row['type_name']); ?></span>
                            <?php if($row['delay_status'] == 'Delayed'): ?>
                                <span class="badge bg-danger mb-2"><?php echo isset($lang['delayed']) ? $lang['delayed'] : 'Delayed'; ?></span>
                            <?php else: ?>
                                <span class="badge bg-success mb-2"><?php echo isset($lang['on_track']) ? $lang['on_track'] : 'On Track'; ?></span>
                            <?php endif; ?>
                            
                            <h5 class="card-title"><?php echo htmlspecialchars($row['project_name']); ?></h5>
                            <p class="card-text text-muted small">
                                📍 <?php echo htmlspecialchars($row['district']); ?> > <?php echo htmlspecialchars($row['ds_division']); ?>
                            </p>
                            
                            <div class="mt-3">
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span><?php echo isset($lang['phy_progress']) ? $lang['phy_progress'] : 'Progress'; ?></span>
                                        <span><?php echo (int)$row['physical_progress']; ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo (int)$row['physical_progress']; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                           <button class="btn btn-outline-primary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#details-<?php echo $pid; ?>">
                                <?php echo isset($lang['view_details']) ? $lang['view_details'] : 'View Details'; ?>
                            </button>
                           <div class="collapse mt-2" id="details-<?php echo $pid; ?>">
                                <ul class="list-group list-group-flush small">
                                    <li class="list-group-item"><strong><?php echo isset($lang['contractor']) ? $lang['contractor'] : 'Contractor'; ?>:</strong> <?php echo htmlspecialchars($row['contractor_name']); ?></li>
                                    <li class="list-group-item"><strong>Contract Amount:</strong> Rs. <?php echo number_format($row['contract_amount'], 2); ?></li>
                                    <li class="list-group-item"><strong>Office:</strong> <?php echo htmlspecialchars($row['office_name']); ?></li>
                                    <li class="list-group-item"><strong>Start:</strong> <?php echo htmlspecialchars($row['start_date']); ?></li>
                                    <li class="list-group-item"><strong>End:</strong> <?php echo htmlspecialchars($row['completion_date']); ?></li>
                                    <?php if(!empty($row['delay_reason'])): ?>
                                        <li class="list-group-item text-danger"><strong>Delay:</strong> <?php echo htmlspecialchars($row['delay_reason']); ?></li>
                                    <?php endif; ?>
                                </ul>
                                <?php
                                $photos_res = $conn->query("SELECT photo_path FROM project_photos WHERE project_id=$pid ORDER BY uploaded_at DESC");
                                if ($photos_res && $photos_res->num_rows > 0):
                                ?>
                                <div class="px-3 pb-3">
                                    <h6 class="text-muted small mt-2 mb-2">Project Photos</h6>
                                    <div class="row g-2">
                                        <?php while($photo_row = $photos_res->fetch_assoc()): 
                                            $photo_url = str_replace("../../", "../", $photo_row['photo_path']);
                                        ?>
                                        <div class="col-4 col-sm-3">
                                            <img src="<?php echo htmlspecialchars($photo_url); ?>" class="img-fluid rounded shadow-sm" onclick="openLightbox('<?php echo htmlspecialchars($photo_url); ?>')" style="height: 60px; object-fit: cover; width: 100%; cursor: pointer;">
                                        </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted">No projects found matching your criteria.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination Controls -->
    <?php if ($total_pages > 1): 
        $query_string = $_GET;
        unset($query_string['page']);
        $qs = http_build_query($query_string);
        $base_url = $qs ? '?' . $qs . '&' : '?';
    ?>
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo $base_url . 'page=' . ($page - 1); ?>">Previous</a>
            </li>
            
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo $base_url . 'page=' . $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            
            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo $base_url . 'page=' . ($page + 1); ?>">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<!-- Lightbox Modal -->
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-transparent border-0">
      <div class="modal-header border-0 justify-content-end p-2">
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
      </div>
      <div class="modal-body p-0 text-center">
        <img id="lightboxImage" src="" class="img-fluid rounded shadow-lg" style="max-height: 80vh; max-width: 100%;">
      </div>
    </div>
  </div>
</div>

<footer class="bg-light text-center text-muted py-3 mt-auto border-top">
    <div class="container">
        <small>Developed by Digital Division of Chief Secretary Office (NWP)</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openLightbox(url) {
        document.getElementById('lightboxImage').src = url;
        var myModal = new bootstrap.Modal(document.getElementById('lightboxModal'));
        myModal.show();
    }

    const districtSelect = document.getElementById('district');
    const dsSelect = document.getElementById('ds_division');
    const selectedDS = document.getElementById('selected_ds').value;

    function loadDSDivisions(district, selected = '') {
        dsSelect.innerHTML = '<option value="">Loading...</option>';
        if(!district) {
            dsSelect.innerHTML = '<option value="">All Divisions</option>';
            return;
        }

        fetch('../api/get_locations.php?type=ds&district=' + encodeURIComponent(district))
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
            })
            .catch(error => {
                dsSelect.innerHTML = '<option value="">Error loading</option>';
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
