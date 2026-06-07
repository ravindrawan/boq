<?php
include '../../includes/db_connect.php';
include '../../includes/header.php';
include '../../includes/navbar.php';

$current_office = $_SESSION['office_name'] ?? null;
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Handle Add
if (isset($_POST['add_type'])) {
    $type_name = $conn->real_escape_string($_POST['type_name']);
    // Save office_name only for non-admin users; admins create global records (NULL)
    $office_val = (!$is_admin && !empty($current_office)) ? "'" . $conn->real_escape_string($current_office) . "'" : "NULL";
    $conn->query("INSERT INTO project_types (type_name, office_name) VALUES ('$type_name', $office_val)");
    echo "<script>window.location='project_types.php';</script>";
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Only allow delete if admin, or record belongs to this office
    if ($is_admin) {
        $conn->query("DELETE FROM project_types WHERE id=$id");
    } else {
        $safe_office = $conn->real_escape_string($current_office);
        $conn->query("DELETE FROM project_types WHERE id=$id AND office_name='$safe_office'");
    }
    echo "<script>window.location='project_types.php';</script>";
}

// Build query: admin sees all; office users see their own + global
if ($is_admin) {
    $result = $conn->query("SELECT * FROM project_types ORDER BY office_name, type_name");
} else {
    $safe_office = $conn->real_escape_string($current_office);
    $result = $conn->query("SELECT * FROM project_types WHERE office_name = '$safe_office' OR office_name IS NULL OR office_name = '' ORDER BY office_name, type_name");
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-5">
            <div class="card p-4">
                <h4>Add Project Type</h4>
                <?php if (!$is_admin && !empty($current_office)): ?>
                    <div class="alert alert-info py-2 small">
                        <i class="bi bi-building"></i> Records you add will be linked to: <strong><?php echo htmlspecialchars($current_office); ?></strong>
                    </div>
                <?php elseif ($is_admin): ?>
                    <div class="alert alert-warning py-2 small">
                        <i class="bi bi-globe"></i> As Admin, added records will be <strong>Global</strong> (visible to all offices).
                    </div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label>Type Name</label>
                        <input type="text" name="type_name" class="form-control" required>
                    </div>
                    <button type="submit" name="add_type" class="btn btn-primary">Add Type</button>
                </form>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card p-4">
                <h4>Existing Project Types</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type Name</th>
                            <th>Office</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = $result->fetch_assoc()) {
                            $office_label = (!empty($row['office_name'])) ? "<span class='badge bg-secondary'>{$row['office_name']}</span>" : "<span class='badge bg-success'>Global</span>";
                            // Only show delete if admin, or record belongs to this office
                            $can_delete = $is_admin || ($row['office_name'] === $current_office);
                            $delete_btn = $can_delete
                                ? "<a href='?delete={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")'>Delete</a>"
                                : "<span class='text-muted small'>—</span>";
                            echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['type_name']}</td>
                                <td>$office_label</td>
                                <td>$delete_btn</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
