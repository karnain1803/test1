<?php
require_once('config.php');
error_reporting(0);
ini_set('display_errors', 'Off');
if (!isset($_GET['login'])) die("Error");
if ((strlen($_GET['login'])<$login_min_len)||(strlen($_GET['login'])>$login_max_len)) die("length");
if (preg_match($login_filter, $_GET['login'])) die("bad");
$poststr = array( 
	'op' => 'checklogin',
	'ip' => $_SERVER['REMOTE_ADDR'],
	'login' => $_GET['login']
);
$data = CurlPage($poststr, 5, 1, true);
if (!$data) die("Error");
echo $data;
