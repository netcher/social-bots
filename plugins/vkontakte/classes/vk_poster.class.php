<?
require_once "minicurl.class.php";
class vk_auth
{

	private $email = '';
	private $pwd = '';
	private $phone = '';
	private $sleeptime = 1;
	private $minicurl;
	private $images = array();
	private $debug = false;


	function __construct($email, $pass, $phone, $sleepTime)
	{
		$this->email = $email;
		$this->pwd = $pass;
		$this->phone = $phone;
		$this->sleeptime = $sleepTime;
		$this->minicurl = new minicurl(TRUE, COOKIES_FILE, 'Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1');
	}

/*
* public auth functions
*/

	public function check_auth()
	{
		if (strlen($this->phone) != 4)
		{
			$this->put_error_in_logfile('4 LAST DIGITS from phone!!!');
			exit();
		}

		if($this->need_auth())
		{
			if(!$this->auth())
			{
				$this->put_error_in_logfile('Not authorised!');
				return FALSE;
			}
		}

		return TRUE;
	}

/*
* public attach to post functions
*/
	public function attach_photos($photos=array())
	{
		if (sizeof($photos) <= 0)
		{
			$this->put_error_in_logfile('Photo links not found!');
			return FALSE;
		}

		if (is_array($photos))
		{
			foreach ($photos as $url)
			{
				$this->images[] = $url;
			}
		} 
		elseif (is_string($photos)) 
		{
			$this->images[] = $photos;
		}
		else {
			$this->put_error_in_logfile('Var not array or string!');
			return FALSE;
		}

		return TRUE;
	}

/*
* public posting functions 
*/

	public function post_to_user($user_id, $message, $album_id = null, $img = null, $friends_only = FALSE)
	{
		// check_auth() - так ли тут нужно? по-моему, нет
		
		if (!is_numeric($user_id))
		{
			$this->put_error_in_logfile('$user_id - only numbers!');
			return FALSE;
		}

		$user_id = 'id' . $user_id;
		$hash = $this->get_hash($user_id);
		if (empty($hash))
		{
			$this->put_error_in_logfile('JS-Field "post_hash" not found!');
			return FALSE;
		}
		if(!$this->post_to_wall_query($hash, $user_id, $message, $album_id, $img, FALSE, $friends_only, 'feed'))
		{
			$this->put_error_in_logfile('Message not posted!');
			return FALSE;
		}

		return TRUE;
	}

	public function post_to_group($page_id, $message, $album_id = null, $img = null, $official = FALSE, $debug = false)
	{
		
		$this->debug = $debug;
		if (!is_numeric($page_id))
		{
			$this->put_error_in_logfile('$page_id - only numbers!');
			return FALSE;
		}

		$page_id = 'club' . $page_id;
		$hash = $this->get_hash($page_id);
		if (empty($hash))
		{
			$this->put_error_in_logfile('JS-Field "post_hash" not found!');
			return FALSE;
		}

		$page_id = '-' . $page_id;

		if(!$this->post_to_wall_query($hash, $page_id, $message, $album_id, $img, $official, FALSE ))
		{	
			$this->put_error_in_logfile('Message not posted!');
			return FALSE;
		}

		return TRUE;
	}

	public function post_to_public_page($page_id, $message, $album_id = null, $img = null, $official = FALSE, $debug = false)
	{
		$this->debug = $debug;
		if (!is_numeric($page_id))
		{
			$this->put_error_in_logfile('$page_id - only numbers!');
			return FALSE;
		}

		$page_id = 'public' . $page_id;
		$hash = $this->get_hash($page_id);
		if (empty($hash))
		{
			$this->put_error_in_logfile('JS-Field "post_hash" not found!');
			return FALSE;
		}

		$page_id = '-' . $page_id;

		if(!$this->post_to_wall_query($hash, $page_id, $message, $album_id, $img, $official, FALSE ))
		{
			$this->put_error_in_logfile('Message not posted!');
			return FALSE;
		}

		return TRUE;
	}

/*
* public other functions
*/

	public function print_last_error()
	{
        if (defined('DEBUG') AND (DEBUG == TRUE))
        {
                var_dump($self->minicurl->debug_pages());
        }
        
		$errors = array_reverse(file(LOG_FILE));
		return '<b>Error!</b><br>' . $errors[0];
	}

/*
* private auth functions
*/

	private function need_auth()
	{
		$result = $this->minicurl->get_file('http://vk.com/settings');
		$this->sleep();
		return strpos($result, 'HTTP/1.1 302 Found') !==FALSE;
	}

	private function auth()
	{
		$this->minicurl->clear_cookies();

		$location = $this->get_auth_location();
		if($location === FALSE){
			$this->put_error_in_logfile('Not recieved Location!');
			return FALSE;
		}

		$sid = $this->get_auth_cookies($location);
		if(!$sid){
			$this->put_error_in_logfile('Not received cookies!');
			return FALSE;
		}

		$this->minicurl->set_cookies('remixsid=' . $sid . '; path=/; domain=.vk.com');

		return TRUE;
	}

	private function get_auth_location()
	{
		$html = $this->minicurl->get_file('http://vk.com/');
		preg_match('#<input type="hidden" name="ip_h" value="([a-z0-9]*?)" \/>#isU', $html, $matches);

		$post = array(
			'act' => 'login',
			'al_frame' => '1',
			'captcha_key' => '',
			'captcha_sid' => '',
			'email' => $this->email,
			'expire' => '',
			'from_host' => 'vk.com',
			'ip_h' => (isset($matches[1]) ? $matches[1]: ''),
			'pass' => $this->pwd,
			'q' => '1',
		);

		$auth = $this->minicurl->get_file('http://login.vk.com/?act=login', $post, 'http://vk.com/');
		preg_match('#Location\: ([^\r\n]+)#is', $auth, $match);

		$this->sleep();
		return ((isset($match[1])) ? $match[1] : FALSE);
	}

	private function get_auth_cookies($location)
	{
		$result = $this->minicurl->get_file($location);

		$this->sleep();
		return ((strpos($result, "setCookieEx('sid', ") === FALSE) ? FALSE :
				substr($result, strpos($result, "setCookieEx('sid', '") + 20, 60));
	}

/*
* private posting functions
*/

	private function post_to_wall_query($hash, $to_id,  $message, $album_id=null, $img=null, $official=FALSE, $friends_only=FALSE, $type='all' )
	{
		$official = $official ? '1' : '';
		$friends_only = $friends_only ? '1' : '';

		$to_id_post = str_replace(array('id', 'public', 'club'), '', $to_id);
		
		if ($img != null) {
			$photo_id = $this->loadImgToVK($img, $album_id, $to_id_post);
			if ($this -> debug) 
				echo '<p>Photo id: '.$photo_id.'</p>';
		}
		
		$post = array(
			'act' => 'post',
			'al' => '1',
			'facebook_export' => '',
			'friends_only' => $friends_only,
			'hash' => $hash,
			'message' => $message,
			'note_title' => '',
			'official' => $official,
			'status_export' => '',
			'to_id' => $to_id_post,
			'type' => $type,
			'attach1_type' => 'photo',
			'attach1'  => $photo_id,/*
			'attach2_type' => 'share',
			'url'  => 'http://muzhub.net/novosti/v-belorusi/ne-vse-poderzhivayut-pussy-riot.html',
			'title' => 'Не все поддерживают Pussy Riot',
			'description' => $message
			*/
		);

		$result = $this->minicurl->get_file('http://vk.com/al_wall.php', $post);

		
		$this->sleep();
		preg_match('#>\d<!>\d+<!>([\d]+)<!>#isU', $result, $match);
		return (isset($match[1]) AND ($match[1] == '0'));
	}
 
	
	private function get_hash($page_id)
	{
		$result = $this->minicurl->get_file('http://vk.com/' . $page_id);
		$this->sleep();

		preg_match('#Location\: ([^\r\n]+)#is', $result, $match);
        if (isset($match[1]) AND !empty($match[1]))
        {
        	$result = $this->minicurl->get_file('http://vk.com' . $match[1]);
			$this->sleep();
			unset($match);

			preg_match("#act: '([^']+)', code: ge\('code'\)\.value, to: '([^']+)', al_page: '([^']*)', hash: '([^']+)'#i", $result, $match);

			$post = array(
				'act' => $match[1],
				'al' => '1', // хз что это
				'al_page' => $match[3],
				'code' => $this->phone,
				'hash' => $match[4],
				'to' => $match[2]
			);

			$result = $this->minicurl->get_file('http://vk.com/login.php', $post);
			$this->sleep();
			unset($match);

			preg_match('#>/([a-z0-9\.\-_]+)<#is', $result, $match);

			if (isset($match[1]) AND !empty($match[1]))
			{
				$result = $this->minicurl->get_file('http://vk.com/' . $match[1]);
				$this->sleep();
				unset($match);
			}
        }
		preg_match('#"post_hash":"([^"]+)"#isU', $result, $match);

		if (strpos($result, 'action="https://login.vk.com/?act=login'))
		{
			unset($match[1]);
		}

		return (isset($match[1]) ? $match[1] : '');
	}


private function loadImgToVK($img, $album, $group) {

	$group = str_replace('-', '', $group);

	$vkontakteUserId = USER_ID;
	$vkontakteAccessToken = 'c1510f86c43af55295d7fc97448cc0a675d851497965d6e253295285630c57576d27891b7535e349ab31d';

	// строка запроса к серверу Вконтакте
	$request = "https://api.vk.com/method/photos.getUploadServer?owner_id=$vkontakteUserId&access_token=$vkontakteAccessToken&aid=$album&gid=$group";
	// ответ от Вконтакте
	$answer = json_decode(file_get_contents($request));
	if ($this -> debug) {
		echo '<p>(Answer) photos.getUploadServer: ';
		nl2br(print_r($answer));
		echo '</p>';
	}
	$res = $answer -> {'response'};

	$server = $res -> {'upload_url'};
	$photo = $img;



				$ch = curl_init();
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
				curl_setopt($ch, CURLOPT_URL, $server);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, array('file1' => '@'.$photo));
				$result = curl_exec($ch);
				$error = curl_error($ch);
				curl_close ($ch);

	$answer = json_decode($result);
	if ($this -> debug) {
		echo '<p>(Answer) File Upload: ';
		nl2br(print_r($answer));
		echo '</p>';
	}
	$res = $answer;

	$server = $res -> {'server'};
	$hash = $res -> {'hash'};
	$photo = $res -> {'photos_list'};



	// строка запроса к серверу Вконтакте
	$request = "https://api.vk.com/method/photos.save?owner_id=$vkontakteUserId&access_token=$vkontakteAccessToken&server=$server&photos_list=$photo&hash=$hash&aid=$album&gid=$group";
	// ответ от Вконтакте
	$answer = json_decode(file_get_contents($request));
	if ($this -> debug) {
		echo '<p>(Answer) photos.save: ';
		nl2br(print_r($answer));
		echo '</p>';
	}
	$data = ($answer -> {'response'});
	$data = $data[0];
	$pid = $data -> {'pid'};
	$owner_id = $data -> {'owner_id'};
	return $owner_id . '_' . $pid;
	
}
	
	private function load_photos($page_id, $photo)
	{
		$result = $this->minicurl->get_file('http://vk.com/album-15127189_104055874?act=add');
		$this->sleep();

		preg_match('#"upload":\{"url":"([^"]+)","params":\{"act":"([^"]+)","aid":([^"]+),"gid":([^"]+),"mid":([^"]+),"hash":"([^"]+)","rhash":"([^"]+)","vk":"([^"]*)","from_host":"([^"]+)"},"opts":\{"server":"([^"]+)","default_error"#isU', $result, $match);
		$url = str_replace('\\', '', $match[1]);
		$server = $match[10];

		$get_params = array(
			'act' => $match[2],
			'aid' => $match[3],
			'ajx' => '1',
			'gid' => $match[4],
			'mid' => $match[5],
			'hash' => $match[6],
			'rhash' => $match[7],
			'vk' => $match[8],
			'from_host' => $match[9]
		);

		$url .= '?' . http_build_query($get_params);

		$vkimages = array();
			$imgdata = file_get_contents($photo);
			$imgformat = end(explode('.', $photo));
			$imgfilename = DATA_DIR . '/' . rand(199122, 1992314) . '.' . $imgformat;
			$imghandle = fopen($imgfilename, 'w');
			fwrite($imghandle, $imgdata);
			fclose($imghandle);

			$post = array(
				'photo' => '@' . $imgfilename . ';type=image/' . $imgformat
			);

			$result = $this->minicurl->get_file($url, $post);
			$this->sleep();

			preg_match('#mid=([^&]+)&aid=([^&]+)&gid=([^&]+)&server=([^&]+)&photos=([^&]+)&hash=([^&]+)#isU', $result, $match);


			var_dump($result);

			unlink($imgfilename);
		return $vkimages;
	}
	

/*
* private other functions
*/

	private function sleep()
	{
		if ($this->sleeptime)
		{
			sleep($this->sleeptime + rand(1, 4));
		}
	}

	private function put_error_in_logfile($msg)
	{
		$msg = '[' . date('Y.m.d H:i:s') . ']: ' . $msg . "\n";
		$fp = fopen(LOG_FILE, 'a');
		fwrite($fp, $msg);
		fclose($fp);
	}
}

?>