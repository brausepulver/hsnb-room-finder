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
    $start = $options->getStart();
    $end = $options->getFinish();
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
