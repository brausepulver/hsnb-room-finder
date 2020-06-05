<?php declare(strict_types=1);
namespace UI;

/**
 * Diese Klasse dient dazu, HTML Ansichten zu erstellen, um diese in query.php anzuzeigen.
 */
class View
{
    /**
     * Die Standard-Ansicht die wir bis jetzt hatten, nur in einer extra Funktion.
     * 
     * @return string $html
     */
    public static function makeSingleDayView(
        array $rooms,
        \DateTimeInterface $start,
        \DateTimeInterface $end
    ) : string
    {
        $html = '';
        foreach ($rooms as $room) {
            if (count($room->getAvailableTimeFrames($start, $end)) === 0) continue;
            $html .= '<li>' . self::makeRoomTableView($room, $start, $end) . '</li>' . PHP_EOL;
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
    public static function makeSingleDayWeekView(
        array $rooms, 
        int $weekCount,
        \DateTimeInterface $start,
        \DateTimeInterface $end
    ) : string
    {
        /* Ersetzen und entsprechend implementieren.
        Es könnte wie in der Importer Klasse ein Counter die Wochen durchlaufen, 
        und zum gleichen Wochentag die Räume mittels $room->getAvailableTimeFrames($start + k*week, $end + k*week) abfragen. */
        $html = '';

        foreach ($rooms as $room) {
            $html .= '<li>' . self::makeRoomView($room, $start, $end) . '</li>' . PHP_EOL;
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
    public static function makeWeekView(
        array $rooms, 
        int $weekCount,
        \DateTimeInterface $start,
        \DateTimeInterface $end
    ) : string
    {
        ;
    }

    /**
     * Darstellen eines Room Objekts in HTML.
     * 
     * @param Room $room
     * @return string HTML
     */
    public static function makeRoomView(
        \Import\Utility\Room $room,
        \DateTimeInterface $start,
        \DateTimeInterface $end
    ) : string
    {
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
    public static function makeRoomTableView(
        \Import\Utility\Room $room,
        \DateTimeInterface $start,
        \DateTimeInterface $end
    ) : string
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
        if ($room->building != '2' && $room->building != '3') {
            return $room->number;
        } else {
            $url = "https://userwww2.hs-nb.de/ris/index.php?room=$room->building$room->number";
            $output = "<a href=$url>$room->number</a>";
            return $output;
        }
    }
}
