<?php

require_once "/home/designwe/slava.co.ua/www/bots/includes/minicurl.class.php";

class instantcmsPlugin {

	private $minicurl;
	
	function __construct($plugin_info, $bot_info) {
		if ( ! $this->minicurl ) 
			$this->minicurl = new minicurl(TRUE, COOKIES_FILE, 'Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1');
	}
	
	function postMessage($message, $bot_info) {
		$inConf = Config::getInstance();
		$status = false;
		$message["text"] = iconv( 'CP1251', "UTF-8//IGNORE", $message["text"]);
		
		$article['title'] =   'Интересные факты';
		$posSpace = stripos(substr($message["text"], -strlen($message["text"]) + 220), ' ') + 220;
		$article['description'] =  substr($message["text"], 0, $posSpace) . '...' ;
		$article['content'] =  $message["text"];
			 
		$article['imgSrc'] =  'http://slava.co.ua/bots/images/temporary/'.$message["imgSrc"];
		
		$params['article'] = $article;
		$params['category'] = 20;
		
		$post = array(
			'action' => 'setArticleToDB',
			'params' => json_encode($params)
		);

		$res = $this->minicurl->get_file('http://www.polesye-eco.com.ua/includes/myphp/publish.php', $post);
		if (stripos($res, 'success') !== false) {
			$status = true;
		} else {
			echo $res;
		}
		return $status;
	}
	
} 
?>
