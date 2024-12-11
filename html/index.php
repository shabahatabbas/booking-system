<?php 
//include 'header.php';
include 'calendar_functions.php'; 

// Hämta aktuell vecka, månad och år från GET, eller sätt till nuvarande vecka, månad och år
$currentWeek = isset($_GET['week']) && is_numeric($_GET['week']) ? (int)$_GET['week'] : date('W');
/*if($currentWeek < $currentWeek - 1) {
    $currentWeek = date('W');
} */
// Hämta månad och år från URL (GET) eller använd nuvarande månad och år
//$month = isset($_GET['month']) && is_numeric($_GET['month']) ? (int)$_GET['month'] : date('n');  // Standard till aktuell månad
$year = isset($_GET['year']) && is_numeric($_GET['year']) ? (int)$_GET['year'] : date('Y');

$monthOfWeek = (new DateTime()) -> setISODate($year, $currentWeek) ->format('m');
echo $monthOfWeek;

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


echo $startOfWeek ->format('d');
echo $endOfWeek  ->format('d');

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
        $year+=1;     // Inkrementera året med 1
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

    <!-- Kalender MÅNAD -->
    <div class="monthly-calendar">
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
    <div class="weekly-calendar">
    
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