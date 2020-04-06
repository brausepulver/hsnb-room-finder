<?php

declare(strict_types=1);

use Import\Importer;

$start = new DateTime("Monday next week 08:00:00");
$end = new DateTime("Monday next week 10:00:00");

$times = Importer::query($start, $end);

$dayCounter = $start;

while ($dayCounter < $end) {



    $dayCounter->add(new DateInterval('P1D'));
}
