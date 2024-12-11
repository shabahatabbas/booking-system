<?php
require_once 'config.php';


$date = $_POST['date'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];

$query = "INSERT INTO availability (date, start_time, end_time) VALUES ('$date', '$start_time', '$end_time')";

if ($conn->query($query)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
}
?>
