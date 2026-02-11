<?php
include '../../includes/db_connect.php';
include '../../includes/header.php';
include '../../includes/navbar.php';

// Filter by Office for non-admins
$office_filter = "";
if ($_SESSION['role'] !== 'admin') {
    $office_name = $_SESSION['office_name'];
    $office_filter = "WHERE office_name = '$office_name'";
}

$pending_filter = ($office_filter === "") ? "WHERE" : "$office_filter AND";
// Stats Queries
$total_projects = $conn->query("SELECT COUNT(*) as c FROM projects $office_filter")->fetch_assoc()['c'];
$ongoing = $conn->query("SELECT COUNT(*) as c FROM projects $pending_filter physical_progress < 100 AND completion_date >= CURDATE()")->fetch_assoc()['c'];
$delayed = $conn->query("SELECT COUNT(*) as c FROM projects $pending_filter delay_status = 'Delayed'")->fetch_assoc()['c'];
$completed = $conn->query("SELECT COUNT(*) as c FROM projects $pending_filter physical_progress = 100")->fetch_assoc()['c'];
$pending_apr = $conn->query("SELECT COUNT(*) as c FROM projects $pending_filter approval_status = 'pending'")->fetch_assoc()['c'];

?>
<style>
    body {
        background: url('../../img/dashboard.jpg') no-repeat center center fixed;
        background-size: cover;
    }
    .overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.85); /* White overlay with opacity */
        z-index: -1;
    }
</style>
<div class="overlay"></div>

<div class="container mt-4 mb-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="fw-bold text-secondary">Dashboard Overview</h2>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md">
            <div class="card p-3 border-0 shadow-sm bg-white" style="border-left: 5px solid #4f46e5 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 text-uppercase small fw-bold">Total Projects</p>
                        <h3 class="fw-bold text-dark mb-0"><?php echo $total_projects; ?></h3>
                    </div>
                    <div class="bg-indigo-100 text-primary rounded-circle p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-folder" viewBox="0 0 16 16">
                          <path d="M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4H2.19zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139C1.72 3.042 1.95 3 2.19 3h9.096l-.235-1.08a.5.5 0 0 0-.354-.38L10.35 1.4a.5.5 0 0 0-.25.02l-3.213.973z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md">
            <div class="card p-3 border-0 shadow-sm bg-white" style="border-left: 5px solid #3b82f6 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 text-uppercase small fw-bold">Ongoing</p>
                        <h3 class="fw-bold text-dark mb-0"><?php echo $ongoing; ?></h3>
                    </div>
                    <div class="bg-blue-100 text-primary rounded-circle p-3">
                       <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                          <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/>
                          <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md">
            <div class="card p-3 border-0 shadow-sm bg-white" style="border-left: 5px solid #ef4444 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 text-uppercase small fw-bold">Delayed</p>
                        <h3 class="fw-bold text-dark mb-0"><?php echo $delayed; ?></h3>
                    </div>
                    <div class="bg-red-100 text-danger rounded-circle p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                          <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z"/>
                          <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md">
            <div class="card p-3 border-0 shadow-sm bg-white" style="border-left: 5px solid #10b981 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 text-uppercase small fw-bold">Completed</p>
                        <h3 class="fw-bold text-dark mb-0"><?php echo $completed; ?></h3>
                    </div>
                    <div class="bg-green-100 text-success rounded-circle p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
                          <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                          <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pending Approval Card -->
        <?php if($_SESSION['role'] !== 'office'): ?>
        <div class="col-md">
            <div class="card p-3 border-0 shadow-sm bg-white" style="border-left: 5px solid #f59e0b !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 text-uppercase small fw-bold">Pending</p>
                        <h3 class="fw-bold text-dark mb-0"><?php echo $pending_apr; ?></h3>
                    </div>
                    <div class="bg-warning text-dark rounded-circle p-3" style="--bs-bg-opacity: .2;">
                         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-hourglass-split" viewBox="0 0 16 16">
                          <path d="M2.5 15a.5.5 0 1 1 0-1h1v-1a4.5 4.5 0 0 1 2.557-4.06c.29-.139.443-.377.443-.59v-.7c0-.213-.154-.451-.443-.59A4.5 4.5 0 0 1 3.5 3V2h-1a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-1v1a4.5 4.5 0 0 1-2.557 4.06c-.29.139-.443.377-.443.59v.7c0 .213.154.451.443.59A4.5 4.5 0 0 1 12.5 13v1h1a.5.5 0 0 1 0 1h-11zm2-13v1c0 .537.12 1.045.337 1.5h6.326c.216-.455.337-.963.337-1.5V2h-7zm3 6.35c0 .701-.478 1.236-1.011 1.492A3.5 3.5 0 0 0 4.5 13s.866-1.299 3-1.48V8.35zm1 0v3.17c2.134.181 3 1.48 3 1.48a3.5 3.5 0 0 0-1.989-3.158C8.978 9.586 8.5 9.052 8.5 8.35z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold">Recent Projects</h5>
                    <a href="../projects/index.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Status</th>
                            <th>Physical Progress</th>
                            <th>Financial Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $recent_sql = "SELECT * FROM projects $office_filter ORDER BY id DESC LIMIT 5";
                        $recent = $conn->query($recent_sql);
                        while ($r = $recent->fetch_assoc()) {
                            // Calculate Status
                            $status = "Ongoing";
                            $status_class = "badge bg-info";
                            
                            if ($r['approval_status'] == 'pending') {
                                $status = "Pending Approval";
                                $status_class = "badge bg-warning text-dark";
                            } elseif ($r['physical_progress'] == 100) {
                                $status = "Completed";
                                $status_class = "badge bg-success";
                            } elseif ($r['delay_status'] == 'Delayed') {
                                $status = "Delayed";
                                $status_class = "badge bg-danger";
                            }

                            echo "<tr>
                                <td>{$r['project_name']}</td>
                                <td><span class='$status_class'>$status</span></td>
                                <td>
                                    <div class='progress' style='height: 5px; width: 80px;'>
                                        <div class='progress-bar bg-primary' style='width: {$r['physical_progress']}%'></div>
                                    </div>
                                    <small>{$r['physical_progress']}%</small>
                                </td>
                                <td>
                                    <div class='progress' style='height: 5px; width: 80px;'>
                                        <div class='progress-bar bg-success' style='width: {$r['financial_progress']}%'></div>
                                    </div>
                                    <small>{$r['financial_progress']}%</small>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-4">
             <div class="card p-4 h-100 border border-2 border-primary">
                <h4 class="text-primary fw-bold">Quick Actions</h4>
                <div class="d-grid gap-3 mt-4">
                    <a href="../projects/add.php" class="btn btn-primary fw-bold">
                        + Add New Project
                    </a>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="../users/manage.php" class="btn btn-outline-primary">
                        Manage Users
                    </a>
                    <?php endif; ?>
                </div>
             </div>
        </div>
    </div>

</div>

<?php include '../../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
