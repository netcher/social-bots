<?php

require_once  __DIR__ . '/../../core/Config.php';
require_once __DIR__ . '/../../includes/minicurl.class.php';

class wordpressPlugin {

	private $minicurl;
	
	function __construct($plugin_info, $bot_info) { 
 		if ( ! $this->minicurl ) 
		 	$this->minicurl = new minicurl(TRUE, COOKIES_FILE, 'Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1');
	}
	
	function postMessage($message, $bot_info) {
		$inConf = Config::getInstance();
		$status = false;
		$message["text"] = iconv( 'CP1251', "UTF-8//IGNORE", $message["text"]);
		$message["title"] = iconv( 'CP1251', "UTF-8//IGNORE", $message["title"]);
		$imgSrc = $message["imgSrc"] ? $inConf->site_url.'/images/temporary/'.$message["imgSrc"] : null;
        $post = array(
            'action' => 'setArticleToDB',
            'params' => array(
                'article' => array(
                    'sourceName' => $message["imgSrc"],
                    'source' => $imgSrc,
                    'title' => $message["title"],
                    'description' => substr($message["text"], 0, $posSpace) . '...',
                    'content' => $message["text"],
                )
            )
        );
        $res = $this->minicurl->get_file('http://a-tek.com.ua/publish.php', $post);
        if (stripos($res, 'success') !== false) {
            $status = true;
        } else {
            echo $res;
        }
        return $status;

//        $media = array(
//            'slug' => $imgPath
//        );
//		$res = $this->minicurl->get_file('http://preview.lsd-group.com.ua/?rest_route=/wp/v2/media', $media);
//
//        var_dump($res);exit;
//		$posSpace = stripos(substr($message["text"], -strlen($message["text"]) + 220), ' ') + 220;
//		$article = array(
//		    'status' => 'publish',
//		);
//
//		$res = $this->minicurl->get_file('http://preview.lsd-group.com.ua/?rest_route=/wp/v2/posts', $article);
//		if (stripos($res, 'success') !== false)
//			$status = true;
//		return $status;
	}
	
} 
?>
