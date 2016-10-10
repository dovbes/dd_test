<?php
echo "Migration started\n";
$time_start = microtime(true);

require_once('autoloader.php');

$dbmodel = new \models\db();
$db = $dbmodel->getDb();

echo "Creating tables\n";
$db->query("CREATE TABLE IF NOT EXISTS beers (id INT, brewery_id INT, name VARCHAR(255), cat_id INT, style_id INT,
  abv DOUBLE, ibu DOUBLE, srm DOUBLE, upc INT, filepath VARCHAR(255), descript TEXT, add_user INT,
  last_mod VARCHAR(255), PRIMARY KEY (id))");

$db->query("CREATE TABLE IF NOT EXISTS breweries (id INT, name VARCHAR(255), address1 VARCHAR(255),
  address2 VARCHAR(255), city VARCHAR(255), state VARCHAR(255), code VARCHAR(255), country VARCHAR(255),
  phone VARCHAR(255), website VARCHAR(255), filepath VARCHAR(255), descript TEXT, add_user INT,
  last_mod VARCHAR(255), PRIMARY KEY (id))");

$db->query("CREATE TABLE IF NOT EXISTS categories (id INT, cat_name VARCHAR(255), last_mod VARCHAR(255),
  PRIMARY KEY (id))");

$db->query("CREATE TABLE IF NOT EXISTS geocodes (id INT, brewery_id INT, latitude VARCHAR(255),
  longitude VARCHAR(255), accuracy VARCHAR(255), PRIMARY KEY (id))");

$db->query("CREATE TABLE IF NOT EXISTS styles (id INT, cat_id INT, style_name VARCHAR(255),
  last_mod VARCHAR(255), PRIMARY KEY (id))");

$files = array('beers', 'breweries', 'categories', 'geocodes', 'styles');
insertToDB($db, $files);

$time_end = microtime(true);
$time = $time_end - $time_start;
echo "Migration finished in " . $time . " seconds\n";

/**
 * Insert data from resources folder to database
 * @param object $db
 * @param array $files
 */
function insertToDB($db, $files)
{
    foreach ($files as $name) {
        echo "Inserting " . $name . " into database\n";
        $file = fopen('resources/' . $name . '.csv', 'r');
        $data = array();
        while(! feof($file))
        {
            $row = fgetcsv($file);
            if ($row != false) {
                array_push($data, $row);
            }
        }

        fclose($file);
        $query = generateInsertQuery($data, $name);
        $db->query($query);
    }
}

/**
 * Creates insert data to given table query
 * @param array $data
 * @param string $table
 * @return string
 */
function generateInsertQuery($data, $table)
{
    $query = "INSERT INTO " . $table;
    $keys = array_keys($data);
    $last = end($keys);
    foreach ($data as $key => $row){
        $query .= "(";
        foreach ($row as $column => $value){
            if ($key != 0) {
                $query .= '"';
            }
            $query .= str_replace('"', '', $value);
            if ($key != 0) {
                $query .= '"';
            }
            if ($column != count($row)-1){
                $query .= ",";
            }
        }
        $query .= ")";
        if ($key == 0) {
            $query .= " VALUES ";
        }
        if ($key != $last && $key != 0){
            $query .= ",";
        }
    }

    return $query;
}
