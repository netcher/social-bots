<?php
define('PATH', $_SERVER['DOCUMENT_ROOT']);
require_once  __DIR__ . '/../core/Config.php';
require_once  __DIR__ . '/../core/Core.php';

$site_links[0][0] = "http://www.segodnya.ua/tags/%D0%B3%D1%80%D1%83%D0%B7%D0%BE%D0%BF%D0%B5%D1%80%D0%B5%D0%B2%D0%BE%D0%B7%D0%BA%D0%B8.html";
$site_links[0][1] = 18;
$site_links[0][2] = "novosti/v-ukraine/";
$site_links[0][3] = "http://www.segodnya.ua/";
$site_links[0][4] = "CP1251";

$site_links[1][0] = "http://www.segodnya.ua/tags/%D1%84%D1%83%D1%80%D0%B0.html";
$site_links[1][1] = 19;
$site_links[1][2] = "novosti/v-sng/";
$site_links[1][3] = "http://www.segodnya.ua/";
$site_links[1][4] = "CP1251";

parseLinks($site_links);

function parseLinks($site_links) {
	foreach($site_links as $s_link) {
		$html=(file_get_contents($s_link[0]));
        $links = getAllLinks(
            $s_link[3],
            preg_replace('/\s+/', '', $html),
            preg_replace('/\s+/', '', '<div class="overflow-wrap white-frame"><a href="/'),
            preg_replace('/\s+/', '', '"><div class="big-image-wrap">'),
            0
        );
        $news = getAllNews(
            $s_link[4],
            $s_link[3],
            $links,
            '<img src="',
            '" alt="',
            '<h1>',
            "</h1>",
            '<span class="_ga1_on_">',
            "</span>",
            '<div class="sub-title">',
            "</div>"
        );
        setNewsToBD($news, $s_link[1], $s_link[2], 1, false);


//        if ($site == 2) {
//            $links = getAllLinks($s_link[3], $html, "<font face=arial,sans-serif size=2 color=#666666>
//            <a href=", " class=hl");
//            $news = getAllNews($s_link[4], $s_link[3], $links, "<td><img src=", " width", "<font color=#000000 size=2><b>", "</b>", "</font><br>", "</font>", "</p>", true, "<font size=1 class=ch color=#666666>", "<br>");
//            setNewsToBD($news, $s_link[1], $s_link[2], 0, true);
//        } elseif ($site == 1)  {
//            $links = getAllLinks($s_link[3], $html, "<font face=arial,sans-serif size=2 color=#666666><a href=", " class=hl");
//            $news = getAllNews($s_link[4], $s_link[3], $links, "<td><img src=", " width", "<font color=#000000 size=2><b>", "</b>", "<index>", "</index>", "</p>", false, "", "");
//            setNewsToBD($news, $s_link[1], $s_link[2], 1, false);
//        } elseif ($site == 3)  {
//            $links = getAllLinks($s_link[3], $html, '<h3 class="entry-title"><a href="/news', '"', 11, 1);
//            $news = getAllNews($s_link[4], $s_link[3], $links, 'img src="', '"', '<h3 class="entry-title">', "</h3>", '<div class="entry-content">', "</div>", "</p>", false, "", "");
//            setNewsToBD($news, $s_link[1], $s_link[2], 1, false);
//        }  elseif ($site == 4)  {
//            $links = getAllLinks($s_link[3], $html, '<div class="title"><a href="/events/', '"', 30, 2);
//            $news = getAllNews($s_link[4], $s_link[3], $links, 'http://ultra-music.com/images/afisha', '"', '<h3 class="entry-title">', "</h3>", '<p>', "</div>", "</p>", true, "", "", 2);
//            setNewsToBD($news, $s_link[1], $s_link[2], 0, true);
//        }
	}
}

function getAllLinks($url, $html_text, $text_start, $text_end, $limit = 0) {
	$links;
	$arr_pos = 0;
	$pos=0;
	do {
		$pos=(stripos($html_text, $text_start) + strlen($text_start));
		if (!($pos==0)) {
			$html_text = substr($html_text, -strlen($html_text) + $pos);
			$pos=(stripos($html_text, $text_end));
			if (!($pos==0)) {
			    $links[$arr_pos++] = $url . (substr($html_text, 0, $pos));
			}
		}
		if (($arr_pos >= $limit) && ($limit!=0)) break;
	} while(!($pos==0));
//	foreach ($links as $link) {
//		echo $link.'<br>';
//	}
	return $links;
}

function getTextInside($html_text, $text_start, $text_end, $encode, $include_text = false) {
		$pos=(stripos($html_text, $text_start));
		if (!($pos===false)) {
			if (!$include_text)
				$pos += strlen($text_start);
			//echo strlen($html_text) . '<br>';
			$html_text = substr($html_text, -strlen($html_text) + $pos);
			//echo strlen($html_text) . '<br>';
			$pos=(stripos($html_text, $text_end));
			if (!($pos===false)) {
				if ($include_text)
					$pos += strlen($text_end);
				$text = iconv( $encode, "CP1251//IGNORE", substr($html_text, 0, $pos));
				//echo $text . '<br>';
				return $text;
			}
		}
}

function getAllNews(
    $encode,
    $url,
    $links,
    $img_start,
    $img_end,
    $header_start,
    $header_end,
    $text_start,
    $text_end,
    $description_start,
    $description_end
) {
	$arr_pos = 0;
	foreach($links as $link) {
		$html_text = file_get_contents($link);
		$pos=0;

		// getting image link
		$news[$arr_pos][3] = $url.getTextInside($html_text, $img_start, $img_end, $encode, false);

		// getting header
		$news[$arr_pos][0] = getTextInside($html_text, $header_start, $header_end, $encode, false);

		// getting text
		$news[$arr_pos][2] = '<p>'.getTextInside($html_text, $text_start, $text_end, $encode, false);

		// getting description
		$news[$arr_pos][1] = getTextInside($html_text, $description_start, $description_end, $encode, false);

		$arr_pos++;
	}
	return $news;
}

function setNewsToBD($news, $category, $link, $showdate, $setdate) {
	foreach($news as $new) {
		$meta_desk = strtolower($new[0]);

		$time = date('Y-m-d H:i:s', strtotime($new[4]));

        setNewsToBotBD($new[0], $new[2], $new[3]);
	}
}

function setNewsToBotBD($title, $text, $imgUrl) {
	$inConf = Config::getInstance();
	$data = array(
		"group"=>'atek',
		"nodeText"=>trim(str_replace('\r', "\r", str_replace('\n', "\n", strip_tags(trim(html_entity_decode($text, ENT_QUOTES, 'UTF-8'), "\xc2\xa0"))))),
		"nodeTitle"=>trim(str_replace('\r', "\r", str_replace('\n', "\n", strip_tags(trim(html_entity_decode($title, ENT_QUOTES, 'UTF-8'), "\xc2\xa0"))))),
		"nodeImgSrc"=>$imgUrl
	);
	Core::appendNode($data['group'], $data['nodeTitle'], $data['nodeText'], $data['nodeImgSrc'], date("Y-m-d"));
}

