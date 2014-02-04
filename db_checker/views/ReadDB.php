<?

use db_checker\database\DB;
use db_checker\checker\ReadDB;
use db_checker\views\Header;
use db_checker\storage;

$cache = storage::getInstance();

$dat = $_GET['dat'] ? $_GET['dat'] : "db2";
$tab = $_GET['tab'] ? $_GET['tab'] : NULL;

$zobraz = new ReadDB($dat);

if ( $tab ) {
    $sloupcePopis = $zobraz->sloupcePopis($tab);
    $tabulkaObsah = $zobraz->tabulkaObsah($tab);
}

$db[] = $zobraz->vratJmenoDB();
$db[] = $zobraz->dbSet($db[0]);
$db[] = $zobraz->vratJmenoServeru();

$tabulky = $zobraz->tabulky($db[0]);
foreach ( $tabulky as $tab2 ) {
    $seznam .= "<a href='./index.php?str=zobrazeni&dat=$dat&tab=$tab2'>$tab2</a><br />";
}

$dopln = "<h4>$db[0] (".$db[2].", ".$db[1][0]." - ".$db[1][1].")";
if ( $tab ) $dopln .= ", $tab";
$dopln .= "</h4>";

        $head = new Header();
        $head->box1 = "<h1>DB checker</h1>";
        $head->box2 = $dopln;
        echo $head->makeHead()."<div id='content'>";

echo "<div id='left'>$seznam</div><div id='right'>";

if ( sizeof($sloupcePopis) ) echo sestavTabulku($sloupcePopis, "popis", "popis");

if ( sizeof($tabulkaObsah) ) echo sestavTabulku($tabulkaObsah, "obsah");

echo "</div><div class='cleaner'></div></div>";

/**
 * Sestavi tabulku z pole nactenych dat
 *
 * @return $dopln, vysledna tabulka
 */
function sestavTabulku($array, $popis, $id = "")
{
    $dopln = "";
    
    $nadpis = "<th>" . implode("<th>", array_keys(reset($array)));
    
    if ( $id ) {
        $dopln .= "<br /><span class='odkaz modra' onclick='zobrazSkryj(\"$id\")'>$popis</span>
        <span id='$id' class='skryj'>";
    } else {
        $dopln .= "<span class='odkaz modra'>$popis</span>";
    }
    
    $dopln .= "<table class='persist-area'><thead><tr class='persist-header'>$nadpis</thead><tbody>";
    
    foreach ( $array as $k => $pole ) {
        $dopln .= "<tr>";
        
        foreach ( $pole as $klic => $hodnota ) {
            $dopln .= "<td>";
            if (mb_strlen($hodnota) <= 30) $dopln .= "<input type='text' value='$hodnota' size='".mb_strlen($hodnota)."'>";
            else {
                $rows = mb_strlen($hodnota)<120 ? 2 : (mb_strlen($hodnota)<240 ? 4 : 6);
                $dopln .= "<textarea name='' rows='$rows' cols='60' />$hodnota ".mb_strlen($hodnota)."</textarea>";
            }
        }
    }
    
    $dopln .= "</tbody></table>";
    if ( $id ) $dopln .= "</span><br />";
    return $dopln;
}

?>