<?php
include '../../includes/db_connect.php';
include '../../includes/header.php';
include '../../includes/navbar.php';

if ($_SESSION['role'] !== 'supervisor' && $_SESSION['role'] !== 'admin') {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Access Denied.</div></div>";
    exit();
}

// Handle Approve
if (isset($_GET['approve'])) {
    $id = $_GET['approve'];
    $conn->query("UPDATE projects SET approval_status='approved' WHERE id=$id");
    echo "<script>alert('Project Approved!'); window.location='approvals.php';</script>";
}



$office = $_SESSION['office_name'];
$sql = "SELECT p.*, t.type_name, ANY_VALUE(u.username) as created_by 
        FROM projects p 
        LEFT JOIN project_types t ON p.project_type_id = t.id 
        LEFT JOIN users u ON p.office_name = u.office_name 
        WHERE p.approval_status = 'pending'";

if ($_SESSION['role'] !== 'admin') {
    // SQL Injection වලින් බේරෙන්න escape කරන්න (optional but recommended)
    $office_escaped = $conn->real_escape_string($office);
    $sql .= " AND p.office_name = '$office_escaped'";
}
$sql .= " GROUP BY p.id ORDER BY p.id DESC";

// SQL mode එකත් මාරු කරමු safe වෙන්න (ඔයා index.php එකේ කළා වගේමයි)
$conn->query("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");

$result = $conn->query($sql);


?>

<div class="container mt-4">
    <div class="card p-4">
        <h4>Pending Approvals</h4>
        <?php if ($result->num_rows == 0): ?>
            <p class="text-muted">No text pending approvals found.</p>
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
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['project_name']; ?></td>
                        <td><?php echo $row['type_name']; ?></td>
                        <td><?php echo $row['office_name']; ?></td>
                        <td><?php echo $row['contractor_name']; ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">Review</a>
                            <a href="?approve=<?php echo $row['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Approve this project? It will become public.')">Approve</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
