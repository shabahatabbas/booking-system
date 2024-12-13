<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_time = $_POST['event_time'];
    $description = $_POST['description'];

    $sql = "INSERT INTO event_times (event_time, description) VALUES ('$event_time', '$description')";
    if ($conn->query($sql) === TRUE) {
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
