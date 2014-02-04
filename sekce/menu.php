
<ul class="menu">

<?

    $str = $_GET['str'] ? $_GET['str'] : "";
    
    $polozky = array('data' => "Data", 'test' => "test");
    
    foreach ( $polozky as $k => $v )
    {
        $odk = $k==$str ? "id=left_akt" : "";
        echo "<li><a href='./index.php?str=$k' $odk onclick='(\'dat\').submit();'>$v</a></li>";
    }

?>

</ul>