<?php
require_once __DIR__ . '/../config.php';

$conn = db_mysqli();

if (isset($_GET['tour_id'])) {
    $tour_id = $_GET['tour_id'];
    $sql = "SELECT * FROM tours WHERE tour_id = '$tour_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $tour = $result->fetch_assoc();
        echo json_encode($tour);
    } else {
        echo json_encode([]);
    }
}

$conn->close();
