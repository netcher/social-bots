<?php
require __DIR__ . '/../../vendor/autoload.php';
require_once('facebook.php');

use Facebook\FacebookRequest;
use Facebook\GraphObject;
use Facebook\FacebookRequestException;


class facebookPlugin {

	private $account;
	
	function __construct($plugin_info, $bot_info) {
//		$this->account = new Facebook(array(
//		  'appId'  => $plugin_info['app_id'],
//		  'secret' => $plugin_info['app_key'],
//		  'cookie' => true,
//		  'fileUpload' => true,
//		  'scope' => 'user_status,publish_stream,user_photos,photo_upload,manage_pages'
//		));

		$access_token = "AAAFuZArKnP4ABAPzCWh8Xvq46BRI5MLiY4aGdOz2BYzBtULgfnG95znCQ8n19zZCWRx2w0sJ3xajBFZAQhGOZAvIkZCCeIZAE5PogqLt78YQZDZD";
		$this->account = new \Facebook\Facebook([
           'app_id' => $plugin_info['app_id'],
           'app_secret' => $plugin_info['app_key'],
           'default_graph_version' => 'v2.9',
           'default_access_token' => $access_token, // optional
         ]);
//        try {
//          // Get the \Facebook\GraphNodes\GraphUser object for the current user.
//          // If you provided a 'default_access_token', the '{access-token}' is optional.
//          $response = $this->account->get('/me');
//        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
//          // When Graph returns an error
//          echo 'Graph returned an error: ' . $e->getMessage();
//          exit;
//        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
//          // When validation fails or other local issues
//          echo 'Facebook SDK returned an error: ' . $e->getMessage();
//          exit;
//        }
//
//        $me = $response->getGraphUser();
//        echo 'Logged in as ' . $me->getName();
//        exit;
	}
	
	function postNews($news, $bot_info) {
		$inConf = Config::getInstance();
	//get user access token
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
		$status = false;
		$inConf = Config::getInstance();
        if(!$message['imgSrc'] && !$message['text']) {
            $status = 'Post is empty';
		    return $status;
        }
		$message['imgSrc'] = $inConf->dir . '/images/temporary/' . $message['imgSrc'];
        if($message['imgSrc'] && !@GetImageSize($message['imgSrc'])) {
            $status = 'Image does not exist';
		    return $status;
        }
		$pageId = $bot_info['page_id'];
		$message["text"] = iconv( "CP1251", "UTF-8//IGNORE", $message['text']);
        $post_data = [
          'message' => $message["text"],
          'source' => $this->account->fileToUpload($message['imgSrc']),
          ];

        try {
          // Returns a `Facebook\FacebookResponse` object
          $response = $this->account->get('/me/accounts');
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit;
        }

        $accounts = $response->getGraphEdge()->asArray();

		foreach($accounts as $account)
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

        try {
          // Returns a `Facebook\FacebookResponse` object
          $response = $this->account->post('/'.$bot_info['page_id'].'/photos', $post_data, $page_access_token);
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit;
        }

        $graphNode = $response->getGraphNode();

        $status = 'Posted with id: ' . $graphNode['id'];
		return $status;
	}
	
	
}
?>
