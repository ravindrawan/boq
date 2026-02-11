<?php
include 'includes/db_connect.php';

// 1. Update Projects Table - Retry
// Check existing columns to determine where to place new ones
$res = $conn->query("SHOW COLUMNS FROM projects");
$columns = [];
while($row = $res->fetch_assoc()) {
    $columns[] = $row['Field'];
}

// Add approval_status (default pending)
if (!in_array('approval_status', $columns)) {
    // Just add it at the end to be safe, or after a known column
    $conn->query("ALTER TABLE projects ADD COLUMN approval_status ENUM('pending', 'approved') DEFAULT 'pending'");
    // Set existing projects to approved
    $conn->query("UPDATE projects SET approval_status = 'approved'");
    echo "Column approval_status added to projects (default pending). Existing projects marked approved.<br>";
} else {
    echo "Column approval_status already exists.<br>";
}

echo "Database update for Office Workflow complete.";
?>
