<?php
include 'includes/db_connect.php';

// 1. Create cida_grades table
$sql = "CREATE TABLE IF NOT EXISTS cida_grades (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    grade_name VARCHAR(50) NOT NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Table cida_grades created successfully<br>";
    
    // Insert some default grades
    $defaults = ['C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'C7', 'C8', 'C9'];
    foreach ($defaults as $grade) {
        $conn->query("INSERT INTO cida_grades (grade_name) SELECT '$grade' WHERE NOT EXISTS (SELECT 1 FROM cida_grades WHERE grade_name = '$grade')");
    }
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// 2. ALTER projects table
// First, check if column exists
$check = $conn->query("SHOW COLUMNS FROM projects LIKE 'cida_grade_id'");
if ($check->num_rows == 0) {
    // Drop old column if needed, or just add new one
    // We'll drop the old 'cida_grade' string column and add 'cida_grade_id'
    $conn->query("ALTER TABLE projects DROP COLUMN cida_grade");
    $conn->query("ALTER TABLE projects ADD COLUMN cida_grade_id INT(11) AFTER agreement_no");
    $conn->query("ALTER TABLE projects ADD FOREIGN KEY (cida_grade_id) REFERENCES cida_grades(id)");
    echo "Table projects altered successfully<br>";
} else {
    echo "Column cida_grade_id already exists<br>";
}

echo "Database updated!";
?>
