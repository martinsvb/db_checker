
<div id="form" style="display: none;">

<?

$popisky = array("ser" => array("Server"),
                 "uz" => array("Uživatel"),
                 "hes" => array("Heslo"),
                 "dat" => array("Databáze"));

$udaje = parse_ini_file('./.htdata.ini');

$form = "";
for ($i = 1; $i <= 2; $i++)
{
    if ( $i == 1 ) $form .= "<div class='lkont'>";
    if ( $i == 2 ) $form .= "<div class='pkont'>";
    
    foreach ($popisky as $k => $v)
    {
        $typ = $k == "hes" ? "password" : "text";
        $typ = "text";
        $val = isset($udaje[$k.$i]) ? $udaje[$k.$i] : "";
        $form .= "<label for='$k$i'>$v[0] $i</label><br/>\n
                    <input id='$k$i' name='$k$i' type='$typ' size='30' maxlength='30' value='$val' /><br/><br/>\n";
    }
    
    if ( $i == 1 ) $form .= "</div>";
    if ( $i == 2 ) $form .= "</div>";
}

echo "<form name='dat' id='dat' action='' method='post'>\n$form</form>\n";

?>

</div>