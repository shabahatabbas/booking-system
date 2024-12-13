<?php
//include 'header.php';
include 'calendar_functions.php';
require_once 'x-function/config.php';
session_start(); // Start the session

// Check if user_id is set in session
if (!isset($_SESSION['user_id'])) {
    // If not, redirect to login page
    header("Location: login.php");
    exit(); // Ensure that the script stops executing after the redirect
}

// Hämta aktuell vecka, månad och år från GET, eller sätt till nuvarande vecka, månad och år
$currentWeek = isset($_GET['week']) && is_numeric($_GET['week']) ? (int)$_GET['week'] : date('W');
/*if($currentWeek < $currentWeek - 1) {
    $currentWeek = date('W');
} */
// Hämta månad och år från URL (GET) eller använd nuvarande månad och år
//$month = isset($_GET['month']) && is_numeric($_GET['month']) ? (int)$_GET['month'] : date('n');  // Standard till aktuell månad
$year = isset($_GET['year']) && is_numeric($_GET['year']) ? (int)$_GET['year'] : date('Y');

$monthOfWeek = (new DateTime())->setISODate($year, $currentWeek)->format('m');
// echo $monthOfWeek;

// Regler för att det ska hålla sig mellan 1 och 52 veckor per år.
$nextWeek = $currentWeek + 1 > 52 ? 1 : $currentWeek + 1;
$prevWeek = $currentWeek - 1 < 1 ? 52 : $currentWeek - 1;

$firstWeekOfNextMonth = getFirstWeekOfNextMonth($year, $monthOfWeek);
$firstWeekOfPreviousMonth = getFirstWeekOfPreviousMonth($year, $monthOfWeek);

//if user goes into previous month. the view gets updated automatically
/////////////////

//echo "Första veckan i nästa månad: $firstWeekOfNextMonth<br>";
//echo "Första veckan i föregående månad: $firstWeekOfPreviousMonth<br>";

//////////////////

// Hämta start- och slutdatum för veckan med DateTime::setISODate()
//$currentWeek = date('F Y');

$startOfWeek = new DateTime();
$startOfWeek->setISODate($year, $currentWeek); // Sätter datumet till måndagen i den aktuella veckan


// Klona startdatumet och lägg till 6 dagar för att få slutdatumet (söndag)
$endOfWeek = clone $startOfWeek;
$endOfWeek->modify('+6 days');


// echo $startOfWeek->format('d');
// echo $endOfWeek->format('d');

//$currentWeek->setISODate($year, $currentWeek);

////////////////////////

$currentWeekMonth = (int)$currentWeek; // Hämta månaden för nuvarande vecka
$currentWeekYear = $year; // Året för den valda veckan


// Om den valda veckan tillhör en annan månad, uppdatera då månad och år
if ($currentWeekMonth != $monthOfWeek || $currentWeekYear != $year) {
    $monthOfWeek = $currentWeekMonth;
    $year = $currentWeekYear;
}


// Justera månad och år baserat på aktuell navigering
if (isset($_GET['nextMonth'])) {
    if ($monthOfWeek == 12) {
        $monthOfWeek = 1;  // Sätt månaden till januari (1)
        $year += 1;     // Inkrementera året med 1
    } else {
        $monthOfWeek++;    // Annars bara öka månaden med 1
    }
}

if (isset($_GET['prevMonth'])) {
    if ($monthOfWeek == 1) {
        $monthOfWeek = 12;
        $year -= 1;
    } else {
        $monthOfWeek--;
    }
}
///////////
//$currentWeek->setISODate($year, $currentWeek);
//echo $date->format('Y-m-d'); // This will print the date in 'Year-Month-Day' formatsss
// Startdatum (måndag)
//$startOfWeek = $currentWeek->format('d-m-y');

// Slutdatum (söndag)
//$endOfWeek = $currentWeek->modify('+6 days')->format('d-m-Y');
//$monthOfWeek = $currentWeek->format('m'); // 'm' will give you the month in two digits (e.g., '11' for November)

// Hämta kalenderdata (för månad och navigering)
$calendarData = getCalendarData($monthOfWeek, $year, $currentWeek);

echo "</table>";


// Använd kalenderdata för att hämta månad och år samt föregående och nästa månad
$monthOfWeek = $calendarData['month'];
$year = $calendarData['year'];
$prevMonth = $calendarData['prevMonth'];
$nextMonth = $calendarData['nextMonth'];
$prevYear = $calendarData['prevYear'];
$nextYear = $calendarData['nextYear'];

// Fetch events for the current week
$events = fetchEvents($startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d'));

// echo $events;
// Fetch events function
function fetchEvents($startDate, $endDate)
{
    global $conn; // Database connection
    $query = "SELECT * FROM bookings WHERE event_date BETWEEN '$startDate' AND '$endDate' ORDER BY event_time";
    $result = mysqli_query($conn, $query); // Execute the query

    $events = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $events[] = $row; // Add each row to the events array
        }
    }

    return $events;
}

// Fetch time conditions
$sql = "SELECT event_time, description FROM event_times";
$result = $conn->query($sql);

$lunch_times = [];
while ($row = $result->fetch_assoc()) {
    $lunch_times[$row['event_time']] = $row['description'];
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
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
        <div class="row">

            <!-- Kalender MÅNAD -->
            <div class="monthly-calendar col-md-2 mt-5">
                <h2><?= date('F Y', strtotime("$year-$monthOfWeek-01")) ?></h2> <!-- Display current month -->

                <table>
                    <?php renderMonthlyCalendar($monthOfWeek, $year, $currentWeek, $currentWeekMonth); ?>
                </table>

                <?php echo "Current Week: " . $calendarData['week'] . "<br>";
                echo "First Week of the Current Month: " . $calendarData['firstWeekNextMonth'] . "<br>"; ?>


                <div class="calendar-navigation">
                    <!-- Navigation mellan MÅNADER -->
                    <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>&week=<?= $firstWeekOfPreviousMonth ?>" class="prev">&lt;</a>
                    <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>&week=<?= $firstWeekOfNextMonth ?>" class="next">&gt;</a>
                </div>

            </div>

            <!-- Kalender VECKA -->
            <div class="weekly-calendar col-md-9">

                <!-- Navigation VECKA -->
                <div class="week-navigation">
                    <!-- Föregående vecka -->
                    <a href="?week=<?= $prevWeek ?>&month=<?= $monthOfWeek ?>&year=<?= $year ?>" class="prev">&lt;</a>

                    <!-- Nuvarande Vecka -->
                    <span>Vecka <?= $currentWeek ?></span>

                    <!-- Nästa Vecka -->
                    <a href="?week=<?= $nextWeek ?>&month=<?= $monthOfWeek ?>&year=<?= $year ?>" class="next">&gt;</a>
                </div>

                <table>
                    <!-- Rubrik för veckodagar -->
                    <tr>
                        <th>Tid</th>
                        <th>Måndag</th>
                        <th>Tisdag</th>
                        <th>Onsdag</th>
                        <th>Torsdag</th>
                        <th>Fredag</th>
                        <th>Lördag</th>
                        <th>Söndag</th>
                    </tr>

                    <?php
                    // Create an associative array with event dates and times for fast lookup
                    $eventLookup = [];
                    $datetime    = [];
                    $datetimeend = [];
                    foreach ($events as $event) {
                        $eventLookup[$event['event_date']][$event['event_time']] = $event;

                        // Convert event time to a timestamp
                        $startTimestamp = strtotime($event['event_date'] . ' ' . $event['event_time']);

                        // Add massage_length (in minutes) to the start time
                        $endTimestamp = strtotime("+{$event['massage_length']} minutes", $startTimestamp);

                        // Format the end time back to a readable format
                        $adjustedTime = date('Y-m-d H:i:s', $endTimestamp);

                        // Store the original start time and the adjusted end time
                        $datetime[] = [
                            'start_time' => $event['event_date'] . ' ' . $event['event_time'],
                            'end_time'   => $adjustedTime
                        ];
                    }

                    // Loop through the hours and intervals (e.g., every 30 minutes)
                    for ($hour = 8; $hour <= 21; $hour++):
                        for ($minute = 0; $minute < 60; $minute += 30): // 30-minute intervals
                            $formattedTime = sprintf('%02d:%02d', $hour, $minute);
                            $rowspan = 1;
                    ?>
                            <tr>
                                <td class="hour"><?= $formattedTime ?></td>
                                <?php for ($day = 0; $day < 7; $day++): ?>
                                    <?php
                                    $currentDate = clone $startOfWeek;
                                    $currentDate->modify("+$day days");
                                    $formattedDate = $currentDate->format('Y-m-d');
                                    $eventTime = $formattedTime . ':00'; // Add seconds to the time format
                                    ?>
                                    <div class="slot" data-date="<?= $formattedDate ?>" data-time="<?= $eventTime ?>">
                                        <?php
                                        // Check if an event exists for this time slot
                                        if (isset($eventLookup[$formattedDate][$eventTime])):
                                            // Get the event details
                                            $event = $eventLookup[$formattedDate][$eventTime];

                                            // Get the massage length from the event (assuming this is stored in the database)
                                            $massageLength = $event['massage_length']; // e.g., 30, 45, 60 minutes
                                            // Calculate the start and end time
                                            $startTime = strtotime($eventTime);
                                            $endTime = strtotime("+$massageLength minutes", $startTime);
                                            $endFormattedTime = date('g:i a', $endTime); // Format the end time to AM/PM format
                                            // Determine the number of rows to merge based on the massage length
                                            $mergeRows = ceil($massageLength / 30); // Number of rows the event spans
                                        ?>
                                            <!-- Check if the event spans multiple rows -->
                                            <?php if ($mergeRows > 1): ?>
                                                <!-- Apply rowspan for the first slot -->
                                                <td rowspan="<?= $mergeRows ?>" class="merged-cell" style="vertical-align: middle; text-align: center;">
                                                    <div class="event-name" style="background-color: #f0f0f0; padding: 10px; text-align: center;">
                                                        <?= htmlspecialchars($event['name']) ?> (<?= date('g:i a', strtotime($eventTime)) ?> - <?= $endFormattedTime ?>)
                                                    </div>
                                                </td>
                                            <?php else: ?>
                                                <td>
                                                    <div class="event-name" style="background-color: #f0f0f0; padding: 10px; text-align: center;">
                                                        <?= htmlspecialchars($event['name']) ?> (<?= date('g:i a', strtotime($eventTime)) ?> - <?= $endFormattedTime ?>)
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php
                                            $exists = false;
                                            $current_datetime = $formattedDate . ' ' . $eventTime;

                                            foreach ($datetime as $timeSlot) {
                                                if (strtotime($current_datetime) >= strtotime($timeSlot['start_time']) && strtotime($current_datetime) < strtotime($timeSlot['end_time'])) {
                                                    $exists = true;
                                                    break;
                                                }
                                            }

                                            // Check for specific time range
                                            if (!$exists) {
                                                if (isset($lunch_times[$eventTime])) {
                                                    if ($rowspan == 1) {


                                            ?>
                                                        <td rowspan="1" colspan="7" class="background-color: #f0f0f0; padding: 10px; text-align: center;">
                                                            <?php echo $lunch_times[$eventTime] ?>
                                                        </td>
                                                    <?php
                                                        $rowspan = 2;
                                                    }
                                                } else {
                                                    ?>
                                                    <td>
                                                        <button class="btn btn-outline-primary btn-sm open-modal"
                                                            data-toggle="modal"
                                                            data-target="#eventModal"
                                                            data-date="<?= $formattedDate ?>"
                                                            data-time="<?= $eventTime ?>">
                                                            +
                                                        </button>
                                                    </td>
                                            <?php
                                                }
                                            }
                                            ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endfor; ?>
                            </tr>
                        <?php endfor; ?>
                    <?php endfor; ?>

                </table>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Book a Massage</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Booking Form -->
                    <form action="x-function/add_event.php" method="POST" id="bookingForm">
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="massage_length">Length of Massage (minutes):</label>
                            <select class="form-control" id="massage_length" name="massage_length" required>
                                <option value="30">Nack-, ansikts och skalpmassage 30 min</option>
                                <option value="30">Svensk klassisk massage 30 min</option>
                                <option value="45">Svensk klassisk massage 45 min</option>
                                <option value="60">Svensk klassisk massage 60 min</option>
                                <option value="90">Svensk klassisk massage 90 min</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="event_date">Date:</label>
                            <input type="text" class="form-control" id="event_date" name="event_date" readonly>
                        </div>
                        <div class="form-group">
                            <label for="event_time">Preferred Time:</label>
                            <input type="time" class="form-control" id="event_time" name="event_time" readonly>
                        </div>
                        <button type="submit" class="btn btn-primary">Book Massage</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // When the "open-modal" button is clicked
            $(".open-modal").click(function() {
                // Get the selected slot's date and time
                const date = $(this).data('date');
                const time = $(this).data('time');

                // Autofill the form fields
                $('#event_date').val(date); // Set the date (readonly)
                $('#event_time').val(time); // Set the time

                // Enforce 30-minute gaps by calculating the next available time slot
                const timeSlot = $(this).data('time');
                const timeArray = timeSlot.split(':');
                let hour = parseInt(timeArray[0]);
                let minute = parseInt(timeArray[1]);

            });
            $('#bookingForm').submit(function(e) {
                e.preventDefault(); // Prevent the form from submitting normally

                var formData = $(this).serialize(); // Serialize the form data

                // Extract the event date, time, and massage length from the form data for the check
                var eventDate = $("input[name='event_date']").val(); // Adjust the selector as needed
                var eventTime = $("input[name='event_time']").val(); // Adjust the selector as needed
                var massageLength = $("select[name='massage_length']").val(); // Massage length
                var newEventDateTime = eventDate + ' ' + eventTime;
                // Send a request to check for the 30-minute gap
                $.ajax({
                    type: 'POST',
                    url: 'x-function/add_event.php', // PHP file to check the event time gap
                    data: {
                        event_date_time: newEventDateTime,
                        massage_length: massageLength
                    },
                    success: function(response) {
                        if (response === 'no_conflict') {
                            // If no conflict, submit the form data
                            $.ajax({
                                type: 'POST',
                                url: 'x-function/add_event.php', // The PHP file to handle the form submission
                                data: formData,
                                success: function(response) {
                                    // Handle the response from PHP
                                    alert(response); // For simplicity, just show the response in an alert
                                    $('#eventModal').modal('hide'); // Close the modal
                                    $('#bookingForm')[0].reset(); // Reset the form fields
                                    location.reload();
                                },
                                error: function() {
                                    alert('Error submitting the form.');
                                }
                            });
                        } else {
                            // If there is a conflict, show an alert
                            alert('Error: There must be at least a 30-minute gap between events.');
                        }
                    },
                    error: function() {
                        alert('Error checking event time.');
                    }
                });
            });



        });
    </script>
</body>

</html>