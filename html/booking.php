<?php
//include 'header.php';
include 'calendar_functions.php';
require_once 'x-function/config.php';
session_start();
require_once 'x-function/config.php';
// Fetch bookings
$result = $conn->query("SELECT * FROM bookings");

// Check if user_id is set in session
if (!isset($_SESSION['user_id'])) {
    // If not, redirect to login page
    header("Location: login.php");
    exit(); // Ensure that the script stops executing after the redirect
}else{
    // Check if user_id is set in session
    if ($_SESSION['role'] != 'admin' ) {
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
        <table class="table table-bordered mt-5">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Massage Length</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= $row['massage_length'] ?> minutes</td>
                        <td><?= $row['event_date'] ?></td>
                        <td><?= $row['event_time'] ?></td>
                        <td>
                            <button
                                class="btn btn-warning btn-sm edit-button"
                                data-bs-toggle="modal"
                                data-bs-target="#editModal"
                                data-id="<?= $row['id'] ?>"
                                data-name="<?= htmlspecialchars($row['name']) ?>"
                                data-email="<?= htmlspecialchars($row['email']) ?>"
                                data-massage-length="<?= $row['massage_length'] ?>"
                                data-event-date="<?= $row['event_date'] ?>"
                                data-event-time="<?= $row['event_time'] ?>">
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

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
                </div>
                <div class="modal-body">
                    <form id="editForm" action="x-function/update_booking.php" method="POST">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_massage_length" class="form-label">Massage Length (minutes)</label>
                            <select class="form-control" id="edit_massage_length" name="massage_length" required>
                                <option value="30">Nack-, ansikts och skalpmassage 30 min</option>
                                <option value="30">Svensk klassisk massage 30 min</option>
                                <option value="45">Svensk klassisk massage 45 min</option>
                                <option value="60">Svensk klassisk massage 60 min</option>
                                <option value="90">Svensk klassisk massage 90 min</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_event_date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="edit_event_date" name="event_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_event_time" class="form-label">Time</label>
                            <input type="time" class="form-control" id="edit_event_time" name="event_time" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Booking</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fill the modal with data
        document.querySelectorAll('.edit-button').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('edit_id').value = this.dataset.id;
                document.getElementById('edit_name').value = this.dataset.name;
                document.getElementById('edit_email').value = this.dataset.email;
                document.getElementById('edit_massage_length').value = this.dataset.massageLength;
                document.getElementById('edit_event_date').value = this.dataset.eventDate;
                document.getElementById('edit_event_time').value = this.dataset.eventTime;
            });
        });
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');

                if (confirm('Are you sure you want to delete this event?')) {
                    fetch('x-function/delete_booking.php', {
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