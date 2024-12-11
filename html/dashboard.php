<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar/main.min.css">
    <style>
        /* Custom styles for event colors */
        .fc-event {
            border: none;
            border-radius: 4px;
            font-size: 14px;
            text-align: center;
            line-height: 20px;
            padding: 2px;
        }

        .fc-event-green {
            background-color: green;
            color: white;
        }

        .fc-event-gray {
            background-color: gray;
            color: white;
        }

        .fc-event-red {
            background-color: red;
            color: white;
        }

        #booking-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        #booking-modal form {
            background: white;
            padding: 20px;
            border-radius: 5px;
            width: 300px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>Your role: <?php echo htmlspecialchars($_SESSION['role']); ?></p>
        <a href="logout.php" class="btn btn-danger">Logout</a>

        <div id="calendar"></div>

        <!-- Booking Modal -->
        <div id="booking-modal">
            <form id="booking-form">
                <h3>Book Your Appointment</h3>
                <input type="text" name="name" placeholder="Name" required class="form-control mb-2">
                <input type="email" name="email" placeholder="Email" required class="form-control mb-2">
                <input type="hidden" name="date">
                <input type="hidden" name="start_time">
                <select name="duration" required class="form-control mb-2">
                    <option value="30">30 Minutes</option>
                    <option value="45">45 Minutes</option>
                    <option value="60">60 Minutes</option>
                    <option value="90">90 Minutes</option>
                </select>
                <button type="submit" class="btn btn-primary mt-2">Book Now</button>
                <button type="button" id="close-modal" class="btn btn-secondary mt-2">Close</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar/main.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                initialView: 'timeGridWeek', // Display the timeGridWeek view
                slotMinTime: "08:00:00", // Start time of the time grid
                slotMaxTime: "21:00:00", // End time of the time grid
                allDaySlot: false, // Disable all-day slot
                events: 'x-function/get-events.php', // Fetch events from the server

                // Date click event to show the modal
                dateClick: function(info) {
                    // Open the booking modal
                    console.log(info);
                    const modal = document.getElementById('booking-modal');
                    modal.style.display = 'flex';
                    document.querySelector('[name="date"]').value = info.dateStr;
                    document.querySelector('[name="start_time"]').value = info.dateStr + 'T' + info.timeStr;
                },

                // Display events on the calendar
                eventContent: function (arg) {
                    let content = document.createElement('div');
                    content.innerHTML = arg.event.title;
                    content.style.color = arg.event.extendedProps.textColor || 'white';
                    return { domNodes: [content] };
                }
            });

            // Render the calendar
            calendar.render();

            // Handle form submission for booking
            document.getElementById('booking-form').addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(e.target);

                fetch('x-function/add-booking.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Booking successful!');
                        calendar.refetchEvents(); // Refresh the calendar
                        document.getElementById('booking-modal').style.display = 'none'; // Close the modal
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            });

            // Close the modal
            document.getElementById('close-modal').addEventListener('click', function () {
                document.getElementById('booking-modal').style.display = 'none';
            });
        });
    </script>
</body>
</html>
