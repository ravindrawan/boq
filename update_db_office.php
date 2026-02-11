<?php
include 'includes/db_connect.php';

// 1. Update Users Table
// Add office_name
$check_office = $conn->query("SHOW COLUMNS FROM users LIKE 'office_name'");
if ($check_office->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN office_name VARCHAR(100) AFTER role");
    echo "Column office_name added to users.<br>";
}

// Update Role Enum
// Note: modifying ENUM can be tricky, we'll use MODIFY COLUMN to redefine it
$conn->query("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'office', 'supervisor') DEFAULT 'office'");
echo "User roles updated (admin, office, supervisor).<br>";

// 2. Update Projects Table
// Add office_name
$check_p_office = $conn->query("SHOW COLUMNS FROM projects LIKE 'office_name'");
if ($check_p_office->num_rows == 0) {
    $conn->query("ALTER TABLE projects ADD COLUMN office_name VARCHAR(100) AFTER project_name");
    echo "Column office_name added to projects.<br>";
}

// Add approval_status
$check_approval = $conn->query("SHOW COLUMNS FROM projects LIKE 'approval_status'");
if ($check_approval->num_rows == 0) {
    $conn->query("ALTER TABLE projects ADD COLUMN approval_status ENUM('pending', 'approved') DEFAULT 'pending' AFTER status");
    // Set existing projects to approved
    $conn->query("UPDATE projects SET approval_status = 'approved'");
    echo "Column approval_status added to projects (default pending). Existing projects marked approved.<br>";
}

echo "Database update for Office Workflow complete.";
?>
