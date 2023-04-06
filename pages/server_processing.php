<?php
include('../config.php');
IsAdmin(-1);
if (!isset($global_admin) || !$global_admin)
	if (!isset($_SESSION['isadmin']) || !$_SESSION['isadmin']) die('');
if (isset($_GET['item_desc']) && isset($_POST['itemid'])){
	$fn = '../item_ext_desc.txt';
	if (!file_exists($fn)) die('File item_ext_desc.txt not found');
	$file = file_get_contents( $fn );		
	if (!$file) die('File open error item_ext_desc.txt');	
 	$file = @mb_convert_encoding( $file, "UTF-8", "UTF-16LE" );	
	$content = explode("\n", $file);
	foreach ($content as $i => $val){
		if (preg_match('/^(\d+)\s+\"(.*)\"$/u', trim($val), $m)) {
			if ($m[1] == $_POST['itemid']) {
				$m = preg_replace('/\^([0-9abcdefABCDEF]{6})([^\^]*)/u', '<font color="#$1">$2</font>', $m[2]);
				$m = preg_replace('/(\\\r)/u', '<br>', $m);
				echo $m;
				die();
			}
		}
	}
	die();
} else
if (isset($_GET['update_info'])) {
	$data = UpdatePage('lastver');
	$object = new stdClass;
	$answ = @unserialize($data);
	if (!is_array($answ)) {
		$ver_txt = '<span class="label label-important">Ошибка связи с сервером обновлений</span>';
		$answ = array();
		$answ['last_ver'] = $lk_ver; $answ['history'] = '';
	} else {
		if ($answ['last_ver'] == $lk_ver) {
			$ver_txt = '<i class="icon icon-color icon-check"></i>';
		} else $ver_txt = '<a href="index.php?op=update" class="btn btn-mini btn-inverse"><i class="icon icon-color icon-refresh"></i> Обновить до последней версии</a>';
	}
	$answ['ver_txt'] = $ver_txt;
	if ($answ['last_ver'] != $lk_ver) $answ['upd_notice']='<i class="icon icon-color icon-notice"></i> '; else $answ['upd_notice'] = '';
	$object->last_ver = $answ['last_ver'];
	$object->history = $answ['history'];
	$object->ver_txt = $answ['ver_txt'];
	$object->upd_notice = $answ['upd_notice'];
	echo json_encode($object);
	die();
} else
if (isset($_GET['id'])||isset($_GET['login'])) {	
	if (isset($_GET['id'])) {
		$postdata = array(
			'op' => 'userinfo',
			'servid' => $servid,
			'id' => intval($_GET['id'])
		);
		$donsumm = GetUserDonateSumm('', $_GET['id'], true);	
	} else {
		if (preg_match($login_filter, $_GET['login']) || strlen($_GET['login']) < $login_min_len || strlen($_GET['login']) > $login_max_len) {
			echo GetErrorTxt(10);
			die();
		}
		$postdata = array(
			'op' => 'userinfo',
			'servid' => $servid,
			'login' => $_GET['login']
		);
		$donsumm = GetUserDonateSumm($_GET['login'], '', true);	
	}
	printf('Пожертвовано: <b><font color="#ffff00">%s руб.</font></b><br>', $donsumm);
} else 
if (isset($_GET['saveeditrole']))
{
	if (CheckNum($_GET['saveeditrole'])) die('Input data error');
	$postdata = $_POST;
	$postdata['op'] = 'saveeditrole';
	$postdata['roleid'] = intval($_GET['saveeditrole']);
	$postdata['id'] = $_SESSION['id'];
	$postdata['ip'] = getIP();
} else
if (isset($_GET['editrole']))
{
	if (CheckNum($_GET['editrole'])) die('Input data error');	
	$postdata = array(
		'op' => 'editrole',		
		'roleid' => intval($_GET['editrole'])
	);
} else
if (isset($_GET['rid2uid'])) {	
	if (CheckNum($_GET['rid2uid'])) die('Input data error');	
	$postdata = array(
		'op' => 'rid2uid',
		'roleid' => intval($_GET['rid2uid'])
	);
} else 
if (isset($_GET['banrole'])) {
	$postdata = array(
		'op' => 'banrole',
		'id' => $_SESSION['id'],
		'ip' => getIP()
	);
	$postdata = array_merge($_POST, $postdata);
} else 
if (isset($_GET['findrolename'])) {	
	$postdata = array(
		'op' => 'findrolename',
		'rolename' => $_GET['findrolename']
	);
} else 
if (isset($_GET['roleid'])) {	
	if (CheckNum($_GET['roleid'])) die('Input data error');	
	$postdata = array(
		'op' => 'roleinfo',
		'servid' => $servid,
		'roleid' => intval($_GET['roleid'])
	);
} else 
if (isset($_GET['accmanage'])) {
	$postdata = array(
		'op' => 'accmanage'
	);
	$postdata = array_merge($_GET, $postdata);
} else 
if (isset($_GET['loginlog'])) {
	$postdata = array(
		'op' => 'loginlog_adm',
		'servid' => $servid
	);
	$postdata = array_merge($_GET, $postdata);
} else 
if (isset($_GET['klanart'])) {
	$postdata = array(
		'op' => 'klanart',
		'servid' => $servid
	);
	$postdata = array_merge($_GET, $postdata);
} else 
if (isset($_GET['shop_buffs'])) {
	$postdata = array(
		'op' => 'shop_buffs'
	);
	$postdata = array_merge($_GET, $postdata);
} else 
if (isset($_GET['shop_skills'])) {
	$postdata = array(
		'op' => 'shop_skills'
	);
	$postdata = array_merge($_GET, $postdata);
} else 
if (isset($_GET['promo_codes'])) {
	$postdata = array(
		'op' => 'promo_codes'
	);
	$postdata = array_merge($_GET, $postdata);
} else 
if (isset($_GET['mmotop'])) {
	$postdata = array(
		'op' => 'mmotoplogs'
	);
	$postdata = array_merge($_GET, $postdata);
} else 
if (isset($_GET['qtop'])) {
	$postdata = array(
		'op' => 'qtoplogs'
	);
	$postdata = array_merge($_GET, $postdata);
} else 
if (isset($_GET['edit_klanart'])) {
	$postdata = array(
		'op' => 'edit_klanart',
		'id' => intval($_GET['edit_klanart']),
		'servid' => $servid
	);
	$postdata = array_merge($_GET, $postdata);
} else
if (isset($_GET['edit_shop_buffs'])) {
	$postdata = array(
		'op' => 'edit_shop_buffs',
		'id' => intval($_GET['edit_shop_buffs'])
	);
	$postdata = array_merge($_GET, $postdata);
} else 
if (isset($_GET['edit_shop_skills'])) {
	$postdata = array(
		'op' => 'edit_shop_skills',
		'id' => intval($_GET['edit_shop_skills'])
	);
	$postdata = array_merge($_GET, $postdata);
} else 
if (isset($_GET['edit_promo_code'])) {
	$postdata = array(
		'op' => 'edit_promo_code',
		'id' => intval($_GET['edit_promo_code'])
	);
	$postdata = array_merge($_GET, $postdata);
} else {
	$postdata = array(
		'op' => 'lklogs'
	);
	$postdata = array_merge($_GET, $postdata);
}
$res = CurlPage($postdata, 10, 1, true);
$res1 = @unserialize($res);
if (is_array($res1)) {
	echo $res1['errtxt'];	
} else echo $res;
?>