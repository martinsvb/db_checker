<?

namespace db_checker;

class storage
{
    private
    
    $data = array();
    
    private static $instance; // Instance tridy
    
    // Testuje, zda trida je jiz zalozena
    public static function getInstance()
    {
        if ( !self::$instance instanceof self ) self::$instance = new self();
        
        return self::$instance;
    }
    
    public function __set( $name, $value )
    {
        $this->data[$name] = $value;
    }
    
    public function __get( $name )
    {
        if ( isset( $this->data[$name] ) ) return $this->data[$name];
        
        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }
    
    public function saveItem($point, $name)
    {
        $_SESSION[$point][] = $this->data[$name];
    }
    
    public function saveExternalData($point, $data)
    {
        $_SESSION[$point][] = $data;
    }
    
    public function giveData()
    {
        return $this->data;
    }
    
    public function get_items_string()
    {
        return join(",", $this->globe);
    }
    
    /**  As of PHP 5.1.0  */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }
    
    /**  As of PHP 5.1.0  */
    public function __unset($name)
    {
        unset($this->data[$name]);
    }
}

?>