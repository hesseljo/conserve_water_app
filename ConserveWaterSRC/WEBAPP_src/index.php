<?php
session_start();
?>
<!-- index.php written by David Hite, March 2016 -->
<!DOCTYPE html public>
<html>
<head>
	<meta charset="utf-8"></meta>
	<meta content="width=device-width, initial-scale=1.0" name="viewport"></meta>
	<link rel="stylesheet" href="style.css" type="text/css"></link>	
	<title>Conserve Water</title>
</head>
<body>
	<div class="menu">
		<a href="?p=main">Home</a> | <a href="?p=cw">Demo</a>
	</div>
	<?php
	$default='main';
	
	$page = isset($_GET['p']) ? $_GET['p'] : $default;
	$page = basename($page);
	if (!file_exists('pages/'.$page.'.php'))
		$page = $default;
	include('pages/'.$page.'.php');
	?>
</body>
<html>