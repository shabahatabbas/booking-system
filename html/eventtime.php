<?php
//include 'header.php';
include 'calendar_functions.php';
require_once 'x-function/config.php';
session_start();
$sql = "SELECT * FROM event_times ORDER BY event_time ASC";
$result = $conn->query($sql);

// Generate event times with half-hour intervals
$startTime = strtotime("08:00");
$endTime = strtotime("23:00");
$eventTimes = [];

while ($startTime <= $endTime) {
    $eventTimes[] = date("H:i", $startTime);
    $startTime = strtotime('+30 minutes', $startTime);
}

// Check if user_id is set in session
if (!isset($_SESSION['user_id'])) {
    // If not, redirect to login page
    header("Location: login.php");
    exit(); // Ensure that the script stops executing after the redirect
} else {
    // Check if user_id is set in session
    if ($_SESSION['role'] != 'admin') {
        // If not, redirect to login page
        header("Location: login.php");
        exit(); // Ensure that the script stops executing after the redirect
    }
}

?>


<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="UTF-8">
    <title>Bokningskalender</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<style>
    .btn-outline-primary {
        color: #285E53;
        border-color: #285E53;
    }

    .btn-outline-primary:hover {
        color: white;
        background-color: #285E53;
        border-color: #285E53;
    }
</style>

<body>

    <?php include 'header.php'; ?>

    <div class="container-fluid mt-5">
        <button type="button" class="btn btn-success mb-2 mt-5" data-bs-toggle="modal" data-bs-target="#addEventModal">
            Add Off Timing
        </button>


        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Event Time</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['event_time']; ?></td>
                        <td><?php echo $row['description']; ?></td>
                        <td>
                            <button
                                class="btn btn-warning btn-sm edit-btn"
                                data-id="<?php echo $row['id']; ?>"
                                data-time="<?php echo $row['event_time']; ?>"
                                data-description="<?php echo $row['description']; ?>"
                                data-bs-toggle="modal"
                                data-bs-target="#editEventModal">
                                Edit
                            </button>

                            <button
                                class="btn btn-danger btn-sm delete-btn"
                                data-id="<?php echo $row['id']; ?>">
                                Delete
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>


    <!-- Add Event Modal -->
    <div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEventModalLabel">Add Event Time</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
                </div>
                <div class="modal-body">
                    <form id="addEventForm" method="POST" action="add_event.php">
                        <div class="mb-3">
                            <label for="event_time" class="form-label">Event Time</label>
                            <select name="event_time" id="event_time" class="form-control" required>
                                <option value="" disabled selected>Select Event Time</option>
                                <?php foreach ($eventTimes as $time): ?>
                                    <option value="<?php echo $time; ?>"><?php echo $time; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" name="description" id="description" class="form-control" placeholder="Optional description">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add Event</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Event Modal -->
    <div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEventModalLabel">Edit Event Time</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
                </div>
                <div class="modal-body">
                    <form id="editEventForm">
                        <input type="hidden" name="id" id="edit-id">
                        <div class="mb-3">
                            <label for="edit-event-time" class="form-label">Event Time</label>
                            <input type="text" name="event_time" id="edit-event-time" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-description" class="form-label">Description</label>
                            <input type="text" name="description" id="edit-description" class="form-control" placeholder="Optional description">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('addEventForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('x-function/addtime.php', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === '') {
                        // Close the modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addEventModal'));
                        modal.hide();

                        // Reload the table or add the new row dynamically
                        location.reload(); // Replace with dynamic table update logic if needed
                    } else {
                        alert('Error: ' + data);
                    }
                })
                .catch(error => console.error('Error:', error));
        });

        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const eventTime = this.getAttribute('data-time');
                const description = this.getAttribute('data-description');

                // Populate the form fields
                document.getElementById('edit-id').value = id;
                document.getElementById('edit-event-time').value = eventTime;
                document.getElementById('edit-description').value = description;
            });
        });
        document.getElementById('editEventForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('x-function/edit_time.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the row dynamically
                        const row = document.querySelector(`button[data-id="${data.id}"]`).closest('tr');
                        row.querySelector('td:nth-child(1)').textContent = data.event_time;
                        row.querySelector('td:nth-child(2)').textContent = data.description;

                        // Close the modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editEventModal'));
                        modal.hide();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        });

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');

                if (confirm('Are you sure you want to delete this event?')) {
                    fetch('x-function/delete_time.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                id: id
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Remove the row dynamically
                                this.closest('tr').remove();
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                }
            });
        });
    </script>

</body>

</html>