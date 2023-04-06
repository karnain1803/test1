<?php
require_once('config.php');
if (isset($_GET['i'])) $i = base64_decode($_GET['i']); else $i = 'unknown.dds';
if (mb_strlen($i, "UTF-8") > 25) die();
$tbl_name = 'shop_icons';
if (isset($_GET['skill'])) $tbl_name = 'skill_icons';
$res = $db->query("SELECT * FROM `".$tbl_name."` WHERE `name`='".$db->real_escape_string($i)."'");
if ($db->errno>0) die('Query to DB fail');
if ($res->num_rows==0){
	$res = $db->query("SELECT * FROM `".$tbl_name."` WHERE `id`=1");
}
$row = mysqli_fetch_assoc($res);
Header("Content-type: image/jpeg");
echo $row['icon'];
