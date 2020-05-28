<?php declare(strict_types=1);
require_once(__DIR__ . '/../src/import/Importer.php');

use Import\Importer;

$start; // global variables are used to make start available in makeRoomHtml()
$end;

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

    return $importer->getFilteredRooms($conditions);
}

/**
 * Represent a FreeRooms object in HTML.
 * 
 * @param FreeRooms $room
 * @return string HTML
 */
function makeRoomHtml(Import\Utility\Room $room) : string
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
?>

<?php
require_once(__DIR__ . '/form.php');
?>

    <section>
        <h3>Ergebnisse</h3>
        <ul id="results">
<?php
$rooms = getRoomsByInput();
foreach ($rooms as $room) {
    echo '<li>' . makeRoomHtml($room) . '</li>' . PHP_EOL;
}
?>
        </ul>
    </section>

<?php
require_once(__DIR__ . '/footer.html');
