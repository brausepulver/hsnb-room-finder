<?php declare(strict_types=1);
require_once(__DIR__ . '/../src/analysis/analyse.php');

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

    $dayEnabled = isset($_GET['day_enabled']);
    $day = $_GET['day'];

    $timeframeEnabled = isset($_GET['timeframe_enabled']);
    $timeframeFrom = $_GET['timeframe_from'];
    $timeframeTo = $_GET['timeframe_to'];

    // $roomNumberEnabled = $_GET['room_number_enabled'];
    // $roomNumber = $_GET['room_number'];

    // $buildingNumberEnabled = $_GET['building_number_enabled'];
    // $buildingNumber = $_GET['building_number'];

    // $roomTypeEnabled = $_GET['room_type_enabled'];
    // $roomType = $_GET['room_type'];

    $debug = isset($_GET['debug']);

    // not applicable because checkbox values can not be read
    // if (isset($dayEnabled)) {
    //     if (isset($timeframeEnabled)) {
    //         echo $timeframeFrom;
    //         $start = new \DateTime("$day $timeframeFrom");
    //         $end = new \DateTime("$day $timeframeTo");
    //     } else {
    //         echo $day;
    //         $start = new \DateTime($day);
    //         $end = clone $start;
    //         $end->add(new \DateInterval('P1D'));
    //     }
    // }

    // these are for testing purposes
    // echo $day;
    // echo $timeframeFrom;
    if (!empty($timeframeFrom) && !empty($timeframeTo)) {
        $start = new \DateTime("$day $timeframeFrom");
        $end = new \DateTime("$day $timeframeTo");
    } else {
        $start = new \DateTime($day);
        $end = clone $start;
        $end->add(new \DateInterval('P1D'));
    }
    return getFreeRooms($start, $end, $debug);
}

/**
 * Make a time from an index given in the arrays contained in the free attribute of a FreeRooms object.
 * The indices are counted in 15 minute steps by default.
 * 
 * @param \DateTimeInterface $start, the timestamp at which the FreeRooms object begins counting.
 * @param int $index, representing one specific time.
 * @return \DateTimeInterface corresponding to the index added to the starting time.
 */
function indexToTime(\DateTimeInterface $start, int $index)
{
    return $start->add(new \DateInterval('PT' . $index * 15 . 'M'));
}

/**
 * Represent a FreeRooms object in HTML.
 * 
 * @param FreeRooms $room
 * @return string HTML
 */
function makeRoomHtml(FreeRooms $room) : string
{
    global $start;

    $ret = "ID: $room->id";
    foreach ($room->free as $timeInterval) {
        $ret .= ', ';
        $ret .= indexToTime(clone $start, $timeInterval[0])->format('H:i:s');
        $ret .= ' bis ';
        $ret .= indexToTime(clone $start, $timeInterval[1])->format('H:i:s');
    }
    return $ret;
}
?>

<?php
require_once(__DIR__ . '/form.html');
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
