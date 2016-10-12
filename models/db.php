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
}
