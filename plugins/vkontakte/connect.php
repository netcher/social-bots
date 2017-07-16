<?php

    require  '../../core/Config.php';

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

?>
<a href="http://api.vk.com/oauth/authorize?client_id=3040519&scope=offline,wall&redirect_uri=http://www.slava.co.ua/bots/plugins/vkontakte/connect.php&response_type=code&scope=friends,photos,groups,wall,offline">Авторизация Вконтакте</a>