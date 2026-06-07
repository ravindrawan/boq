<?php
include '../../includes/db_connect.php';
include '../../includes/functions.php';

// Ensure session is started
if(session_status() === PHP_SESSION_NONE) session_start();

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM projects WHERE id=$id");
    echo "<script>window.location='index.php';</script>";
    exit();
}

// Filter Handling
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$district_filter = isset($_GET['district']) ? $conn->real_escape_string($_GET['district']) : '';
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
$funding_filter = isset($_GET['funding_source']) ? $conn->real_escape_string($_GET['funding_source']) : '';

// Base WHERE clause
$where_conditions = "1=1";
if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
    $office = $conn->real_escape_string($_SESSION['office_name']);
    $where_conditions .= " AND p.office_name = '$office'";
}

if ($search) {
    $where_conditions .= " AND (p.project_name LIKE '%$search%' OR p.contractor_name LIKE '%$search%')";
}
if ($district_filter) {
    $where_conditions .= " AND p.district = '$district_filter'";
}
if ($funding_filter) {
    $where_conditions .= " AND p.funding_source_id = '$funding_filter'";
}
if ($status_filter) {
    if ($status_filter == 'Delayed') {
        $where_conditions .= " AND p.delay_status = 'Delayed'";
    } elseif ($status_filter == 'Completed') {
        $where_conditions .= " AND p.physical_progress = 100";
    } elseif ($status_filter == 'Ongoing') {
        $where_conditions .= " AND p.physical_progress < 100 AND p.delay_status != 'Delayed'";
    }
}

// Export Logic
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=projects_export_' . date('Ymd') . '.csv');
    $output = fopen('php://output', 'w');
    
    // Output UTF-8 BOM to ensure Excel reads Sinhala Unicode correctly
    fputs($output, "\xEF\xBB\xBF");
    
    fputcsv($output, array('ID', 'Project Name', 'Type', 'District', 'DS Division', 'GN Division', 'Contractor', 'Progress %', 'Status', 'Approval Status'));
    
    $export_sql = "SELECT p.*, t.type_name, c.grade_name
            FROM projects p 
            LEFT JOIN project_types t ON p.project_type_id = t.id 
            LEFT JOIN cida_grades c ON p.cida_grade_id = c.id
            WHERE $where_conditions
            ORDER BY p.id DESC";
            
    $exp_res = $conn->query($export_sql);
    while($row = $exp_res->fetch_assoc()){
        fputcsv($output, array(
            $row['id'], 
            $row['project_name'], 
            $row['type_name'], 
            $row['district'], 
            $row['ds_division'], 
            $row['gn_division'], 
            $row['contractor_name'], 
            $row['physical_progress'], 
            $row['delay_status'],
            $row['approval_status']
        ));
    }
    exit();
}

include '../../includes/header.php';
include '../../includes/navbar.php';

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$count_sql = "SELECT COUNT(p.id) as total FROM projects p WHERE $where_conditions";
$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

$funding_sources = getFundingSources($conn);
$districts_res = $conn->query("SELECT DISTINCT district FROM projects");
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <h3>Project Dashboard</h3>
        <div>
            <?php 
            $export_qs = $_GET;
            $export_qs['export'] = 1;
            unset($export_qs['page']); // Exclude pagination for export
            ?>
            <a href="?<?php echo http_build_query($export_qs); ?>" class="btn btn-outline-success me-2">Export CSV</a>
            <a href="add.php" class="btn btn-primary">+ Add New Project</a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card p-3 mb-4 bg-light border-0">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Project or Contractor" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">District</label>
                <select name="district" class="form-select form-select-sm">
                    <option value="">All</option>
                    <?php while($d = $districts_res->fetch_assoc()): ?>
                        <option value="<?php echo $d['district']; ?>" <?php if($district_filter == $d['district']) echo 'selected'; ?>><?php echo $d['district']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Funding Source</label>
                <select name="funding_source" class="form-select form-select-sm">
                    <option value="">All</option>
                    <?php foreach($funding_sources as $fs): ?>
                        <option value="<?php echo $fs['id']; ?>" <?php if($funding_filter == $fs['id']) echo 'selected'; ?>><?php echo $fs['source_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="Ongoing" <?php if($status_filter == 'Ongoing') echo 'selected'; ?>>Ongoing</option>
                    <option value="Completed" <?php if($status_filter == 'Completed') echo 'selected'; ?>>Completed</option>
                    <option value="Delayed" <?php if($status_filter == 'Delayed') echo 'selected'; ?>>Delayed</option>
                </select>
            </div>
            <div class="col-md-2 d-flex">
                <button type="submit" class="btn btn-sm btn-primary w-100 me-1">Filter</button>
                <a href="index.php" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="card p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Project Name</th>
                        <th>Type</th>
                        <th>Location (District / DS / GN)</th>
                        <th>Contractor</th>
                        <th>Completion % (Physical)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT p.*, t.type_name, c.grade_name
                            FROM projects p 
                            LEFT JOIN project_types t ON p.project_type_id = t.id 
                            LEFT JOIN cida_grades c ON p.cida_grade_id = c.id
                            WHERE $where_conditions
                            ORDER BY p.id DESC
                            LIMIT $limit OFFSET $offset";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        $modals = "";
                        while ($row = $result->fetch_assoc()) {
                            $cost = number_format($row['contract_amount'], 2);
                            $est = number_format($row['estimate_cost'], 2);
                            $modals .= "
                            <div class='modal fade' id='projectModal{$row['id']}' tabindex='-1' aria-hidden='true'>
                              <div class='modal-dialog modal-lg'>
                                <div class='modal-content text-start'>
                                  <div class='modal-header'>
                                    <h5 class='modal-title text-primary'>Project Details: {$row['project_name']}</h5>
                                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                  </div>
                                  <div class='modal-body'>
                                    <div class='row'>
                                        <div class='col-md-6'>
                                            <p><strong>Type:</strong> {$row['type_name']}</p>
                                            <p><strong>Location:</strong> {$row['district']} &gt; {$row['ds_division']} &gt; {$row['gn_division']}</p>
                                            <p><strong>Office:</strong> {$row['office_name']}</p>
                                        </div>
                                        <div class='col-md-6'>
                                            <p><strong>Contractor:</strong> {$row['contractor_name']}</p>
                                            <p><strong>CIDA Reg No:</strong> {$row['cida_reg_no']}</p>
                                            <p><strong>Agreement No:</strong> {$row['agreement_no']}</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class='row'>
                                        <div class='col-md-6'>
                                            <p><strong>Start Date:</strong> {$row['start_date']}</p>
                                            <p><strong>Completion Date:</strong> {$row['completion_date']}</p>
                                        </div>
                                        <div class='col-md-6'>
                                            <p><strong>Contract Amount:</strong> Rs. {$cost}</p>
                                            <p><strong>Estimate Cost:</strong> Rs. {$est}</p>
                                        </div>
                                    </div>
                                  </div>
                                  <div class='modal-footer'>
                                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                  </div>
                                </div>
                              </div>
                            </div>
                            ";
                            
                            $delay_badge = ($row['delay_status'] == 'Delayed') ? 
                                '<span class="badge bg-danger">Delayed</span>' : 
                                '<span class="badge bg-success">On Time</span>';
                            
                            echo "<tr>
                                <td>{$row['id']}</td>
                                <td class='fw-bold'>
                                    <a href='#' class='text-decoration-none' data-bs-toggle='modal' data-bs-target='#projectModal{$row['id']}'>
                                        {$row['project_name']}
                                    </a>
                                    ";
                                    if($row['approval_status'] == 'pending'):
                                        echo "<span class=\"badge bg-warning text-dark\">Pending Approval</span>";
                                    endif;
                                echo "
                                </td>
                                <td><span class='badge bg-secondary'>{$row['type_name']}</span></td>
                                <td>
                                    <small class='d-block text-muted'>{$row['district']}</small>
                                    <small>{$row['ds_division']} > {$row['gn_division']}</small>
                                </td>
                                <td>
                                    <div>{$row['contractor_name']}</div>
                                    <small class='text-muted'>{$row['grade_name']}</small>
                                </td>
                                <td>
                                    <div class='mb-1'>Phy: <div class='progress' style='height: 6px; width: 100px; display: inline-flex;'><div class='progress-bar' role='progressbar' style='width: {$row['physical_progress']}%'></div></div> {$row['physical_progress']}%</div>
                                </td>
                                <td>$delay_badge</td>
                                <td>
                                    <div class='btn-group'>
                                        <a href='edit.php?id={$row['id']}' class='btn btn-sm btn-outline-primary'>Edit</a>
                                        <a href='progress.php?id={$row['id']}' class='btn btn-sm btn-outline-warning'>Progress</a>
                                        <a href='?delete={$row['id']}' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                                    </div>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center py-4 text-muted'>No projects found. <a href='add.php'>Create one now</a>.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            <?php if(isset($modals)) echo $modals; ?>
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
                    <a class="page-link" href="<?php echo $base_url . 'page=' . ($page - 1); ?>" tabindex="-1" aria-disabled="true">Previous</a>
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
</div>

<?php include '../../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
