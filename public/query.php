<?php declare(strict_types=1);
require_once(__DIR__ . '/../src/import/Importer.php');
require_once(__DIR__ . '/../src/ui/Options.php');
require_once(__DIR__ . '/../src/ui/View.php');

use Import\Importer;
use UI\{Options, View};

$start;
$end;
$options;

/**
 * Ermitteln aller freien Räume anhand der durch den Nutzer in form.php eingegebenen Daten.
 * 
 * @return array Feld von Room Objekten
 * wobei jedes einen freien Raum repräsentiert, der zu verschiedenen Zeiten während der Periode verfügbar sein kann.
 */
function getRoomsByInput() : array
{
    global $start, $end, $options;
    
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
<?php
$rooms = getRoomsByInput();
$view = new View($rooms, $start, $end);
echo $view->splitAvailableView();
?>
    </section>

</body>
</html>
