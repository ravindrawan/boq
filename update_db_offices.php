<?php
include 'includes/db_connect.php';

// Create offices table
$sql = "CREATE TABLE IF NOT EXISTS offices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    office_name VARCHAR(100) NOT NULL UNIQUE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table offices created successfully.<br>";
    
    // Insert some default offices if empty
    $check = $conn->query("SELECT * FROM offices");
    if ($check->num_rows == 0) {
        $defaults = ["Head Office", "Kurunegala", "Puttalam", "Kuliyapitiya"];
        foreach ($defaults as $off) {
            $conn->query("INSERT INTO offices (office_name) VALUES ('$off')");
        }
        echo "Default offices inserted.<br>";
    }
} else {
    echo "Error creating table: " . $conn->error;
}
?>
