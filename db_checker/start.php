<?

class start
{

    public function __construct()
    {
        self::runAutoloading();
    }
    
    // Funkce pro zaregistrovani funkce pro autoloading
    public function runAutoloading()
    {
        // Funkce definujici postup autoloadingu
        function autoload($class)
        {
            $class = str_replace("\\", "/", $class);
            
            if ( !include_once($class . ".php") ) {
                throw new \Exception("Chyba načtení třídy $class");
                exit;
            }
        }
        
        spl_autoload_register("autoload");
    }
    
    public function lessCSS()
    {
        include_once('../../lessphp/lessc.inc.php');
        $less = new lessc;
        try {
          $less->checkedCompile('./css.less', 'lessPHP.css');
        } catch (exception $e) {
          echo "fatal error: " . $e->getMessage();
        }
    }
    
    public function browserCheck()
    {
        $isMobile = (bool)preg_match('#\b(ip(hone|od|ad)|android|opera m(ob|in)i|windows (phone|ce)|blackberry|tablet'.
        '|s(ymbian|eries60|amsung)|p(laybook|alm|rofile/midp|laystation portable)|nokia|fennec|htc[\-_]'.
        '|mobile|up\.browser|[1-4][0-9]{2}x[1-4][0-9]{2})\b#i', $_SERVER['HTTP_USER_AGENT'] );
        
        if ( $isMobile ) return "Mobile";
    }
    
    public function parseURL($cesta = "")
    {
        if ( $cesta == "" ) $cesta = $_SERVER['REQUEST_URI'];
        
        $par = parse_url($cesta);
        if ( strpos($par['path'], "/") == 0 ) $par['path'] = substr($par['path'], 1);
        
        $slozky = explode("/", $par['path']);
        
        for ( $i = 0; $i < count($slozky); $i++ ) $slozky[$i] = trim($slozky[$i]);
        
        $slozky = array_slice($slozky, 2);
        
        return $slozky;
    }
    
    public function parseURL2($url) {
        $parsedURL = parse_url($url);
        $parsedPath = explode("/",$parsedURL["path"]);
        return array_filter($parsedPath, function($path) {
            return !empty($path);
        });
    }
}

// Vypise data v poli
function vypis_pole($pole)
{
    echo "<pre>";
    print_r ($pole);
    echo "</pre>";
}

class saveData
{
    private static $soubor = ".htdata.ini";
    public $data = array();
    private $str;
    
    // Nacte obsah cache souboru
    public function readData()
    {
        $this->data = parse_ini_file (self::$soubor, true);
    }
    
    // Sestavi retezec z dvourozmerneho pole pro ulozeni v *.ini souboru
    public function addData($point, $data)
    {
        $this->data[$point][] = $data;
        
        return $this;
    }
    
    // Sestavi retezec z dvourozmerneho pole pro ulozeni v *.ini souboru
    public function createString()
    {
        foreach ( $this->data as $key => $val )
        {
            $this->str .= "[$key]\n";
            foreach ( $val as $k => $v ) $this->str .= "$k=$v\n";
        }
        
        return $this;
    }
    
    public function writeToFile()
    {
        $akt_soubor = fopen(self::$soubor, 'w');
        fwrite($akt_soubor, $this->str);
        fclose($akt_soubor);
    }
}

?>