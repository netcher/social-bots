function loadXMLDoc(link, func, params)
{
	var data;
	var xmlhttp;
	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlhttp=new XMLHttpRequest();
	  }
	else
	  {// code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	  }
	xmlhttp.onreadystatechange=function()
	  {
	  if (xmlhttp.readyState==4 && xmlhttp.status==200)
		{
		data = xmlhttp.responseText;
		func(data);
		}
	  }
	xmlhttp.open("POST",link,true);
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
	xmlhttp.send(params);
}
function displayAllAlerts(bot_id_array) {
	for(bot_id in bot_id_array) 
		if (bot_id != 0)
			runBot(bot_id);
}
function setActive(el) {

	var elements = document.getElementsByClassName("subMenuLink");
	for (var i = 0; i<elements.length; i++)
		elements[i].className = "subMenuLink";

	var elements = document.getElementsByClassName("menuItem");
	for (var i = 0; i<elements.length; i++)
		elements[i].className = "menuItem";
	el.className = "menuItem active";
}
function setSubMenuActive(el) {

	var elements = document.getElementsByClassName("menuItem");
	for (var i = 0; i<elements.length; i++)
		elements[i].className = "menuItem";

	var elements = document.getElementsByClassName("subMenuLink");
	for (var i = 0; i<elements.length; i++)
		elements[i].className = "subMenuLink";
	el.parentNode.className = "subMenuLink active";
}
function displayBots(html) {
	if (html==null) {
		loadXMLDoc('core/Forms.php', function(data) {displayBots(data)}, 'form_name=bots');
		return; 
	}
	var element = document.createElement('div');
	element.innerHTML = html;
	document.getElementById('container').innerHTML = '';
	document.getElementById('container').appendChild(element);
}

function displayMenu(html) {
	if (html==null) {
		loadXMLDoc('core/Forms.php', function(data) {displayMenu(data)}, 'form_name=menu');
		return; 
	}
	var element = document.createElement('div');
	element.innerHTML = html;
	document.getElementById('navigation').innerHTML = '';
	document.getElementById('navigation').appendChild(element);
	displaySubMenu();
}

function displaySubMenu(html) {
	if (html==null) {
		loadXMLDoc('core/Forms.php', function(data) {displaySubMenu(data)}, 'form_name=sub_menu');
		return; 
	}
	var element = document.createElement('div');
	element.innerHTML = html;
	document.getElementById('sub_navigation').innerHTML = '';
	document.getElementById('sub_navigation').appendChild(element);
	
	$(document).scroll(function(){
    // If has not activated (has no attribute "data-top"
    if (!$('.subnav').attr('data-top')) {
        // If already fixed, then do nothing
        if ($('.subnav').hasClass('subnav-fixed')) return;
        // Remember top position
        var offset = $('.subnav').offset()
        $('.subnav').attr('data-top', offset.top);
    }

    if ($('.subnav').attr('data-top') - $('.subnav').outerHeight() <= $(this).scrollTop())
        $('.subnav').addClass('subnav-fixed');
    else
        $('.subnav').removeClass('subnav-fixed');
});
}

function runBot(bot_id) {
	var params = {
		"botId":bot_id
	};
	displayAlert('core/Core.php', null, 'action=runBot&params=' + JSON.stringify(params));
}

function displayGroup(group, html, html_form) {
	if (html==null) {
		var params = {
			"group":group
		};
		loadXMLDoc('core/Forms.php', function(data) {displayGroup(group, data)}, 'form_name=newsList&params=' + JSON.stringify(params));
		return;
	}
	if (html_form==null) {
		var params = {
			'group':group
		};
		loadXMLDoc('core/Forms.php', function(data) {displayGroup(group, html, data)}, 'form_name=articleAppend&params=' + JSON.stringify(params));
		return;
	}
	var element = document.createElement('div');
	element.setAttribute('id', 'groupFormArticleAppend');
	document.getElementById('container').innerHTML = '';
	element.innerHTML = html_form;
	document.getElementById('container').appendChild(element);
	var element = document.createElement('div');
	element.setAttribute('id', 'clientNews');
	element.innerHTML = html;
	document.getElementById('container').appendChild(element);
	loadDP();
}

function displayLog(html) {
	if (html==null) {
		loadXMLDoc('core/Forms.php', function(data) {displayLog(data)}, 'form_name=log');
		return;
	}
	var element = document.createElement('div');
	element.setAttribute('id', 'log');
	document.getElementById('container').innerHTML = '';
	element.innerHTML = html;
	document.getElementById('container').appendChild(element);
}

function updateClientNews(group, html) {
	if (html==null) {
		var params = {
			"group":group
		};
		loadXMLDoc('core/Forms.php', function(data) {updateClientNews(group, data)}, 'form_name=newsList&params=' + JSON.stringify(params));
		return;
	}
	var element = document.getElementById('clientNews').innerHTML = html;
}

function displayAlert(link, html, params, func) {
	if (html==null) {
		loadXMLDoc(link, function(data) {displayAlert(link, data, params, func)}, params);
		return;
	}
	var element = document.createElement('div');
	var start = html.indexOf('status="') + ('status="').length;
	var end = html.substring(start).indexOf('"')+start; //adding start pos cuz of cut
	var postStatus = html.substring(start, end);
	if (postStatus != null) {
		switch(postStatus) {
			case 'success': 
				element.className = "alert alert-success fade in";
				break;
			case 'info': 
				element.className = "alert alert-info fade in";
				break;
			case 'warning': 
				element.className = "alert alert-warning fade in";
				break;
			case 'error': 
				element.className = "alert alert-error fade in";
				break;
			default: 
				element.className = "alert alert-warning fade in";
		}
	} else
		element.className = "alert alert-warning fade in";
	var alertId = "alert"+Math.floor((Math.random()*1000)+1);
	element.setAttribute("id", alertId);
	element.innerHTML = '<a class="close" data-dismiss="alert">&times;</a>';
	element.innerHTML = element.innerHTML+html;
	document.getElementById('alerts').insertBefore(element, document.getElementById('alerts').firstChild);
	setTimeout(function() { $("#"+alertId).alert('close') }, 5000);
	if (func != null)
		func();
}

function showEraseModal(group, id) {
	document.getElementById('delItemId').innerHTML = id;
	document.getElementById('delItem').setAttribute("tag", id);
	document.getElementById('delItem').setAttribute("group", group);
	$('#myModal').modal('show');
}

function displayEdit(group, id, html) {
	if (html==null) {
		var params = {
			"group":group,
			"id":id 
		};
		loadXMLDoc('core/Forms.php', function(data) {displayEdit(group, id, data)}, 'form_name=articleEdit&params=' + JSON.stringify(params));
		return;
	}
	document.getElementById('edit').innerHTML = html;
	loadDP();
}

function eraseNode(element) {
	var id = element.getAttribute("tag");
	var group = element.getAttribute("group");
	var params = {
		"id":id 
	};
	loadXMLDoc('core/Core.php', function() {updateClientNews(group)}, 'action=eraseNode&params=' + JSON.stringify(params));
}

function appendNode(group) {
	var text = document.getElementById('nodeText').value;
	var imgUrl = document.getElementById('nodeImgUrl').value;
	var date = document.getElementById('dp1').value;
	var params = {
		"group":group,
		"nodeText":text, 
		"nodeImgSrc":imgUrl, 
		"nodeDate":date 
	};
	displayAlert('core/Core.php', null, 'action=appendNode&params=' + JSON.stringify(params), function() {updateClientNews(group)});
	
	document.getElementById('nodeText').value = '';
	document.getElementById('nodeImgUrl').value = '';
}

function editNode(group, id) {
	var text = document.getElementById('nodeEditText').value;
	var imgUrl = document.getElementById('nodeEditImgUrl').value;
	var date = document.getElementById('dp2').value;
	var params = {
		"nodeId":id,
		"nodeText":text, 
		"nodeImgSrc":imgUrl, 
		"nodeDate":date 
	};
	displayAlert('core/Core.php', null, 'action=editNode&params=' + JSON.stringify(params), function() {updateClientNews(group)});
	$("#editAlert").alert('close')
}

function loadDP() {
	$(function(){
			$('#dp1').datepicker({
				format: 'dd-mm-yyyy'
			});
			$('#dp2').datepicker({
				format: 'dd-mm-yyyy'
			});
		});
}

function setNodeState(group, id, type, state) {
	var params = {
		"nodeId":id,
		"nodeState":state, 
		"nodeType":type
	};
	loadXMLDoc('core/Core.php', function(data) {updateClientNews(group)}, 'action=setNodeState&params=' + JSON.stringify(params));
}

function setCookie(c_name,value,exdays) {
	var exdate=new Date();
	exdate.setDate(exdate.getDate() + exdays);
	var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
	document.cookie=c_name + "=" + c_value;
}

function getCookie(c_name) {
	var i,x,y,ARRcookies=document.cookie.split(";");
	for (i=0;i<ARRcookies.length;i++) {
	  x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
	  y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
	  x=x.replace(/^\s+|\s+$/g,"");
	  if (x==c_name) {
		return unescape(y);
		}
	}
}

function updateNewsListPersonalSettings(group, name, value) {
	setCookie(name, value, 365);
	displayGroup(group);
};

