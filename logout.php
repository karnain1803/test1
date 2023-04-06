<?php
require_once('config.php');
SetCookie($cookie_name, '');
$_SESSION['login'] = '';
$_SESSION['passw'] = '';
$_SESSION['vk_id'] = '';
$_SESSION['vk_name'] = '';
$_SESSION['vk_photo'] = '';
$_SESSION['id'] = 0;
$_SESSION['lkgold'] = 0;
$_SESSION['lksilver'] = 0;
$_SESSION['rules_cnt'] = 0;
$_SESSION['isadmin'] = false;	
session_destroy();
header('Location: index.php');
