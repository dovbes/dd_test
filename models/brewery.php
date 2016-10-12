<?php
namespace models;
use models\db as dbModel;

class brewery
{
    /**
     * @var int
     */
    private $distMax;

    /**
     * @var int
     */
    private $distHome;

    /**
     * @var int
     */
    private $distRem;

    /**
     * @var array
     */
    private $visited = array();

    /**
     * @var array
     */
    private $coordsHome = array();

    /**
     * @var array
     */
    private $coordsLast = array();

    /**
     * @var bool
     */
    private $finished = false;

    /**
     * Get maximum distance
     * @return int
     */
    public function getDistMax()
    {
        return $this->distMax;
    }

    /**
     * Set maximum distance
     * @param int $distMax
     */
    public function setDistMax($distMax)
    {
        $this->distMax = $distMax;
    }

    /**
     * Get home distance
     * @return int
     */
    public function getDistHome()
    {
        return $this->distHome;
    }

    /**
     * Set home distance
     * @param int $distHome
     */
    public function setDistHome($distHome)
    {
        $this->distHome = $distHome;
    }

    /**
     * Get remaining distance
     * @return int
     */
    public function getDistRem()
    {
        return $this->distRem;
    }

    /**
     * Set remaining distance
     * @param int $distRem
     */
    public function setDistRem($distRem)
    {
        $this->distRem = $distRem;
    }

    /**
     * Get visited breweries
     * @return array
     */
    public function getVisited()
    {
        return $this->visited;
    }

    /**
     * Set visited breweries
     * @param array $visited
     */
    public function setVisited($visited)
    {
        $this->visited = $visited;
    }

    /**
     * Get home coordinates
     * @return array
     */
    public function getCoordsHome()
    {
        return $this->coordsHome;
    }

    /**
     * Set home coordinates
     * @param array $coords
     */
    public function setCoordsHome($coords)
    {
        $this->coordsHome = $coords;
    }

    /**
     * Get last visited coordinates
     * @return array
     */
    public function getCoordsLast()
    {
        return $this->coordsLast;
    }

    /**
     * Set last visited coordinates
     * @param array $coords
     */
    public function setCoordsLast($coords)
    {
        $this->coordsLast = $coords;
    }


    /**
     * Check if loop is finished
     * @return boolean
     */
    public function isFinished()
    {
        return $this->finished;
    }

    /**
     * Set if loop is finished
     * @param boolean $finished
     */
    public function setFinished($finished)
    {
        $this->finished = $finished;
    }

    /**
     * Start brewery visit program
     */
    public function getBreweries()
    {
        // Loads database model and database object
        $dbModel = new dbModel();
        $db = $dbModel->getDb();

        $query = $this->buildQuery(false);

        $visited = $this->getVisited();
        $coordsHome = $this->getCoordsHome();
        $distMax = $this->getDistMax();

        // Gets next brewery from database
        $stmt = $db->prepare($query);
        $stmt->bind_param('dddi', $coordsHome['lat'], $coordsHome['long'], $coordsHome['lat'], $distMax);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $row = $result->fetch_assoc();

        if ($row != false) {
            // Prints and sets new values
            echo "[" . $row['brewery_id'] . "] " . $row['name'] . " : " . $row['latitude'] . ", " .
                $row['longitude'] . " distance " . ceil($row['distance']) . "km\n";

            array_push($visited, $row['brewery_id']);
            $this->setVisited($visited);

            $this->setCoordsLast(array('lat' => $row['latitude'], 'long' => $row['longitude']));
            $distHome = ceil($row['distance']);
            $this->setDistHome($distHome);
            $this->setDistRem($distMax - $distHome);

            // Starts brewery travel loop
            while ($this->isFinished() !== true) {
                $this->nextBrewery($db);
            }
        } else {
            // Finishes program
            $end = microtime(true);
            $time = $end - $_SERVER['start'];
            echo "No breweries found. Program finished in " . round($time, 4) . "s.\n";
        }
    }

    /**
     * Finds, visits and prints closest available brewery
     * @param object $db
     */
    private function nextBrewery($db)
    {
        $query = $this->buildQuery(true);

        $visited = $this->getVisited();
        $coordsHome = $this->getCoordsHome();
        $coordsLast = $this->getCoordsLast();
        $distRem = $this->getDistRem();

        // Gets next brewery from database
        $stmt = $db->prepare($query);
        $stmt->bind_param('ddddddi', $coordsLast['lat'], $coordsLast['long'], $coordsLast['lat'],
            $coordsHome['lat'], $coordsHome['long'], $coordsHome['lat'], $distRem);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $row = $result->fetch_assoc();

        if ($row != false) {
            // Prints and sets new values
            echo "[" . $row['brewery_id'] . "] " . $row['name'] . " : " . $row['latitude'] . ", " .
                $row['longitude'] . " distance " . ceil($row['distnext']) . "km\n";

            array_push($visited, $row['brewery_id']);
            $this->setVisited($visited);

            $this->setCoordsLast(array('lat' => $row['latitude'], 'long' => $row['longitude']));
            $this->setDistHome(ceil($row['disthome']));
            $this->setDistRem($distRem - ceil($row['distnext']));
        } else {
            // Prints values and sets loop finished
            $distMax = $this->getDistMax();
            $distHome = $this->getDistHome();
            echo "HOME: " . $coordsHome['lat'] . ", " . $coordsHome['long'] . " distance " . $distHome . "km\n";

            $distRem = $distRem - $distHome;
            $end = microtime(true);
            $time = $end - $_SERVER['start'];
            echo "Algorithm finished in " . round($time, 4) . "s. Visited " . count($visited) .
                " breweries! Distance traveled: " . ($distMax - $distRem) . "km. Distance remaining: " . $distRem . "km\n\n";

            $this->setFinished(true);
            // Prints beer list
            $this->printBeerList($db, $visited);
        }
    }

    /**
     * Build breweries calculation query
     * @param bool $loop
     * @return string
     */
    public function buildQuery($loop = true)
    {
        if ($loop) {
            // Finds distances to (next brewery + home) from current position
            $query = "SELECT temp.*, temp.distnext + temp.disthome as distance FROM
            (SELECT geocodes.brewery_id, geocodes.latitude, geocodes.longitude,
            (3959 * acos(cos(radians(?)) * cos(radians(geocodes.latitude)) *
            cos(radians(geocodes.longitude) - radians(?)) + sin(radians(?)) *
            sin(radians(geocodes.latitude)))) AS distnext,
            (3959 * acos(cos(radians(?)) * cos(radians(geocodes.latitude)) *
            cos(radians(geocodes.longitude) - radians(?)) + sin(radians(?)) *
            sin(radians(geocodes.latitude)))) AS disthome, breweries.name
            FROM geocodes, breweries WHERE geocodes.brewery_id = breweries.id ";
        } else {
            // Finds distances to next brewery from current position (home)
            $query = "SELECT geocodes.brewery_id, geocodes.latitude, geocodes.longitude,
            (3959 * acos(cos(radians(?)) * cos(radians(geocodes.latitude)) *
            cos(radians(geocodes.longitude) - radians(?)) + sin(radians(?)) *
            sin(radians(geocodes.latitude)))) AS distance, breweries.name
            FROM geocodes, breweries WHERE geocodes.brewery_id = breweries.id ";
        }

        // Gets visited breweries, doesn't visit them again
        $visited = $this->getVisited();
        if (!empty($visited)) {
            foreach ($visited as $id) {
                $query .= " AND geocodes.brewery_id != '" . $id . "'";
            }
        }

        if ($loop) {
            $query .= ") as temp";
        }

        // Checks if remaining distance is still enough to travel, travels to closest one
        $query .= " HAVING distance < ? ORDER BY distance LIMIT 0 , 1";

        return $query;
    }

    /**
     * Print beer names from given breweries
     * @param object $db
     * @param array $visited
     */
    public function printBeerList($db, $visited)
    {
        $query = "SELECT DISTINCT name FROM beers WHERE brewery_id IN ('" . implode("', '", $visited) . "')";
        $beers = $db->query($query)->fetch_all();
        echo "Collected " . count($beers) . " beer types:\n";

        foreach ($beers as $beer) {
            if (is_array($beer)) {
                $beer = reset($beer);
                echo $beer . "\n";
            }
        }

        $end = microtime(true);
        $time = $end - $_SERVER['start'];
        echo "\nProgram finished in " . round($time, 4) . "s.\n";
    }
}
