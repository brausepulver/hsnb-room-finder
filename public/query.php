<?php declare(strict_types=1);
require_once(__DIR__ . '/../src/import/Importer.php');

use Import\Importer;

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
    $conditions = [];

    $dayEnabled = isset($_GET['day_enabled']);
    $day = $_GET['day'];

    $timeframeEnabled = isset($_GET['timeframe_enabled']);
    $timeframeFrom = $_GET['timeframe_from'];
    $timeframeTo = $_GET['timeframe_to'];

    if ($dayEnabled && !empty($day)) {
        if ($timeframeEnabled && !empty($timeframeFrom) && !empty($timeframeTo)) {
            $start = new \DateTime("$day $timeframeFrom");
            $end = new \DateTime("$day $timeframeTo");
        } else {
            $start = new \DateTime($day);
            $end = clone $start;
            $end->add(new \DateInterval('P1D'));
        }
    } else {
        $start = new \DateTime('today');
        $end = clone $start;
        $end->add(new \DateInterval('P1D'));
    }

    $roomNumberEnabled = isset($_GET['room_number_enabled']);
    $roomNumber = $_GET['room_number'];
    if ($roomNumberEnabled) $conditions['room_number'] = $roomNumber;

    $buildingNumberEnabled = isset($_GET['building_number_enabled']);
    $buildingNumber = $_GET['building_number'];
    if ($buildingNumberEnabled) $conditions['building_number'] = $buildingNumber;

    $roomTypeEnabled = isset($_GET['room_type_enabled']);
    $roomType = $_GET['room_type'];
    if ($roomTypeEnabled) $conditions['room_type'] = $roomType;

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
 * Die Standard-Ansicht die wir bis jetzt hatten, nur in einer extra Funktion.
 * 
 * @return string $html
 */
function makeSingleDayView() : string
{
    $rooms = getRoomsByInput();
    $html = '';
    foreach ($rooms as $room) {
        $html .= '<li>' . makeRoomTableView($room) . '</li>' . PHP_EOL;
    }
    return $html;
}

/**
 * Erstellen einer Ansicht für die Ergebnisse einer Abfrage über mehrere Wochen,
 * und immer am gleichen Wochentag. (z.B. Montag über das gesamte Semester)
 * 
 * @param int $weekCount Anzahl der anzuzeigenden Wochen.
 * @return string HTML
 * 
 * Weitere Parameter wären möglich, wie z.B. Intervall (ob nur alle 2 Wochen angezeigt werden soll).
 * 
 * Auch ist die Frage, ob verschiedene Importer angelegt werden sollen um die Events an verschiedenen Tagen abzufragen, 
 * oder ob alle Events in die jeweiligen Räume eingelesen werden sollen, und dann abgefragt.
 * 
 * Die Darstellung könnte durch mehrere Tabellen nebeneinander, oder durch Tabs erfolgen.
 */
function makeSingleDayWeekView(int $weekCount) : string
{
    /* Ersetzen und entsprechend implementieren.
       Es könnte wie in der Importer Klasse ein Counter die Wochen durchlaufen, 
       und zum gleichen Wochentag die Räume mittels $room->getAvailableTimeFrames($start + k*week, $end + k*week) abfragen. */
    $rooms = getRoomsByInput();
    $html = '';

    foreach ($rooms as $room) {
        $html .= '<li>' . makeRoomHtml($room) . '</li>' . PHP_EOL;
    }
    return $html;
}

/**
 * Erstellen einer Ansicht für die Ergebnisse einer Abfrage über eine oder mehrere Wochen,
 * aber Darstellung aller Wochentage, außer Sonnabend und Sonntag.
 * 
 * @param int $weekCount Anzahl der anzuzeigenden Wochen.
 * @return string HTML
 */
function makeWeekView(int $weekCount) : string
{
    ;
}

/**
 * Darstellen eines Room Objekts in HTML.
 * 
 * @param Room $room
 * @return string HTML
 */
function makeRoomView(Import\Utility\Room $room) : string
{
    global $start, $end;

    $ret = "Raum: $room->number (Haus $room->building), $room->name";
    foreach ($room->getAvailableTimeFrames($start, $end) as $timeInterval) {
        $ret .= ', ';
        $ret .= $timeInterval[0]->format('H:i:s');
        $ret .= ' bis ';
        $ret .= $timeInterval[1]->format('H:i:s');
    }
    return $ret;
}

/**
 * Darstellen eines Room Objekts in einer HTML Tabellenansicht.
 * 
 * @param Room $room
 * @return string HTML
 */
function makeRoomTableView(Import\Utility\Room $room) : string
{
    global $start, $end;

    ob_start(); 
    ?> 
    <div class="room-info inline-block">
        <?php echo "Raum: $room->number (Haus $room->building)"; ?><br>
        <?php echo "Info: $room->name"; ?>
    </div>
    <div class="inline-block"> 
        <ul class="room-times"> <?php 
            foreach ($room->getAvailableTimeFrames($start, $end) as $timeInterval) {
                echo '<li>';
                echo $timeInterval[0]->format('H:i:s');
                echo ' bis ';
                echo $timeInterval[1]->format('H:i:s');
                echo '</li>';
            } ?>
        </ul>
    </div> <?php
    return ob_get_clean();
}
?>

<?php
require_once(__DIR__ . '/form.php');
?>

    <section>
        <h3>Ergebnisse</h3>
        <ul id="results">
<?php
if ($view === 'single_day') {
    echo makeSingleDayView();
}
?>
        </ul>
    </section>

<?php
require_once(__DIR__ . '/footer.html');
