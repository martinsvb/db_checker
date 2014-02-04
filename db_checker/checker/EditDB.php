<?

namespace db_checker\checker;

use db_checker\storage;
use db_checker\database\Queries;

/**
 * Class for database editing
 *
 * @param $conn, database connection
 * 
 */
class EditDB
{
    
    private
    
    $conn = array(),
    $d1, $d2, $dot;
    
    public
    
    $dat, $tab;
    
    public function __construct()
    {
        $cache = storage::getInstance();
        
        $this->conn[] = $cache->db0;
        $this->conn[] = $cache->db1;
        
        $this->d1 = $_GET['dat'];
        $this->d2 = $this->d1==0 ? 1 : 0;
        $this->dat = $this->conn[$this->d1]->vratJmenoDB();
        
        if ( isset($_GET['tab']) ) {
            $this->tab = $_GET['tab'];
            $this->dot = $_POST[$this->tab];
        }
    }
    
    /**
     * Create missing tab in database
     */
    public function createTab()
    {
        $this->conn[$this->d1]->dotaz(array(
            'dotaz' => $this->dot
        ));
        
        return "Tabulka: <b>".$this->tab."</b> byla vložena do databáze: <b>".$this->dat."</b>.";
    }
    
    /**
     * Copy complete missing tab (structure + data) to database
     * @return = array(), inserted IDs
     */
    public function fullCopyTab()
    {
        self::createTab();
        
        self::copyData();
    }
    
    public function copyData()
    {
        $data = $this->conn[$this->d2]->dotaz(array(
            'dotaz' => "SELECT * FROM `" . $this->tab . "`",
            'vse' => 1,
            'assoc' => 1
        ));
        
        if ( sizeof($data)) {
            $klice = array_keys($data[0]);
            $dotaz = "INSERT INTO `".$this->tab."` (".implode(', ', $klice).") VALUES (".self::hodnoty($klice).")";
            $vysledek = $this->conn[$this->d1]->vlozeni($dotaz, $data);
        }
        
        return "Tabulka: <b>".$this->tab."</b> byla naklonována do databáze: <b>".$this->dat."</b>.";
    }
    
    private function hodnoty($vstup = array())
    {
        foreach ( $vstup as $pol ) $vystup .= ":$pol, ";
        return substr($vystup, 0, -2);
    }
    
    /**
     * Drop table from selected database
     */
    public function dropTab()
    {
        $this->tab = self::getVars();
        
        foreach ( $this->tab as $tab ) {
            $this->conn[$this->d1]->dotaz(array(
                'dotaz' => "DROP TABLE `$tab`"
            ));
            $tabulky[] = $tab;
        }
        
        if ( count($tabulky) > 1 ) return "Tabulky: <br /><b>" . implode("<br />", $tabulky) . "</b><br />byly odstraněny z databáze: <b>".$this->dat.".";
        else return "Tabulka: <b>$tabulky[0]</b> byla odstraněna z databáze: <b>".$this->dat.".";
    }
    
    /**
     * Truncate table in selected database
     */
    public function truncTab()
    {
        $this->tab = self::getVars();
        
        foreach ( $this->tab as $tab ) {
            $this->conn[$this->d1]->dotaz(array(
                'dotaz' => "TRUNCATE `$tab`"
            ));
            $tabulky[] = $tab;
        }
        
        if ( count($tabulky) > 1 ) return "Tabulky: <br /><b>" . implode("<br />", $tabulky) . "</b><br /> v databázi: <b>".$this->dat."</b> byly vyprázdněny.";
        else return "Tabulka: <b>$tabulky[0]</b> v databázi: <b>".$this->dat."</b> byla vyprázdněna.";
    }
    
    /**
     * Repair table in selected database
     */
    public function chngTabSett()
    {
        $this->tab = self::getVars();
        
        return self::chngTab('eng', 'coll');
    }
    
    /**
     * Repair table in selected database
     */
    public function chngTabAuto()
    {
        $this->tab = self::getVars();
        
        return self::chngTab('engOpp', 'collOpp');
    }
    
    /**
     * Repair table in selected database
     */
    public function chngTab($eng, $colla)
    {
        foreach ( $this->tab as $tab ) {
            if ( $_POST[$eng.'#'.$tab] ) {
                $this->conn[$this->d1]->dotaz(array(
                    'dotaz' => "ALTER TABLE `$tab` ENGINE = " . $_POST[$eng.'#'.$tab]
                ));
            }
            
            if ( $_POST[$colla.'#'.$tab] ) {
                $coll = $_POST[$colla.'#'.$tab];
                $this->conn[$this->d1]->dotaz(array(
                    'dotaz' => "ALTER TABLE `$tab` DEFAULT CHARACTER SET ".mb_substr($coll, 0, mb_strpos($coll, "_"))." COLLATE $coll"
                ));
            }
            
            $tabulky[] = $tab;
        }
        
        if ( count($tabulky) > 1 ) return "Tabulkám: <br /><b>" . implode("<br />", $tabulky) . "</b><br /> v databázi: <b>".$this->dat."</b> bylo změněno nastavení.";
        else return "Tabulce: <b>$tabulky[0]</b> v databázi: <b>".$this->dat."</b> bylo změněno nastavení.";
    }
    
    /**
     * Delete column from table
     */
    public function delCol()
    {
        $cols = self::getVars();
        
        foreach ( $cols as $col ) {
            $this->conn[$this->d1]->dotaz(array(
                'dotaz' => "ALTER TABLE `" . $this->tab . "` DROP `$col`"
            ));
            $sloupce[] = $col;
        }
        
        if ( count($sloupce) > 1 ) return "Z tabulky: <b>".$this->tab."</b> byly odstraněny sloupce:<br /><b>" . implode("<br />", $sloupce) . "</b>";
        else return "Z tabulky: <b>".$this->tab."</b> byl odstraněn sloupec: <b>$sloupce[0]</b>";
    }
    
    /**
     * Change column in table
     */
    public function chngCol()
    {
        $cols = self::getVars();
        
        foreach ( $cols as $col ) {
            $vstup = "";
            $vstup .= $_POST[$col.'#type'];
            if ( $_POST[$col.'#coll'] ) $vstup .= " COLLATE ".$_POST[$col.'#coll'];
            if ( $_POST[$col.'#default'] ) $vstup .= " DEFAULT " . $_POST[$col.'#default'];
            $vstup .= $_POST[$col.'#null'] === "YES" ? " NULL" : " NOT NULL";
            if ( $_POST[$col.'#key'] ) {
                $vstup .= $_POST[$col.'#key'] === "PRI" ? " PRIMARY KEY" : "";
                $vstup .= $_POST[$col.'#key'] === "UNI" ? " UNIQUE" : "";
            }
            $vstup .= $_POST[$col.'#extra'] ? " " . $_POST[$col.'#extra'] : "";
            if ( $_POST[$col.'#key'] && $_POST[$col.'#key'] === "MUL" ) $vstup .= ", ADD INDEX ( `$col` )";
            
            $this->conn[$this->d1]->dotaz(array(
                'dotaz' => "ALTER TABLE `" . $this->tab . "` MODIFY $col $vstup"
            ));
            $sloupce[] = $col;
        }
        
        if ( count($sloupce) > 1 ) return "V tabulce: <b>".$this->tab."</b> byly změněny sloupce:<br /><b>" . implode("<br />", $sloupce) . "</b>";
        else return "V tabulce: <b>".$this->tab."</b> byl změněn sloupec: <b>$sloupce[0]</b>";
    }
    
    /**
     * Insert column into table
     */
    public function insCol()
    {
        foreach ( $_POST as $k => $v ) {
            if ( $v == "on" ) $cols[mb_substr($k, mb_strpos($k, "#") + 1)] = $_POST[$k."_col"];
        }
        
        foreach ( $cols as $col => $que ) {
            $this->conn[$this->d1]->dotaz(array(
                'dotaz' => "ALTER TABLE `" . $this->tab . "` ADD $que"
            ));
            $sloupce[] = $col;
        }
        
        if ( count($sloupce) > 1 ) return "Do tabulky: <b>".$this->tab."</b> byly vloženy sloupce:<br /><b>" . implode("<br />", $sloupce);
        else return "Do tabulky: <b>".$this->tab."</b> byl vložen sloupec: <b>$sloupce[0]</b>.";
    }
    
    /**
     * Get array of chosen tables and columns by checkboxes
     */
    private function getVars()
    {
        array_pop($_POST);
        
        foreach ( $_POST as $k => $val ) {
            if ( $val == "on" ) {
                $vrs[] = mb_substr($k, mb_strpos($k, "#") + 1);
                unset($_POST[$k]);
            }
        }
        
        return $vrs;
    }
}

?>