<?php
if (!defined('make_icons')) die();
$db->query('set names utf8');
if ($db->errno != 0 ) die($db->error);

set_time_limit(200);

function GetIconNum($s,$l){	
	foreach ($l as $i => $val){	
		if(strtolower($val)==strtolower($s)) {			
			return $i;						
		}		
	}
	return 0;
}

function ReadIconFile($n){
	global $imw;
	global $imh;
	global $rows;
	global $cols;
	global $l;
	$img = imageCreateFromPng($n.'.png');
	if ($img === false) die($n.'.png open error');
	//imagesavealpha($img,true);	
	$f=fopen($n.'.txt','r');
	if (!$f) die($n.'.txt open error');
	$imw=(int)fgets($f);
	$imh=(int)fgets($f);
	$rows=(int)fgets($f);
	$cols=(int)fgets($f);
	$l=array();
	$c=1;
	while (!feof($f)){
		$line = trim(fgets($f));
		$line = mb_convert_encoding( $line, "UTF-8", "GBK" );		
		$l[$c] = $line;
		//echo $line.'<br>';
		$c++;
	}
	fclose($f);	
	return $img;
}

if ($cron_act == 'make_icons') {
	$tbl_name = 'shop_icons';
	$fname = 'iconlist_ivtr';
} else
if ($cron_act == 'make_skill_icons') {
	$tbl_name = 'skill_icons';
	$fname = 'iconlist_skill';
} else die('Input data error');

$db->query('TRUNCATE TABLE `'.$tbl_name.'`');
if ($db->errno != 0 ) die($db->error);
$img = ReadIconFile($fname);
$im1 = imagecreatetruecolor($imw,$imh);
$n = 0;
foreach ($l as $num => $val){
	if ($val=='') continue;
	if ($num > $cols) $row = floor(($num-1)/$cols); else $row=0;
	$col=$num-($row*$cols)-1;
	if ($col<0) $col=0;	
	imageCopy($im1,$img,0,0,$col*$imw,$row*$imh,$imw,$imh);
	ob_start();
	imagejpeg($im1,null,90);
	$result = ob_get_clean();
	$db->query("INSERT INTO `".$tbl_name."` (`name`,`icon`) VALUES ('".$db->real_escape_string($val)."','".$db->real_escape_string($result)."')");
	if ($db->errno != 0 )
	{
		echo $db->error.' - skipped<br>';
		continue;
	}
	$n++;
}
echo "$n icons done.";
