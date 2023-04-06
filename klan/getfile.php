<?php
header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
require_once('../config.php');
function GetFile($f){
	global $server_side_script_path;
	$serv = $server_side_script_path.'klan/';
	$ch = curl_init();	
	curl_setopt($ch, CURLOPT_URL,$serv.$f);
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);  
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);// allow redirects  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable  
	curl_setopt($ch, CURLOPT_TIMEOUT, 6); // times out after 4s  	
	$result = curl_exec($ch); // run the whole process 
	curl_close($ch);
	echo $result;
}
if ($_GET['f']==1) {
	Header("Content-type: image/png");
	GetFile("iconlist_guild.png");
} else
if ($_GET['f']==2) {
	GetFile("iconlist_guild.txt");
} else
if ($_GET['f']==3) {
	GetFile("version");
}
?>