<?php
require_once(__DIR__ . '/../src/import/Importer.php');
require_once(__DIR__ . '/../src/ui/Options.php');

use Import\Importer;
use UI\Options;

$options = new Options();

if (empty($_GET)) {
    $options->populateDefault();
} else {
    $options->populate();
}
?>

<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Johannes Kindermann">
    <meta name="author" content="Florian Leder">
    <title>Raum-Finder</title>
    <link rel="stylesheet" href="./public/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@exampledev/new.css@1/new.min.css">
</head>
<body>
    <h2>Raum-Finder</h2>

    <section>
        <form action="query.php" method="GET">
            <ul>
                <li>
                    <span>
                        <input type="checkbox" name="day_enabled" id="day_enabled" <?php if ($options->dayEnabled) echo 'checked'; ?>>
                        <label for="day_enabled">Tag</label>
                    </span>
                    <input type="date" name="day" id="day" <?php echo 'value="' . $options->day . '"'; ?>>
                </li>
                <li id="timeframe-container">
                    <span>
                        <input type="checkbox" name="timeframe_enabled" id="timeframe_enabled" 
                            <?php if ($options->timeframeEnabled) echo 'checked'; ?>>
                        <label for="timeframe_enabled">Zeitraum</label>
                    </span>
                </li>
                <li class="no-checkbox">
                    <label for="timeframe_from">von</label>
                    <input type="time" name="timeframe_from" id="timeframe_from" 
                        <?php echo 'value="' . $options->timeframeFrom . '"'; ?>>
                </li>
                <li class="no-checkbox">
                    <label for="timeframe_to">bis</label>
                    <input type="time" name="timeframe_to" id="timeframe_to" 
                        <?php echo 'value="' . $options->timeframeTo . '"'; ?>>
                </li>
                <!-- li>
                   <span>
                       <input type="checkbox" name="min_time_enabled" id="min_time_enabled" checked disabled>
                       <label for="min_time_enabled">mindestens frei für</label> 
                   </span> 
                   <select name="min_time" id="min_time">
                        <option value="15">unter 30 min</option>
                        <option value="30">30 min</option>
                        <option value="45">45 min</option>
                        <option value="60">60 min</option>
                        <option value="120">120 min</option>
                        <option value="720">ganztägig</option>
                   </select>
                </li -->
                <li>
                    <span>
                        <input type="checkbox" name="room_number_enabled" id="room_number_enabled" 
                            <?php if ($options->roomNumberEnabled) echo 'checked'; ?>>
                        <label for="room_number_enabled">Nummer</label>
                    </span>
                    <input type="text" size="5" maxlength="5" id="room_number" name="room_number" 
                        <?php echo 'value="' . $options->roomNumber . '"'; ?>>
                </li>
                <li>
                    <span>
                        <input type="checkbox" name="building_number_enabled" id="building_number_enabled" 
                            <?php if ($options->buildingNumberEnabled) echo 'checked'; ?>>
                        <label for="building_number_enabled">Haus</label>
                    </span>
                    <select name="building_number" id="building_number">
<?php
for ($i = 1; $i <= 4; $i++) {
    $selected = $i == $options->buildingNumber ? 'selected' : '';
    echo "<option value=\"$i\" $selected>$i</option>";
} ?>
                    </select>
                </li>
                <li>
                    <span>
                        <input type="checkbox" name="room_type_enabled" id="room_type_enabled" 
                            <?php if ($options->roomTypeEnabled) echo 'checked'; ?>>
                        <label for="room_type_enabled">Typ</label>
                    </span>
                    <select name="room_type" id="room_type">
<?php
for ($i = 0, $roomTypes = Importer::getRoomTypes(); $i < count($roomTypes); $i++) {
    $selected =  $i == $options->roomType ? 'selected' : '';
    echo "<option value=\"$i\" $selected>" . $roomTypes[$i] . '</option>';
} ?>
                    </select>
                </li>
                <li>
                    <input type="button" onclick="document.location = 'query.php'" value="Zurücksetzen">
                    <input type="submit" value="Suchen">
                </li>
            </ul>
        </form>
    </section>
