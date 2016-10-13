<?php
echo "Migration started\n";
$time_start = microtime(true);

require_once('autoloader.php');

try {
    $dbmodel = new \models\db();

    echo "Creating tables\n";
    $dbmodel->createTables();

    $files = array('beers', 'breweries', 'categories', 'geocodes', 'styles');
    $dbmodel->insertToDB($files);

    $time_end = microtime(true);
    $time = $time_end - $time_start;
    echo "Migration finished in " . round($time, 4) . " seconds\n";
} catch (Exception $e) {
    echo $e->getMessage();
}
