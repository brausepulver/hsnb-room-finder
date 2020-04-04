<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Import\Utility\TimeVector;

class TimeVectorTest extends TestCase
{
    private $testTimeVector;

    public function setUp() : void
    {
        $start = new DateTime("today 08:00:00");
        $end = new DateTime("today 09:00:00");
        $offset = new DateInterval('PT15M');
        $this->testTimeVector = new TimeVector($start, $end, $offset, ['test']);
    }

    public function testCanGetFromValidIndex() : void
    {
        $this->assertEquals(
            'test',
            $this->testTimeVector->get(new DateTime("today 08:30:00"))[0]
        );
    }

    public function testCannotGetFromInvalidIndex() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->testTimeVector->get(new DateTime("today 07:30:00"));
    }
}
