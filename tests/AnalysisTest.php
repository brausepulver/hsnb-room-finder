<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Import\Importer;

require_once(__DIR__ . '/../src/analysis/analyse.php');

class AnalysisTest extends TestCase
{
    public function testCanGetFreeRoomsFromValidTimes() : void
    {
        $start = new DateTime("2020-W16-2 08:00:00");
        $end = new DateTime("2020-W16-2 10:00:00");
        $minTime = minTimeLength(60);

        $getIDarray = function (DateTime $start, DateTime $end) {
            $timevector = Importer::query($start, $end, $debug = true);
         
            $times = $timevector->getAll();
            $timeID;
            for($i = 0; $i < count($times); $i++){
                $times[$i];                         
                for($j = 0; $j < count($times[$i]); $j++){
                    $timeID[$i][$j] = $times[$i][$j]->id2;
                }
        
            }
            echo 'done';
            return $timeID;
        };
        $timeID = $getIDarray($start, $end);

        $uniqueID = getUnique1D($timeID);
        $convertedID = convertID($timeID, $uniqueID);
        $freerooms = checkTime($minTime, $uniqueID, $convertedID);
        foreach ($freerooms as $room) {
            if ($room->start != 0 && $room->end != 7) {
                print_r($room);
            }
        }
        print_r($freerooms);
    }
}
