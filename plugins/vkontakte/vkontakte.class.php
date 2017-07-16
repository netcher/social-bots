<?php
$inConf = Config::getInstance();
require_once($inConf->dir.'/plugins/vkontakte/classes/vk_poster.class.php');

class vkontaktePlugin {

	private $account;
	
	function __construct($plugin_info, $bot_info) {
		$this->account = new vk_auth($bot_info['user_email'], $bot_info['user_pass'], $bot_info['user_phone'], 1);
	}
	
	function postNews($news, $bot_info) {
		$inConf = Config::getInstance();
		$status = false;
		if ($this->account == null)
			return $status;
		foreach($news as $row) {
			$row['imgSrc'] = $inConf->dir . '/images/temporary/' . $row['imgSrc'];
			$row["text"] = iconv( "CP1251", "UTF-8//IGNORE", $row['text']);
			if($this->account->check_auth())
				$status = $this->account->post_to_public_page($bot_info['page_id'], $row['text'], $bot_info['album_id'], $row['imgSrc'], true, false);
				/*if ($status && $row['imgSrc'])
					unlink($row['imgSrc']);*/
		}
		
		return $status;
	}
	function postMessage($message, $bot_info) {
		$inConf = Config::getInstance();
		$message['imgSrc'] = $inConf->dir . '/images/temporary/' . $message['imgSrc'];
		$status = false;
		if ($this->account == null)
			return $status;
		$message["text"] = iconv( "CP1251", "UTF-8//IGNORE", $message['text']);
		if($this->account->check_auth()) {
			switch ($bot_info['page_type']) {
				case 'public':
					$status = $this->account->post_to_public_page($bot_info['page_id'], $message['text'], $bot_info['album_id'], $message['imgSrc'], true, false);
					break;
				case 'club':
					$status = $this->account->post_to_group($bot_info['page_id'], $message['text'], $bot_info['album_id'], $message['imgSrc'], true, false);
					break;
			}
			/*if ($status && $row['imgSrc'])
				unlink($row['imgSrc']);*/
		}
		return $status;
	}
	
}
?>
