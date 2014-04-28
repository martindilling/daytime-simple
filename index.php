<?php
// Set some defaults
date_default_timezone_set('Europe/Copenhagen');
$weeksInYear = 52;
$daysInWeek = 7;

// Current date
$currentTimezone = new DateTimeZone('Europe/Copenhagen');
$currentDate = new DateTime('now', $currentTimezone);
$currentWeek = $currentDate->format('W');
$currentYear = $currentDate->format('Y');

// The week/year to show
$showWeek = $currentWeek;
$showYear = $currentYear;
if (isset($_GET['week']) && isset($_GET['year'])) {
    $showWeek = (int) $_GET['week'];
    $showYear = (int) $_GET['year'];
    
    // Validate
    if ($showWeek < 1 || $showWeek > $weeksInYear)
        die('Invalid week!');
    if ($showWeek < $currentWeek && $showYear <= $currentYear)
        die('Can\'t be in the past!');
    if ($showWeek >= $currentWeek && $showYear > $currentYear)
        die('Can\'t be in the future!');
}

// First day of the week to show
$paddedShowWeek = (strlen($showWeek) == 1 ? '0'.$showWeek : $showWeek);
$monday = new DateTime($showYear . '-W' . $paddedShowWeek . '-1', $currentTimezone);

// Functions
function getSunrise(DateTime $day, $mode = SUNFUNCS_RET_TIMESTAMP)
{
    $location = $day->getTimezone()->getLocation();

    return date_sunrise(
        $day->getTimestamp(),
        $mode,
        $location['latitude'],
        $location['longitude'],
        90+50/60,
        $day->getOffset() / 3600
    );
}

function getSunset(DateTime $day, $mode = SUNFUNCS_RET_TIMESTAMP)
{
    $location = $day->getTimezone()->getLocation();

    return date_sunset(
        $day->getTimestamp(),
        $mode,
        $location['latitude'],
        $location['longitude'],
        90+50/60,
        $day->getOffset() / 3600
    );
}

function getSunnyTime(DateTime $day)
{
    $sunriseTimestamp = getSunrise($day);
    $sunsetTimestamp = getSunset($day);

    $totalSeconds = $sunsetTimestamp - $sunriseTimestamp;
    $totalMinutes = $totalSeconds / 60;

    $formattedHours = floor($totalMinutes / 60);
    $formattedMinutes = ($totalMinutes % 60);

    return sprintf('%d:%02d', $formattedHours, $formattedMinutes);
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daytime</title>
    <style>
        a.week-list,
        a.week-list:active,
        a.week-list:visited {
            color: blue;
        }
        a.week-list.active {
            color: red;
        }
    </style>
</head>
<body>
    <?php
        $weekCounter = $currentWeek;
        $yearCounter = $currentYear;
        for ($i = 1; $i <= $weeksInYear; $i++) {
            if ($weekCounter > $weeksInYear) {
                $weekCounter = 1;
                $yearCounter++;
            }
            
            $active = ($weekCounter == $showWeek ? 'active' : '');
            echo '<a href="?week=' . $weekCounter . '&year=' . $yearCounter . '" class="week-list ' . $active . '">';
            echo $weekCounter;
            echo '</a>';
            if ($i !== $weeksInYear) {
                echo ' | ';
            }

            $weekCounter++;
        }
    ?>
    <hr>
    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>Date:</th>
                <th>Sunrise:</th>
                <th>Sunny:</th>
                <th>Sunset:</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $day = $monday;
                for ($i = 0; $i < $daysInWeek; $i++) {
                    echo '<tr>';
                        echo '<td>' . $day->format('l (d-m-Y)') . '</td>';
                        echo '<td>' . getSunrise($day, SUNFUNCS_RET_STRING) . '</td>';
                        echo '<td>' . getSunnyTime($day) . ' hours </td>';
                        echo '<td>' . getSunset($day, SUNFUNCS_RET_STRING) . '</td>';
                    echo '</tr>';
                    $day->modify('+1 day');
                }
            ?>
        </tbody>
    </table>
</body>
</html>
