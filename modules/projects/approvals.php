<?php
include '../../includes/db_connect.php';
include '../../includes/header.php';
include '../../includes/navbar.php';

if ($_SESSION['role'] !== 'supervisor' && $_SESSION['role'] !== 'admin') {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Access Denied.</div></div>";
    exit();
}

// Handle Approve (Prepared Statement භාවිතයෙන් Security වැඩි කර ඇත)
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $stmt = $conn->prepare("UPDATE projects SET approval_status='approved' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "<script>alert('Project Approved!'); window.location='approvals.php';</script>";
}

$office = $_SESSION['office_name'] ?? '';
$role = $_SESSION['role'] ?? '';

// Query එක පැහැදිලිව සකස් කිරීම
if ($role === 'admin') {
    $sql = "SELECT p.*, t.type_name 
            FROM projects p 
            LEFT JOIN project_types t ON p.project_type_id = t.id 
            WHERE p.approval_status = 'pending' 
            ORDER BY p.id DESC";
    $result = $conn->query($sql);
} else {
    $sql = "SELECT p.*, t.type_name 
            FROM projects p 
            LEFT JOIN project_types t ON p.project_type_id = t.id 
            WHERE p.approval_status = 'pending' AND p.office_name = ? 
            ORDER BY p.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $office);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<div class="container mt-4">
    <div class="card p-4">
        <h4>Pending Approvals</h4>
        <?php if ($result->num_rows == 0): ?>
            <p class="text-muted">No pending approvals found.</p>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Project Name</th>
                        <th>Type</th>
                        <th>Office</th>
                        <th>Contractor</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $modals = "";
                    while ($row = $result->fetch_assoc()): 
                        $cost = number_format($row['contract_amount'], 2);
                        $est = number_format($row['estimate_cost'], 2);
                        
                        // Modals HTML එකතු කිරීම
                        $modals .= "
                        <div class='modal fade' id='projectModal{$row['id']}' tabindex='-1' aria-hidden='true'>
                          <div class='modal-dialog modal-lg'>
                            <div class='modal-content text-start'>
                              <div class='modal-header'>
                                <h5 class='modal-title text-primary'>Project Details: ".htmlspecialchars($row['project_name'])."</h5>
                                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                              </div>
                              <div class='modal-body'>
                                <div class='row'>
                                    <div class='col-md-6'>
                                        <p><strong>Type:</strong> ".htmlspecialchars($row['type_name'])."</p>
                                        <p><strong>Location:</strong> ".htmlspecialchars($row['district'])." &gt; ".htmlspecialchars($row['ds_division'])." &gt; ".htmlspecialchars($row['gn_division'])."</p>
                                        <p><strong>Office:</strong> ".htmlspecialchars($row['office_name'])."</p>
                                    </div>
                                    <div class='col-md-6'>
                                        <p><strong>Contractor:</strong> ".htmlspecialchars($row['contractor_name'])."</p>
                                        <p><strong>CIDA Reg No:</strong> ".htmlspecialchars($row['cida_reg_no'])."</p>
                                        <p><strong>Agreement No:</strong> ".htmlspecialchars($row['agreement_no'])."</p>
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
                    ?>
                    <tr>
                        <td>
                            <a href="#" class="text-decoration-none fw-bold" data-bs-toggle="modal" data-bs-target="#projectModal<?php echo $row['id']; ?>">
                                <?php echo htmlspecialchars($row['project_name']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($row['type_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['office_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['contractor_name']); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">Review</a>
                            <a href="?approve=<?php echo $row['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Approve this project? It will become public.')">Approve</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php echo $modals; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
