<?php declare(strict_types=1);
require_once(__DIR__ . '/../src/import/Importer.php');
require_once(__DIR__ . '/../src/ui/Options.php');
require_once(__DIR__ . '/../src/ui/View.php');

use Import\Importer;
use UI\{Options, View};

$start;
$end;
$options;

/* Die $view Variable könnte dazu dienen, den Modus auszuwählen, wie die Räume angezeigt werden sollen.
   Eine Auswahl müsste in form.php eingebaut werden. */
$viewOptions = [
    'single_day',
    'single_day_week',
    'week'
];
$view = $viewOptions[0]; // Standard

/**
 * Ermitteln aller freien Räume anhand der durch den Nutzer in form.php eingegebenen Daten.
 * 
 * @return array Feld von Room Objekten
 * wobei jedes einen freien Raum repräsentiert, der zu verschiedenen Zeiten während der Periode verfügbar sein kann.
 */
function getRoomsByInput() : array
{
    global $start, $end, $options;
    
    $start = processTime($options->getStart());
    $end = processTime($options->getFinish());
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
function processTime(\DateTimeInterface $time)
{
    $interval = new \DateInterval('PT15M');
    return $time;
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

</body>
</html>
