<?php
//show php errors
ini_set('display_errors', 1);
//stop caching the page
header('Cache-Control: no-cache');
?>
<?php
function adjustToLastDayOfMonth($dateString)
{
    // Create a DateTime object from the provided date string
    $date = DateTime::createFromFormat('Y-m-d', $dateString);
    echo print_r($date);
    // Check if the date is valid
    if (!$date) {
        return "Invalid date format";
    }

    // Get the last day of the month by setting the day to 1 of the next month and then subtracting 1 day
    $lastDay = clone $date;
    $lastDay->modify('first day of next month')->modify('-1 day');

    // Return the adjusted date as a string
    return $lastDay->format('Y-m-d');
}

echo adjustToLastDayOfMonth('2024-02-31'); // Output: 2024-02-29
exit();
function get_weekly_invoices($times, $today = false)
{
    $dates = array();
    $date = date('Y-m-01');
    $end = date('Y-m-t');
    while ($date < $end) {
        $dates[] = $date;
        $days = round(30 / $times);
        $date = date('Y-m-d', strtotime('+' . $days . ' day', strtotime($date)));
        //if it match $date today date and $today is true return true
        if ($today && $date == date('Y-m-d')) {
            return true;
        }
    }
    //
    return $dates;
}

function get_monthly_invoices($start, $end, $months, $today = false)
{
    $dates = array();
    //get the last day of the month
    $last_day = date('t', strtotime($start));
    echo
    date('Y-m-d', strtotime($start));
    //if the start date is greater than the last day of the month then set the last day of the month
    if (date('d', strtotime($start)) > $last_day) {
        $start = date('Y-m-t', strtotime($start));
    } else {
        $start = date('Y-m-d', strtotime($start));
    }
    echo $start;

    exit();
    $date = date('Y-m-d', strtotime($start));
    $end = date('Y-m-d', strtotime($end));

    while ($date <= $end) {
        $dates[] = $date;
        $date = date('Y-m-d', strtotime('+' . $months . ' month', strtotime($date)));
        //if it match $date today date and $today is true return true
        if ($today && $date == date('Y-m-d')) {
            return true;
        }
    }
    return $dates;
}

$invoices_dates = get_monthly_invoices('2023-03-39', '2026-02-10', 6, true);
print_r($invoices_dates);
//invoicing times a month
$dates = get_weekly_invoices(2, true);
print_r($dates);
