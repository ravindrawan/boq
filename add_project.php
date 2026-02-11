<?php
session_start();
include 'db_connect.php';

if (isset($_POST['submit'])) {
    $pname = $_POST['pname'];
    $district = $_POST['district'];
    $contractor = $_POST['contractor'];
    $budget = $_POST['budget'];
    
    // Image Upload Logic
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);

    $sql = "INSERT INTO projects (project_name, district, contractor, budget, image_path) 
            VALUES ('$pname', '$district', '$contractor', '$budget', '$target_file')";
    
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Project Added Successfully'); window.location='dashboard.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5 bg-light">
    <div class="card p-4 mx-auto" style="max-width: 600px;">
        <h3>Add New BOQ / Project</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Project Name</label>
                <input type="text" name="pname" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>District</label>
                <select name="district" class="form-control">
                    <option>Kurunegala</option>
                    <option>Puttalam</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Contractor Name</label>
                <input type="text" name="contractor" class="form-control">
            </div>
            <div class="mb-3">
                <label>Budget (Rs.)</label>
                <input type="number" name="budget" class="form-control">
            </div>
            <div class="mb-3">
                <label>Project Photo</label>
                <input type="file" name="image" class="form-control">
            </div>
            <button type="submit" name="submit" class="btn btn-success">Save Project</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>