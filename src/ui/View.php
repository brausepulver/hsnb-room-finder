<?php declare(strict_types=1);
namespace UI;

/**
 * Diese Klasse dient dazu, HTML Ansichten zu erstellen, um diese in query.php anzuzeigen.
 */
class View
{
    private $rooms;
    private $start;
    private $finish;

    public function __construct(array $rooms, \DateTimeInterface $start, \DateTimeInterface $finish)
    {
        $this->rooms = $rooms;
        $this->start = $start;
        $this->finish = $finish;
    }

    /**
     * Ansicht, die vollständig verfügbare Räume getrennt von teilweise verfügbaren Räumen anzeigt.
     * 
     * @return string html
     */
    public function splitAvailableView() : string
    {
        $completelyAvailable = []; $partiallyAvailable = [];
        foreach ($this->rooms as $room) {
            $timeFrames = $room->getAvailableTimeFrames($this->start, $this->finish);
            if (count($timeFrames) === 1 && $timeFrames[0][0] == $this->start && $timeFrames[0][1] == $this->finish) {
                $completelyAvailable[] = $room;
            } else if (count($timeFrames) >= 1) {
                $partiallyAvailable[] = $room;
            }
        }

        $r = count($completelyAvailable);
        $html = "<h3>$r vollständig " . ($r === 1 ? 'verfügbarer Raum' : 'verfügbare Räume') . '</h3>';
        $html .= '<ul id="results">';
        foreach ($completelyAvailable as $room) {
            $html .= '<li>' . self::makeRoomTableView($room, $this->start, $this->finish) . '</li>';
        }
        $html .= '</ul>';

        $r = count($partiallyAvailable);
        $html .= "<h3>$r teilweise " . ($r === 1 ? 'verfügbarer Raum' : 'verfügbare Räume') . '</h3>';
        $html .= '<ul id="results">';
        foreach ($partiallyAvailable as $room) {
            $html .= '<li>' . self::makeRoomTableView($room, $this->start, $this->finish) . '</li>';
        }
        $html .= '</ul>';

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
    public function makeSingleDayWeekView(int $weekCount) : string
    {
        /* Zu implementieren.
        Es könnte wie in der Importer Klasse ein Counter die Wochen durchlaufen, 
        und zum gleichen Wochentag die Räume mittels $room->getAvailableTimeFrames($start + k*week, $end + k*week) abfragen. */
        ;
    }

    /**
     * Erstellen einer Ansicht für die Ergebnisse einer Abfrage über eine oder mehrere Wochen,
     * aber Darstellung aller Wochentage, außer Sonnabend und Sonntag.
     * 
     * @param int $weekCount Anzahl der anzuzeigenden Wochen.
     * @return string HTML
     */
    public function makeWeekView(int $weekCount) : string
    {
        ; // Zu implementieren
    }

    /**
     * Darstellen eines Room Objekts in einer HTML Tabellenansicht.
     * 
     * @param Room $room
     * @return string HTML
     */
    public function makeRoomTableView(\Import\Utility\Room $room) : string
    {
        $roomNumberHtml = self::linkToFloorPlan($room);
        ob_start(); 
        ?> 
        <div class="room-info inline-block">
            <?php echo "Raum: $roomNumberHtml (Haus $room->building)"; ?><br>
            <?php echo "Info: $room->name"; ?>
        </div>
        <div class="inline-block"> 
            <ul class="room-times"> <?php 
                foreach ($room->getAvailableTimeFrames($this->start, $this->finish) as $timeInterval) {
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

    /**
     * Generieren eines Links zum Gebäudeplan. (GitHub Issue #12)
     * 
     * Die URL ist in der Form https://userwww2.hs-nb.de/ris/index.php?room=<Haus><Raum>, 
     * z.B. https://userwww2.hs-nb.de/ris/index.php?room=2224 für Haus 2 und Raum 224.
     * 
     * @param \Import\Utility\Room $room
     * @return string Anchor
     */
    public static function linkToFloorPlan(\Import\Utility\Room $room) : string
    {
        if ($room->building !== '2' && $room->building !== '3') {
            return $room->number;
        }
        $url = "https://userwww2.hs-nb.de/ris/index.php?room=$room->building$room->number";
        $output = "<a href=\"$url\">$room->number</a>";
        return $output;
    }
}
