<?php
include '../../includes/db_connect.php';
include '../../includes/header.php';
include '../../includes/navbar.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM projects WHERE id=$id");
    echo "<script>window.location='index.php';</script>";
}
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Project Dashboard</h3>
        <a href="add.php" class="btn btn-primary">+ Add New Project</a>
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
                        <th>Completion % (Phy / Fin)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $where_clause = "";
                    if ($_SESSION['role'] !== 'admin') {
                        $office = $_SESSION['office_name'];
                        $where_clause = "WHERE p.office_name = '$office'";
                    }

                    $sql = "SELECT p.*, t.type_name, c.grade_name
                            FROM projects p 
                            LEFT JOIN project_types t ON p.project_type_id = t.id 
                            LEFT JOIN cida_grades c ON p.cida_grade_id = c.id
                            $where_clause
                            ORDER BY p.id DESC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $delay_badge = ($row['delay_status'] == 'Delayed') ? 
                                '<span class="badge bg-danger">Delayed</span>' : 
                                '<span class="badge bg-success">On Time</span>';
                            
                            echo "<tr>
                                <td>{$row['id']}</td>
                                <td class='fw-bold'>
                                    {$row['project_name']}
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
                                    <div>Fin: <div class='progress' style='height: 6px; width: 100px; display: inline-flex;'><div class='progress-bar bg-info' role='progressbar' style='width: {$row['financial_progress']}%'></div></div> {$row['financial_progress']}%</div>
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
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
