<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Import\Importer;

class ImporterTest extends TestCase
{
    public function testCanGetQueryFromValidTimes() : void
    {
        $start = new DateTime("today 08:00:00");
        $end = new DateTime("today next week 10:00:00");

        $times = Importer::query($start, $end);
        $this->assertNotEmpty(
            $times->getAll()
        );
    }
}
