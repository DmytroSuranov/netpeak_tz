<?php
if($_COOKIE['data'] != false){
	$data_object = json_decode($_COOKIE['data']);
	setcookie("data", false);
}
?>
<html>
	<head>
		<?php require_once 'head.php';?>
	</head>
	<body>