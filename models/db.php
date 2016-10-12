<?php
namespace models;

class db
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var object
     */
    private $db;

    /**
     * Construct function
     */
    public function __construct()
    {
        $this->loadConfig();
        $config = $this->config;

        if (!isset($config) || empty($config)) {
            throw new \Exception('Could not load config file.');
        }

        $db = new \mysqli($config['host'], $config['username'], $config['password'], $config['dbname']);

        if ($db->connect_error) {
            throw new \Exception('Database connection failed: ' . $db->connect_error);
        }

        $this->setDb($db);
    }

    /**
     * Get database object
     * @return object
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Set database object
     * @param object $db
     */
    public function setDb($db)
    {
        $this->db = $db;
    }

    /**
     * Load config file and set it to private variable
     */
    private function loadConfig()
    {
        $this->config = parse_ini_file('\config.ini', true);
    }

    /**
     * Insert data from resources folder to database
     * @param array $files
     */
    public function insertToDB($files)
    {
        $db = $this->getDb();
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
            $query = $this->generateInsertQuery($data, $name);
            $db->query($query);
        }
    }

    /**
     * Creates insert data to given table query
     * @param array $data
     * @param string $table
     * @return string
     */
    public function generateInsertQuery($data, $table)
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

    /**
     * Create database tables for resources
     */
    public function createTables()
    {
        $db = $this->getDb();

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
    }
}
