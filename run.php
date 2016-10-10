<?php
require_once('autoloader.php');

$dbmodel = new \models\db();
$db = $dbmodel->getDb();
$distance = 2000;

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

        getBreweries($db, $lat, $long, $distance);
    } else {
        throw new Exception('Please enter two parameters latitude and longitude! Example: "php run.php 51.355468 11.100790".');
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

function getBreweries($db, $lat, $long, $distance)
{
    $query = "SELECT geocodes.brewery_id, geocodes.latitude, geocodes.longitude,
        (3959 * acos(cos(radians(?)) * cos(radians(geocodes.latitude)) *
        cos(radians(geocodes.longitude) - radians(?)) + sin(radians(?)) *
        sin(radians(geocodes.latitude)))) AS distance, breweries.name
        FROM geocodes, breweries WHERE geocodes.brewery_id = breweries.id
        HAVING distance < ? AND distance != 0 ORDER BY distance LIMIT 0 , 1";

    $stmt = $db->prepare($query);
    $stmt->bind_param('dddd', $lat, $long, $lat, $distance);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $row = $result->fetch_assoc();

    if ($row != false) {
        echo "[" . $row['brewery_id'] . "] " . $row['name'] . " : " . $row['latitude'] . ", " .
            $row['longitude'] . " distance " . ceil($row['distance']) ."\n";

        $disrem = $distance - ceil($row['distance']);
        getBreweries($db, $row['latitude'], $row['longitude'], $disrem);
    }
}