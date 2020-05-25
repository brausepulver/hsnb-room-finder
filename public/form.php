<?php
require_once(__DIR__ . '/../src/analysis/analyse.php');

use Import\Importer;
?>

<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Johannes Kindermann">
    <meta name="author" content="Florian Leder">
    <title>Raum-Finder</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h2>Raum-Finder</h2>

    <section>
        <form action="query.php" method="GET">
            <ul>
                <li>
                    <span>
                        <input type="checkbox" name="day_enabled" id="day_enabled" checked disabled>
                        <label for="day_enabled">Tag</label>
                    </span>
                    <input type="date" name="day" id="day">
                </li>
                <li id="timeframe-container">
                    <span>
                        <input type="checkbox" name="timeframe_enabled" id="timeframe_enabled" checked disabled>
                        <label for="timeframe_enabled">Zeitraum</label>
                    </span>
                </li>
                <li class="no-checkbox">
                    <label for="timeframe_from">von</label>
                    <input type="time" name="timeframe_from" id="timeframe_from">
                </li>
                <li class="no-checkbox">
                    <label for="timeframe_to">bis</label>
                    <input type="time" name="timeframe_to" id="timeframe_to">
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
                        <input type="checkbox" name="room_number_enabled" id="room_number_enabled">
                        <label for="room_number_enabled">Raum-Nummer</label>
                    </span>
                    <input type="text" size="5" maxlength="5" id="room_number" name="room_number">
                </li>
                <li>
                    <span>
                        <input type="checkbox" name="building_number_enabled" id="building_number_enabled">
                        <label for="building_number_enabled">Haus</label>
                    </span>
                    <select name="building_number" id="building_number">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                    </select>
                </li>
                <li>
                    <span>
                        <input type="checkbox" name="room_type_enabled" id="room_type_enabled">
                        <label for="room_type_enabled">Raum-Art</label>
                    </span>
                    <select name="room_type" id="room_type">
<?php
for ($i = 1, $roomTypes = Importer::getRoomTypes(); $i < count($roomTypes); $i++) {
    echo "<option value=\"$i\">" . $roomTypes[$i] . '</option>';
}
?>
                    </select>
                </li>
            </ul>
        </form>
    </section>
