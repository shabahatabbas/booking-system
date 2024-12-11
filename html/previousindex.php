<?php

// Funktion för att beräkna månad och år samt föregående och nästa månad
function getCalendarData($month, $year, $currentWeek) {
    // Säkerställ att månad och år är giltiga
    $week = isset($_GET['week']) && is_numeric($_GET['week']) && $_GET['week'] >= 1 && $_GET['week'] <= 52 ? (int)$_GET['week'] : date('W');
    $month = isset($_GET['month']) && is_numeric($_GET['month']) && $_GET['month'] >= 1 && $_GET['month'] <= 12 ? (int)$_GET['month'] : date('n'); // Standard till aktuell månad
    $year = isset($_GET['year']) && is_numeric($_GET['year']) ? (int)$_GET['year'] : date('Y'); // Standard till aktuell år

    // Beräkna föregående och nästa månad
    $nextWeek = $currentWeek + 1 > 52 ? 1 : $currentWeek + 1;
    $prevWeek = $currentWeek - 1 < 1 ? 52 : $currentWeek - 1;

    // Beräkna föregående och nästa månad
    $prevMonth = $month == 1 ? 12 : $month - 1;
    $nextMonth = $month == 12 ? 1 : $month + 1;
    $prevYear = $month == 1 ? $year - 1 : $year;
    $nextYear = $month == 12 ? $year + 1 : $year;

    // Returnera månad, år, och föregående och nästa månad
    return [
        'week' => $week,
        'month' => $month,
        'year' => $year,
        'prevWeek' => $prevWeek,
        'nextWeek' => $nextWeek,
        'prevMonth' => $prevMonth,
        'nextMonth' => $nextMonth,
        'prevYear' => $prevYear,
        'nextYear' => $nextYear
    ];
}

// Funktion för att rendera månads-kalendern
function renderMonthlyCalendar($month, $year, $currentWeek) {
    echo "<p>Aktuell vecka: $currentWeek</p>";  // This will display the current week number
    echo "<p>Vecka $currentWeek tillhör månad: $month</p>";

    // Hämta den första dagen i månaden (1 = Måndag, 7 = Söndag)
    $firstDayOfMonth = date('N', strtotime("$year-$month-01"));
    
    // Hämta antalet dagar i månaden
    $daysInMonth = date('t', strtotime("$year-$month-01"));

    // Starta tabellen för kalendern
    echo "<table>"; // Added <table> tag

    // Lägg till tomma celler för dagarna innan den första dagen i månaden
    echo "<tr>"; // Start a new row for the calendar
    for ($i = 1; $i < $firstDayOfMonth; $i++) {
        echo "<td></td>"; // Empty cells before the first day of the month
    }

    // Loopa genom varje dag i månaden och visa den
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $weekNumber = date('W', strtotime("$year-$month-$day"));

        // Kolla om den här dagen tillhör den valda veckan
        $highlightClass = ($weekNumber == $currentWeek) ? 'highlight-week' : '';

        // Starta en ny rad efter 7 dagar (veckoslutsdag)
        if (($day + $firstDayOfMonth - 2) % 7 == 0) {
            echo "</tr><tr>";
        }
        
        // Lägg till cell för den här dagen
        echo "<td class='$highlightClass'>$day</td>";
    }

    // Lägg till tomma celler efter den sista dagen i månaden för att fylla den sista raden (om nödvändigt)
    $remainingCells = (7 - (($daysInMonth + $firstDayOfMonth - 1) % 7)) % 7;
    for ($i = 0; $i < $remainingCells; $i++) {
        echo "<td></td>";
    }

    // Stäng sista raden
    echo "</tr>";
    echo "</table>"; // Close the table tag
}
?>

<?php
// Hämta den aktuella veckan, månaden och året från GET eller sätt till nuvarande vecka/månad/år
$currentWeek = isset($_GET['week']) && is_numeric($_GET['week']) ? (int)$_GET['week'] : date('W');
$monthOfWeek = isset($_GET['month']) && is_numeric($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) && is_numeric($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Beräkna föregående och nästa vecka samt månad
$calendarData = getCalendarData($monthOfWeek, $year, $currentWeek);

// Tilldela värden från kalenderdata
$monthOfWeek = $calendarData['month'];
$year = $calendarData['year'];
$prevMonth = $calendarData['prevMonth'];
$nextMonth = $calendarData['nextMonth'];
$prevYear = $calendarData['prevYear'];
$nextYear = $calendarData['nextYear'];
$prevWeek = $calendarData['prevWeek'];
$nextWeek = $calendarData['nextWeek'];

?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Bokningskalender</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <!-- Månadsvis kalender -->
    <div class="monthly-calendar">
        <h2><?= date('F Y', strtotime("$year-$monthOfWeek-01")) ?></h2> <!-- Visa aktuell månad -->
        <table>
            <?php renderMonthlyCalendar($monthOfWeek, $year, $currentWeek); ?>
        </table>

        <div class="calendar-navigation">
            <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="prev">&lt;</a>
            <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="next">&gt;</a>
        </div>
    </div>

    <!-- Veckokalender -->
    <div class="weekly-calendar">
    
        <!-- Veckoinformation och navigering -->
        <div class="week-navigation">
            <!-- Previous week link -->
            <a href="?week=<?= $prevWeek ?>&month=<?= $monthOfWeek ?>&year=<?= $year ?>" class="prev">&lt;</a>
        
            <!-- Current week display -->
            <span>Vecka <?= $currentWeek ?></span>

            <!-- Next week link -->
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

            <!-- Tidsblock för dagen -->
            <?php for ($hour = 8; $hour <= 21; $hour++): ?>
                <tr>
                    <td class="hour"><?= sprintf('%02d:00', $hour) ?></td>
                    <?php for ($day = 0; $day < 7; $day++): ?>
                        <td></td>
                    <?php endfor; ?>
                </tr>
                <tr>
                    <td class="hour"><?= sprintf('%02d:30', $hour) ?></td>
                    <?php for ($day = 0; $day < 7; $day++): ?>
                        <td></td>
                    <?php endfor; ?>
                </tr>
            <?php endfor; ?>
        </table>
    </div>
</div>

</body>
</html>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Bokningskalender</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <!-- Månadsvis kalender -->
    <div class="monthly-calendar">
    <h2><?= date('F Y', strtotime("$year-$monthOfWeek-01")) ?></h2> <!-- Display current month -->
    <table>
        <?php renderMonthlyCalendar($monthOfWeek, $year, $currentWeek); ?>
    </table>

    <div class="calendar-navigation">
        <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="prev">&lt;</a>
        <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="next">&gt;</a>
    </div>
</div>


    <!-- Veckokalender -->
    <div class="weekly-calendar">
    
    <!-- Veckoinformation och navigering -->
    <div class="week-navigation">
        <!-- Previous week link -->
        <a href="?week=<?= $prevWeek ?>&month=<?= $monthOfWeek ?>&year=<?= $year ?>" class="prev">&lt;</a>
    
        <!-- Current week display -->
        <span>Vecka <?= $currentWeek ?></span>

        <!-- Next week link -->
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

            <!-- Tidsblock för dagen -->
            <?php for ($hour = 8; $hour <= 21; $hour++): ?>
                <tr>
                    <td class="hour"><?= sprintf('%02d:00', $hour) ?></td>
                    <?php for ($day = 0; $day < 7; $day++): ?>
                        <td></td>
                    <?php endfor; ?>
                </tr>
                <tr>
                    <td class="hour"><?= sprintf('%02d:30', $hour) ?></td>
                    <?php for ($day = 0; $day < 7; $day++): ?>
                        <td></td>
                    <?php endfor; ?>
                </tr>
            <?php endfor; ?>
        </table>
    </div>
</div>

</body>
</html>