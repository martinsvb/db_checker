<?

namespace db_checker\database;

/**
 * Queries library
 *
 */
class Queries
{
    /**
     *  @return create database query
     */
    public function createDB($conn, $dat)
    {
        return $conn->dotaz(array(
                                'dotaz' => "SHOW CREATE DATABASE `$dat`",
                                'vse' => 1,
                                'dimenze' => 1
                                ));
    }
    
    /**
     *  @return database settings
     */
    public function dbSet($conn, $dat)
    {
        return $conn->dotaz(array(
                'dotaz' => "SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME FROM SCHEMATA WHERE SCHEMA_NAME = ?",
                'parametry' => array($dat)
            ));
    }
    
    /**
     *  @return tables contained in database
     */
    public function tables($conn, $dbName = NULL)
    {
        /*
        return $conn->dotaz(array(
                                'dotaz' => "SHOW TABLES",
                                'vse' => 1,
                                'dimenze' => 1
                                ));
        */
        
        return $conn->dotaz(array(
                                'dotaz' => "SELECT TABLE_NAME FROM TABLES WHERE TABLE_SCHEMA = ?",
                                'parametry' => array($dbName),
                                'vse' => 1,
                                'dimenze' => 1
                                ));
    }
    
    /**
     *  @return create table query
     */
    public function createTab($conn, $tab)
    {
        return $conn->dotaz(array(
                                'dotaz' => "SHOW CREATE TABLE `$tab`"
                                ));
    }
    
    /**
     *  @return table description
     */
    public function describeTab($conn, $tab)
    {
        return $conn->dotaz(array(
                                'dotaz' => "SHOW FULL COLUMNS FROM `$tab`",
                                'vse' => 1,
                                'assoc' => 1
                                ));
    }
    
    /**
     *  @return table content
     */
    public function contentOfTab($conn, $tab)
    {
        return $conn->dotaz(array(
                                'dotaz' => "SELECT * FROM `$tab`",
                                'vse' => 1,
                                'assoc' => 1
                                ));
    }
    
    /**
     *  @return table settings
     */
    public function tabSet($conn, $tab, $dat)
    {
        return $conn->dotaz(array(
                    'dotaz' => "SELECT ENGINE, TABLE_COLLATION FROM TABLES WHERE TABLE_NAME = ? && TABLE_SCHEMA = ?",
                    'parametry' => array($tab, $dat),
                    'assoc' => 1
                ));
    }
}

?>