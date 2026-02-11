<?php
include 'db_connect.php';

echo "<h2>Tables in boq_db</h2>";
$result = $conn->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_array()) {
        echo "<h3>Table: " . $row[0] . "</h3>";
        $columns = $conn->query("DESCRIBE " . $row[0]);
        if ($columns) {
            echo "<ul>";
            while ($col = $columns->fetch_assoc()) {
                echo "<li>" . $col['Field'] . " (" . $col['Type'] . ")</li>";
            }
            echo "</ul>";
        }
    }
} else {
    echo "Error showing tables: " . $conn->error;
}
?>
