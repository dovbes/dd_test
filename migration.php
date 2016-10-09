<?php
require_once('autoloader.php');

$dbmodel = new \models\db();
$db = $dbmodel->getDb();

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

//$files = array('beers', 'breweries', 'categories', 'geocodes', 'styles');
$files = array('beers');
insertToDB($db, $files);

/**
 * Insert data from resources folder to database
 * @param object $db
 * @param array $files
 */
function insertToDB($db, $files)
{
    foreach ($files as $name) {
        $data = array_map('str_getcsv', file('resources/' . $name . '.csv'));
        $data = fixArray($data);
        $query = generateInsertQuery($data, $name);
        $db->query($query);
    }
}

/**
 * Fix array got from csv when descriptions adds new lines
 * @param array $data
 * @return array
 */
function fixArray($data)
{
    $results = array();
    $temp = array();
    $count = count($data[0]);
    foreach ($data as $key => $line){
        if (count($line) == $count) {
            $results[$key] = $line;
        }
        else {
            if (!empty($temp)){
                if (count($line) == 1) {
                    if (reset($line) != null) {
                        $temp[count($temp)-1] .= reset($line);
                    }
                } else {
                    $temp[count($temp)-1] .= reset($line);
                    unset($line[0]);
                    $temp = array_merge($temp, $line);
                }
                if (count($temp) == $count){
                    $results[$key] = $temp;
                    $temp = array();
                }
//                elseif (count($temp) > $count) {
//                    $temp = array();
//                }
            } else {
                $temp = $line;
            }
        }

        if ($key == 7774){
            break;
        }
    }
    return $results;
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
