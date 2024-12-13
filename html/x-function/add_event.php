<?php
require_once 'config.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if it's a request for checking the event time gap or inserting a booking
    if (isset($_POST['event_date_time']) && isset($_POST['massage_length'])) {
        // Get the event date, time, and massage length from the AJAX request
        $eventDateTime = $_POST['event_date_time'];
        $massageLength = $_POST['massage_length'];

        // Split the datetime string into date and time
        list($event_date, $event_time) = explode(' ', $eventDateTime);
        $newEventStartTime = strtotime($event_date . ' ' . $event_time); // Start time of new event
        
        // Add the massage length (converted to seconds) to calculate the event's end time
        $newEventEndTime = $newEventStartTime + ($massageLength * 60); // Add massage length in seconds

        // Add 30 minutes buffer to the end time for the next available slot
        $newEventEndTime += 1800; // 30 minutes buffer (1800 seconds)

        // Query to fetch all event times from the database for the same date
        $sql = "SELECT event_date, event_time, massage_length FROM bookings WHERE event_date = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $event_date);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($existingEventDate, $existingEventTime, $existingMassageLength);

            // Check if any existing events are too close (less than 30 minutes gap)
            $hasConflict = false;
            while ($stmt->fetch()) {
                // Combine the existing event date and time to get the full datetime
                $existingEventStartTime = strtotime($existingEventDate . ' ' . $existingEventTime);
                $existingEventEndTime = $existingEventStartTime + ($existingMassageLength * 60); // Existing event's end time
                $existingEventEndTime += 1800; // Add the 30-minute buffer to the end time

                // Check if the new event overlaps with any existing events
                if (($newEventStartTime < $existingEventEndTime && $newEventEndTime > $existingEventStartTime)) {
                    $hasConflict = true;
                    break;
                }
            }

            // Return a response based on whether there is a conflict
            if ($hasConflict) {
                echo "conflict";  // Indicate that there is a conflict
            } else {
                echo "no_conflict";  // No conflict, it's safe to insert
            }

            // Close the select statement
            $stmt->close();
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        // Handle booking insertion if no conflict
        $name = $_POST['name'];
        $email = $_POST['email'];
        $massage_length = $_POST['massage_length'];
        $event_date = $_POST['event_date'];
        $event_time = $_POST['event_time'];

        // Validate the data (for simplicity, assume data is valid)
        if (empty($name) || empty($email) || empty($massage_length) || empty($event_date) || empty($event_time)) {
            echo "All fields are required!";
            exit;
        }

        // Insert the new booking into the database
        $insertSql = "INSERT INTO bookings (name, email, massage_length, event_date, event_time) 
                      VALUES (?, ?, ?, ?, ?)";

        if ($insertStmt = $conn->prepare($insertSql)) {
            $insertStmt->bind_param("ssiss", $name, $email, $massage_length, $event_date, $event_time);

            if ($insertStmt->execute()) {
                echo "Booking successfully added!";
            } else {
                echo "Error: " . $insertStmt->error;
            }

            // Close the insert statement
            $insertStmt->close();
        } else {
            echo "Error: " . $conn->error;
        }
    }
}

// Close the database connection
$conn->close();
?>
