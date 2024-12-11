<?php
require_once 'config.php';

$startDate = $_GET['start'];
$endDate = $_GET['end'];

// Fetch availability
$availabilityQuery = "SELECT * FROM availability WHERE date BETWEEN '$startDate' AND '$endDate'";
$availabilityResult = $conn->query($availabilityQuery);

// Fetch bookings
$bookingsQuery = "SELECT * FROM bookings WHERE date BETWEEN '$startDate' AND '$endDate'";
$bookingsResult = $conn->query($bookingsQuery);

$events = [];

// Add availability as green blocks
while ($row = $availabilityResult->fetch_assoc()) {
    $events[] = [
        'title' => 'Available',
        'start' => $row['date'] . 'T' . $row['start_time'],
        'end' => $row['date'] . 'T' . $row['end_time'],
        'color' => 'green',
        'editable' => false
    ];
}

// Add bookings as red blocks
while ($row = $bookingsResult->fetch_assoc()) {
    $events[] = [
        'title' => 'Booked',
        'start' => $row['date'] . 'T' . $row['start_time'],
        'end' => $row['date'] . 'T' . $row['end_time'],
        'color' => 'red',
        'editable' => false
    ];
}

echo json_encode($events);
?>
