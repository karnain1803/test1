<?php
require_once('config.php');
require_once('templates.php');
require_once('captcha.php');
$captcha = new captcha();
$ch_link = GetLink().'remember.php?ch=';
$errtxt = '<font color="#900">Ошибка запроса, попробуйте позже</font>';
$tmplogin = '';
if ($kaptcha_type == 'ReCaptcha v2') $captcha_name = 'g-recaptcha-response'; else $captcha_name = 'kapcha';
function HideEmail($email){
	$c = strcspn($email, "@", 0, 5);
	return substr_replace($email, str_repeat("*", $c), 0, $c);
}

function generate($length = 6){
  $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
  $numChars = strlen($chars);
  $string = '';
  for ($i = 0; $i < $length; $i++) {
    $string .= substr($chars, rand(1, $numChars) - 1, 1);
  }
  return $string;
}

function ValidateChKey($key){
	global $tmplogin;
	if ($key == '' || strlen($key)<8) return 0;
	$d = @encode(decrypt($key));	
	if ($d == '' || strlen($d)<8) return 0;
	$str = explode('|', $d);
	if (count($str)!=5) return 0;
	if (intval($str[0]) != $str[0]) return 0;
	$tmplogin = $str[1];
	//print_r($str);
	return $str;
}
function SendRequest($poststr, $second = false){
	global $err, $logotext;
	global $chtxt, $result1;
	//print_r($poststr);
	$result = CurlPage($poststr, 10);
	$result1 = @unserialize($result);
	if (is_array($result1)) $result = $result1['errtxt'];
	$wrongsend = '<font color="#900">Ошибка отправки почты на указанный E-Mail</font><br>';
	//echo $result;
	if (!$second) {
		if ($result=='wrongacc') $err = '<font color="#900">Укажите существующий аккаунт</font><br>'; else
		if ($result=='wrongemail') $err = '<font color="#900">Укажите существующий E-Mail</font><br>'; else
		if ($result=='bademail') $err = '<font color="#900">При регистрации аккаунта, был указан некорректный E-Mail, восстановление невозможно</font><br>'; else
		if ($result=='wrongaccemail') $err = '<font color="#900">По указанному E-Mail не найдено привязанных аккаунтов</font><br>'; else
		if ($result=='time') $err = '<font color="#900">Ваш айпи забанен на 10 минут, за превышение лимита запросов</font><br>'; else
		if ($result=='ok') {
			$subj = "Восстановление пароля от аккаунта на сервере ".$logotext;
			$t = new templates();
			$text = $t->RememberMail(GetIP(), $logotext, $result1['accounts']);
			if (@send_mail($result1['to'], $subj, $text)) $err = ''; else $err = $wrongsend;
		} else $err='<font color="#900">Произошла ошибка при запросе к серверу, попробуйте позже</font><br>';
	} else {
		if ($result=='wrongkey') $chtxt = '<font color="#900">Введен неверный ключ смены пароля</font><br>'; else
		if ($result=='time') $chtxt = '<font color="#900">Ваш айпи забанен на 10 минут, за превышение лимита запросов</font><br>'; else
		if ($result=='ok') {
			$subj = "Сброс пароля от аккаунта на сервере ".$logotext;
			$t = new templates();
			$text = $t->ResetPasswMail(GetIP(), $result1['login'], $poststr['newpasswdecoded'], $result1['question'], $result1['answer'], $logotext);
			@send_mail($result1['to'], $subj, $text);
			$chtxt = '<font color="#007f00">Пароль успешно изменён и отправлен Вам на почту <font color="#0000ff">'.HideEmail($result1['to']).'</font></font><br>';
		} else
		if ($result=='ipcheckdisabled') {
			$subj = "Отключение ограничения входа по IP в ЛК на аккаунт сервера ".$logotext;
			$t = new templates();
			$text = $t->ResetIPMail(GetIP(), $result1['login'], $logotext);
			@send_mail($result1['to'], $subj, $text);
			$chtxt = '<font color="#007f00">Ограничение на вход с определенных IP успешно отключено</font><br>';
		} else
		$chtxt = '<font color="#900">Произошла ошибка при запросе к серверу, попробуйте позже</font><br>';
	}
}

$chpass = false;
$rmacc = false;

if (isset($_GET['ch'])){				// Смена пароля
	$chpass = true;
	$ch = $_GET['ch'];
	$str = ValidateChKey($ch);
	$srvid = intval($str[0]);
	if (!$srvid || $srvid != $servid) $chtxt = '<font color="#900">Введен неверный ключ смены пароля</font><br>'; else
	if ($str[4] == 0)
	{
		// Запрос на обнуление пароля
		$newpasswdecoded = generate($passw_min_len);
		$newpassw = GetPasswHash($tmplogin, $newpasswdecoded);
		$poststr = array(
			'op' => 'nullpass',
			'key' => $ch,
			'newpassw' => $newpassw,
			'newpasswdecoded' => $newpasswdecoded,
			'ip' => getIP(),
			'servid' => $servid,
			'cookie_pasw' => $cookie_pasw,
			'encoder_Salt' => $encoder_Salt
		);		
		SendRequest($poststr, true);
	} else 
	if ($str[4] == 1)
	{
		// Запрос на снятие ограничения по IP
		$poststr = array(
			'op' => 'disableipcheck',
			'key' => $ch,
			'ip' => getIP(),
			'servid' => $servid,
			'cookie_pasw' => $cookie_pasw,
			'encoder_Salt' => $encoder_Salt
		);		
		SendRequest($poststr, true);
	}
}

if (isset($_POST['ch'])){			// Отправка письма с ссылкой смены пароля
	if ($_POST['ch']==1){
		$err='';		
		if (preg_match($login_filter, $_POST['login'])) {
			$err='logerr1';
		}
		if ( strlen($_POST['login']) < $login_min_len || strlen($_POST['login']) > $login_max_len ) {
			$err='logerr2';
		}
		if (!$err)
		{
			if (!$captcha->Validate()) $err = 'неправильно введен код безопасности';
		}
		$poststr = array(
			'op' => 'forgetpass',
			'login' => $_POST['login'],
			'ip' => getIP(),
			'servid' => $servid,
			'link' => $ch_link,
			'cookie_pasw' => $cookie_pasw,
			'encoder_Salt' => $encoder_Salt
		);
		if ($err=='') SendRequest($poststr);
		if ($err=='') $err = 'На ваш почтовый ящик <font color="#0000ff">'.HideEmail($result1['to']).'</font> отправлены дальнейшие инструкции по восстановлению доступа.<br>';
		echo $err;
		die();
	}
} else
if (isset($_POST['rm'])){			// Отправка письма с аккаунтами по выбранному E-Mail
	if ($_POST['rm']==1){
		$err='';
		if (!checker($_POST['email'])) {
			$err='emailerr';
		}
		if (!$err)
		{
			if (!$captcha->Validate()) $err = 'неправильно введен код безопасности';
		}
		$poststr = array(
			'op' => 'forgetlogin',
			'email' => $_POST['email'],
			'ip' => getIP(),
			'servid' => $servid,
			'link' => $ch_link,
			'cookie_pasw' => $cookie_pasw,
			'encoder_Salt' => $encoder_Salt
		);		
		if ($err=='') SendRequest($poststr);
		if ($err=='') $err = 'Данные о найденных аккаунтах успешно отправлены на Ваш почтовый ящик.<br>';
		echo $err;
		die();
	}
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
</head>

<body>
<div class="container-fluid">
<div class="row-fluid">
	<div class="well box">
		<div class="box-header well" data-original-title>
			<h2><i class="icon icon-color icon-locked"></i> Восстановление данных аккаунта</h2> <div style="float:right"><a href="index.php" class="btn btn-inverse">Перейти к авторизации</a></div>						
		</div>
		<div class="box-content">
			<div class="row-fluid">
			<div class="well box span8">
			<?php
			if ($chpass || $rmacc) {
				echo '<div style="height:400px" align="center"><font size="3">';
				if ($chpass) echo $chtxt;
				if ($rmacc) echo $rmtxt;
				echo '</font></div></div></div></div></div></body></html>';
				die();
			}
			?><h2>Восстановление доступа по логину аккаунта</h2>
			<p>В этом разделе Вы можете отправить на почту ссылку для сброса пароля от аккаунта и для снятия ограничения входа по IP.<br>
Обратите внимание, что без доступа к почте восстановить аккаунт почти невозможно. <br>Если так вдруг случилось, что Вы действительно её забыли, либо изначально ввели неверную - пишите в Skype <?=$contacts?></p>
			<form class="form-horizontal" method="post" id="ch_pass" name="ch_pass">			   
			  <fieldset>			    
			    <div class="control-group">
			      <!-- Login -->
			      <label class="control-label" for="login">Логин</label>
			      <div class="controls">
			        <input type="text" value="" name="login" id="login" class="input-xlarge" onKeyUp="document.getElementById('answ').innerHTML='';document.ch_pass.login.value=document.ch_pass.login.value.toLowerCase();document.getElementById('logerr1').style.display='none'; document.getElementById('logerr2').style.display='none';" maxlength="20"> <input class="btn btn-small btn-primary" class="check_forg" type="button" name="nullpass" id="null_pass" onClick="callServer()" value="Восстановить доступ"/><br>
				<span id="answ" style="color:#090; font-weight:bold;"></span>
			        <span style="display:none; color:#900; font-weight:bold;" id="logerr1"><br>в логине использованы недопустимые символы<br></span>
				<span style="display:none; color:#900; font-weight:bold;" id="logerr2"><br>логин должен быть не менее 4 и не более 20 символов<br></span>
			      </div>
			    </div>			 
			  </fieldset>
			</form>   

			<h2>Восстановление доступа по E-mail аккаунта</h2>
			<p>В данном разделе Вы можете отправить на почту напоминание с данными от аккаунта, который был зарегистрирован на указанный E-mail. <br>Обратите внимание, что без доступа к почте восстановить аккаунт почти невозможно. <br>Если так вдруг случилось, что Вы действительно её забыли, либо изначально ввели неверную - пишите в skype <?=$contacts?></p>
			<form class="form-horizontal" method="post" id="rm_pass" name="rm_pass">			   
			  <fieldset>			    
			    <div class="control-group">
			      <!-- Login -->
			      <label class="control-label" for="email">E-Mail</label>
			      <div class="controls">
			        <input value="" name="email" id="email" class="input-xlarge" onKeyUp="document.getElementById('answ1').innerHTML='';document.getElementById('emailerr').style.display='none';" maxlength="35"> <input class="btn btn-small btn-primary" class="check_forg" type="button" name="rememberpass" id="remember_pass" onClick="callServer1()" value="Восстановить доступ"/><br>
				<span id="answ1" style="color:#090; font-weight:bold;"></span>
			        <span style="display:none; color:#900; font-weight:bold;" id="emailerr">введите корректный E-Mail</span>
			      </div>
			    </div>			 
			  </fieldset>
			</form>  
			</div>
			<div class="well box span4"><?php echo $captcha->ShowCaptcha(); ?></div>
</div> 
		</div>
	</div>
</div>
</div>
<script type="text/javascript">
function callServer(){
	F=document.ch_pass;
	if (F.login.value.length < <?=$login_min_len?> || F.login.value.length > <?=$login_max_len?>) { 
		alert ("Логин должен быть не менее <?=$login_min_len?> и не более <?=$login_max_len?> символов");
		return;
	}
	Ca=document.getElementById('<?=$captcha_name?>');
	A=document.getElementById('answ');
	F.nullpass.disabled=true;
	F.nullpass.value = 'Отправка...';
	A.innerHTML='';
	$.ajax({
	  type: 'POST',
	  async: false,
	  url: 'remember.php',
	  cache: false,
	  data: '<?=$captcha_name?>='+Ca.value+'&ch=1&login='+F.login.value+'&r='+Math.random(),		  
	  success: function(html){
	    if (html=='logerr1') document.getElementById('logerr1').style.display = 'block'; else
	    if (html=='logerr2') document.getElementById('logerr2').style.display = 'block'; else
	    {
	    	F.login.value = '';
	    	A.innerHTML=html;
	    }
	  }
	});
	F.nullpass.disabled=false;
	F.nullpass.value='Восстановить доступ';
}
function callServer1(){
	F=document.rm_pass;
	if (F.email.value.length < 5 || F.email.value.length > 35) { 
		alert ("Введите корректный e-mail");
		return;
	}
	Ca=document.getElementById('<?=$captcha_name?>');
	A=document.getElementById('answ1');
	F.rememberpass.disabled=true;
	F.rememberpass.value = 'Отправка...';
	A.innerHTML='';
	$.ajax({
	  type: 'POST',
	  async: false,
	  url: 'remember.php',
	  cache: false,
	  data: '<?=$captcha_name?>='+Ca.value+'&rm=1&email='+F.email.value+'&r='+Math.random(),		  
	  success: function(html){
	    if (html=='emailerr') document.getElementById('emailerr').style.display = 'block'; else
	    {
	    	F.email.value = '';
	    	A.innerHTML=html;
	    }
	  }
	});
	F.rememberpass.disabled=false;
	F.rememberpass.value='Восстановить доступ';
}
function iload()
{
img = new Image();
img.src = "captcha/?tt="+Math.random();
document.getElementById("image").src = img.src;
}
</script>
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
