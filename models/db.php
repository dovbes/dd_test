<?php
namespace models;

class db
{
    /**
     * @var string
     */
    static public $host = "localhost";

    /**
     * @var string
     */
    static public $username = "test";

    /**
     * @var string
     */
    static public $password = "test";

    /**
     * @var string
     */
    static public $dbname = 'dd_test';

    /**
     * @var object
     */
    private $db;

    /**
     * Construct function
     */
    public function __construct()
    {
        $db = new \mysqli(self::$host, self::$username, self::$password, self::$dbname);

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
}
