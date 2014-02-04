<?

namespace db_checker\database;

use PDO;

/**
 * Trida pro praci s databazi
 *
 * @param string $jmeno_databaze
 */
class DB extends PDO
{
    private static
    
    $options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"),
    $selectionQue = array('SELECT', 'SHOW');
    
    public $data;
    
    private
    
    $host, $jmeno_databaze,
    $navrat, // Vysledek dotazu
    
    $pocet; // Pocet ovlivnenych radku databaze
    
    /**
     * Zavede spojeni s databazi
     * 
     * @param string $host Server
     * @param string $uzivatel Uzivatel
     * @param string $heslo Heslo
     * @param string $databaze Databaze
     */
    public function __construct($host, $uzivatel, $heslo, $databaze)
    {
        parent::__construct("mysql: host=$host; dbname=$databaze", $uzivatel, $heslo, self::$options);
        
        $this->host = $host;
        $this->jmeno_databaze = $databaze;
    }
    
    public function vratJmenoServeru()
    {
        return $this->host;
    }
    
    public function vratJmenoDB()
    {
        return $this->jmeno_databaze;
    }
    
    /**
     * Nacte data z databaze dle dotazu
     * @param $dotaz = array(), Pole obsahujici parametry dotazu
     * 'dotaz' = MySQL dotaz
     * 'parametry' = parametry dotazu
     * 'vse' = vraci vsechny polozky dotazu, jinak pouze 1 radek
     * 'assoc' = klice vysledneho pole ASSOC nebo NUM
     * 'dimenze' = upravi dvourozmerne pole nactennych dat (v pripade nacitaneho 1 sloupce) na jednorozmerne
     */
    public function dotaz($dotaz = array())
    {
        // Provedeni dotazu
        try {
            $this->navrat = parent::prepare($dotaz['dotaz']);
            $dotaz['parametry'] ? $this->navrat->execute($dotaz['parametry']) : $this->navrat->execute();
            
            // Vrati pole vsech zaznamu tabulky nebo 1 konkretni
            if ( in_array(mb_substr($dotaz['dotaz'], 0, mb_strpos($dotaz['dotaz'], " ")), self::$selectionQue) ) {
                if ( $dotaz['vse'] ) {
                    $this->data = $dotaz['assoc'] ? $this->navrat->fetchAll(PDO::FETCH_ASSOC) : $this->navrat->fetchAll(PDO::FETCH_NUM);
                } else {
                    $this->data = $dotaz['assoc'] ? $this->navrat->fetch(PDO::FETCH_ASSOC) : $this->navrat->fetch(PDO::FETCH_NUM);
                }
            }
        }
        catch(PDOException $e){
            echo "<br />".$dotaz['dotaz']."<br /><br />\n
            Vyskytla se chyba při spolupráci s databází.<br />\n
            ".$e->getMessage()."\n";
        }
        
        // Zaznamy obsahuji pouze 1 sloupec
        if ( $dotaz['dimenze'] && is_array($this->data[0]) && count($this->data[0]) == 1 )
        {
            foreach ( $this->data as $key => $val )
            {
                foreach ( $val as $k => $v )
                {
                    if ( $dotaz['assoc'] )
                    {
                        unset($this->data[$key]);
                        $this->data[$k."-".$key] = $v;
                    }
                    else $this->data[$key] = $v;
                }
            }
        }
        
        if ( $this->data ) return $this->data;
    }
    
    // Ulozi data do databaze, parametry obsahuji hodnoty a jsou povinne
    public function vlozeni($dotaz, $parametry=array())
    {
        // Vynulovani citace ovlivnenych radku
        $this->pocet = 0;
        
        // Provedeni dotazu
        try
        {
            $this->navrat = parent::prepare($dotaz);
            foreach ( $parametry as $hodnoty )
            {
                    $this->navrat->execute($hodnoty);
                    $this->pocet++;
            }
        }
        catch(PDOException $e)
        {
            echo "Vyskytla se chyba při spolupráci s databází.<br />\n";
            echo $e->getMessage(),"\n";
        }
        
        // Vytvoreni pole ovlivnenych ID
        $posledni = parent::lastInsertId();
        $ovlivnene_ID = array();
        for ( $i = $posledni; $i > $posledni - $this->pocet; $i--)
        {
            $ovlivnene_ID[] = $i;
        }
        $ovlivnene_ID = array_reverse($ovlivnene_ID);
        
        return $ovlivnene_ID;
    }
    
}

?>