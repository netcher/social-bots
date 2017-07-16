<?php

require_once "Config.php";

class DataBase {

    private static $instance;
	
	public $db_link;

    private function __construct(){
        
		$inConf = Config::getInstance();
		$dbConnString = "mysql:host=" . $inConf->db_host . "; dbname=" . $inConf->db_base;
		
		$this->db_link = new PDO($dbConnString, $inConf->db_user, $inConf->db_pass);

		//$this->db_link->setAttribute(PDO_ATTR_ERRMODE, PDO_ERRMODE_EXCEPTION);
		/* check connection */
		$error = $this->db_link->errorInfo();
		if($error[0] != "") {
		  print "<p>DATABASE CONNECTION ERROR:</p>";
		  print_r($error);
		}
        
	}

    private function __clone() {}


    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }        
        return self::$instance;
    }
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function query($sql, $values, $limit){
		$inConf = Config::getInstance();
		
		$stmt = $this->db_link->prepare($sql);
		if ($stmt->execute($values)) {
			return $stmt;
		} else return false;
	}
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function numRows($stmt){
		return count(self::fetchAll($stmt));
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function fetchRow($stmt){
		return $stmt->fetch();
	}
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function fetchAll($stmt){
		return $stmt->fetchAll();
	}
}
?>