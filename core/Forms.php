<?php

require_once "Core.php";

if (Core::checkAuth())
	echo Forms::getForm($_POST['form_name'], json_decode($_POST['params'], true));
else 
	header('Location: http://slava.co.ua/bots/');

class Forms {

	function getForm($form_name, $params) {
		switch ($form_name) {
			case 'bots':
				return Forms::getFormBots($params);
				break;
			case 'articleAppend':
				return Forms::getFormArticleAppend($params);
				break;
			case 'articleEdit':
				return Forms::getFormArticleEdit($params);
				break;
			case 'log':
				return Forms::getFormLog($params);
				break;
			case 'newsList':
				return Forms::getFormNewsList($params);
				break;
			case 'menu':
				return Forms::getMenu($params);
				break;
			case 'sub_menu':
				return Forms::getSubMenu($params);
				break;
		}
	}
	
	function getMenu($params) {
	$groups = Core::getGroupsList();
	$html = '<div class="navbar navbar-fixed-top">
				  <div class="navbar-inner">
					<div class="container">
				 
					  <!-- .btn-navbar is used as the toggle for collapsed navbar content -->
					  <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					  </a>
					  
				 
					  <!-- Be sure to leave the brand out there if you want it shown -->
					  <a class="brand" href="#">Honey bots</a>
				 
					  <!-- Everything you want hidden at 940px or less, place within here -->
					  <div class="nav-collapse">
						<!-- .nav, .navbar-search, .navbar-form, etc -->
						<ul class="nav">
						  <li class="menuItem active" onclick="setActive(this)">
							<a href="#" onclick="displayBots()">Bots</a>
						  </li>';
						/*  <li class="dropdown">
							<a href="#"
								  class="dropdown-toggle"
								  data-toggle="dropdown">
								  News
								  <b class="caret"></b>
							</a>
							<ul class="dropdown-menu">';
	foreach($groups as $group) {
		$html = $html . '<li class="menuItem"><a href="#" onclick="displayGroup('."'".$group['group']."'".'); setActive(this);">'.$group['name'].'</a></li>';
	}
	$html = $html . '
							</ul>
						  </li>*/;
						 $html = $html . ' <li class="menuItem" onclick="setActive(this)">
							<a href="#" onclick="displayLog()">Log</a>
						  </li>
						</ul>
					  </div>
				 
					</div>
				  </div>
				</div>';
	return $html;
	}
	
	function getSubMenu($params) {
		$groups = Core::getGroupsList();
		$html = '	<div class="container" style="margin-top: 50px; margin-bottom: 10px;"><div class="subnav">
		<ul class="nav nav-pills">
			';
		foreach($groups as $group) {
			$html = $html . '
		  <li class="subMenuLink"><a href="#" onclick="displayGroup('."'".$group['group']."'".');  setSubMenuActive(this);">'.$group['name'].'</a></li>';
		}
		$html = $html . '
		</ul>
		</div>
		</div>';
		return $html;
	}
	
	function getFormBots($params) {
		$groups = Core::getGroupsList();
		$plugins = Core::getPluginsList();
		$html = "<table align='center' class='table table-bordered' style = 'width: ".(170+(count($plugins)*75))."px' >
			<thead>
				<tr style='font-weight: bold'>
					<td width='10'>#</td>
					<td>Name</td>";
		foreach($plugins as $plugin) {
		$html = $html . "
					<td width='75'>".$plugin['name']."</td>
					";
		}
		$html = $html . "
				</tr>
			</thead>
			<tbody>";
		$i = 1;
		foreach($groups as $group) {
			$html = $html . "
				<tr>
					<td><span class='badge'>".$i++."</span></td>
					<td>".$group['name']."</td>
					";
			 
			foreach($plugins as $plugin) {
				$bot = Core::getBotByGroupAndPlugin($group['group'], $plugin['type']);
				$html = $html . "<td>";
				if ($bot) {
					$html = $html . "<button type='button' class='btn' onclick=".'"'."runBot(".$bot['id'].")".'"'.">Run Bot</button>";
					$bot_id_array[] = $bot['id'];
				}
				$html = $html . "</td>";
			}
			$html = $html . "
				</tr>";
		}
			$bot_id_array[] = null;
			$html = $html . "
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td colspan='".count($plugins)."'><center><button type='button' class='btn btn-primary' onclick=displayAllAlerts(".json_encode($bot_id_array).");>Run All Bots</button></center></td>
			</tr>";
		$html = $html . "</tbody>
		</table>";
		$html = $html . "<div id='alerts'></div>";
		return $html;
	}
	
	function getFormArticleAppend($params) {
		$group = $params['group'];
		$html = '<form class="well">
			<div class="row">
			  <div class="span8"><textarea class="input-xlarge" id="nodeText" rows="3"  placeholder="Write something..." style="width: 600px"></textarea></div>
			  <div class="span3"><table><tr>
			  <td><input class="span3" type="text" id="nodeImgUrl" placeholder="Image url..."> </td></tr><tr><td>
			  <input class="span2" value="'.date('j-m-Y').'" id="dp1" type="text">
			  <button class="btn btn-small btn-info" onclick="appendNode('."'".$group."'".')" style="margin-bottom: 9px;">Append</button>
			  </td></tr></table></div>
			</div>
			</form><div id="edit"></div><div id="alerts"></div>';
		return $html;
	}
	
	function getFormArticleEdit($params) {
		$group = $params['group'];
		$id = $params['id'];
		$article = Core::getArticle($id);
		$date = date('j-m-Y', strtotime($article['date']));
		$html = '<div id="editAlert" class="alert alert-block alert-info fade in">
					<button type="button" class="close" data-dismiss="alert">x</button>
			<div class="row">
			  <div class="span8"><textarea class="input-xlarge" id="nodeEditText" rows="3"  placeholder="Type text..." style="width: 600px">'.iconv( "CP1251", "UTF-8//IGNORE", $article['text']).'</textarea></div>
			  <div class="span3"><table><tr>
			  <td><input class="span3" type="text" id="nodeEditImgUrl" placeholder="Image url..." value="'.$article['imgSrc'].'"> </td></tr><tr><td>
			  <input class="span2" value="'.$date.'" id="dp2" type="text">
			  <button class="btn btn-small btn-info" onclick="editNode('."'".$group."', ".$id.') " style="margin-bottom: 9px;">Edit</button>
			  </td></tr></table></div>
			</div>
		</div>';
		return $html;
	}
	
	function getFormLog($params) {
		$data = Core::getLog();
		if (count($data)>0) {
			$html = '<table align="center" class="table table-bordered table table-striped table-condensed"  >
					<thead>
						<tr style="font-weight: bold">
							<td width="10">#</td>
							<td width="70">Date</td>
							<td width="10">Status</td>
							<td>Message</td>
						</tr>
					</thead>
					<tbody>';
			foreach($data as $row) {
				switch ($row['status']) {
					case 0: 
						$spanClass = 'badge-success';
						break;
					case 1: 
						$spanClass = 'badge-primary';
						break;
					case 2: 
						$spanClass = 'badge-info';
						break;
					case 3: 
						$spanClass = 'badge-warning';
						break;
					case 4: 
						$spanClass = 'badge-danger';
						break;
				}
				$html = $html . "<tr>
						<td><center><span class='badge'>".$row['id']."</span></center></td>
						<td>".date('j-m-Y', strtotime($row['date']))."</td>
						<td><center><span class='badge ".$spanClass."'>".$row['status']."</span></center></td>
						<td>".iconv( "CP1251", "UTF-8//IGNORE", $row['message'])."</td>
					  </tr>	";
			}
			$html = $html . '</tbody>
				</table>';
		};
		return $html;
	}
	
	function getFormNewsList($params) {
		$inConf = Config::getInstance();
		
		$group = $params['group'];
		$plugins_to_use = Core::getGroupPluginsList($group);
		
		$data = Core::getNewsList($group, $plugins_to_use);
		foreach($plugins_to_use as $plugin)
			if (Core::getCookie($plugin['type'])) 
				$cookie[$plugin['type']] = "active";	
		$html = '<table align="center" class="table table-bordered table table-striped table-condensed"  >
			<thead>
				<tr style="font-weight: bold">
					<td width="10"><center>#</center></td>
					<td>Text</td>
					<td width="70">Date</td>
					<td width="25">Img</td>';

			foreach($plugins_to_use as $plugin) {
					$html = $html . '<td width="60"><center><button class="btn btn-primary '.$cookie[$plugin['type']].' btn-mini" data-toggle="button"  onClick="updateNewsListPersonalSettings('."'".$group."', "."'".$plugin['type']."', ".(int) !Core::getCookie($plugin['type']).');">'.$plugin['name'].'</button></center></td>';
			}
					$html = $html . '<td width="57"><center>Action</center></td>
				</tr>
			</thead>
			<tbody>';
		if (count($data)>0) {
		
			foreach($data as $row) {
				$html = $html."<tr>
						<td><center><span class='badge'>".$row['id']."</span></center></td>
						<td>".iconv( "CP1251", "UTF-8//IGNORE", substr($row['text'], 0, 300))."...</td>
						<td>".date('j-m-Y', strtotime($row['date']))."</td>
						<td><center>".(@GetImageSize($inConf->dir . '/images/temporary/' . $row['imgSrc']) ? '<span class="label label-success"><i class="icon-ok icon-white"></i></span>' : '<span class="label"><i class="icon-remove icon-white"></i></span>' )."</center></td>";
						
				foreach($plugins_to_use as $plugin) {			
						$atr = $row[$plugin['type']];
						$btn_atr = $atr ? '' : 'btn-info';
						$btn_text = $atr ? 'Used' : 'Not used';
						$html = $html. "<td><center><button class='btn btn-mini ".$btn_atr."' onclick='setNodeState(".'"'.$group.'",'.$row['id'].', "'.$plugin['type'].'",'.$atr.")'>".$btn_text."</button></center></td>";
				}
						$html = $html. "<td><div class='btn-group'>
							  <a class='btn btn-warning btn-mini' onclick='displayEdit(".'"'.$group.'"'.",".$row['id'].")'><i class='icon-edit icon-white'></i></a>
							  <a class='btn btn-danger btn-mini' onclick='showEraseModal(".'"'.$group.'"'.",".$row['id'].")'><i class='icon-trash icon-white'></i></a>
							</div>
						</td>
					  </tr>	";
			}
		};
			$html = $html.'</tbody>
				</table>';
		return $html;
	}
	
}

?>