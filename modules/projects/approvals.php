<?php
include '../../includes/db_connect.php';
include '../../includes/header.php';
include '../../includes/navbar.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'supervisor' && $_SESSION['role'] !== 'admin')) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Access Denied.</div></div>";
    exit();
}

// Handle Approve
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $conn->query("UPDATE projects SET approval_status='approved' WHERE id=$id");
    echo "<script>alert('Project Approved!'); window.location='approvals.php';</script>";
}

$office = $_SESSION['office_name'];
$role = $_SESSION['role'];

// සරල සහ නිවැරදි Query එක
$sql = "SELECT p.*, t.type_name 
        FROM projects p 
        LEFT JOIN project_types t ON p.project_type_id = t.id 
        WHERE p.approval_status = 'pending'";

if ($role !== 'admin') {
    // Admin නොවේ නම් තමන්ගේ කාර්යාලයේ ඒවා පමණක් පෙන්වන්න
    $sql .= " AND p.office_name = '" . $conn->real_escape_string($office) . "'";
}

$sql .= " ORDER BY p.id DESC";

$result = $conn->query($sql);

// Query එක fail වුණොත් Error එක පෙන්වන්න (Debugging)
if (!$result) {
    die("<div class='container mt-5 alert alert-danger'>SQL Error: " . $conn->error . "</div>");
}
?>

<div class="container mt-4">
    <div class="card shadow-sm p-4">
        <h4 class="mb-4 text-primary"><i class="bi bi-clock-history"></i> Pending Approvals</h4>
        
        <?php if ($result->num_rows == 0): ?>
            <div class="alert alert-info">No pending approvals found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover border">
                    <thead class="table-light">
                        <tr>
                            <th>Project Name</th>
                            <th>Type</th>
                            <th>Office</th>
                            <th>Contractor</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $modals = "";
                        while ($row = $result->fetch_assoc()): 
                            $cost = number_format($row['contract_amount'], 2);
                            $est = number_format($row['estimate_cost'], 2);
                            
                            // Modals store කරනවා පස්සේ print කරන්න
                            $modals .= "
                            <div class='modal fade' id='projectModal{$row['id']}' tabindex='-1' aria-hidden='true'>
                              <div class='modal-dialog modal-lg'>
                                <div class='modal-content'>
                                  <div class='modal-header bg-light'>
                                    <h5 class='modal-title text-primary'>Project Details: {$row['project_name']}</h5>
                                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                  </div>
                                  <div class='modal-body'>
                                    <div class='row'>
                                        <div class='col-md-6'>
                                            <p><strong>Type:</strong> {$row['type_name']}</p>
                                            <p><strong>Location:</strong> {$row['district']} &gt; {$row['ds_division']}</p>
                                            <p><strong>Office:</strong> {$row['office_name']}</p>
                                        </div>
                                        <div class='col-md-6'>
                                            <p><strong>Contractor:</strong> {$row['contractor_name']}</p>
                                            <p><strong>Agreement No:</strong> {$row['agreement_no']}</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class='row text-success'>
                                        <div class='col-md-6'>
                                            <p><strong>Start Date:</strong> {$row['start_date']}</p>
                                            <p><strong>Contract Amount:</strong> Rs. {$cost}</p>
                                        </div>
                                        <div class='col-md-6'>
                                            <p><strong>Completion:</strong> {$row['completion_date']}</p>
                                            <p><strong>Estimate:</strong> Rs. {$est}</p>
                                        </div>
                                    </div>
                                  </div>
                                  <div class='modal-footer'>
                                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                  </div>
                                </div>
                              </div>
                            </div>";
                        ?>
                        <tr>
                            <td>
                                <a href="#" class="text-decoration-none fw-bold" data-bs-toggle="modal" data-bs-target="#projectModal<?php echo $row['id']; ?>">
                                    <?php echo htmlspecialchars($row['project_name']); ?>
                                </a>
                            </td>
                            <td><?php echo $row['type_name']; ?></td>
                            <td><?php echo $row['office_name']; ?></td>
                            <td><?php echo $row['contractor_name']; ?></td>
                            <td class="text-center">
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary">Review</a>
                                <a href="?approve=<?php echo $row['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Approve this project?')">Approve</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php echo $modals; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
