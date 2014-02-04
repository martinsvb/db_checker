<?

namespace db_checker\views;

use ArrayObject;
use ArrayIterator;
use db_checker\database\Queries;
use db_checker\checker\CompareDBs;
use db_checker\checker\EditDB;
use db_checker\views\Header;

/**
 * Class for 2 databases comparison
 *
 * @param $tabulky
 * @param $shodne
 * @param $sloupce
 * @param act, action: tabs or cols
 * @param ab, alphabet range
 */
class CompareDBsView
{
    private
    
    $databaze, $tabulky, $shodne, $sloupce, $engines, $collations,
    $act, $ab;
    
    public function __construct()
    {
        $this->act = $_POST['operace'] ? $_POST['operace'] : ($_GET['act'] ? $_GET['act'] : 'Kontrola tabulek');
        $this->ab = $_POST['ab'] ? $_POST['ab'] : 0;
        if ( $this->act == 'Kontrola tabulek' ) $this->act = 'tabs';
        if ( $this->act == 'Kontrola sloupců shodných tabulek' ) $this->act = 'cols';
        
        if ( $_GET['act2'] ) {
            $edit = new EditDB();
            
            $funkce = array('Vytvořit tabulku' => "createTab",
                            'Klonovat tabulku' => "fullCopyTab",
                            'Vyprázdnit' => "truncTab",
                            'Odstranit' => "dropTab",
                            'Změnit' => "chngTabSett",
                            'Opravit' => "chngTabAuto",
                            'Odstranit sloupce' => "delCol",
                            'Změnit sloupce' => "chngCol",
                            'Vložit sloupce' => "insCol");
            
            $vysl = $edit->$funkce[$_POST['tlacitko']]();
        }
        
        $porovnej = new CompareDBs();
        
        $this->databaze = $porovnej->dbInfo();
        
        $porovnej->tabsList();
        $porovnej->selectedTabs($this->ab);
        $this->shodne = array_intersect($porovnej->tabs0, $porovnej->tabs1);
        
        $this->collations = $porovnej->collations();
        
        $labels = array('a - g', 'h - m', 'n - t', 'u - z', 'Vše');
        
        for ($i=0; $i<5; $i++) {
            $chk = "";
            if ($i == $this->ab) $chk = "checked";
            $dopln .= "<input id='ab$i' name='ab' type='radio' value='$i' $chk /><label for='ab$i'> $labels[$i] &nbsp;&nbsp;&nbsp;</label>";
        }
        
        $ovl = "<div>
        <form action='./index.php?str=porovnani' method='post'>
                $dopln<br />
                <input name='operace' type='submit' value='Kontrola tabulek' class='odkaz zluta' />
                <input name='operace' type='submit' value='Kontrola sloupců shodných tabulek' class='odkaz zluta' />
        </form>
        </div>";
        
        $head = new Header();
        $head->box1 = "<h1>DB checker</h1>";
        if ($ovl) $head->box2 = $ovl;
        if ($vysl) $head->info = $vysl;
        $head->box3 = "<div class='leftHalf margRes'><h3>
        <a href='./index.php?str=zobrazeni&dat=db0'>".$this->databaze['db0']."</a>
         (".$this->databaze['host0'].", ".$this->databaze['db0_nastav'][0]." - ".$this->databaze['db0_nastav'][1].")</h3></div>
        <div class='rightHalf margRes'><h3>
        <a href='./index.php?str=zobrazeni&dat=db1'>".$this->databaze['db1']."</a>
         (".$this->databaze['host1'].", ".$this->databaze['db1_nastav'][0]." - ".$this->databaze['db1_nastav'][1].")</h3></div>";
        echo $head->makeHead();
        
        if ( $this->act == 'Kontrola tabulek' || $this->act == 'tabs' ) {
            $this->tabulky = $porovnej->tabulky($porovnej->tabs0, $porovnej->tabs1);
            $this->chybTab = $porovnej->chybTab();
            $this->engines = $porovnej->engines();
            self::tabulkyVypis();
        }
        
        if ( $this->act == 'Kontrola sloupců shodných tabulek' || $this->act == 'cols' ) {
            $this->sloupce = $porovnej->sloupce($this->shodne);
            $this->tabulky = $porovnej->tabulky($this->shodne, $this->shodne);
            self::sloupceVypis();
        }
        
        echo "</div>";
    }
    
    private function databazeVypis()
    {
        echo "<div class='leftHalf'>
        <table><thead><tr><th>DB<th>def. charset<th>def. collation</thead>
        <tr><td><a href='./index.php?str=zobrazeni&dat=db0'>".$this->databaze['db0']."</a>
        <td>".$this->databaze['db0_nastav'][0]."
        <td>".$this->databaze['db0_nastav'][1]."
        </table>
        </div>
        <div class='rightHalf'>
        <table><thead><tr><th>DB<th>def. charset<th>def. collation</thead>
        <tr><td><a href='./index.php?str=zobrazeni&dat=db1'>".$this->databaze['db1']."</a>
        <td>".$this->databaze['db1_nastav'][0]."
        <td>".$this->databaze['db1_nastav'][1]."
        </table>
        </div>
        <div class='cleaner'></div>";
    }
    
    /**
     * Vypis vysledku porovnani tabulek
     */
    private function tabulkyVypis()
    {
        echo "<h2>Všechny tabulky</h2>
        <div class='leftHalf'>".self::TabVyp(0)."</div>
        <div class='rightHalf'>".self::TabVyp(1)."</div>
        <div class='cleaner'></div>
        <h2>Chybějící tabulky</h2>
        <div class='leftHalf'>".self::chybTabVyp(0)."</div>
        <div class='rightHalf'>".self::chybTabVyp(1)."</div>
        <div class='cleaner'></div>";
    }
    
    /**
     * Vytvori vystup vsech tabulek
     */
    private function TabVyp($db)
    {
        if ( sizeof($this->tabulky[$db]) ) {
            $it = new ArrayIterator( $this->tabulky[$db] );
            
            foreach ( $it as $tab => $descrpt ) {
                $engRozd = false;
                $collRozd = false;
                $engRozd = self::rozdNastav($db, $tab, 'ENGINE', $descrpt['ENGINE']);
                $collRozd = self::rozdNastav($db, $tab, 'TABLE_COLLATION', $descrpt['TABLE_COLLATION']);
                $dopln .= "<tr>
                <td><input name='$db#$tab' id='$db#$tab' type='checkbox' />
                <label for='$db#$tab' class='ruka'>".self::tab($tab)."</label>
                <td><select name='eng#$tab' onchange='document.getElementById(\"$db#$tab\").checked=true;'>".self::sel($this->engines, $descrpt['ENGINE'])."</select>";
                if ($engRozd) $dopln .= "<br /><b>$engRozd</b><input name='engOpp#$tab' type='hidden' value='$engRozd' />";
                $dopln .= "<td><select name='coll#$tab' onchange='document.getElementById(\"$db#$tab\").checked=true;'>".self::sel($this->collations, $descrpt['TABLE_COLLATION'])."</select>";
                if ($collRozd) $dopln .= "<br /><b>$collRozd</b><input name='collOpp#$tab' type='hidden' value='$collRozd' />";
                $dopln .= "<td><a href='./index.php?str=zobrazeni&dat=db$db&tab=$tab' class='plujVpravo'>zobraz</a>";
            }
            
            return "<table>
                <form name='form_$tab' id='form_$tab' action='./index.php?str=porovnani&act=tabs&act2=editace&dat=$db' method='post'>
                <tr><thead><th>Table<th>Engine<th>Collation<th></thead>
                $dopln
                </tbody><tfoot><tr><td colspan='4'>
                <input name='ab' type='hidden' value='".$this->ab."' />
                <input name='tlacitko' type='submit' value='Vyprázdnit' class='odkaz cervena' />
                <input name='tlacitko' type='submit' value='Odstranit' class='odkaz cervena' />
                <input name='tlacitko' type='submit' value='Změnit' class='odkaz modra' />
                <input name='tlacitko' type='submit' value='Opravit' class='odkaz modra' />
                </tfoot></form></table>";
        }
    }
    
    /**
     * Vrati nazev tabulky, pokud shodna s protejskem z 2. DB
     * nebo zvyrazni tabulku pri neshode
     */
    private function tab($tab)
    {
        if ( in_array($tab, $this->shodne ) ) {
            if ( $this->tabulky[0][$tab]['ENGINE'] !== $this->tabulky[1][$tab]['ENGINE'] || $this->tabulky[0][$tab]['TABLE_COLLATION'] !== $this->tabulky[1][$tab]['TABLE_COLLATION'] ) {
                return "<span class='zvyrazni2'>$tab</span>";
            }
            else return $tab;
        }
        else return $tab;
    }
    
    /**
     * Naplni select hodnotami z predaneho pole a vybere nastavenou hodnotu
     */
    private function sel($array = array(), $sel)
    {
        foreach ( $array as $item ) {
            if ( $item == $sel ) $ret .= "<option SELECTED>$item</option>";
            else $ret .= "<option>$item</option>";
        }
        
        return $ret;
    }
    
    /**
     * Vrati vypis nastaveni tabulky z 2. DB, pokud je rozdilne
     */
    private function rozdNastav($dat, $tab, $co, $cemu)
    {
        $dat2 = $dat==0 ? 1 : 0;
        if ( $this->tabulky[$dat2][$tab][$co] !== $cemu ) return $this->tabulky[$dat2][$tab][$co];
    }
    
    /**
     * Vytvori vystup chybejicich tabulek
     */
    private function chybTabVyp($db)
    {
        if ( sizeof($this->chybTab[$db]) )
        {
            foreach ( $this->chybTab[$db] as $tab => $create )
            {
                $druhaDat = $db == 0 ? 1 : 0;
                $dopln .= "<tr><td> <span class='icon-plus' id='tab odr $tab'></span> 
                <span class='ruka' onclick='zobrazSkryj(\"tab $tab\")'>$tab</span>
                <a href='./index.php?str=zobrazeni&dat=db$druhaDat&tab=$tab' class='plujVpravo'>zobraz</a>
                <span id='tab $tab' class='skryj'><br />
                <form name='form_$tab' id='form_$tab' action='./index.php?str=porovnani&act=tabs&act2=editace&dat=$db&tab=$tab' method='post'>
                <textarea name='$tab' id='$tab' rows='10' cols='80' />$create</textarea><br />
                <input name='ab' type='hidden' value='".$this->ab."' />
                <input name='tlacitko' type='submit' value='Vytvořit tabulku' class='odkaz modra' />
                <input name='tlacitko' type='submit' value='Klonovat tabulku' class='odkaz modra' />
                </form>
                </span>";
            }
            
            return "<table>$dopln</table>";
        }
    }
    
    /**
     * Vypis vysledku porovnani sloupcu shodnych tabulek
     */
    private function sloupceVypis()
    {
        echo "<h2>Porovnání nastavení sloupců</h2>
        <div class='leftHalf'>".self::kliceTab('db0_vseKlice', 0, 'db0')."</div>
        <div class='rightHalf'>".self::kliceTab('db1_vseKlice', 1, 'db1')."</div>
        <div class='cleaner'></div>
        <h2>Chybějící sloupce</h2>
        <div class='leftHalf'>".self::kliceTab('db0_chybKlice', 0)."</div>
        <div class='rightHalf'>".self::kliceTab('db1_chybKlice', 1)."</div>
        <div class='cleaner'></div>";
    }
    
    /**
     * Sestavi vystup pro zobrazeni struktury tabulek databaze
     *
     * @param $array = array(), pole obsahujici klice a jejich popis vybranych tabulek
     * @param $odkaz, bude pridan odkaz pro skryti/odkryti popisu dane tabulky
     * @param $pridej, znacka pridavana do id popisu tabulky s odkazem
     * @return $dopln, retezec zobrazujici strukturu tabulek
     */
    private function kliceTab($co, $dat, $pridej = false)
    {
        if ( sizeof($this->sloupce[$co]) )
        {
            $size = count(reset(reset($this->sloupce[$co]))) + 1;
            
            $nadpis = "<th>Chk<th>Name<th>";
            if ( $size == 8 ) {
                $nadpisy = array_keys(reset(reset($this->sloupce[$co])));
                unset($nadpisy[count($nadpisy) - 1]);
                $nadpis .= implode("<th>", $nadpisy);
            }
            else $nadpis .= implode("<th>", array_keys(reset(reset($this->sloupce[$co]))));
            
            $it = new ArrayIterator( $this->sloupce[$co] );
            
            foreach ( $it as $tabName => $tab )
            {
                $popisTab = "<b>$tabName</b> (".$this->tabulky[$dat][$tabName]['TABLE_COLLATION'].")";
                if ( $pridej ) {
                    $id = "$pridej tab $tabName";
                    $popisTab = " <span class='icon-plus' id='$pridej odr $tabName'></span> $popisTab";
                    $dopln .= "<br /><span class='ruka' onclick='zobrazSkryj(\"$id\")'>$popisTab</span>
                    <span id='$id'".self::tabRozd($dat, $tabName).">";
                }
                else {
                    $dopln .= "<br />$popisTab";
                }
                
                $dopln .= "<table><thead>$nadpis</thead>
                    <form name='uprav_$tabName' action='./index.php?str=porovnani&act=cols&act2=editace&dat=$dat&tab=$tabName' method='post'>
                    <tbody>";
                
                foreach ( $tab as $key => $pole )
                {
                    if ( $size == 8 ) $prev = array_pop($pole);
                    
                    $dopln .= "<tr><td><input name='$dat$tabName#$key' id='$dat$tabName#$key' type='checkbox' />";
                    
                    // Rozdilne nastaveni sloupcu
                    if ( $size == 7 ) {
                        $dat2 = $dat==0 ? 1 : 0;
                        $k = "db".$dat2."_rozdKlice";
                        
                        $dopln .= "<td><label for='$dat$tabName#$key' class='ruka'>".self::sl($dat, $tabName, $key)."</label>";
                        $dopln .= "<td><input type='text' name='$key#type' value='".$pole['Type']."' size='10'>";
                        if ( $this->sloupce[$k][$tabName][$key]['Type'] ) $dopln .= "<br /><b>".$this->sloupce[$k][$tabName][$key]['Type']."</b>";
                        
                        array_unshift($this->collations, "");
                        $dopln .= "<td><select name='$key#coll' class='sirkaM'>".self::sel($this->collations, $pole['Collation'])."</select>";
                        if ( $this->sloupce[$k][$tabName][$key]['Collation'] ) $dopln .= "<br /><b>".$this->sloupce[$k][$tabName][$key]['Collation']."</b>";
                        
                        $null = array("YES", "NO");
                        $dopln .= "<td><select name='$key#null'>".self::sel($null, $pole['Null'])."</select>";
                        if ( $this->sloupce[$k][$tabName][$key]['Null'] ) $dopln .= "<br /><b>".$this->sloupce[$k][$tabName][$key]['Null']."</b>";
                        
                        $kliceTypy = array("", "PRI", "UNI", "MUL");
                        $dopln .= "<td><input name='$key#keyprev' type='hidden' value='".$pole['Key']."' />
                        <select name='$key#key'>".self::sel($kliceTypy, $pole['Key'])."</select>";
                        if ( $this->sloupce[$k][$tabName][$key]['Key'] ) $dopln .= "<br /><b>".$this->sloupce[$k][$tabName][$key]['Key']."</b>";
                        
                        $dopln .= "<td><input type='text' name='$key#default' value='".$pole['Default']."' size='5'>";
                        if ( $this->sloupce[$k][$tabName][$key]['Default'] ) $dopln .= "<br /><b>".$this->sloupce[$k][$tabName][$key]['Default']."</b>";
                        
                        $dopln .= "<td><input type='text' name='$key#extra' value='".$pole['Extra']."' size='10'>";
                        if ( $this->sloupce[$k][$tabName][$key]['Extra'] ) $dopln .= "<br /><b>".$this->sloupce[$k][$tabName][$key]['Extra']."</b>";
                    }
                    
                    // Chybejici sloupce
                    if ( $size == 8 ) {
                        $dopln .= "<td><label for='$dat$tabName#$key' class='ruka'>$key</label>";
                        $dopln .= "<td>" . implode("<td>", $pole);
                        $vstup = "$key ";
                        $vstup .= $pole['Type'];
                        if ( $pole['Default'] ) $vstup .= " DEFAULT " . $pole['Default'];
                        if ( $pole['Collation'] ) {
                            $coll = $pole['Collation'];
                            $vstup .= " CHARACTER SET ".mb_substr($coll, 0, mb_strpos($coll, "_"))." COLLATE $coll";
                        }
                        $vstup .= $pole['Null'] == "YES" ? " NULL" : " NOT NULL";
                        if ( $pole['Key'] ) {
                            $vstup .= $pole['Key'] === "PRI" ? " PRIMARY KEY" : "";
                            $vstup .= $pole['Key'] === "UNI" ? " UNIQUE" : "";
                        }
                        $vstup .= $pole['Extra'] ? " " . $pole['Extra'] : "";
                        $vstup .= $prev === 0 ? " FIRST" : " AFTER " . $prev;
                        if ( $pole['Key'] && $pole['Key'] === "MUL" ) $vstup .= ", ADD INDEX ( `$key` )";
                        
                        $dopln .= "<input name='$dat$tabName#".$key."_col' type='hidden' value='$vstup' />";
                    }
                    
                    if ( $key == end(array_keys($tab)) ) {
                        if ( $size == 7 ) {
                            $dopln .= "</tbody><tfoot><tr><td colspan='10'>
                            <input name='ab' type='hidden' value='".$this->ab."' />
                            <input name='tlacitko' type='submit' value='Odstranit sloupce' class='odkaz cervena' />
                            <input name='tlacitko' type='submit' value='Změnit sloupce' class='odkaz modra' />";
                        }
                        if ( $size == 8 ) {
                            $dopln .= "</tbody><tfoot><tr><td colspan='10'>
                            <input name='ab' type='hidden' value='".$this->ab."' />
                            <input name='tlacitko' type='submit' value='Vložit sloupce' class='odkaz modra' />";
                        }
                        $dopln .= "</tfoot></form></table>";
                        if ( $pridej ) $dopln .= "</span>";
                    }
                }
            }
            
            return $dopln;
        }
    }
    
    /**
     * Vrati nazev tabulky, pokud shodna s protejskem z 2. DB
     * nebo zvyrazni tabulku pri neshode
     */
    private function tabRozd($dat, $tabName)
    {
        $k = "db".$dat."_rozdKlice";
        if ( array_key_exists($tabName, $this->sloupce[$k]) ) return;
        $dat2 = $dat==0 ? 1 : 0;
        $k = "db".$dat2."_rozdKlice";
        if ( array_key_exists($tabName, $this->sloupce[$k]) ) return;
        return " class='skryj'";
    }
    
    /**
     * Vrati nazev sloupce, pokud je shodny s protejskem z 2. DB
     * nebo zvyrazni sloupec pri neshode
     */
    private function sl($dat, $tabName, $key)
    {
        $k = "db".$dat."_rozdKlice";
        if ( array_key_exists($tabName, $this->sloupce[$k]) && array_key_exists($key, $this->sloupce[$k][$tabName]) ) return "<span class='zvyrazni2'>$key</span>";
        $dat2 = $dat==0 ? 1 : 0;
        $k = "db".$dat2."_rozdKlice";
        if ( array_key_exists($tabName, $this->sloupce[$k]) && array_key_exists($key, $this->sloupce[$k][$tabName]) ) return "<span class='zvyrazni2'>$key</span>";
        else return $key;
    }
    
    /**
     * Sestavi vystup pro zobrazeni struktury tabulek databaze
     *
     * @param $array = array(), pole obsahujici klice a jejich popis vybranych tabulek
     * @param $odkaz, bude pridan odkaz pro skryti/odkryti popisu dane tabulky
     * @param $pridej, znacka pridavana do id popisu tabulky s odkazem
     * @return $dopln, retezec zobrazujici strukturu tabulek
     */
    private function klice($co, $odkaz = false, $pridej = "")
    {
        if ( count($this->sloupce[$co]) )
        {
            foreach ( $this->sloupce[$co] as $k => $tab )
            {
                if ( $odkaz ) {
                    $id = "$pridej tab $k";
                    $dopln .= "<br /><span class='odkaz modra' onclick='zobrazSkryj(\"$id\")'>Tabulka <b>$k</b></span>
                    <span id='$id' class='skryj'>";
                }
                else {
                    $dopln .= "<br />Tabulka <b>$k</b>";
                }
                
                foreach ( $tab as $key => $pole )
                {
                    $dopln .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    Klíč <b>$key</b><br />";
                    
                    foreach ( $pole as $klic => $hodnota )
                    {
                        $dopln .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        [$klic] = $hodnota<br />";
                    }
                    
                    if ( $key == end(array_keys($tab)) && $odkaz ) $dopln .= "</span>";
                }
            }
            
            return $dopln;
        }
    }
}

?>