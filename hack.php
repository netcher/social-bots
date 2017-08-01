<?php 
require_once "core/Core.php";
if (Core::checkAuth()) {
echo ('<!DOCTYPE html>
<html lang="en">
<head>

<link href="bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
<link href="bootstrap/css/bootstrap.css" rel="stylesheet">
<link href="bootstrap/css/datepicker.css" rel="stylesheet">
  <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>
<body onLoad="displayMenu(); displayBots(); " >
<div id="navigation">
</div>

<div id="sub_navigation">

</div>


<div id="container" class="container">
	<!--Body content-->
</div>
  
<div class="modal hide fade" id="myModal">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">x</button>
    <h3>Are you sure?</h3>
  </div>
  <div class="modal-body">
    <p>You really want to delete item <span id="delItemId"></span>?</p>
  </div>
  <div class="modal-footer">
    <a href="#" class="btn" data-dismiss="modal">No</a>
    <a href="#" class="btn btn-danger" id="delItem" tag="0"  data-dismiss="modal" onclick=eraseNode(this)>Delete</a>
  </div>
</div>

<script src="assets/page_scripts.js"></script>
<script src="bootstrap/js/jquery-1.7.2.js"></script>
<script src="bootstrap/js/bootstrap.js"></script>
<script src="bootstrap/js/bootstrap-datepicker.js"></script>

</body>
</html>
');
} else {
	header('Location: /');
}
?>