<?php
class Config {

    private static $instance;

    private function __construct(){
        
        $cfg_file = '/home/designwe/slava.co.ua/www/bots/includes/config.inc.php';

        if (file_exists($cfg_file)){
        
            include($cfg_file);

            foreach ($_CFG as $id=>$value) {
                $this->{$id} = $value;
            }

        }

        return true;
        
	}

    private function __clone() {}


    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }        
        return self::$instance;
    }


    	
}
?>