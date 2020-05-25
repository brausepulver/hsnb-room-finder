<?php declare(strict_types=1);

require_once(__DIR__ . '/../import/Importer.php');

use Import\Importer;
use Import\Importer\{Event, Room, TimeVector};

class FreeRooms{

     public $id;
     public $building;
     public $number;
     public $info;
     public $free;

     public function __construct(){

        $this->free = [];
        
    }
}

/**
 * Creates array with every available room id(multiple occupancy possible)
 * $i equals time index(counted in 15 minutes steps)
 * @param Importer $importer, use query() method to get room information
 * @return array $timeID, contains IDs of available rooms
 */
function getIDarray(Importer $importer) : array{
    global $importer;
    $timevector = $importer->query();
    
    $times = $timevector->getAll();
    $timeID = [];
    for($i = 0; $i < count($times); $i++){
        $timeID[$i] = [];
        foreach ($times[$i] as $room){
            $timeID[$i][] = $room->id;
        }
    }
    return $timeID;
}

/**
 * Calculate index based on preferred minimal time
 * @param int $minTime, minimum time amount the room should be available
 * @return int integer representing a specific amount of time
 */
 function minTimeLength(int $minTime) : int {
    return intval($minTime / 15);
}


/**
 * Transforms a 2d array into 1d array with unique values, removes multiple occurring IDs 
 * @param array $input, $timeID array containing room ids
 * @return array $unique, contains unique values (IDs)
 */
function getUnique1D(array $input) : array {
    $output = array();
    for($i = 0; $i < count($input); $i++){
        for($j = 0; $j < count($input[$i]); $j++){
            $output[] = $input[$i][$j];
        }
    }
    $tmp = array_unique($output);                
    $unique = [];
    foreach($tmp as $r){
        $unique[] = $r;
    }
    return $unique;
}

/**
 * Checks if a unique ID is contained in $timeID
 * Every ID gets an array with 1 or 0 values
 * 1 indicates that the room is available at that time(unique ID in $timeID)
 * 0 indicates that the room is not available at that time(unique ID not in $timeID)
 * @param array $timeID, contains information about availabilty based on query() method
 * @param array $uniqueID, contains unique values(IDs)
 * @return array $convertedID, each room ID has information about the availability at a specific time
 */
function convertID(array $timeID, array $uniqueID) : array {
    $convertedID[][] = array();
    for($i = 0; $i < count($uniqueID); $i++){
        $id = $uniqueID[$i];
        for($j = 0; $j < count($timeID); $j++){
            if(in_array($id, $timeID[$j]) == true){
                $convertedID[$i][$j] = 1;
            }else{
                $convertedID[$i][$j] = 0;
            }
        }

    }
    return $convertedID;
}

/**
 * Checks if room is available in a given time interval
 * If available the FreeRooms object is updated(id, start, end, building, roomnr, info)
 * @param int $minTime, minimum amount of the time, the room should be free
 * @param array $uniqueID, contains unique values(IDs)
 * @param array $convertedID, contains information about the availability at a specific time
 * @return array $output, updated FreeRooms objects, that are available 
 */
//checks availabilty based on minTime and the converted ID array (output convertID())
function checkTime(int $minTime, array $uniqueID, array $convertedID) : array {
    $mind = $minTime;
    $output = array();
    for($i = 0; $i < count($convertedID); $i++){
        $count = 0;
        $free = new FreeRooms();
        //room is available during given time interval
        if(!in_array(0, $convertedID[$i])){                 
            $free->id = $uniqueID[$i];
            $free->free[0][0] = 0;
            $free->free[0][1] = count($convertedID[$i]);
            $output[$i] = $free;
        }else{
            $temp = 0;
            for($j = 0; $j < count($convertedID[$i]); $j++){
                if ($count >= $mind && ($convertedID[$i][$j] === 0 || $j === count($convertedID[$i])-1)) {
                    $free->id = $uniqueID[$i];
                    $free->free[$temp][0] = $j - $count;
                    $free->free[$temp][1] = $j;
                    $output[$i] = $free;
                    $temp++;
                    // $free = new FreeRooms();
                     $count = 0;
            } else if ($convertedID[$i][$j] === 1) {
                $count++;
                }
            }
        }
    }
    updateFreeRoom($output);
    return $output;
}



// $name = 'L022017.2'
// L = categorie
// 0 = 0
// 2 = building
// 1 = building complex number
// 304 = room number

/**
 * Gets building number from shortname
 * @param string $name, shortname as seen above
 * @return string building number
 */
function getBuilding(string $name) : string{
    return substr($name, 2, 1);
}
/**
 * Gets room number from shortname
 * @param string $name, shortname as seen above
 * @return string room number
 */
function getRoomnumber(string $name) : string{
    return substr($name, 4);
}


/**
 * Update information of a FreeRooms object by adding building, room number and info
 * @param array $freerooms, contains several FreeRooms objects
 */
function updateFreeRoom(array $freerooms){

    global $importer;
    $roomdata = $importer->getRooms();

    foreach($freerooms as $rooms){
        $id = $rooms->id;
        foreach($roomdata as $data){
            if($data->id == $id){
                $name = $data->shortName;
                if(!empty($name)){                   
                    $rooms->building = getBuilding($name);
                    $rooms->number = getRoomnumber($name);
                    $rooms->info = $data->name;
                }else{
                    //id merken und in Liste packen
                    continue;
                }
            }else{
                continue;
            }
        }
    }
}


//hier Bearbeitung
/**
 * Combines analyse methods
 * Proceeds time interval and search conditions
 * @param \DateTimeInterface $start, the timestamp at which the FreeRooms object begins counting
 * @param \DateTimeInterdace $end, the timestamp at which the FreeRooms object ends counting
 * @param boolean $debug, if true the function uses test data; if false the function uses real data
 * @param array $conditions, inforamtion to limit the search by certain criteria(set by the user)
 * @return array $output, contains all available rooms which fullfill all given conditions
 */
function getFreeRooms($start, $end, $debug, $conditions) : array
{
    global $importer;
    $importer = new Importer($start, $end, $debug);

    // $minTime = minTimeLength(intval($minTimeIn));
    $minTime = minTimeLength(15);
    $timeID = getIDarray($importer);
    $uniqueID = getUnique1D($timeID);
    $convertedID = convertID($timeID, $uniqueID);

    $rooms = checkTime($minTime, $uniqueID, $convertedID);
    if (isset($conditions['room_number'])) {
        $rooms = getRoomsbyNumber($rooms, $conditions['room_number']);
    }
    if (isset($conditions['building_number'])) {
        $rooms = getRoomsbyBuilding($rooms, $conditions['building_number']);
    }
    if (isset($conditions['room_type'])) {
        echo 'hi';
        echo $conditions['room_type'];
        $rooms = getRoomsByType($rooms, $conditions['room_type']);
    }
    return $rooms;
}

/**
 * Filters available rooms by a given room number
 * @param array $freerooms, contains several FreeRooms objects
 * @param string $numberin, wanted room number
 * @return array $output, contains FreeRooms objects where room number equals the wanted room number
 */

function getRoomsbyNumber(array $freerooms, string $numberin) : array {
    $output = [];
    $number = floatval($numberin);
    foreach($freerooms as $room){
        $roomnumber = floatval($room->number);
        if($roomnumber == $number){
            $output[] = $room;
        }else{
            continue;
        }
    }
    return $output;
}

/**
 * Filters available rooms by a given building number
 * @param array $freerooms, contains several FreeRooms objects
 * @param string $buildingin, wanted building number
 * @param array $output, contains FreeRooms objects located in the wanted building
 */
function getRoomsbyBuilding(array $freerooms, string $buildingin) : array {
    $output = [];
    $building = intval($buildingin);
    foreach($freerooms as $room){
        $roombuilding = intval($room->building);
        if($roombuilding == $building){
            $output[] = $room;
        }else{
            continue;
        }
    }
    return $output;
}

/**
 * Filters rooms by a given room type.
 * 
 * @param array $rooms
 * @param string $roomTypeIndex Index for the array given by Importer::getRoomTypes().
 * 
 * @return array
 */
function getRoomsByType(array $rooms, string $roomTypeIndex) : array
{
    $filteredRooms = [];

    $roomTypes = Importer::getRoomTypes();
    $roomType = $roomTypes[$roomTypeIndex];

    foreach ($rooms as $room) {
        if (!empty($room->info) && strpos($room->info, $roomType) !== false) {
            $filteredRooms[] = $room;
        }
    }
    return $filteredRooms;
}

$importer;


// test methods
// uses test data

// $start = new DateTime("2020-W16-2 08:00:00");
// $end = new DateTime("2020-W16-2 10:00:00");
// $minTime = minTimeLength(60);

// $importer = new Importer($start, $end, true);

// $conditions = [];
// $conditions['building'] = null;
// $conditions['number'] = null;
// $conditions['type'] = "seminar";
// $freerooms = getFreeRooms($start, $end, true, $conditions);
// print_r($freerooms);