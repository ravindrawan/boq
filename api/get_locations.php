<?php
include '../includes/db_connect.php';

$type = isset($_GET['type']) ? $_GET['type'] : '';

if ($type == 'districts') {
    $sql = "SELECT DISTINCT district FROM nwpgnd ORDER BY district ASC";
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row['district'];
    }
    echo json_encode($data);

} elseif ($type == 'ds') {
    $district = isset($_GET['district']) ? $_GET['district'] : '';
    $sql = "SELECT DISTINCT dsd FROM nwpgnd WHERE district = '$district' ORDER BY dsd ASC";
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row['dsd'];
    }
    echo json_encode($data);

} elseif ($type == 'gn') {
    $ds = isset($_GET['ds']) ? $_GET['ds'] : '';
    $sql = "SELECT gnd_name FROM nwpgnd WHERE dsd = '$ds' ORDER BY gnd_name ASC";
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row['gnd_name'];
    }
    echo json_encode($data);
}
?>
