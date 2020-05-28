<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Import\Importer;

class ImporterTest extends TestCase
{
    public function testCanGetQueryFromValidTimes() : void
    {
        $start = new DateTime("2020-W16-2 08:00:00");
        $finish = new DateTime("2020-W16-2 10:00:00");

        $importer = new Importer($start, $finish, $debug = true);
        $availableRooms = $importer->getAvailableRooms();
        $availableTimeFrames = $availableRooms['11']->getAvailableTimeFrames($start, $finish);
        print_r($availableTimeFrames);
        $this->assertNotEmpty(
            $availableTimeFrames
        );
    }
}
