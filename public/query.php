<?php declare(strict_types=1);
require_once(__DIR__ . '/../src/import/Importer.php');
require_once(__DIR__ . '/../src/ui/Options.php');
require_once(__DIR__ . '/../src/ui/View.php');

use Import\Importer;
use UI\{Options, View};

$start; // global variables are used to make start available in makeRoomHtml()
$end;

/* Die $view Variable könnte dazu dienen, den Modus auszuwählen, wie die Räume angezeigt werden sollen.
   Eine Auswahl müsste in form.php eingebaut werden. */
$viewOptions = [
    'single_day',
    'single_day_week',
    'week'
];
$view = $viewOptions[0]; // Standard

/**
 * Get all free rooms specified by the data entered by the user.
 * The method of choice is GET, to make the query RESTful.
 * 
 * @return array of FreeRooms objects, 
 * each representing a free room that can be available at different times during the period.
 */
function getRoomsByInput() : array
{
    global $start, $end;
    
    $options = new Options();
    $start = roundUpTime($options->getStart());
    $end = roundUpTime($options->getFinish());
    $conditions = $options->getConditions();

    $debug = false;
    $importer = new Importer($start, $end, $debug);

    $rooms = $importer->getFilteredRooms($conditions);
    // Räume sortieren
    if (usort($rooms, ['Import\\Utility\\Room', 'compareRoom'])) {
        return $rooms;
    } else {
        return $rooms;
    }
}

/**
 * Zeit aufrunden. Zeiten, die zwischen 15-Minuten Intervallen liegen, werden damit eingefügt.
 * 
 * Problem: 13:50 Uhr soll auf 14:00 Uhr und nicht 13:00 aufgerundet werden.
 * 
 * @param \DateTimeInterface $time Zeit, die aufgerundet werden soll.
 * @return \DateTimeInterface Gleiches Objekt, nur zeitlich bearbeitet.
 */
function roundUpTime(\DateTimeInterface $time) : \DateTimeInterface
{
    $timeclone = (clone $time);
    $timeclone->add(new DateInterval('PT1H'));

    $day = $timeclone->format('Y-m-d');
    $hour = $time->format('H');
    $hour2 = $timeclone->format('H');
    
    $time2 = date_create_from_format('Y-m-d H:i:s', "$day $hour2:00:00");
    $interval = $time->diff($time2);

    $diff = intval($interval->format('%i'));

    if ($diff % 15 === 0) {
        return $time;
    }
    if ($diff < 15){
        return $time2;
    } else if ($diff < 30) {
        $output = date_create_from_format('Y-m-d H:i:s', "$day $hour:45:00");
        return $output;
    } else if ($diff < 45) {
        $output = date_create_from_format('Y-m-d H:i:s', "$day $hour:30:00");
        return $output;
    }
    $output = date_create_from_format('Y-m-d H:i:s', "$day $hour:15:00");
    return $output;
}
?>

<?php
require_once(__DIR__ . '/form.php');
?>

    <section>
        <h3>Ergebnisse</h3>
        <ul id="results">
<?php
$rooms = getRoomsByInput();
if ($view === 'single_day') {
    echo View::makeSingleDayView($rooms, $start, $end);
}
?>
        </ul>
    </section>

<?php
require_once(__DIR__ . '/footer.html');
