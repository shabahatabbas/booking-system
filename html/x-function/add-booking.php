<?php
require_once 'config.php';


$date = $_POST['date'];
$start_time = $_POST['start_time'];
$duration = $_POST['duration'];
$end_time = date("H:i:s", strtotime($start_time) + ($duration * 60));
$name = $_POST['name'];
$email = $_POST['email'];

// Check for conflicts
$query = "SELECT * FROM bookings WHERE date='$date' AND (
    ('$start_time' BETWEEN start_time AND end_time) OR
    ('$end_time' BETWEEN start_time AND end_time) OR
    (start_time BETWEEN '$start_time' AND '$end_time')
)";

$result = $conn->query($query);

if ($result->num_rows == 0) {
    $insertQuery = "INSERT INTO bookings (date, start_time, end_time, customer_name, customer_email, duration) 
                    VALUES ('$date', '$start_time', '$end_time', '$name', '$email', '$duration')";
    if ($conn->query($insertQuery)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Time slot not available']);
}
?>
