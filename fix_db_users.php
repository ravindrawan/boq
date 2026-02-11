<?php
include 'includes/db_connect.php';

// Check for created_at column
$check_col = $conn->query("SHOW COLUMNS FROM users LIKE 'created_at'");
if ($check_col->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    echo "Column created_at added.<br>";
} else {
    echo "Column created_at already exists.<br>";
}

echo "Database fix complete.";
?>
