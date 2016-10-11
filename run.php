<?php
require_once('autoloader.php');

$dbmodel = new \models\db();
$db = $dbmodel->getDb();
$distance = 2000;
$visited = array();
$start = microtime(true);

try {
    if ($argc == 3) {
        $lat = $argv[1];
        $long = $argv[2];
        if (!preg_match('/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/', $lat)) {
            throw new Exception('Incorrect latitude format. Please enter two parameters latitude and longitude! Example: "php run.php 51.355468 11.100790".');
        }
        if (!preg_match('/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/', $long)) {
            throw new Exception('Incorrect longitude format. Please enter two parameters latitude and longitude! Example: "php run.php 51.355468 11.100790".');
        }

        echo "HOME: " . $lat . ", " . $long . " distance 0km\n";

        getBreweries($lat, $long, $distance, $visited);
    } else {
        throw new Exception('Please enter two parameters latitude and longitude! Example: "php run.php 51.355468 11.100790".');
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

/**
 * Start brewery visit program
 * @param double $lat
 * @param double $long
 * @param int $distance
 * @param array $visited
 */
function getBreweries($lat, $long, $distance, $visited)
{
    global $db;
    $query = "SELECT geocodes.brewery_id, geocodes.latitude, geocodes.longitude,
        (3959 * acos(cos(radians(?)) * cos(radians(geocodes.latitude)) *
        cos(radians(geocodes.longitude) - radians(?)) + sin(radians(?)) *
        sin(radians(geocodes.latitude)))) AS distance, breweries.name
        FROM geocodes, breweries WHERE geocodes.brewery_id = breweries.id ";
    if (!empty($visited)) {
        foreach ($visited as $id) {
            $query .= " AND geocodes.brewery_id != '" . $id . "'";
        }
    }
    $query .= " HAVING distance < ? ORDER BY distance LIMIT 0 , 1";

    $stmt = $db->prepare($query);
    $stmt->bind_param('dddd', $lat, $long, $lat, $distance);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $row = $result->fetch_assoc();

    if ($row != false) {
        echo "[" . $row['brewery_id'] . "] " . $row['name'] . " : " . $row['latitude'] . ", " .
            $row['longitude'] . " distance " . ceil($row['distance']) ."km\n";

        array_push($visited, $row['brewery_id']);
        $coords = array("lathome" => $lat, "latlast" => $row['latitude'],
            "longhome" => $long, "longlast" => $row['longitude']);
        $disrem = $distance - ceil($row['distance']);
        getBreweriesLoop($coords, $disrem, $row['distance'], $visited);
    } else {
        global $start;
        $end = microtime(true);
        $time = $end - $start;
        echo "No breweries found. Program finished in " . round($time, 4) . "s.\n";
    }
}

/**
 * Create loop which finds, visits and prints closest available brewery
 * @param array $coords
 * @param int $distance
 * @param int $disthome
 * @param array $visited
 */
function getBreweriesLoop($coords, $distance, $disthome, $visited)
{
    global $db;
    $query = "SELECT temp.*, temp.distnext + temp.disthome as distance FROM
        (SELECT geocodes.brewery_id, geocodes.latitude, geocodes.longitude,
        (3959 * acos(cos(radians(?)) * cos(radians(geocodes.latitude)) *
        cos(radians(geocodes.longitude) - radians(?)) + sin(radians(?)) *
        sin(radians(geocodes.latitude)))) AS distnext,
        (3959 * acos(cos(radians(?)) * cos(radians(geocodes.latitude)) *
        cos(radians(geocodes.longitude) - radians(?)) + sin(radians(?)) *
        sin(radians(geocodes.latitude)))) AS disthome, breweries.name
        FROM geocodes, breweries WHERE geocodes.brewery_id = breweries.id ";
    if (!empty($visited)) {
        foreach ($visited as $id) {
            $query .= " AND geocodes.brewery_id != '" . $id . "'";
        }
    }
    $query .= ") as temp HAVING distance < ? ORDER BY distance LIMIT 0 , 1";

    $stmt = $db->prepare($query);
    $stmt->bind_param('ddddddd', $coords['latlast'], $coords['longlast'], $coords['latlast'],
        $coords['lathome'], $coords['longhome'], $coords['lathome'], $distance);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $row = $result->fetch_assoc();

    if ($row != false) {
        echo "[" . $row['brewery_id'] . "] " . $row['name'] . " : " . $row['latitude'] . ", " .
            $row['longitude'] . " distance " . ceil($row['distnext']) ."km\n";

        $coords['latlast'] = $row['latitude'];
        $coords['longlast'] = $row['longitude'];
        array_push($visited, $row['brewery_id']);
        $disrem = $distance - ceil($row['distnext']);
        getBreweriesLoop($coords, $disrem, ceil($row['disthome']), $visited);
    } else {
        global $start;
        echo "HOME: " . $coords['lathome'] . ", " . $coords['longhome'] . " distance " .  $disthome. "km\n";
        $end = microtime(true);
        $time = $end - $start;
        echo "Algorithm finished in " . round($time, 4) . "s. Visited " . count($visited) . " breweries!\n\n";

        printBeerList($visited);
    }
}

/**
 * Print beer types from given breweries
 * @param array $visited
 */
function printBeerList($visited)
{
    global $db;

    $query = "SELECT DISTINCT name FROM beers WHERE brewery_id IN ('" . implode("', '", $visited) . "')";
    $beers = $db->query($query)->fetch_all();
    echo "Collected " . count($beers) . " beer types:\n";

    foreach ($beers as $beer) {
        if (is_array($beer)) {
            $beer = reset($beer);
            echo $beer . "\n";
        }
    }

    global $start;
    $end = microtime(true);
    $time = $end - $start;
    echo "\nProgram finished in " . round($time, 4) . "s.\n";
}
