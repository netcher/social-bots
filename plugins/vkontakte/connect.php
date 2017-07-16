<?php

    require  '../../core/Config.php';
	$inConf = Config::getInstance();
	$pluginUrl = $inConf->site_url.'/plugins/vkontakte/connect.php&response_type=code&scope=friends,photos,groups,wall,offline';

    if (!empty($_GET['code'])){

        // вконтакт присылает нам код        
        $vkontakteCode=$_GET['code'];
        
        // получим токен 
        $sUrl = "https://api.vk.com/oauth/access_token?client_id=3040519&client_secret=9OFYII2Od8igD5FQ2nMy&code=$vkontakteCode";

// создадим объект, содержащий ответ сервера Вконтакте, который приходит в формате JSON
        $oResponce = json_decode(file_get_contents($sUrl));
        
        $fp = fopen('token.txt', 'w');
        fputs($fp, $oResponce->access_token);
        fclose($fp);
        echo $oResponce->access_token.'<br>';
    }

    echo '<a href="http://api.vk.com/oauth/authorize?client_id=3040519&scope=offline,wall&redirect_uri='.$pluginUrl.'">Авторизация Вконтакте</a>';
?>
