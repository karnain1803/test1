<?php
include('config.php');
$cur_adm = IsAdmin(0);
if (!$_SESSION['isadmin'] && !$global_admin) die();
if (!isset($_POST['klan'])) $act = 'itemname'; else $act = 'klanname';
$postdata = array(
	'op' => 'adm',
	'act' => $act,
	'id' => $_SESSION['id'],
	'ip' => $_SERVER['REMOTE_ADDR'],
	'servid' => $servid,
	'itemid' => intval($_POST['id'])
);
$res = CurlPage($postdata, 5, 1, true);
$res1 = @unserialize($res);
if (is_array($res1)) {
	echo '<font color="#ff0000">'.$res1['errtxt'].'</font>';
	die();
}
echo $res;
