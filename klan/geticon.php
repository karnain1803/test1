<?php
require_once('../config.php');
header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
$serv = $server_side_script_path.'klan/geticon.php?';
if ( !isset($_GET['klan']) || CheckNum($_GET['klan']) || !isset($_GET['servid']) || CheckNum( $_GET['servid'])) die(''); else {
	header("Content-type: image/png");
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$serv."klan=".$_GET['klan']."&servid=".$_GET['servid'].'&default='.$default_icon_num);
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);  
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);// allow redirects  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable  
	curl_setopt($ch, CURLOPT_TIMEOUT, 5); // times out after 4s  	
	$result = curl_exec($ch); // run the whole process 
	curl_close($ch);	
	echo $result;
	}
?>