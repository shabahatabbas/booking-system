<?php
require_once 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $massage_length = $_POST['massage_length'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];

    $query = "UPDATE bookings SET 
               name = '$name', 
                email = '$email', 
                massage_length = $massage_length, 
                event_date = '$event_date', 
                event_time = '$event_time' 
              WHERE id = $id";

    if ($conn->query($query)) {
        header('Location: ../booking.php');
        exit;
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>
