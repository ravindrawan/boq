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
// If $office_name is provided, returns records for that office + global records (NULL office_name)
// If $office_name is NULL/empty, returns ALL records (admin view)
function getProjectTypes($conn, $office_name = null) {
    $types = [];
    if (!empty($office_name)) {
        $safe = $conn->real_escape_string($office_name);
        $sql = "SELECT * FROM project_types WHERE office_name = '$safe' OR office_name IS NULL OR office_name = '' ORDER BY type_name";
    } else {
        $sql = "SELECT * FROM project_types ORDER BY type_name";
    }
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $types[] = $row;
        }
    }
    return $types;
}

// Function to get Funding Sources
// If $office_name is provided, returns records for that office + global records (NULL office_name)
// If $office_name is NULL/empty, returns ALL records (admin view)
function getFundingSources($conn, $office_name = null) {
    $sources = [];
    if (!empty($office_name)) {
        $safe = $conn->real_escape_string($office_name);
        $sql = "SELECT * FROM funding_sources WHERE office_name = '$safe' OR office_name IS NULL OR office_name = '' ORDER BY source_name";
    } else {
        $sql = "SELECT * FROM funding_sources ORDER BY source_name";
    }
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $sources[] = $row;
        }
    }
    return $sources;
}

// Function to get CIDA Grades
// If $office_name is provided, returns records for that office + global records (NULL office_name)
// If $office_name is NULL/empty, returns ALL records (admin view)
function getCidaGrades($conn, $office_name = null) {
    $grades = [];
    if (!empty($office_name)) {
        $safe = $conn->real_escape_string($office_name);
        $sql = "SELECT * FROM cida_grades WHERE office_name = '$safe' OR office_name IS NULL OR office_name = '' ORDER BY grade_name";
    } else {
        $sql = "SELECT * FROM cida_grades ORDER BY grade_name";
    }
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $grades[] = $row;
        }
    }
    return $grades;
}
?>
