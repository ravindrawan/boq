<?php
include '../../includes/db_connect.php';
include '../../includes/header.php';
include '../../includes/navbar.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Access Denied. Only Admins can view this page.</div></div>";
    include '../../includes/footer.php';
    exit();
}

// Handle Add Office
if (isset($_POST['add_office'])) {
    $office = $_POST['office_name'];
    
    $check = $conn->query("SELECT * FROM offices WHERE office_name='$office'");
    if ($check->num_rows > 0) {
        $error = "Office already exists!";
    } else {
        $sql = "INSERT INTO offices (office_name) VALUES ('$office')";
        if ($conn->query($sql)) {
            $success = "Office added successfully!";
        } else {
            $error = "Error adding office: " . $conn->error;
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM offices WHERE id=$id");
    echo "<script>window.location='offices.php';</script>";
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card p-4">
                <h4>Add New Office</h4>
                <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label>Office Name</label>
                        <input type="text" name="office_name" class="form-control" required>
                    </div>
                    <button type="submit" name="add_office" class="btn btn-primary w-100">Add Office</button>
                </form>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card p-4">
                <h4>Existing Offices</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Office Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $offices = $conn->query("SELECT * FROM offices");
                        while ($row = $offices->fetch_assoc()) {
                            echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['office_name']}</td>
                                <td>
                                    <a href='?delete={$row['id']}' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"Delete this office?\")'>Delete</a>
                                </td>
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
