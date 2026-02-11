<?php
// Function to handle file uploads
function uploadFile($file, $uploadDir) {
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $targetFile = $uploadDir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    // Rename file to avoid collisions (timestamp_filename)
    $newFileName = time() . '_' . basename($file["name"]);
    $targetPath = $uploadDir . $newFileName;

    if (move_uploaded_file($file["tmp_name"], $targetPath)) {
        return $targetPath;
    } else {
        return false;
    }
}

// Function to get Project Types
function getProjectTypes($conn) {
    $types = [];
    $sql = "SELECT * FROM project_types";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $types[] = $row;
        }
    }
    return $types;
}

// Function to get Funding Sources
function getFundingSources($conn) {
    $sources = [];
    $sql = "SELECT * FROM funding_sources";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $sources[] = $row;
        }
    }
    return $sources;
}
// Function to get CIDA Grades
function getCidaGrades($conn) {
    $grades = [];
    $sql = "SELECT * FROM cida_grades ORDER BY grade_name";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $grades[] = $row;
        }
    }
    return $grades;
}
?>
