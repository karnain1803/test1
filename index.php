<?php
require_once('config.php');
$cron_act = (isset($_GET['cron_act']))?$_GET['cron_act']:'';
$data = (isset($_POST['data']))?$_POST['data']:'';
if (isset($argv) && count($argv)==3) $cron_act = $argv[1];
$cron_passw = (isset($_GET['cron_passw']))?$_GET['cron_passw']:'';
$au = (isset($_POST['auth']))?$_POST['auth']:false;
if (isset($argv) && count($argv)==3) $cron_passw = $argv[2];
if ($cron_act && $cron_passw == $cron_act_passw) {
	$_SERVER['REMOTE_ADDR'] = (isset($_SERVER['REMOTE_ADDR']))?$_SERVER['REMOTE_ADDR']:'localhost';
	switch ($cron_act) {
		case 'clean_klan_cache':
		   $postdata = array( 'op' => 'clean_klan_cache',
		   'ip' => $_SERVER['REMOTE_ADDR']
		   );
		   $result = CurlPage($postdata, 20);
		   $data = @unserialize($result);
		   if (!is_array($data)) die($result);
		   die();
		break;
		case 'online_stat_site':
		   $file = 'online.txt';
		   $postdata = array( 'op' => 'online_stat',
		   'ip' => $_SERVER['REMOTE_ADDR']
		   );
		   $result = CurlPage($postdata, 20);
		   $data = @unserialize($result);
		   if (!is_array($data)) die($result);
		   file_put_contents($file, $data['online_acc']);   
		   die();
		break;
		case 'online_stat':
			$postdata = array( 'op' => 'online_stat',
			   'ip' => $_SERVER['REMOTE_ADDR']
			);
			$result = CurlPage($postdata, 20);
			$data = @unserialize($result);
			if (!is_array($data)) die($result);
			$sql = sprintf("INSERT INTO `online_stat` (`data`, `online_acc`, `online_pers`, `online_world`, `online_instance`) VALUES (now(), %d, %d, %d, %d)", $data['online_acc'], $data['online_pers'], $data['online_world'], $data['online_instance']);
			$db->query($sql);
			if ($db->errno) echo $db->error;
			die();
			break;
		case 'refresh_top':
			$postdata = array( 'op' => 'refresh_top',
			   'servid' => $servid,
			   'ip' => $_SERVER['REMOTE_ADDR']
			);
			$result = CurlPage($postdata, 500, true, true);			
			$data = @AssignPData($result);
			if (!$data) die($result);
			$data = @unserialize($data);
			if (!is_array($data)) die($result);
			$topfile = 'top/top';
			foreach ($data as $i => $val){
				$f = fopen($topfile.$i.'.html',"w");
				if ($f) {
					fwrite($f, $val);
					fclose($f);
				}
			}			
			die();
			break;
		case 'mmotop_bonus':
		case 'qtop_bonus':		
			$res = $db->query("select * from `".$db->real_escape_string($config_tbl_name)."` WHERE `section`=4");
			if (!$res) die($db->error);
			$postdata = array( 'op' => $cron_act,
			   'servid' => $servid,
			   'top_log_lifetime' => $top_log_lifetime,
			   'ip' => $_SERVER['REMOTE_ADDR']
			);
			while ($row = mysqli_fetch_assoc($res)){
				$postdata[$row['name']] = $row['value'];
			}			
			CurlPage($postdata, 20, false, true);					
			die();
			break;
		case 'update_user_info':			
			$postdata = array( 'op' => $cron_act,
			   'ip' => $_SERVER['REMOTE_ADDR'],
			   'ref_level_bonus_enabled' => $ref_level_bonus_enabled,
			   'ref_require_level' => $ref_require_level,
			   'ref_item' => serialize($ref_item),
			   'ref_require_rb' => $ref_require_rb,
			   'id' => (isset($_GET['id']))?intval($_GET['id']):0
			);					
			CurlPage($postdata, 15, false, true);					
			die();
			break;	
		case 'make_icons':
		case 'make_skill_icons':
			define('make_icons', true);
			include('makeicons_mysql.php');
			die();
		case 'make_names':
			$postdata = array( 'op' => $cron_act,
			   'ip' => $_SERVER['REMOTE_ADDR']
			);					
			@CurlPage($postdata, 25, false, true);					
			die();
			break;	
		default:
			die('Unknown action');
			break;
	}
}
if ($au) {
	switch ($au) {
		case 'auth_data':
			$postdata = array( 'op' => $au,
			   'ip' => $_SERVER['REMOTE_ADDR'],
			   'data' => $data
			);					
			@CurlPage($postdata, 25, false, true);					
			die();
		break;
	}
}
$CurlRet = '';
if (isset($_GET['doVkLogin']) && $vk_login_enable){
	$_SESSION['vk_link_redirect'] = GetLink();
	$link = sprintf("https://oauth.vk.com/authorize?client_id=%s&redirect_uri=%s&display=page", $vk_app_id, htmlspecialchars($_SESSION['vk_link_redirect']));
	header('Location: '.$link);
	die();
}
if (isset($_GET['doSteamLogin']) && $steam_login_enable){
	require_once('Openid.php');	
	$link = GetLink(false);
	$openid = new LightOpenID($link);
	if (!isset($_GET['openid_ns'])) {
		$openid->identity = 'http://steamcommunity.com/openid';
		header('Location: '.$openid->authUrl());
		die();
	} else {
	if ($openid->validate()) {
		$id = $openid->identity;
		preg_match("/^http:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/", $id, $matches);
		$steamid = $matches[1];	
		$url = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" . $steam_app_key . "&steamids=" . $steamid);
		if ($url) {
			$content = json_decode($url, true);	
			$_SESSION['steam_id'] = $content['response']['players'][0]['steamid'];
			$_SESSION['steam_name'] = $content['response']['players'][0]['personaname'];
			$_SESSION['steam_photo'] = $content['response']['players'][0]['avatarmedium'];
			$_SESSION['do_steamlogin'] = true;
			header('location: index.php');
			die();
		}
	} else {
		$CurlRet = "Пользователь не авторизован";
	        }
	}	
}
if (isset($_GET['error']) && isset($_GET['error_description'])) {
	$CurlRet = $_GET['error_description'];
}
if (isset($_GET['addvk']) && $vk_login_enable) {
	if (!isset($_SESSION['id']) || !$_SESSION['id']) {
		header('Location: index.php');
		die();
	}
	$_SESSION['vk_link_redirect'] = GetLink();
	$_SESSION['addvk'] = true;
	$link = sprintf("https://oauth.vk.com/authorize?client_id=%s&redirect_uri=%s&display=page", $vk_app_id, htmlspecialchars($_SESSION['vk_link_redirect']));
	header('Location: '.$link);
	die();
}
if (isset($_GET['addsteam']) && $steam_login_enable) {
	if (!isset($_SESSION['id']) || !$_SESSION['id']) {
		header('Location: index.php');
		die();
	}
	$_SESSION['addsteam'] = true;
	require_once('Openid.php');	
	$link = GetLink(false);
	$openid = new LightOpenID($link);
	if (!isset($_GET['openid_ns'])) {
		$openid->identity = 'http://steamcommunity.com/openid';
		header('Location: '.$openid->authUrl());
		die();
	} else {
	if ($openid->validate()) {
		$id = $openid->identity;
		preg_match("/^http:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/", $id, $matches);
		$steamid = $matches[1];	
		$url = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" . $steam_app_key . "&steamids=" . $steamid);
		if ($url) {
			$content = json_decode($url, true);	
			$_SESSION['steam_id'] = $content['response']['players'][0]['steamid'];
			$_SESSION['steam_name'] = $content['response']['players'][0]['personaname'];
			$_SESSION['steam_photo'] = $content['response']['players'][0]['avatarmedium'];
			if (isset($_SESSION['addsteam']) && $_SESSION['addsteam'] && $_SESSION['id']) {
				unset($_SESSION['addsteam']);
				$postdata = array(
					'op' => 'addsteam',
					'steam_id' => $_SESSION['steam_id'],
					'steam_name' => $_SESSION['steam_name'],
					'steam_photo' => $_SESSION['steam_photo'],
					'id' => $_SESSION['id'],
					'ip' => getIP()
				);
				$result = CurlPage($postdata, 15);
				$r = '?r='.$result;
			} else {
				$_SESSION['do_steamlogin'] = true;
				$r = '';
			}
			header('location: index.php'.$r);
			die();
		}
	} else {
		$CurlRet = "Пользователь не авторизован";
	        }
	}	
}
if (isset($_GET['code'])) 
	if ($vk_login_enable && isset($_SESSION['vk_link_redirect']) && $_SESSION['vk_link_redirect'] != ''){
		$link = sprintf("https://oauth.vk.com/access_token?client_id=%s&client_secret=%s&code=%s&redirect_uri=%s", $vk_app_id, $vk_app_key, $_GET['code'], htmlspecialchars($_SESSION['vk_link_redirect']));
		unset($_SESSION['vk_link_redirect']);
		$data = _CurlPage($link);
		if ($data !== false){
			$data = json_decode($data);
			if (isset($data->error)) {
				$CurlRet = $data->error_description;
			} else {
				$d = array(
					'user_id' => $data->user_id,
					'v' => '5.107',
					'fields' => 'photo_50',
					'lang' => 'ru',
					'access_token' => $data->access_token
				);		
				$data = _CurlPage('https://api.vk.com/method/users.get', $d);
				if ($data !== false){
					$data = @json_decode($data);
					if (isset($data->response[0]) && isset($data->response[0]->id) && $data->response[0]->id != '') {
						$_SESSION['vk_id'] = $data->response[0]->id;
						$_SESSION['vk_name'] = @$data->response[0]->first_name.' '.@$data->response[0]->last_name;
						$_SESSION['vk_photo'] = @$data->response[0]->photo_50;	
						if (isset($_SESSION['addvk']) && $_SESSION['addvk'] && $_SESSION['id']) {
							unset($_SESSION['addvk']);
							$postdata = array(
								'op' => 'addvk',
								'vk_id' => $_SESSION['vk_id'],
								'vk_name' => $_SESSION['vk_name'],
								'vk_photo' => $_SESSION['vk_photo'],
								'id' => $_SESSION['id'],
								'ip' => getIP()
							);
							$result = CurlPage($postdata, 15);
							$r = '?r='.$result;
						} else {
							$_SESSION['do_vklogin'] = true;
							$r = '';
						}
					} 
					header('location: index.php'.$r);
					die();				
				}
			}
		}
	} else {
		header('location: index.php');
		die();
	}
$cur_adm = IsAdmin(0);
$doconfig = (isset($_GET['doconfig'])&&$_GET['doconfig']==1)?true:false;
$_SESSION['login']=(isset($_SESSION['login']))?$_SESSION['login']:'';
$_SESSION['passw']=(isset($_SESSION['passw']))?$_SESSION['passw']:'';
$_SESSION['email']=(isset($_SESSION['email']))?$_SESSION['email']:'';
if ($cur_adm && $doconfig) {
echo HeaderTemplate($title, $description, $favicon);
define('main_def', true);
echo '	<div class="row-fluid">
		<div class="box span12">
			<div class="box-header well" data-original-title>
				<h2><i class="icon icon-color icon-gear"></i> Настройки ЛК</h2> <div style="float:right"><a href="index.php" class="btn btn-inverse">Перейти к авторизации</a></div>
			</div>
		<div class="box-content">
';
		include('pages/config.php');
echo '		</div>
	</div>
';
		include('footer.php');
	die();
}
include('header.php');
if (!$no_visible_elements) {	
$cur_add = '';
if ($op == 'stattop') {
	if (!isset($_SESSION['cur_top'])) $_SESSION['cur_top'] = 'mmotop';
	if (isset($_GET['curtop'])) {
		if ($_GET['curtop'] == 'mmotop') $_SESSION['cur_top'] = 'mmotop';
		if ($_GET['curtop'] == 'qtop') $_SESSION['cur_top'] = 'qtop';
	}
	if ($_SESSION['cur_top'] == 'mmotop') $cur_add = ' <span class="btn btn-success">MMOTOP</span> '; else
	$cur_add = ' <a class="btn" href="index.php?op=stattop&curtop=mmotop">MMOTOP</a> ';
	if ($_SESSION['cur_top'] == 'qtop') $cur_add .= ' <span class="btn btn-success">QTOP</span> '; else
	$cur_add .= ' <a class="btn" href="index.php?op=stattop&curtop=qtop">QTOP</a> ';
}
?>	
			<div class="row-fluid">
				<div class="box span12">
					<div class="box-header well" data-original-title>
						<h2><i class="icon icon-color <?=$cur_icon?>"></i> <?php echo $cur_name.$cur_add; ?></h2>
					</div>
					<div class="box-content">
						<?php						
						if (!$lk_active_for_all && !$_SESSION['isadmin']) {
							?>
						<div class="alert alert-info">
						<h4 class="alert-heading">Объявление!</h4>
						Личный кабинет временно недоступен из-за технических работ. Попробуйте зайти позже.
						</div>
						<?php
						} else 
						if (($cur_dost==2 && !$_SESSION['isadmin'])||($cur_dost==1 && !$_SESSION['isadmin'] && $_SESSION['rules_cnt']==0) || ($cur_dost==3 && !in_array($_SESSION['id'], $ext_users))) {
							?>
						<div class="alert alert-warning">
						<h4 class="alert-heading">Доступ запрещен!</h4><br>
						<?=GetErrorTxt(33)?>
						</div>
						<?php
						} else {							
							switch ($op){
								case 'main': include('pages/main.php');
								break;

								case 'pers': include('pages/pers.php');
								break;

								case 'shop': include('pages/shop.php');
								break;

								case 'money': include('pages/money.php');
								break;

								case 'klan': include('pages/klan.php');
								break;

								case 'donate': include('pages/donate.php');
								break;

								case 'donsuccess': include('pages/donsuccess.php');
								break;

								case 'donfail': include('pages/donfail.php');
								break;

								case 'history': include('pages/history.php');
								break;

								case 'stat': include('pages/stat.php');
								break;	

								case 'online': include('pages/online.php');
								break;	

								case 'top': include('pages/top.php');
								break;							

								default:  include('pages/main.php');
								break;
	
								case 'account': include('pages/account.php');
								break;

								case 'statmmotop': 
								case 'statqtop':
								case 'stattop':
									include('pages/stattop.php');
								break;	

								case 'loginlogs': include('pages/loginlogs.php');
								break;	

								case 'accounts': include('pages/accmanage.php');
								break;						

								case 'lklogs': include('pages/lklogs.php');
								break;

								case 'config': include('pages/config.php');
								break;

								case 'update': 
									if (!$_SESSION['isadmin']) {
										echo '
										<div class="alert alert-warning">
										<h4 class="alert-heading">Доступ запрещен!</h4><br>
										'.GetErrorTxt(33).'
										</div>';
									} else {
										include('pages/update.php');
									}
								break;
							}
						} ?>
						<script type="text/javascript"><?php 
						$notice = @unserialize($auth_result);
						$notice = (isset($notice['notice']))?$notice['notice']:'';
						if (is_array($notice)) {
							foreach ($notice as $i => $val){
								if ($val[0] == 0) printf('noty({"text":"%s","layout":"top","type":"information"});', AssignPData($val[1])); else
								if ($val[0] == 1 && $_SESSION['isadmin']) printf('noty({"text":"%s","layout":"top","type":"error"});', AssignPData($val[1]));
							}
						}		
	?></script>
					</div>
				</div><!--/span-->
			
			</div><!--/row-->
<?php } else { // Авторизация
if ($email_require) { ?>
<style>
.username-text {
	margin-top: 27px;
}
.password-text {
	margin-top: 27px;
}
input[type="checkbox"] + label {
	margin-top: 6px;
}
.forgot-usr-pwd {
	margin-top: 6px;
}
input[type="submit"] {
	margin-top: -8px;
}
</style>
<?php }
?>
<div id="container" class="login-box1" style="display:none"><?php if ($vk_login_enable || $steam_login_enable) {?>
			<div class="applogin">
					<?php if ($vk_login_enable) echo '<a href="index.php?doVkLogin" title="Авторизоваться через ВКонтакте"><div class="vklogin"></div></a>';
	                		if ($steam_login_enable) echo '<a href="index.php?doSteamLogin" title="Авторизоваться через Steam"><div class="steamlogin"></div></a>'; ?>
				</div><div class="clearfix"></div><?php } ?>
			<form action="index.php" method="post" name="lfrm">
			<input type="hidden" name="dologin" value="1">
				<div class="login"><?=$title?><?php
					if ($cur_adm) echo ' <a href="index.php?doconfig=1"><span class="icon icon-color icon-gear" data-rel="tooltip" data-original-title="Настройки ЛК"></span></a>'; ?></div>				
				<div class="username-text">Логин:</div>				
				<div class="password-text">Пароль:</div>
				<div class="username-field">
					<input type="text" name="username" value="<?=$_SESSION['login']?>" maxlength="<?$login_max_len?>" />
				</div>
				<div class="password-field">
					<input type="password" name="password" value="" />
				</div>
				<?php if ($email_require) { ?>
				<style>
				.applogin {
					top: 105px;
				}
				</style>
				<div class="email-field">
					<input type="text" name="email" style="width:215px" value="<?=$_SESSION['email']?>"/>
				</div><?php } ?>
				<input type="checkbox" name="remember" id="remember-me" /><label for="remember-me">Запомнить меня</label>
				<div class="forgot-usr-pwd">Забыли <a href="remember.php">логин или пароль</a>?</div>
				<input type="submit" value="Вход" />
				<div class="register"><a href="register.php">Регистрация аккаунта</a></div>
				<a href="#" onclick="document.lfrm.submit();"><div class="login-btn"></div></a>
			</form>
		</div>
		<div id="footer">by <a href="#">alexdnepro</a> <?php echo @date('Y') ?> <font color="#a0a0a0">ver <?=$lk_ver?></font>
		<br><br>
		</div>
<?php
if ($show_result && isset ($auth_result) && $cur_adm) { ?>
	<div class="alert alert-error">		
		<h4 class="alert-heading">Ответ сервера авторизации:</h4>
		<?php if ($curl_error_msg == '') echo $auth_result; else echo $curl_error_msg; ?>		
	</div>
<?php } ?>
<script>
$(document).ready(function(){	
	<?php	
	if ($CurlRet) { ?>	
	$( ".login-box1" ).css("display","none").show();
	noty({"text":"Ошибка! <?=$CurlRet?>","layout":"top","type":"error"});
	<?php } else
	if ($auth_errno!=1 && ((isset($_SESSION['login']) && $_SESSION['login'] != '') || $_SESSION['do_vklogin'] || $_SESSION['do_steamlogin'])) { ?>	
	$( ".login-box1" ).css("display","none").show();
	noty({"text":"Ошибка! <?=sprintf($auth_errors[$auth_errno], $_SERVER['REMOTE_ADDR'])?>","layout":"top","type":"error"});
	<?php } else 
	if (!$lk_active_for_all) { ?>
	$( ".login-box1" ).css("display","none").show();
	noty({"text":"<?=sprintf($auth_errors[9])?>","layout":"bottom","type":"information"});	
	<?php } else { ?>
	$( ".login-box1" ).css("display","none").slideDown();
	<?php } ?>
});
</script>
</body>
</html>
<?php 
die();
}
if ($show_result && isset ($auth_result) && $_SESSION['isadmin']) {
?>
<div class="logs-btn" title="Показать ответы сервера" onclick="$('#server_response').slideToggle()"><span class="icon32 icon-orange icon-script"></span></div>
<div class="server_response" id="server_response">
	<button type="button" class="close close-white" onclick="$('#server_response').slideToggle()">×</button><div class="clearfix"></div>
	<div class="alert alert-success">
		<button type="button" class="close" data-dismiss="alert">×</button>
		<h4 class="alert-heading">Ответ сервера авторизации</h4>
		<pre><?=$auth_result?></pre>
	</div>
<?=$curl_messages?>
</div>
<?php }
include('footer.php'); 
