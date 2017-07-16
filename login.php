<?php
require_once "core/Config.php";
require_once "core/DataBase.php";

$pass = $_POST["pass"];

if ($pass!="") {
	$hash = hash('tiger192,4',$pass);
	
	$inConf = Config::getInstance();
	$inDB = DataBase::getInstance();
	
	$query = "SELECT * FROM `".$inConf->db_prefix."_settings` WHERE `pass` = :hash";
	$values = array(
		":hash" => $hash
	);
	$result = $inDB->query($query, $values, 256);
	$users = $inDB->fetchAll($result);
	if (count($users)>0) {
		$user = array_shift($users);
		$hash_time = hash('tiger192,4',time());
		$end_date = date( 'Y-m-d H:i:s', time() + 60*20 );
		$query = "INSERT INTO `".$inConf->db_prefix."_login` (`user_id`, `hash`, `date_end`) VALUES (:user_id, :hash, :date)";
		$values = array(
			":user_id" => $user['id'],
			":hash" => $hash_time,
			":date" => $end_date
		);
		
		$result = $inDB->query($query, $values, 256);
		setcookie("hash", $hash_time, time() + 60*60*3, '/', '.'.$inConf->domain);
		header('Location: /hack.php');
	} else {
		header('Location: /');
	}
} else
		header('Location: /');


?>