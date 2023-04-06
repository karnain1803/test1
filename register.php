<?php
require_once('config.php');
require_once('templates.php');
require_once('captcha.php');
$captcha = new captcha();
if ($email_confirm) $act_txt = '<br><b><font color="#ff0000">Обратите внимание!</font> Перед использованием, аккаунт нужно активировать, пройдя по ссылке из письма, отправленного Вам на почту</b>'; else $act_txt = '';
$error=0;
$errtext='';
$goreg = (isset($_POST['goreg']))?$_POST['goreg']:0;
if (isset($_GET['resendmail']) && isset($_SESSION['login']) && ($_SESSION['login'] != '') && !preg_match($login_filter, $_SESSION['login']) && (strlen($_SESSION['login']) >= $login_min_len && strlen($_SESSION['login']) <= $login_max_len ) && $email_confirm) {
	$goreg = 3;
	$lg = $_SESSION['login'];
	$postdata = array( 
		'op' => 'resendmail',
		'ip' => getIP(),
	        'login' => $_SESSION['login']);
	$result = CurlPage($postdata, 10);
	$a = @unserialize($result);
	if ($result == '' || !is_array($a)) {
		$errtext = 'Ошибка запроса к серверу';
		$error = 1;
	} else
	if ($a['errorcode'] != 0) {
		$error = 1; $errtext = $a['errtxt'];
		if ($a['errtxt'] == 'time') $errtext = sprintf($auth_errors[7], getIP()); else
		if ($a['errtxt'] == 'errorbase') $errtext = 'Ошибка запроса в базу данных'; else
		if ($a['errtxt'] == 'already') $errtext = 'Данный аккаунт уже активирован';
	} else {
		$realid = $a['realid'];
		$to = $a['email'];
		$subj = "Активация аккаунта на сервере ".$logotext;
		$confirm_link = GetLink().'register.php?activate='.mycrypt(encode(sprintf('%d|%s|%d', $servid, $_SESSION['login'], $realid)));
		$t = new templates();
		$text = $t->ActTemplate($confirm_link);
		if (!@send_mail($to, $subj, $text)) {
			$error = 1;
			$errtext = 'Ошибка отправки письма активации';
		}
	}
} else
if (isset($_GET['activate']) && $register_active){
	$act_code = @encode(decrypt($_GET['activate']));
	$v = explode('|', $act_code);
	if (count($v)==3) {
		if ($v[0]==$servid){
			if (!preg_match($login_filter, $v[1]) && strlen($v[1]) >= $login_min_len && strlen($v[1]) <= $login_max_len){
				$postdata = array( 'op' => 'actreg',
				   'ip' => getIP(),
		                   'login' => $v[1],
		                   'idacc' => intval($v[2])
				   );
				$result = CurlPage($postdata, 10);
				if ($result=='10') {
					$error=1;
					$errtext='Ошибка входящих данных';
				} else
				if ($result=='already') {
					$error=1;
					$errtext='Данный аккаунт уже активирован';
				} else
				if ($result=='errorbase') {
					$error=1;
					$errtext='Произошла ошибка при запросе к БД, попробуйте позже';
				} else
				if ($result=='ok') {
					$goreg = 2; $lg = $v[1];
				} else {
					$error=1;
					$errtext='При активации произошла ошибка, попробуйте позже';
				}
			} else {
				$error=1;
				$errtext='Ошибка входящих данных';
			}		
		} else {
			$error=1;
			$errtext='Ошибка входящих данных';
		}
	} else {
		$error=1;
		$errtext='Ошибка входящих данных';
	}
	//print_r($v);
}
$reflink = GetLink().'register.php?ref=';
if (isset($_GET['ref'])) {
	$referal=intval($_GET['ref']);
	SetCookie($cookie_name.'_reg_ref', $referal, time()+7200);
} else {
	if (isset($_COOKIE[$cookie_name.'_reg_ref'])) $referal = intval($_COOKIE[$cookie_name.'_reg_ref']); else
	$referal=0;
}
$postlogin = (isset($_POST['login']))?$_POST['login']:'';
$postpass = (isset($_POST['pass']))?$_POST['pass']:'';
$postpass_chek = (isset($_POST['pass_chek']))?$_POST['pass_chek']:'';
$postanswer = (isset($_POST['answer']))?$_POST['answer']:'';
$postquestion = (isset($_POST['question']))?$_POST['question']:'';
$postemail = (isset($_POST['email']))?$_POST['email']:'';
$postname = (isset($_POST['name']))?$_POST['name']:'';
$postreferal = (isset($_POST['referal']))?$_POST['referal']:'';
if ($goreg==1 && $register_active) {
	//Проверки
	if (!$captcha->Validate())
	{
		$error=1;
		$errtext.=' - неправильно введен код безопасности<br>';
	}
	if (preg_match($login_filter, $postlogin)) {
		$error=1;
		$errtext.=' - в логине использованы недопустимые символы<br>';
	}
	if (strlen($postlogin) < $login_min_len || strlen($postlogin)>$login_max_len ) {
		$error=1;
		$errtext.=' - логин должен быть не менее '.$login_min_len.' и не более '.$login_max_len.' символов';
	}
	if (CheckPassw($postpass)) {
		$error=1;
		$errtext.=' - в пароле использованы недопустимые символы<br>';
	}
	if (strlen($postpass)<$passw_min_len || strlen($postpass)>$passw_max_len) {
		$error=1;
		$errtext.=' - пароль должен быть не менее '.$passw_min_len.' и не более '.$passw_max_len.' символов<br>';
	}
	if ($postpass_chek != $postpass) {
		$error=1;
		$errtext.=' - пароли не совпадают<br>';
	}
	if ($postquestion == $postanswer) {
		$error=1;
		$errtext.=' - вопрос и ответ не должны быть одинаковыми<br>';
	}
	if ((mb_strlen($postquestion, 'utf8') < 3)||(mb_strlen($postquestion, 'utf8') > 30)) {
		$error=1;
		$errtext.=' - вопрос должен быть не менее 3 и не более 30 символов<br>';
	}
	if ((mb_strlen($postanswer, 'utf8') < 3)||(mb_strlen($postanswer, 'utf8') > 25)) {
		$error=1;		
		$errtext.=' - ответ должен быть не менее 3 и не более 25 символов<br>';
	}
	if (!preg_match($check_rus, $postquestion)) {
		$error=1;
		$errtext.=' - в вопросе использованы недопустимые символы<br>';
	}
	if (!preg_match($check_rus, $postanswer)) {
		$error=1;
		$errtext.=' - в ответе использованы недопустимые символы<br>';
	}
	if ((mb_strlen($postname, 'utf8')<3)||(mb_strlen($postname, 'utf8')>20)) {
		$error=1;		
		$errtext.=' - имя должно быть не менее 3 и не более 20 символов<br>';
	}
	if (!preg_match($check_rus, $postname)) {
		$error=1;
		$errtext.=' - в имени использованы недопустимые символы<br>';
	}
	if (!checker($postemail)) {
		$error=1;
		$errtext.=' - введите корректный E-Mail<br>';
	}
	$referal = intval($postreferal);
	//Отправка запроса на регистрацию
	if ($error==0){
		if ($referal!=0) $referal = ($referal+1)*16;
		if ($register_gold > 0 && $get_gold_btn) $register_gold = 0;
		$postdata = array( 'op' => 'reg',
			   'ip' => getIP(),
	                   'login' => $postlogin,
			   'ip_max_reg' => $ip_max_reg,
			   'email_max_reg' => $email_max_reg,
	                   'pass' => base64_encode(GetPasswHash($postlogin, $postpass)),
			   'email' => $postemail,
			   'email_confirm' => $email_confirm,
			   'question' => $postquestion,
	                   'answer' => $postanswer,
	                   'realname' => $postname,
			   'register_gold' => $register_gold,
			   'zoneid' => $zoneid,
			   'aid' => $aid,
	                   'referal' => $referal,
		           'vk_id' => $_SESSION['vk_id'],
		           'vk_name' => $_SESSION['vk_name'],
		           'vk_photo' => $_SESSION['vk_photo'],
		           'steam_id' => $_SESSION['steam_id'],
		           'steam_name' => $_SESSION['steam_name'],
		           'steam_photo' => $_SESSION['steam_photo']);
		$result = CurlPage($postdata, 10);		
		if ($result=='10') {
			$error=1;
			$errtext='Ошибка входящих данных';
		} else
		if ($result=='needauth') {
			header('Location: index.php');
			die();
		} else
		if ($result=='11') {
			$error=1;
			$errtext='Ошибка инициализации соединения с сервером';
		} else
		if ($result=='errorbase') {
			$error=1;
			$errtext='Произошла ошибка при запросе к БД, попробуйте позже';
		} else
		if ($result=='iplimit') {
			$error=1;
			$errtext='C вашего айпи адреса <b>'.getIP().'</b> зарегистрировано максимально возможное количество аккаунтов';
		} else
		if ($result=='emaillimit') {
			$error=1;
			$errtext='На данный E-Mail зарегистрировано максимально возможное количество аккаунтов';
		} else
		if ($result=='exists') {
			$error=1;
			$errtext='Данный аккаунт уже зарегистрирован, придумайте другой логин';
		} else
		if (substr($result, 0, 2)=='ok') {
			$realid = substr($result, 2);
			$idacc = intval(($realid/16)-1);
			$reflink = $reflink.$idacc;
			$error=0;
			$to = $postemail;
			$subj = "Регистрация аккаунта на сервере ".$logotext;
			if ($email_confirm) {
				$confirm_link = GetLink().'register.php?activate='.mycrypt(encode(sprintf('%d|%s|%d', $servid, $postlogin, $realid)));
			} else $confirm_link = '';
			$t = new templates();
			$text = $t->RegisterMail($postlogin, $postpass, $postquestion, $postanswer, $postname, $confirm_link, $reflink);
			@send_mail($to, $subj, $text);
			$lg=$postlogin;
			$postlogin=''; $postpass=''; $postpass_chek=''; $postquestion=''; $postanswer=''; $postname=''; $postemail='';			
		} else {
			$error=1;
			$errtext='При регистрации произошла ошибка, попробуйте позже';
			if ($show_result && IsAdmin(0)) {
				if ($curl_error_msg === '') $show_txt = $result; else $show_txt = $curl_error_msg;
				$errtext .= '
				<div class="alert alert-info">
				<button type="button" class="close" data-dismiss="alert">×</button>
				<h4 class="alert-heading">Ответ сервера</h4>
				'.$show_txt.'
				</div>
				'; 
			}
		}		
	} else $errtext='<b>При запросе обнаружены следующие ошибки:</b><br>'.$errtext;
}
?><!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<title><?=$title?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?=$description?>">
	<meta name="author" content="alexdnepro">

	<!-- The styles -->
	<link id="bs-css" href="css/bootstrap-classic.css" rel="stylesheet">
	<style type="text/css">
	  body {
		padding-bottom: 40px;
	  }
	  .sidebar-nav {
		padding: 9px 0;
	  }
	</style>
	<link href="css/bootstrap-responsive.css" rel="stylesheet">
	<link href="css/charisma-app.css" rel="stylesheet">
	<link href="css/jquery-ui-1.8.21.custom.css" rel="stylesheet">
	<link href='css/fullcalendar.css' rel='stylesheet'>
	<link href='css/fullcalendar.print.css' rel='stylesheet'  media='print'>
	<link href='css/chosen.css' rel='stylesheet'>
	<link href='css/uniform.default.css' rel='stylesheet'>
	<link href='css/colorbox.css' rel='stylesheet'>
	<link href='css/jquery.cleditor.css' rel='stylesheet'>
	<link href='css/jquery.noty.css' rel='stylesheet'>
	<link href='css/noty_theme_default.css' rel='stylesheet'>
	<link href='css/elfinder.min.css' rel='stylesheet'>
	<link href='css/elfinder.theme.css' rel='stylesheet'>
	<link href='css/jquery.iphone.toggle.css' rel='stylesheet'>
	<link href='css/opa-icons.css' rel='stylesheet'>
	<link href='css/uploadify.css' rel='stylesheet'>

	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

	<!-- jQuery -->
	<script src="js/jquery-1.7.2.min.js"></script>
	<!-- jQuery UI -->
	<script src="js/jquery-ui-1.8.21.custom.min.js"></script>
	<!-- transition / effect library -->
	<script src="js/bootstrap-transition.js"></script>
	<!-- alert enhancer library -->
	<script src="js/bootstrap-alert.js"></script>
	<!-- modal / dialog library -->
	<script src="js/bootstrap-modal.js"></script>
	<!-- custom dropdown library -->
	<script src="js/bootstrap-dropdown.js"></script>
	<!-- scrolspy library -->
	<script src="js/bootstrap-scrollspy.js"></script>
	<!-- library for creating tabs -->
	<script src="js/bootstrap-tab.js"></script>
	<!-- library for advanced tooltip -->
	<script src="js/bootstrap-tooltip.js"></script>
	<!-- popover effect library -->
	<script src="js/bootstrap-popover.js"></script>
	<!-- button enhancer library -->
	<script src="js/bootstrap-button.js"></script>	
	<!-- autocomplete library -->
	<script src="js/bootstrap-typeahead.js"></script>
	<!-- tour library -->
	<script src="js/bootstrap-tour.js"></script>
	<!-- library for cookie management -->
	<script src="js/jquery.cookie.js"></script>
	<!-- calander plugin -->
	<script src='js/fullcalendar.min.js'></script>
	<!-- data table plugin -->
	<script src='js/jquery.dataTables.min.js'></script>

	<!-- chart libraries start -->
	<script src="js/excanvas.js"></script>
	<script src="js/jquery.flot.min.js"></script>
	<script src="js/jquery.flot.pie.min.js"></script>
	<script src="js/jquery.flot.stack.js"></script>
	<script src="js/jquery.flot.resize.min.js"></script>
	<!-- chart libraries end -->

	<!-- select or dropdown enhancer -->
	<script src="js/jquery.chosen.min.js"></script>
	<!-- checkbox, radio, and file input styler -->
	<script src="js/jquery.uniform.min.js"></script>
	<!-- plugin for gallery image view -->
	<script src="js/jquery.colorbox.min.js"></script>	
	<!-- rich text editor library -->
	<script src="js/jquery.cleditor.min.js"></script>
	<!-- notification plugin -->
	<script src="js/jquery.noty.js"></script>
	<!-- file manager library -->
	<script src="js/jquery.elfinder.min.js"></script>
	<!-- star rating plugin -->
	<script src="js/jquery.raty.min.js"></script>
	<!-- for iOS style toggle switch -->
	<script src="js/jquery.iphone.toggle.js"></script>
	<!-- autogrowing textarea plugin -->
	<script src="js/jquery.autogrow-textarea.js"></script>	
	<!-- multiple file upload plugin -->
	<script src="js/jquery.uploadify-3.1.min.js"></script>
	<!-- history.js for cross-browser state change on ajax -->
	<script src="js/jquery.history.js"></script>
	<!-- application script for Charisma demo -->
	<script src="js/charisma.js"></script>
	<?=$captcha->GetKaptchaScript()?>

	<!-- The fav icon -->
	<link rel="shortcut icon" href="<?=$favicon?>">
	<script>
	var login_min_len = <?=$login_min_len?>;
	var login_max_len = <?=$login_max_len?>;
	var passw_min_len = <?=$passw_min_len?>;
	var passw_max_len = <?=$passw_max_len?>;
</script>
	<script src="js/regcheck.js"></script>	
</head>

<body>
<div class="container-fluid">
<div class="row-fluid">
	<div class="well box">
		<div class="box-header well" data-original-title>
			<h2><i class="icon icon-color icon-user"></i> Регистрация аккаунта на сервере <?=$logotext?></h2> <div style="float:right"><a href="logout.php" class="btn btn-inverse">Перейти к авторизации</a></div>						
		</div>
		<div class="box-content">
			<?php			
			if ($register_active) {
			if (($goreg==1)&&($error==0)) echo '<div class="alert alert-success">Поздравляем, аккаунт <b><font color=#0000ff>'.$lg.'</font></b> успешно зарегистрирован!<br> Данные от аккаунта отправлены Вам на почту (проверьте также в папке Спам).'.$act_txt.'</div>'; else
			if (($goreg==2)&&($error==0)) echo '<div class="alert alert-success">Спасибо, аккаунт <b><font color=#0000ff>'.$lg.'</font></b> успешно активирован.</div>'; else
			if (($goreg==3)&&($error==0)) echo '<div class="alert alert-success">Письмо с ссылкой активации аккаунта <b><font color=#0000ff>'.$lg.'</font></b> успешно отправлено (проверьте также в папке Спам).</div>'; else
			if ($error==1) echo '<div class="alert alert-error">'.$errtext.'</div>';
?>			
			<form class="form-horizontal" id="register" name="reg" method="POST">
			  <input type="hidden" name="goreg" value="1">
			  <input type="hidden" name="referal" value="<?=$referal?>">
			  <fieldset>			    
			    <div class="control-group">
			      <!-- Login -->
			      <label class="control-label" for="login">Логин</label>
			      <div class="controls">
			        <input type="text" id="login" name="login" placeholder="" class="input-xlarge" maxlength="<?=$login_max_len?>" value="<?=$postlogin?>" onKeyUp="document.getElementById('answ').innerHTML='';document.reg.login.value=document.reg.login.value.toLowerCase()"> <input type="button" id="check" value="Проверить" class="btn btn-small btn-primary" onClick="callServer()" style="cursor: pointer;"><br>
				<span id="answ" style="color:#090; font-weight:bold;"></span>
			        <p class="help-block">Логин должен быть от <?=$login_min_len?> до <?=$login_max_len?> символов и содержать только маленькие буквы латинского алфавита и/или цифры (a-z, 0-9)</p>
			      </div>
			    </div>
			 
			    <div class="control-group">
			      <!-- Password-->
			      <label class="control-label" for="pass">Пароль</label>
			      <div class="controls">
			        <input type="password" value="<?=$postpass?>" maxlength="<?=$passw_max_len?>" name="pass" id="pass" class="input-xlarge">
			        <p class="help-block">Пароль должен быть от <?=$passw_min_len?> до <?=$passw_max_len?> символов</p>
			      </div>
			    </div>
			 
			    <div class="control-group">
			      <!-- Password -->
			      <label class="control-label"  for="pass_chek">Повтор пароля</label>
			      <div class="controls">
			        <input type="password" value="<?=$postpass_chek?>" name="pass_chek" id="pass_chek" maxlength="<?=$passw_max_len?>" class="input-xlarge">
			        <p class="help-block">Подтвердите пароль</p>
			      </div>
			    </div>

			    <div class="control-group">
			      <!-- Question -->
			      <label class="control-label" for="question">Секретный вопрос</label>
			      <div class="controls">
			        <select name="question" id="qst" data-rel="chosen" style="width:280px"><?php
					foreach ($quest_template as $i => $val){
						if ($postquestion==$val) $ss = ' selected'; else $ss='';
						printf('<option value="%s"%s>%s</option>', $val, $ss, $val);
					}
				?>
				</select>
			      </div>
			    </div>

			    <div class="control-group">
			      <!-- Answer -->
			      <label class="control-label" for="answer">Ответ на вопрос</label>
			      <div class="controls">
			        <input type="text" value="<?=$postanswer?>" name="answer" id="ans" maxlength="25" class="input-xlarge">
			        <p class="help-block">Ответ должен быть не менее 3-х и не более 25-ти символов</p>
			      </div>
			    </div>

			    <div class="control-group">
			      <!-- Real name -->
			      <label class="control-label" for="name">Ваше имя</label>
			      <div class="controls">
			        <input type="text" value="<?=$postname?>" name="name" id="name" maxlength="20" class="input-xlarge">
			        <p class="help-block">Ваше настоящее имя</p>
			      </div>
			    </div>

			    <div class="control-group">
			      <!-- E-mail -->
			      <label class="control-label" for="email">E-mail</label>
			      <div class="controls">
			        <input type="text" value="<?=$postemail?>"  onKeyUp="document.reg.email.value=document.reg.email.value.toLowerCase()" name="email" id="e-mail" maxlength="35" class="input-xlarge">
			        <p class="help-block">Укажите реальный E-mail. Он необходим для окончания регистрации и последующей смены пароля.</p>
			      </div>
			    </div>
			 
			    <div class="control-group">
			      <!-- Captcha -->
			      <label class="control-label" for="kapcha">Код безопасности</label>
			      <div class="controls">
			        <?=$captcha->ShowCaptcha()?>
			      </div>
			    </div>
			 
			    <div class="control-group">
			      <!-- Rules -->
			      <div class="controls">
			        <input type="checkbox" name="ok">
			        <a target="_blank" href="<?=$rules_link?>">Правила и условия пользования</a> прочитал(а) и принимаю</label></p>
			      </div>
			    </div>
			 
			    <div class="control-group">
			      <!-- Button -->
			      <div class="controls">
			        <button class="btn btn-success" onclick="return subm()">Зарегистрировать</button>
			      </div>
			    </div>
			  </fieldset>
			</form>   <?php } else
			echo '<br><br><div class="alert alert-error">Регистрация временно недоступна, попробуйте повторить попытку позже.</div>';
?>
		</div>
	</div>
</div>
</div>
<hr>

		<footer>
			<p class="pull-left" style="margin-top:5px">by <a href="http://alexdnepro.net" target="_blank">alexdnepro</a> <?php echo @date('Y') ?></p><center>
			<!-- begin WebMoney Transfer : accept label -->
<a href="http://www.megastock.ru/" target="_blank"><img src="img/acc_blue_on_white_ru.png" alt="www.megastock.ru" border="0"></a>
<!-- end WebMoney Transfer : accept label --></center>
			<p class="pull-right"><?=$footer_right?></p>
		</footer>

	</div><!--/.fluid-container-->	
</body>
</html>
