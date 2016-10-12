<?php
require_once('autoloader.php');

$_SERVER['start'] = microtime(true);
$distMax = 2000;

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

        $brewery = new \models\brewery();
        $brewery->setDistMax($distMax);
        $brewery->setCoordsHome(array('lat' => $lat, 'long' => $long));
        $brewery->getBreweries();
    } else {
        throw new Exception('Please enter two parameters latitude and longitude! Example: "php run.php 51.355468 11.100790".');
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
