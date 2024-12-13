<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $event_time = $_POST['event_time'];
    $description = $_POST['description'];

    // Validate input
    if (empty($id) || empty($event_time)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE event_times SET event_time = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssi", $event_time, $description, $id);
    $result = $stmt->execute();

    if ($result) {
        echo json_encode([
            'success' => true,
            'id' => $id,
            'event_time' => $event_time,
            'description' => $description
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update event.']);
    }
    $stmt->close();
}
?>
