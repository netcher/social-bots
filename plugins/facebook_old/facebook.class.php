<?php
require_once('facebook.php');


class facebookPlugin {

	private $account;
	
	function __construct($plugin_info, $bot_info) {
		$this->account = new Facebook(array(
		  'appId'  => $plugin_info['app_id'],
		  'secret' => $plugin_info['app_key'],
		  'cookie' => true,
		  'fileUpload' => true,
		  'scope' => 'user_status,publish_stream,user_photos,photo_upload,manage_pages'
		));
	}
	
	function postNews($news, $bot_info) {
		$inConf = Config::getInstance();
	//get user access token
		$access_token = "AAAFuZArKnP4ABAPzCWh8Xvq46BRI5MLiY4aGdOz2BYzBtULgfnG95znCQ8n19zZCWRx2w0sJ3xajBFZAQhGOZAvIkZCCeIZAE5PogqLt78YQZDZD";
		$this->account->setAccessToken($access_token);
		//get page access token
		$responce = $this->account->api('/'.$bot_info['page_id']);
		$pageId = $responce['id'];
		$accounts = $this->account->api('/me/accounts', 'GET', array('access_token' => $access_token));
		
		foreach($accounts['data'] as $account)
		{
		  if($account['id'] == $pageId)
		  {
			$page_access_token = $account['access_token'];
		  }
		}
		if ($page_access_token == null) {
			echo "You are not admin of this group";
			return false;
			
		}
		$status = false;
		if ($this->account == null)
			return $status;
		//if ($this->account->getUser()) {
			foreach($news as $row) {
				$row['imgSrc'] = $inConf->dir . '/images/temporary/' . $row['imgSrc'];
				$row["text"] = iconv( "CP1251", "UTF-8//IGNORE", $row['text']);
				$a = array(
				'name'       =>  $row['text'],
				'source'  =>  '@' . $row['imgSrc'],
				'access_token' => $page_access_token
				);
				$status = ($this->account->api('/'.$bot_info['page_id'].'/photos', 'post', $a));
				/*if ($status && $row['imgSrc'])
					unlink($row['imgSrc']);*/
			}
		/*} else {
			
			$loginUrl = $this->account->getLoginUrl(array(
				'req_perms' => 'user_status,publish_stream,user_photos,photo_upload,manage_pages'
		   ));
		   */
		
		return $status;
	}
	function postMessage($message, $bot_info) {
		$inConf = Config::getInstance();
		$message['imgSrc'] = $inConf->dir . '/images/temporary/' . $message['imgSrc'];
	
	//get user access token
		$access_token = "AAAFuZArKnP4ABAPzCWh8Xvq46BRI5MLiY4aGdOz2BYzBtULgfnG95znCQ8n19zZCWRx2w0sJ3xajBFZAQhGOZAvIkZCCeIZAE5PogqLt78YQZDZD";
		
		//get page access token
		$responce = $this->account->api('/'.$bot_info['page_id']);
		$pageId = $responce['id'];
		$accounts = $this->account->api('/me/accounts', 'GET', array('access_token' => $access_token));
		
		foreach($accounts['data'] as $account)
		{
		  if($account['id'] == $pageId)
		  {
			$page_access_token = $account['access_token'];
		  }
		}
		if ($page_access_token == null) {
			echo "You are not admin of this group";
			return false;
			
		}
		$status = false;
		if ($this->account == null)
			return $status;
		//if ($this->account->getUser()) {
				$message["text"] = iconv( "CP1251", "UTF-8//IGNORE", $message['text']);
				$a = array(
				'name'       =>  $message['text'],
				'source'  =>  '@' . $message['imgSrc'],
				'access_token' => $page_access_token
				);
				$status = ($this->account->api('/'.$bot_info['page_id'].'/photos', 'post', $a));
				/*if ($status && $row['imgSrc'])
					unlink($row['imgSrc']);*/
		/*} else {
			
			$loginUrl = $this->account->getLoginUrl(array(
				'req_perms' => 'user_status,publish_stream,user_photos,photo_upload,manage_pages'
		   ));
		   */
		
		return $status;
	}
	
	
}
?>
