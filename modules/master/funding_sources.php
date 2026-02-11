<?php
include '../../includes/db_connect.php';
include '../../includes/header.php';
include '../../includes/navbar.php';

// Handle Add
if (isset($_POST['add_source'])) {
    $source_name = $_POST['source_name'];
    $conn->query("INSERT INTO funding_sources (source_name) VALUES ('$source_name')");
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM funding_sources WHERE id=$id");
    echo "<script>window.location='funding_sources.php';</script>";
}
?>

<div class="container">
    <div class="row">
        <div class="col-md-5">
            <div class="card p-4">
                <h4>Add Funding Source</h4>
                <form method="POST">
                    <div class="mb-3">
                        <label>Source Name</label>
                        <input type="text" name="source_name" class="form-control" required>
                    </div>
                    <button type="submit" name="add_source" class="btn btn-primary">Add Source</button>
                </form>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card p-4">
                <h4>Existing Funding Sources</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Source Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM funding_sources");
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['source_name']}</td>
                                <td><a href='?delete={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")'>Delete</a></td>
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
