<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
require_once('./src/import-utility.php'); // do this using namespaces?

class TimeVectorTest extends TestCase
{
    private $testTimeVector;

    public function setUp() : void
    {
        $start = new DateTime("today 08:00:00");
        $end = new DateTime("today 09:00:00");
        $offset = new DateInterval('PT15M');
        $this->testTimeVector = new TimeVector($start, $end, $offset, []);
    }

    public function testCanCreateIndexFromValidTime() : void
    {
        $this->assertEquals(
            2,
            $this->testTimeVector->timeToIndex(new DateTime("today 08:30:00"))
        );
    }

    public function testCannotCreateIndexFromInvalidTime() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->testTimeVector->timeToIndex(new DateTime("today 07:30:00"));
    }
}
