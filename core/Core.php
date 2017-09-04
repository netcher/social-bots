<?php
require_once "ResultCode.php";
require_once "Config.php";
require_once "DataBase.php";

if (isset($_POST['action'])) 
	Core::printResult( Core::performAction($_POST['action'], json_decode($_POST['params'], true)));

if (isset($_GET['code'])) 
	Core::auth($_GET['state'], $_GET['code']);
	
if (isset($_GET['action'])) 
	if ($_GET['action'] == 'runSQL' )
		Core::runSQL();
	
class Core {

	function performAction($action, $params) {
		switch ($action) {
			case 'eraseNode':
				$id = $params['id'];
				return Core::eraseNode($id);
				break;
			case 'appendNode':
				$group = $params['group'];
				$nodeText = $params['nodeText'];
				$nodeImgSrc = $params['nodeImgSrc'];
				$nodeDate = $params['nodeDate'];
				return Core::appendNode($group, '', $nodeText, $nodeImgSrc, $nodeDate);
				break;
			case 'editNode':
				$nodeId = $params['nodeId'];
				$nodeText = $params['nodeText'];
				$nodeImgSrc = $params['nodeImgSrc'];
				$nodeDate = $params['nodeDate'];
				return Core::editNode($nodeId, $nodeText, $nodeImgSrc, $nodeDate);
				break;
			case 'setNodeState':
				$nodeId = $params['nodeId'];
				$nodeType = $params['nodeType'];
				$nodeState = !$params['nodeState'];
				return Core::setNodeState($nodeId, $nodeType, $nodeState);
				break;
			case 'runBot':
				$botId = $params['botId'];
				return Core::runBot($botId);
				break;
			default:
			return array( "status" => ResultCode::Warning, "header" => 'Error', 'message' => 'Unknown action');
		}
	}
	
	function eraseNode($id) {
		$inConf = Config::getInstance();
		$inDB = DataBase::getInstance();
		$node = self::getNode($id);
	
		$query = "DELETE FROM `".$inConf->db_prefix."_news` WHERE `id` = :id";
		$values = array(
			":id" => $id
		);
		
		$result = $inDB->query($query, $values, 64);
	
		if ($result) {
			if(@GetImageSize($inConf->dir . '/images/temporary/' . $node['imgSrc'])){
				unlink($node['imgSrc']);
			}
			return array( "status" => ResultCode::Success, "header" => 'Article '.$id, 'message' => 'Article erased!');
		} else {
			return array( "status" => ResultCode::Danger, "header" => 'Article '.$id, 'message' => 'Article erased!');
			echo $result;
		}
	}
	
	function getNode($id) {
		$inConf = Config::getInstance();
		$inDB = DataBase::getInstance();
	
		$query = "SELECT * FROM `".$inConf->db_prefix."_news` WHERE `id` = :id LIMIT 1";
		$values = array(
			":id" => $id
		);
		
		$result = $inDB->query($query, $values, 64);
	
		return $inDB->fetchRow($result);
	}
	
	function appendNode($group, $nodeTitle, $nodeText, $nodeImgSrc, $nodeDate) {
		$timestamp = strtotime($nodeDate);
		$nodeDate = date("Y-m-d", $timestamp);
		
		$inConf = Config::getInstance();
		$inDB = DataBase::getInstance();
		
		$nodeTitle = iconv( "UTF-8", "CP1251//IGNORE", $nodeTitle);
		$nodeText = iconv( "UTF-8", "CP1251//IGNORE", $nodeText);
		$nodeTextHash = iconv( "UTF-8", "CP1251//IGNORE", md5($nodeText));
		if(@GetImageSize($nodeImgSrc)){
			$nodeImgSrc = self::savePhotoToServer($nodeImgSrc);
		} else {
			$nodeImgSrc = "";
		}
		
		$query = "INSERT INTO `".$inConf->db_prefix."_news` (`group`, `title`, `text`, `textHash`, `imgSrc`, `date`) VALUES (:group, :title, :text, :textHash, :imgSrc, :date) ON DUPLICATE KEY UPDATE `textHash` = :textHash;";
		$values = array(
			":title" => $nodeTitle,
			":text" => $nodeText,
			":textHash" => $nodeTextHash,
			":imgSrc" => $nodeImgSrc,
			":date" => $nodeDate,
			":group" => $group
		);
		
		$result = $inDB->query($query, $values, 8192);

		if ($result) {
			return array( "status" => ResultCode::Success, "header" => 'New article', 'message' => 'Article appended!');
		} else {
			return array( "status" => ResultCode::Danger, "header" => 'New article', 'message' => 'Article appended!');
			echo $result;
		}
	}
	
	function editNode($nodeId, $nodeText, $nodeImgSrc, $nodeDate) {
		$timestamp = strtotime($nodeDate);
		$nodeDate = date("Y-m-d", $timestamp);
		$nodeText = iconv( "UTF-8", "CP1251//IGNORE", $nodeText);
		if (stripos($nodeImgSrc, '/') !== false )
			if(@GetImageSize($nodeImgSrc)){
				$oldImg = $nodeImgSrc;
				$nodeImgSrc = self::savePhotoToServer($nodeImgSrc);
				unlink($oldImg);
			} else {
				$nodeImgSrc = "";
			}
		
		$inConf = Config::getInstance();
		$inDB = DataBase::getInstance();
		
		$query = "UPDATE `".$inConf->db_prefix."_news` SET `text`=:text, `imgSrc`=:imgSrc, `date`=:date WHERE `id`=:id";
		$values = array(
			":text" => $nodeText,
			":imgSrc" => $nodeImgSrc,
			":date" => $nodeDate,
			":id" => $nodeId
		);
		$result = $inDB->query($query, $values, 8192);
		
		if ($result) {
			return array( "status" => ResultCode::Success, "header" => 'Article '.$nodeId, 'message' => 'Article edited!');
		} else {
			return array( "status" => ResultCode::Danger, "header" => 'Article '.$nodeId, 'message' => 'Article edited!');
			echo $result;
		}
	}
	
	function setNodeState($nodeId, $nodeType, $nodeState) {
		$inConf = Config::getInstance();
		$inDB = DataBase::getInstance();
		$query = "UPDATE `".$inConf->db_prefix."_news` SET `".$nodeType."`=:nodeState WHERE `id`=:nodeId";
		$values = array(
			":nodeState" => $nodeState,
			":nodeId" => $nodeId
		);
		$result = $inDB->query($query, $values, 64);

		if ($result) {
			return array( "status" => ResultCode::Success, "header" => 'Article '.$nodeId, 'message' => 'State changed to '.(int)$nodeState);
		} else {
			return array( "status" => ResultCode::Danger, "header" => 'Article '.$nodeId, 'message' => '');
			echo $result;
		}
	}
	
	function putToLog($status, $message) {
		$inConf = Config::getInstance();
		$inDB = DataBase::getInstance();
		
		if ($message != null) {
			$query = "INSERT INTO `".$inConf->db_prefix."_log` (`date`, `status`, `message`) VALUES (NOW(), :status, :message)";
			$values = array(
				":status" => $status,
				":message" => $message
			);
			$inDB->query($query, $values, 1012);
		}
	}

	function getNews($bot_id) {
		$inConf = Config::getInstance();
		$inDB = DataBase::getInstance();
		
		$bot = self::getBot($bot_id);
		$bot_group = $bot['group'];
		$bot_type = $bot['type'];
		$query = "SELECT * FROM `".$inConf->db_prefix."_news` WHERE `date` <= CURRENT_DATE() AND `group` = :group AND `".$bot_type."` = 0 AND (`text` != '' OR `imgSrc` != '')";
		$values = array(
			":group" => $bot_group
		);
		$result = $inDB->query($query, $values, 64);
		
		return $inDB->fetchAll($result);
	}
	
	
	// USE 
	////DOMAIN_NAME/core/Core.php?action=runSQL
	function runSQL() {
		$inConf = Config::getInstance();
		$inDB = DataBase::getInstance();
		$query = "SELECT `id`, `vkontakte`, `facebook`, `instantcms` FROM `bot_news`";
		$result = $inDB->query($query, null, 64);
		$data = $inDB->fetchAll($result);
		foreach($data as $row) {
			$values = array(
				'vkontakte' => $row['vkontakte'],
				'facebook' => $row['facebook'],
				'instantcms' => $row['instantcms']
			);
			$query = "UPDATE `bot_news` SET `used` = '".json_encode($values)."' WHERE `id` = ".$row['id'];
			$result = $inDB->query($query, null, 64);
		}
		echo 'success';
	}

	function getArticle($id) {
		$inConf = Config::getInstance();
		$inDB = DataBase::getInstance();
		
		$query = "SELECT * FROM `".$inConf->db_prefix."_news` WHERE `id`=:id LIMIT 1";
		$values = array(
			":id" => $id
		);
		$result = $inDB->query($query, $values, 64);
		
		return $inDB->fetchRow($result);
	}


	function getLog() {
		$inConf = Config::getInstance();
		$inDB = DataBase::getInstance();
		
		$query = "SELECT * FROM `".$inConf->db_prefix."_log`  ORDER BY `date` DESC LIMIT 30";
		$result = $inDB->query($query, null, 64);
		
		return $inDB->fetchAll($result);
	}

	function getBot($bot_id) {
		$res = false;
		$inConf = Config::getInstance();
		$inDB = DataBase::getInstance();
		
		$query = "SELECT * FROM `".$inConf->db_prefix."_bots` WHERE `id`=:id LIMIT 1";
		$values = array(
			":id" => $bot_id
		);
		$result = $inDB->query($query, $values, 64);
		
		return $inDB->fetchRow($result);
	}

	function getBotByGroupAndPlugin($group, $plugin) {
		$res = false;
		$inConf = Config::getInstance();
		$inDB = DataBase::getInstance();
		
		$query = "SELECT * FROM `".$inConf->db_prefix."_bots` WHERE `group` = :group AND `type` = :plugin LIMIT 1";
		$values = array(
			":group" => $group,
			":plugin" => $plugin
		);
		$result = $inDB->query($query, $values, 64);
		
		return $inDB->fetchRow($result);
	}
	
	function getPlugin($plugin_type) {
		$inConf = Config::getInstance();
		$inDB = DataBase::getInstance();
		
		$query = "SELECT * FROM `".$inConf->db_prefix."_plugins` WHERE `type` = :type LIMIT 1";
		$values = array(
			":type" => $plugin_type
		);
		$result = $inDB->query($query, $values, 64);
		
		return $inDB->fetchRow($result);
	}
	
	
	function getGroupsList() {
		$inConf = Config::getInstance();
		$inDB = DataBase::getInstance();
		
		
		$query = "SELECT * FROM `".$inConf->db_prefix."_groups` WHERE user_id = :user_id";
		$result = $inDB->query($query, array(
			':user_id' => self::getUserId()
		), 64);
		
		return $inDB->fetchAll($result);
	}
	
	function getPluginsList() {
		$inConf = Config::getInstance();
		$inDB = DataBase::getInstance();
		
		$query = "SELECT * FROM `".$inConf->db_prefix."_plugins`";
		$result = $inDB->query($query, null, 64);
		
		return $inDB->fetchAll($result);
	}
	
	function getGroupPluginsList($group) {
		$plugins = self::getPluginsList();
		
		foreach($plugins as $plugin) {	
			$bot = self::getBotByGroupAndPlugin($group, $plugin['type']);		
			if ($bot) 
				$plugins_to_use[] = $plugin;
		}
		
		return $plugins_to_use;
	}

	function getNewsList($group, $plugins_to_use, $limit = 300) {
		$inConf = Config::getInstance();
		$inDB = DataBase::getInstance();
		$values[':group'] = $group;

		$query = "SELECT * FROM `".$inConf->db_prefix."_news` WHERE `group` = :group AND ((";
		foreach($plugins_to_use as $plugin) {
			$plugin_type = $plugin['type'];
			$values[':'.$plugin_type] = Core::getCookie($plugin_type);
			$query = $query . '`' . $plugin_type . '` = 0 OR ';
			$subQuery = $subQuery . " OR `".$plugin_type."` = :".$plugin_type;
		}
		$query = substr($query, 0, strlen($query)-4) . ')' . $subQuery . ") ORDER BY `date` ASC LIMIT ".$limit;
		$result = $inDB->query($query, $values, 64);
		
		return $inDB->fetchAll($result);
	}
	
	
	function savePhotoToServer($imgUrl) {
		//saving to file
		
		$inConf = Config::getInstance();
		$imgdata = file_get_contents($imgUrl);
		$imgformat = end(explode('.', $imgUrl));
		$imgname = rand(199122, 1992314) . '.' . $imgformat;
		$imgfilename = $inConf->dir . '/images/temporary/' . $imgname;
		$imghandle = fopen($imgfilename, 'w');
		fwrite($imghandle, $imgdata);
		fclose($imghandle);
		return $imgname;
	}
	
	function printResult($data) {
		$result = '';
		$resultCode = $data["status"];
		$header = $data["header"];
		$message = $data["message"];
		switch($resultCode) {
			case (ResultCode::Success):
				$result = $result . '<h4 status="success" class="alert-heading">'.$header.'</h4>';
				if ($message != '')
					$result = $result . '<p id="message">'.$message.'</p>'; 
				self::putToLog(ResultCode::Success, $header . ': ' . $message);
				break;
			case (ResultCode::Info):
				$result = $result . '<h4 status="info" class="alert-heading">'.$header.'</h4>';
				if ($message != '')
					$result = $result . '<p id="message">'.$message.'</p>'; 
				self::putToLog(ResultCode::Info, $header . ': ' . $message);
				break;
			case (ResultCode::Warning):
				$result = $result . '<h4 status="warning" class="alert-heading">'.$header.'</h4>';
				if ($message != '')
					$result = $result . '<p id="message">'.$message.'</p>'; 
				self::putToLog(ResultCode::Warning, $header . ': ' . $message);
				break;
			case (ResultCode::Danger):
				$result = $result . '<h4 status="error" class="alert-heading">'.$header.'</h4>';
				$result = $result . '<p id="postStatus">Unknown error occurred!</p>';
				if ($message != '')
					$result = $result . '<p id="message">'.$message.'</p>'; 
				self::putToLog(ResultCode::Danger, $header . ': Unknown error occurred! ' . $message);
				break;
		}
		echo $result;
	}
	
	
	function runBot($botId) {
		$status = ResultCode::Danger;
		$message = '';
		
		$inConf = Config::getInstance();
		 $bot = self::getBot($botId);
		 $plugin = self::getPlugin($bot['type']);
		 $pluginType = $plugin["type"];
		 $news = self::getNews($botId);
		 if (count($news) > 0) {
			 require_once($inConf->dir.'/plugins/'.$pluginType.'/'.$pluginType.'.class.php');
			 $pluginClassName = $pluginType.'Plugin';
			 $account = new $pluginClassName(json_decode($plugin['settings'], true), json_decode($bot['settings'], true), $minicurl);
			 foreach($news as $article)
			 $article['imgSrc'] = $article['imgSrc'];
			 if ( $account->postMessage($article, json_decode($bot['settings'], true)) ) {
				$status = ResultCode::Success;
				$message = 'Article published';
				self::setNodeState($article["id"], $pluginType, 1);
			 } else {
				$message .= 'Cannot post message';
			 }
		} else {
			$status = ResultCode::Info;
			$message = 'Nothing to publish';
		}
		return array( "status" => $status, "header" => $bot['name'], 'message' => $message);
	}
	
	
	function auth($state, $code) {
		$inConf = Config::getInstance();
	
		$plugin = (self::getPlugin('facebook'));
		$plugin = json_decode($plugin['settings'], true);
		header("Location: ". "https://graph.facebook.com/oauth/access_token?
                client_id=".$plugin['app_id']."
               &redirect_uri=".$inConf->site_url."/hack.html
               &client_secret=".$plugin['app_key']."
               &code=$code");
	}
	
	function checkAuth() {
		$status = false;
		if (!isset($_COOKIE["hash"])) return $status;
		$cookie = $_COOKIE["hash"];
		
		$inConf = Config::getInstance();
		$inDB = DataBase::getInstance();
		
		$query = "SELECT * FROM `".$inConf->db_prefix."_login` WHERE `hash` = :cookie AND `date_end` > NOW()";
		$values = array(
			":cookie" => $cookie
		);
		$result = $inDB->query($query, $values, 256);
		if ((count($inDB->fetchAll($result))) > 0)
			$status = true;
		
		return $status;
	}
	
	function getUser() {
		$status = false;
		if (!isset($_COOKIE["hash"])) return $status;
		$cookie = $_COOKIE["hash"];
		
		$inConf = Config::getInstance();
		$inDB = DataBase::getInstance();
		
		$query = "SELECT * FROM `".$inConf->db_prefix."_login` WHERE `hash` = :cookie AND `date_end` > NOW()";
		$values = array(
			":cookie" => $cookie
		);
		$result = $inDB->query($query, $values, 256);
		$hashes = $inDB->fetchAll($result);
		if (count($hashes)>0) {
			$hash = array_shift($hashes);
			$user_id = $hash['user_id'];
			$query = "SELECT * FROM `".$inConf->db_prefix."_settings` WHERE `id` = :user_id";
			$values = array(
				":user_id" => $user_id
			);
			$result = $inDB->query($query, $values, 256);
			$users = $inDB->fetchAll($result);
			if (count($users)>0) 
				$user = array_shift($users);
		}
		
		return $user;
	}
	function getUserId() {
		$status = false;
		if (!isset($_COOKIE["hash"])) return $status;
		$cookie = $_COOKIE["hash"];
		
		$inConf = Config::getInstance();
		$inDB = DataBase::getInstance();
		
		$query = "SELECT * FROM `".$inConf->db_prefix."_login` WHERE `hash` = :cookie AND `date_end` > NOW()";
		$values = array(
			":cookie" => $cookie
		);
		$result = $inDB->query($query, $values, 256);
		$hashes = $inDB->fetchAll($result);
		if (count($hashes)>0) {
			$hash = array_shift($hashes);
			$user_id = $hash['user_id'];
		}
		
		return $user_id;
	}
	
	function getCookie($name) {
		if (isset($_COOKIE[$name])) return $_COOKIE[$name];
	}
	
	function setCookie($name, $value, $time_end) {
		setcookie($name, $value, $time_end, null, null, null, true);
	}
	
}
?>