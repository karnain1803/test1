<?php
if (!defined('main_def')) die();
require_once('config.php');
function AddLoginLog($state,$id=''){
	$poststr = array(
		'op' => 'AddLoginLog',
		'ip' => getIP(),
		'userid' => ($id!='')?$id:$_SESSION['id'],
		'login' => $_SESSION['login'],
		'action' => $state
	);
	CurlPage($poststr, 15, 1, true);
}
$login_log = false;
if (isset($_POST['dologin'])){	
	if (isset($_POST['username'])) $_SESSION['login'] = strtolower($_POST['username']);
	if (isset($_POST['password'])) $_SESSION['passw'] = $_POST['password'];
	if (isset($_POST['email'])) $_SESSION['email'] = strtolower($_POST['email']);
	$login_log = true;
}
if (!isset($_SESSION['login']) || $_SESSION['login'] == '') {
	if (isset($cookie['ip']) && $cookie['ip'] == getIP()){
		$_SESSION['login'] = (isset($cookie['login']))?$cookie['login']:'';
		$_SESSION['passw'] = (isset($cookie['passw']))?$cookie['passw']:'';
		$_SESSION['email'] = (isset($cookie['email']))?$cookie['email']:'';
		$login_log = true;
	}
}
if (!isset($_SESSION['email'])) $_SESSION['email'] = '';

function do_auth($login, $pass, $email) {
	// Ответ: 0 - неверный логин/пасс, 1 - успешно, 2 - символы в логине, 3 - символы в пароле, 4 - длина логина, 5 - длина пароля, 6 - ошибка бд, 7 - антибрут, 8 - email, 9 - ЛК закрыт, 10 - запрет по IP, 11 - не установлен ioncube, 15 - Новый акк VK
	global $auth_result, $auth_type, $login_filter, $login_min_len, $login_max_len, $passw_min_len, $passw_max_len, $auth_type;
	global $server_side_script;
	global $no_visible_elements;
	global $cookie_name;
	global $email_require;
	global $lk_active_for_all, $login_log, $global_admin;
	$no_visible_elements = true;
	//$_SESSION['login'] = '';
	//$_SESSION['passw'] = '';
	$_SESSION['id'] = 0;
	$_SESSION['lkgold'] = 0;
	$_SESSION['lksilver'] = 0;
	$_SESSION['rules_cnt'] = 0;
	$_SESSION['isadmin'] = false;	
	$_SESSION['ipdata'] = '';
	if ($_SESSION['do_vklogin'] && $_SESSION['vk_id']) {
		// Авторизация через VK		
		$login_log = true;
		// Запрос авторизации с сервера	
		$postdata = array(
			'op' => 'vkauth',
			'vk_id' => $_SESSION['vk_id'],
			'vk_name' => $_SESSION['vk_name'],
			'vk_photo' => $_SESSION['vk_photo'],
			'ip' => getIP()
		);
	} else 
	if ($_SESSION['do_steamlogin'] && $_SESSION['steam_id']) {
		// Авторизация через Steam
		$login_log = true;
		// Запрос авторизации с сервера	
		$postdata = array(
			'op' => 'steamauth',
			'steam_id' => $_SESSION['steam_id'],
			'steam_name' => $_SESSION['steam_name'],
			'steam_photo' => $_SESSION['steam_photo'],
			'ip' => getIP()
		);
	} else {
		// Обычная авторизация
		// Проверка параметров
		if (preg_match($login_filter, $login, $Txt)) return 2; // В логине использованы недопустимые символы
		if (strlen($login) < $login_min_len || strlen($login) > $login_max_len ) return 4; // Логин должен быть от 4 до 20 символов
		if (isset($_POST['dologin'])) {
			if (CheckPassw($pass)) return 3; // В пароле использованы недопустимые символы
			if ((strlen($pass)<$passw_min_len)||(strlen($pass)>$passw_max_len)) return 5; // Пароль долен быть от 6 до 20 символов
			$md = base64_encode(GetPasswHash($login, $pass));
		} else $md = $_SESSION['passw'];
		if ($email_require) {
			if (!checker($email)) return 8; // Ошибка мыла
		}		
		// Запрос авторизации с сервера	
		$postdata = array(
			'op' => 'auth',
			'login' => $login,
			'passw' => $md,		
			'ip' => getIP()
		);
	}
	$postdata['client'] = GetLink();
	if ($login_log) $postdata['session'] = session_id();
	if ($email_require && !$_SESSION['do_vklogin'] && !$_SESSION['do_steamlogin']) $postdata['email'] = $email;
	$auth_result = '1';
	$result = CurlPage($postdata, 15, 1, true);
	$auth_result = $result;
	if (!CheckNum($result)) {
		if ($result == '') $result = 102; // пустой ответ
		return $result;	
	}
	$res = @unserialize($result);
	$act_key = (isset($res['act_key']))?$res['act_key']:'';
	define('act_key', $act_key);
	$ak = @AssignPData(act_key);	
	if (!isset($res['name'])) return 6;
	if ($_SESSION['do_vklogin']) {
		$_SESSION['do_vklogin'] = false;
		$login = $res['name'];
		$_SESSION['login'] = $res['name'];
		$_SESSION['email'] = $res['acc_email'];		
	}	
	if ($_SESSION['do_steamlogin']) {
		$_SESSION['do_steamlogin'] = false;
		$login = $res['name'];
		$_SESSION['login'] = $res['name'];
		$_SESSION['email'] = $res['acc_email'];		
	}
	if ($login != $res['name']) return 6;	
	$_SESSION['isadmin'] = IsAdmin($res['id']);//$res['isadmin'];
	// Проверка запрета входа по IP	
	if ($res['ipdata'][0] && !$global_admin) {
		$ipen = false;
		foreach ($res['ipdata'][1] as $i => $val) {
			if (strpos(getIP(), $val) === 0) $ipen = true;
		}
		if (!$ipen) {
			if ($login_log) AddLoginLog(5,$res['id']);
			return 10;
		}
	}
	if (session_id()!=$res['session_data']) return 1000;	
	$_SESSION['login'] = $login;
	$_SESSION['passw'] = $res['pwd_hash'];
	$_SESSION['lkgold'] = $res['lkgold'];
	$_SESSION['lksilver'] = $res['lksilver'];
	$_SESSION['id'] = $res['id'];
	$_SESSION['ip'] = getIP();
	$_SESSION['rules_cnt'] = $res['rules_cnt'];	
	$_SESSION['question'] = $res['question'];
	$_SESSION['answer'] = $res['answer'];
	$_SESSION['acc_email'] = $res['acc_email'];
	$_SESSION['script'] = $res['script'];
	$_SESSION['ipdata'] = $res['ipdata'];
	$_SESSION['act_key'] = $res['act_key'];
	$_SESSION['vk_id'] = $res['vk_id'];
	$_SESSION['vk_name'] = $res['vk_name'];
	$_SESSION['vk_photo'] = $res['vk_photo'];
	$_SESSION['steam_id'] = $res['steam_id'];
	$_SESSION['steam_name'] = $res['steam_name'];
	$_SESSION['steam_photo'] = $res['steam_photo'];
	$_SESSION['is_timed'] = $res['is_timed'];
	$_SESSION['rest_time'] = $res['rest_time'];
	if (!$lk_active_for_all && !$_SESSION['isadmin']) return 9;
	if (!$ak) return 13;
	if ($login_log) AddLoginLog(3);
	$no_visible_elements = false;
	if (isset($_POST['remember'])) {
		if ($_POST['remember']) {
			$cookie = array();
			$cookie['login'] = $login;
			$cookie['passw'] = $_SESSION['passw'];
			$cookie['email'] = $email;
			$cookie['ip'] = getIP();
			$cookie = base64_encode(encode(serialize($cookie)));
			SetCookie($cookie_name, $cookie, time()+29030400);
		} else if (isset($_POST['dologin']) || $_SESSION['do_vklogin'] || $_SESSION['do_steamlogin']) SetCookie($cookie_name, '');
	} else if (isset($_POST['dologin']) || $_SESSION['do_vklogin'] || $_SESSION['do_steamlogin']) SetCookie($cookie_name, '');	
	return 1;
}
$no_visible_elements = false;
if (isset($_SESSION['login']) && isset($_SESSION['passw'])){	
	//if ($lk_active_for_all)
	$auth_errno = do_auth($_SESSION['login'], $_SESSION['passw'], $_SESSION['email']);
	if ($auth_errno == 15) {
		// Быстрая регистрация VK, Steam
		header('Location: fast_reg.php');
		die();
	}
} else {
	$no_visible_elements = true;
}

