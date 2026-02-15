<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Public Project View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="bg-primary text-white p-3 text-center">
        <h1>Public Project Portal</h1>
        <p>Ongoing Development Projects - Transparency Dashboard</p>
        <a href="index.php" class="btn btn-light btn-sm">Staff Login</a>
    </div>

    <div class="container mt-4">
        <div class="row">
            <?php
            $sql = "SELECT * FROM projects WHERE is_public=1 ORDER BY id DESC";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $img = !empty($row['image_path']) ? $row['image_path'] : 'https://via.placeholder.com/150';
                echo "
                <div class='col-md-4 mb-4'>
                    <div class='card shadow-sm'>
                        <img src='$img' class='card-img-top' style='height: 200px; object-fit: cover;'>
                        <div class='card-body'>
                            <h5 class='card-title'>{$row['project_name']}</h5>
                            <p class='text-muted'>{$row['district']}</p>
                            <div class='d-flex justify-content-between'>
                                <span>Progress:</span>
                                <span class='fw-bold'>{$row['progress']}%</span>
                            </div>
                            <div class='progress mb-2'>
                                <div class='progress-bar bg-success' style='width: {$row['progress']}%'></div>
                            </div>
                            <span class='badge bg-warning text-dark'>{$row['status']}</span>
                        </div>
                    </div>
                </div>";
            }
            ?>
        </div>
    </div>
</body>
</html>