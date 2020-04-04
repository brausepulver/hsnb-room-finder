<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
require_once('./src/import/Importer.php');

class ImporterTest extends TestCase
{
    public function testCanGetQueryFromValidTimes() : void
    {
        $start = new DateTime("today 08:00:00");
        $end = new DateTime("tomorrow + 7 days 10:00:00");

        $times = Importer::query($start, $end);
        print_r($times);
    }
}
