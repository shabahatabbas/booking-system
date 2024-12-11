<?php

// Function to get the current month, year, and calculate the previous and next month
// Funktion för att beräkna månad och år samt föregående och nästa månad
function getCalendarData($month, $year, $currentWeek) {
    // Säkerställ att månad och år är giltiga
    //$week = isset($_GET['week']) && is_numeric($_GET['week']) && $_GET['week'] >=1 && $_GET['week'] <=52 ? (int)$_GET['week'] : date('W');
    $month = isset($_GET['month']) && is_numeric($_GET['month']) ? (int)$_GET['month'] : date('n');  // Standard till aktuell månad
    $year = isset($_GET['year']) && is_numeric($_GET['year']) ? (int)$_GET['year'] : date('Y');

    // Beräkna föregående och nästa månad
    $nextWeek = $currentWeek + 1 > 52 ? 1 : $currentWeek + 1;
    $prevWeek = $currentWeek - 1 < 1 ? 52 : $currentWeek - 1;

    $prevMonth = $month == 1 ? 12 : $month - 1;
    $nextMonth = $month == 12 ? 1 : $month + 1;
    $prevYear = $month == 1 ? $year - 1 : $year;
    $nextYear = $month == 12 ? $year + 1 : $year;

       // Get the first week of the next month
      // Get the first day of the current month
        $firstDayCurrentMonth = new DateTime("$year-$month-01");
// Get the ISO week number for the first day of the current month
        $firstWeekCurrentMonth = $firstDayCurrentMonth->format('W');

    // Returnera månad, år, och föregående och nästa månad
    return [
        'week' => $currentWeek,
        'month' => $month,
        'year' => $year,
        'prevWeek' => $prevWeek,
        'nextWeek' => $nextWeek,
        'prevMonth' => $prevMonth,
        'nextMonth' => $nextMonth,
        'prevYear' => $prevYear,
        'nextYear' => $nextYear,
        'firstWeekNextMonth' => $firstWeekCurrentMonth // Add first week of next month
    ];
}

//Funktion för att få fram kalender MÅNAD
function renderMonthlyCalendar($month, $year, $currentWeek, $currentWeekMonth) {
    
    // Hämta första dagen på månaden (1 = Monday, 7 = Sunday)
    $firstDayOfMonth = date('N', strtotime("$year-$month-01"));
    //Hämta antal dagar i månaden
    $daysInMonth = date('t', strtotime("$year-$month-01"));
    // Börja tablen för kalendern
    echo "<tr>";

    //Lägg till tomma rutor för dagarna före den första dagen i månaden
    for ($i = 1; $i < $firstDayOfMonth; $i++) {
        echo "<td></td>";
    }

    // Loopa genom varje dag i månaden och visa dem
    for ($day = 1; $day <= $daysInMonth; $day++) {

        //Hämta nuvarande veckonummer
        $weekNumber = date('W', strtotime("$year-$month-$day"));
        // Om veckonummret matchar den nuvarande veckan så highlightas dem.
        // Matchar dem inte så händer ingenting
        

        $highlightClass = ($weekNumber == $currentWeek) ? 'highlight-week' : '';

        //Starta en ny rad efter 7 dagar (slutet på veckan)
        if (($day + $firstDayOfMonth - 2) % 7 == 0) {
            echo "</tr><tr>";
        }
        
        //Output för den highlightade dagen
        echo "<td class='$highlightClass'>$day</td>";
    }

    // Lägg till tomma rutor efter den sista dagen på månaden för att fylla ut.
    $remainingCells = (7 - (($daysInMonth + $firstDayOfMonth - 1) % 7)) % 7;
    for ($i = 0; $i < $remainingCells; $i++) {
        echo "<td></td>";
    }

    echo "</tr>";
}

function getFirstWeekOfNextMonth($year, $month) {
    if (!is_numeric($year) || !is_numeric($month) || $month < 1 || $month > 12) {
        throw new Exception("Ogiltigt år eller månad.");
    }

    $date = new DateTime("$year-$month-01");
    $date->modify('+1 month');
    $date->setDate($date->format('Y'), $date->format('n'), 1);

    return (int) $date->format('W');
}

function getFirstWeekOfPreviousMonth($year, $month) {
    if (!is_numeric($year) || !is_numeric($month) || $month < 1 || $month > 12) {
        throw new Exception("Ogiltigt år eller månad.");
    }

    $date = new DateTime("$year-$month-01");
    $date->modify('-1 month');
    $date->setDate($date->format('Y'), $date->format('n'), 1);

    return (int) $date->format('W');
}

function checkHowManyWeeksYearHas($year){
    $max_week = (int)(new DateTime("{$year}-12-28"))->format("W");

    return $max_week;
}
// Om året just nu har 53 veckor då ändras veckan till 1 vid 54
//Om året istället har 52 veckor då ändras veckan till 1 vid 53
function adjustWeekToCurrentYear(&$currentWeek, &$year){ // & Är en referensvariabel (kommer ändra variablerna där metoden kallas)

    $quantityOfWeeksForYear = checkHowManyWeeksYearHas($currentWeek, $year);


    if($currentWeek > $quantityOfWeeksForYear){
        $currentWeek = 1;
        $year++;

    } else if ($currentWeek < 1) {
        $currentWeek = $quantityOfWeeksForYear;
        $year--;
    }

}
?>