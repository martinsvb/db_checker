<?

namespace db_checker\checker;

use db_checker\storage;
use db_checker\database\Queries;

/**
 * Class for database viewing
 *
 * @param $conn, database connection
 */
class ReadDB
{
    
    private
    
    $conn = array();
    
    public function __construct($dat)
    {
        $cache = storage::getInstance();
        
        $this->conn = $cache->$dat;
        $datInfo = $dat == "db0" ? "db2" : "db3";
        $this->connInfo = $cache->$datInfo;
    }
    
    public function vratJmenoServeru()
    {
        return $this->conn->vratJmenoServeru();
    }
    
    public function vratJmenoDB()
    {
        return $this->conn->vratJmenoDB();
    }
    
    /**
     * Nacte nastaveni databaze
     *
     * @return array(), def. charset a collation
     */
    public function dbSet($dat)
    {
        return Queries::dbSet($this->connInfo, $dat);
    }
    
    /**
     * Nacte tabulky v databazi
     *
     * @return array(), seznam tabulek
     */
    public function tabulky($dat)
    {
        return Queries::tables($this->connInfo, $dat);
    }
    
    /**
     * Nacte popis sloupcu tabulky
     *
     * @param $tab, tabulka
     * @return array(), popis sloupcu
     */
    public function sloupcePopis($tab)
    {
        return Queries::describeTab($this->conn, $tab);
    }
    
    /**
     * Nacte obsah tabulky
     *
     * @param $tab, tabulka
     * @return array(), obsah tabulky
     */
    public function tabulkaObsah($tab)
    {
        return Queries::contentOfTab($this->conn, $tab);
    }
}

?>