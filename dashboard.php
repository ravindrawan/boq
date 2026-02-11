<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// ව්‍යාපෘති ගණන් ගැනීම
$total_projects = $conn->query("SELECT COUNT(*) as count FROM projects")->fetch_assoc()['count'];
$total_budget = $conn->query("SELECT SUM(budget) as total FROM projects")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark px-3">
        <span class="navbar-brand">BOQ Admin Panel (<?php echo $_SESSION['role']; ?>)</span>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Projects</h5>
                        <h2><?php echo $total_projects; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Allocated Budget</h5>
                        <h2>Rs. <?php echo number_format($total_budget, 2); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <a href="add_project.php" class="btn btn-primary mb-3">+ Add New Project</a>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Project Name</th>
                    <th>District</th>
                    <th>Contractor</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM projects ORDER BY id DESC";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['project_name']}</td>
                        <td>{$row['district']}</td>
                        <td>{$row['contractor']}</td>
                        <td>
                            <div class='progress'>
                                <div class='progress-bar' style='width: {$row['progress']}%'>{$row['progress']}%</div>
                            </div>
                        </td>
                        <td><span class='badge bg-info'>{$row['status']}</span></td>
                        <td>
                            <a href='#' class='btn btn-sm btn-warning'>Edit</a>
                            " . ($_SESSION['role'] == 'admin' ? "<a href='#' class='btn btn-sm btn-danger'>Delete</a>" : "") . "
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>