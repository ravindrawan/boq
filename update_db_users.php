<?php
include 'includes/db_connect.php';

// Check if 'users' table exists, if not create it
$sql_create = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'office') DEFAULT 'office',
    theme_color VARCHAR(20) DEFAULT 'blue',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_create) === TRUE) {
    echo "Table users checked/created<br>";
} else {
    echo "Error creating table users: " . $conn->error . "<br>";
}

// Add theme_color column if it doesn't exist
$check_col = $conn->query("SHOW COLUMNS FROM users LIKE 'theme_color'");
if ($check_col->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN theme_color VARCHAR(20) DEFAULT 'blue'");
    echo "Column theme_color added<br>";
}

// Add role column if it doesn't exist
$check_role = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($check_role->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN role ENUM('admin', 'office') DEFAULT 'office'");
    echo "Column role added<br>";
}

// Ensure at least one admin exists
$result = $conn->query("SELECT * FROM users WHERE username='admin'");
if ($result->num_rows == 0) {
    $pass = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username, password, role, theme_color) VALUES ('admin', '$pass', 'admin', 'blue')");
    echo "Default admin user created (admin / admin123)<br>";
}

echo "User table updated!";
?>
