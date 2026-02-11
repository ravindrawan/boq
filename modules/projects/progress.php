<?php
include '../../includes/db_connect.php';
include '../../includes/functions.php';
include '../../includes/header.php';
include '../../includes/navbar.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$sql = "SELECT * FROM projects WHERE id = $id";
$result = $conn->query($sql);
$project = $result->fetch_assoc();

// Handle Updates
if (isset($_POST['update_progress'])) {
    $phy = $_POST['physical_progress'];
    $fin = $_POST['financial_progress'];
    $status = $_POST['delay_status'];
    $reason = $_POST['delay_reason'];
    $ext_date = $_POST['extended_date'];

    $sql = "UPDATE projects SET 
            physical_progress = '$phy',
            financial_progress = '$fin',
            delay_status = '$status',
            delay_reason = '$reason',
            extended_date = '$ext_date'
            WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        // Handle New Photos
        if (!empty($_FILES["new_photos"]["name"][0])) {
            $upload_dir = "../../uploads/projects/" . $id . "/";
            foreach ($_FILES["new_photos"]["name"] as $key => $name) {
                $tmp_name = $_FILES["new_photos"]["tmp_name"][$key];
                 if (!empty($name)) {
                    $file_name = basename($name);
                    if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
                    $target = $upload_dir . time() . "_" . $file_name;
                    if (move_uploaded_file($tmp_name, $target)) {
                        $conn->query("INSERT INTO project_photos (project_id, photo_path) VALUES ($id, '$target')");
                    }
                 }
            }
        }
        echo "<script>alert('Progress Updated Successfully!'); window.location='progress.php?id=$id';</script>";
    }
}

// Get Photos
$photos = $conn->query("SELECT * FROM project_photos WHERE project_id = $id ORDER BY uploaded_at DESC");
?>

<div class="container mb-5">
    <h3>Update Progress: <?php echo $project['project_name']; ?></h3>
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card p-4">
                <h5>Progress & Delay Status</h5>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label>Physical Progress (%)</label>
                        <input type="range" class="form-range" min="0" max="100" step="1" name="physical_progress" value="<?php echo $project['physical_progress']; ?>" oninput="this.nextElementSibling.value = this.value">
                        <output><?php echo $project['physical_progress']; ?></output>%
                    </div>
                    <div class="mb-3">
                        <label>Financial Progress (%)</label>
                        <input type="range" class="form-range" min="0" max="100" step="1" name="financial_progress" value="<?php echo $project['financial_progress']; ?>" oninput="this.nextElementSibling.value = this.value">
                        <output><?php echo $project['financial_progress']; ?></output>%
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label>Delay Status</label>
                        <select name="delay_status" class="form-select">
                            <option value="None" <?php echo ($project['delay_status']=='None')?'selected':''; ?>>On Schedule (None)</option>
                            <option value="Delayed" <?php echo ($project['delay_status']=='Delayed')?'selected':''; ?>>Delayed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Reason for Delay</label>
                        <textarea name="delay_reason" class="form-control" rows="3"><?php echo $project['delay_reason']; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Extended Date</label>
                        <input type="date" name="extended_date" class="form-control" value="<?php echo $project['extended_date']; ?>">
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label>Add New Photos</label>
                        <input type="file" name="new_photos[]" class="form-control" multiple accept="image/*">
                    </div>

                    <button type="submit" name="update_progress" class="btn btn-primary w-100">Update Progress</button>
                    <a href="index.php" class="btn btn-secondary w-100 mt-2">Back to Dashboard</a>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-4">
                <h5>Project Photos</h5>
                <div class="row g-2">
                    <?php 
                    if ($photos->num_rows > 0) {
                        while($photo = $photos->fetch_assoc()) {
                            echo "<div class='col-4'>
                                <a href='{$photo['photo_path']}' target='_blank'>
                                    <img src='{$photo['photo_path']}' class='img-fluid rounded' style='height: 100px; object-fit: cover; width: 100%;'>
                                </a>
                            </div>";
                        }
                    } else {
                        echo "<p class='text-muted'>No photos uploaded yet.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
