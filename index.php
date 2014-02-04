<?
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
    header('Pragma: no-cache'); // HTTP 1.0.
    header('Expires: 0'); // Proxies.
    
    mb_internal_encoding("UTF-8");
    
    // Spusteni incializace
    include_once("./db_checker/start.php");
    $initialize = new start();
    $initialize->lessCSS();
    
    use db_checker\storage;
    $cache = storage::getInstance();
    
    use db_checker\database\DB;
    $cache->db0 = new DB("localhost", "spanielovasvj_cz", "chat_edit", "spanielovasvj_cz_isak");
    $cache->db1 = new DB("localhost", "spanielovasvj_cz", "chat_edit", "spanielovasvj_cz_isak2");
    $cache->db2 = new DB("localhost", "spanielovasvj_cz", "chat_edit", "information_schema");
    $cache->db3 = new DB("localhost", "spanielovasvj_cz", "chat_edit", "information_schema");
    
    use db_checker\views\CompareDBsView;
    
    $cache->str = $_GET['str'] ? $_GET['str'] : "porovnani";
    
?>

<!DOCTYPE html>

<html lang="cs">

<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, minimumscale=1.0, maximum-scale=1.0" />
    
<title>Database checker</title>

<link rel="stylesheet" type="text/css" href="./reset.css" />
<link rel="stylesheet" type="text/css" href="./lessPHP.css" />
<? if ( $cache->str == "porovnani" ) echo "<style>#content { margin-top: 95px; }</style>"; ?>

<script src="./skripty.js" language="JavaScript" type="text/javascript"></script>

</head>

<body>

        <?
            $time = -microtime(true);
            if ( $cache->str == "porovnani" ) new CompareDBsView();
            if ( $cache->str == "zobrazeni" ) include_once("./db_checker/views/ReadDB.php");
            $time += microtime(true);
        ?>
    
    <footer>
        <?
            /*
            $log = new saveData();
            
            $log->readData();
            $log->addData(0, round($time, 5));
            $log->createString()->writeToFile();
            $log->readData();
            */
            
            echo "Aktuální čas operace: ".round($time, 5)." s";
            
            /*
            Průměr iterátoru: ".round(array_sum($log->data[0]) / count($log->data[0]), 5)." s<br />
            Průměr normálně: ".round(array_sum($log->data[1]) / count($log->data[1]), 5)." s";
            */
        ?>
    </footer>

</body>

</html>