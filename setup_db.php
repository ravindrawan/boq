<?php
include 'db_connect.php';

// Drop existing tables if they exist (except users and nwpgnd)
$conn->query("DROP TABLE IF EXISTS project_photos");
$conn->query("DROP TABLE IF EXISTS projects");
$conn->query("DROP TABLE IF EXISTS funding_sources");
$conn->query("DROP TABLE IF EXISTS project_types");

// Table: project_types
$sql = "CREATE TABLE project_types (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(255) NOT NULL
)";
if ($conn->query($sql) === TRUE) {
    echo "Table project_types created successfully<br>";
} else {
    echo "Error creating table project_types: " . $conn->error . "<br>";
}

// Table: funding_sources
$sql = "CREATE TABLE funding_sources (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    source_name VARCHAR(255) NOT NULL
)";
if ($conn->query($sql) === TRUE) {
    echo "Table funding_sources created successfully<br>";
} else {
    echo "Error creating table funding_sources: " . $conn->error . "<br>";
}

// Table: projects
$sql = "CREATE TABLE projects (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    project_name VARCHAR(255) NOT NULL,
    project_type_id INT(11),
    district VARCHAR(100),
    ds_division VARCHAR(100),
    gn_division VARCHAR(100),
    contractor_name VARCHAR(255),
    cida_reg_no VARCHAR(100),
    agreement_no VARCHAR(100),
    cida_grade VARCHAR(50),
    start_date DATE,
    contract_period_months INT(11),
    completion_date DATE,
    contract_amount DECIMAL(15, 2),
    estimate_cost DECIMAL(15, 2),
    funding_source_id INT(11),
    physical_progress INT(11) DEFAULT 0,
    financial_progress INT(11) DEFAULT 0,
    delay_status ENUM('None', 'Delayed') DEFAULT 'None',
    delay_reason TEXT,
    extended_date DATE,
    boq_file VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_type_id) REFERENCES project_types(id),
    FOREIGN KEY (funding_source_id) REFERENCES funding_sources(id)
)";
if ($conn->query($sql) === TRUE) {
    echo "Table projects created successfully<br>";
} else {
    echo "Error creating table projects: " . $conn->error . "<br>";
}

// Table: project_photos
$sql = "CREATE TABLE project_photos (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    project_id INT(11),
    photo_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
    echo "Table project_photos created successfully<br>";
} else {
    echo "Error creating table project_photos: " . $conn->error . "<br>";
}

$conn->close();
?>
