<?

namespace db_checker\checker;

use db_checker\storage;
use db_checker\database\Queries;

/**
 * Class for 2 databases comparison
 *
 * @param $conn, Both databases connection
 * @param $connInfo, Information schema connection
 * @param $data0, DB1 name
 * @param $data1, DB2 name
 * @param $d0, DB 0 missing tables
 * @param $d1, DB 1 missing tables
 * @param $db0, DB 0 same tables description
 * @param $db1, DB 1 same tables description
 * 
 */
class CompareDBs
{
    
    private
    
    $cache,
    $conn = array(), $connInfo,
    $data0 = array(), $data1 = array(),
    $d0 = array(), $d1 = array(),
    $db0 = array(), $db1 = array(),
    $allTabs0 = array(), $allTabs1 = array();
    
    public
    
    $tabs0 = array(), $tabs1 = array();
    
    public function __construct()
    {
        $this->cache = storage::getInstance();
        
        $this->conn[] = $this->cache->db0;
        $this->conn[] = $this->cache->db1;
        $this->connInfo[] = $this->cache->db2;
        $this->connInfo[] = $this->cache->db3;
    }
    
    /**
     * @return array(), DB settings (name, def. charset and collation)
     */
    public function dbInfo()
    {
        foreach ( $this->conn as $k => $db ) {
            $this->{host.$k} = $db->vratJmenoServeru();
            $this->{data.$k} = $db->vratJmenoDB();
            ${set.$k} = Queries::dbSet($this->connInfo[$k], $this->{data.$k});
        }
        
        return array('host0' => $this->host0,
                     'db0' => $this->data0,
                     'db0_nastav' => $set0,
                     'host1' => $this->host1,
                     'db1' => $this->data1,
                     'db1_nastav' => $set1);
    }
    
    /**
     * Nacte seznam tabulek z obou databazi
     */
    public function tabsList()
    {
        foreach ( $this->connInfo as $k => $db ) {
            $this->{allTabs.$k} = Queries::tables($db, $this->{data.$k});
        }
    }
    
    /**
     * Vybere vybrane tabulky ze vsech dostupnych
     */
    public function selectedTabs($section)
    {
        $alphabet = array(
            0=> array('a', 'b', 'c', 'd', 'e', 'f', 'g'),
            1=> array('h', 'i', 'j', 'k', 'l', 'm'),
            2=> array('n', 'o', 'p', 'q', 'r', 's', 't'),
            3=> array('u', 'v', 'w', 'x', 'y', 'z'),
            4=> array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z')
        );
        for ($i=0; $i<2; $i++) {
            foreach ($this->{allTabs.$i} as $tab) {
                if ( in_array(strtolower(mb_substr($tab, 0, 1)), $alphabet[$section]) ) $this->{tabs.$i}[] = $tab;
            }
        }
    }
    
    /**
     * Nacte nastaveni tabulek v kazde DB
     */
    public function tabulky($tabs0 = array(), $tabs1 = array())
    {
        foreach ( $this->conn as $k => $db ) {
            foreach ( ${tabs.$k} as $tab ) {
                ${tabsDesc.$k}[$tab] = Queries::tabSet($this->connInfo[$k], $tab, $this->{data.$k});
            }
        }
        
        return array($tabsDesc0, $tabsDesc1);
    }
    
    /**
     * Zjisti chybejici tabulky v kazde DB
     */
    public function chybTab()
    {
        $this->d0 = array_diff($this->tabs1, $this->tabs0);
        $this->d1 = array_diff($this->tabs0, $this->tabs1);
        
        self::chybTabTvorba();
        
        return array($this->d0, $this->d1);
    }
    
    /**
     * Nacte create dotazy k chybejicim tabulkam
     */
    private function chybTabTvorba()
    {
        for ( $i = 0; $i <= 1; $i++ )
        {
            $db = $i==0 ? 1 : 0;
            foreach ( $this->{d.$i} as $k => $tab )
            {
                $vysl = Queries::createTab($this->conn[$db], $tab);
                $this->{d.$i}[$tab] = $vysl[1];
                unset($vysl);
                unset($this->{d.$i}[$k]);
            }
        }
    }
    
    /**
     * Read engines list
     * 
     * @return array()
     */
    public function engines()
    {
        return $this->connInfo[0]->dotaz(array(
            'dotaz' => "SELECT ENGINE FROM ENGINES",
            'vse' => 1,
            'dimenze' => 1
        ));
    }
    
    /**
     * Read collations list
     * 
     * @return array()
     */
    public function collations()
    {
        return $this->connInfo[0]->dotaz(array(
            'dotaz' => "SELECT COLLATION_NAME FROM COLLATIONS",
            'vse' => 1,
            'dimenze' => 1
        ));
    }
    
    /**
     * Nacte sloupce shodnych tabulek a porovna je
     *
     * @param $databaze = array(), pole prihlasovacich udaju k databazim
     * @param $shodne = array(), pole shodnych tabulek v obou databazich
     * @return array($jm0, $db0, $d0, $dk0, $jm1, $db1, $d1, $dk1)
     * $jm0, jmeno databaze1
     * $db0, popis vsech klicu databaze1 ze shodnych tabulek
     * $d0, sloupce z databaze2 chybejici v databazi1
     * $dk0, popis klicu databaze1, ktere maji jine nastaveni v databzi2
     * $jm1, jmeno databaze2
     * $db1, popis vsech klicu databaze2 ze shodnych tabulek
     * $d1, sloupce z databaze1 chybejici v databazi2
     * $dk1, popis klicu databaze2, ktere maji jine nastaveni v databzi1
     */
    public function sloupce($shodne)
    {
        foreach ( $this->conn as $k => $db ) {
            foreach ( $shodne as $tabulka ) {
                $this->{db.$k}[$tabulka] = Queries::describeTab($db, $tabulka);
            }
        }
        
        self::zmenIndexy('db0');
        self::zmenIndexy('db1');
        
        $d0 = self::chybKlice($this->db0, $this->db1);
        $d1 = self::chybKlice($this->db1, $this->db0);
        
        $dk0 = self::rozdilShodneKlice($this->db1, $this->db0);
        $dk1 = self::rozdilShodneKlice($this->db0, $this->db1);
        
        return array('db0_vseKlice' => $this->db0,
                     'db0_chybKlice' => $d0,
                     'db0_rozdKlice' => $dk0,
                     'db1_vseKlice' => $this->db1,
                     'db1_chybKlice' => $d1,
                     'db1_rozdKlice' => $dk1);
    }
    
    /**
     * Zmeni indexy poli z cisel na nazev klicu tabulek z databaze
     *
     * @param $array = array(), puvodni ciselne indexy
     * @return $array = array(), zmenene indexy na nazvy klicu
     */
    private function zmenIndexy($co)
    {
        foreach ( $this->$co as $k => $tab ) {
            foreach ( $tab as $key => $pole ) {
                $this->{$co}[$k][$tab[$key]['Field']] = array_slice($pole, 1, -2);
                unset($this->{$co}[$k][$key]);
            }
        }
    }
    
    /**
     * Zjisti chybejici indexy mezi 2 poli
     *
     * @param $array1 = array(), pole s hledanymi indexy
     * @param $array2 = array(), prohledavane pole
     * @return $rozdil = array(), pole chybejich indexu v poli 2
     */
    private function chybKlice($array1, $array2)
    {
        $rozdil = array();
        
        foreach ( $array1 as $jm => $tab )
        {
            if ( array_diff_key($array2[$jm], $array1[$jm]) ) {
                $rozdil[$jm] = array_diff_key($array2[$jm], $array1[$jm]);
            }
        }
        
        foreach ( $rozdil as $tab => $klice )
        {
            $tab_klice = array_keys($array2[$tab]);
            
            foreach ( $klice as $klic => $hodnoty )
            {
                $rozdil[$tab][$klic]['prev'] = 0;
                if ( array_search($klic, $tab_klice) > 0 ) $rozdil[$tab][$klic]['prev'] = $tab_klice[array_search($klic, $tab_klice)-1];
            }
        }
        
        return $rozdil;
    }
    
    /**
     * Porovna vlastnosti klicu tabulek
     *
     * @param $array1 = array(), pole s hledanymi indexy
     * @param $array2 = array(), prohledavane pole
     * @return $rozdil = array(), pole klicu s rozdilnymi vlastnostmi oproti poli 2
     */
    private function rozdilShodneKlice($array1, $array2)
    {
        $rozdil = array();
        
        foreach ( $array1 as $tabulka => $obsah )
        {
            foreach ( $obsah as $klic => $hodnota )
            {
                if ( array_key_exists($klic, $array2[$tabulka]) && array_diff($array2[$tabulka][$klic], $array1[$tabulka][$klic]) ) {
                    $rozdil[$tabulka][$klic] = array_diff($array2[$tabulka][$klic], $array1[$tabulka][$klic]);
                }
                
            }
        }
        
        return $rozdil;
    }
}
?>