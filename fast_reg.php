<?php
require_once('config.php');
require_once('templates.php');
require_once('captcha.php');
$captcha = new captcha();
if ($email_confirm) $act_txt = '<br><b><font color="#ff0000">Обратите внимание!</font> Перед использованием, аккаунт нужно активировать, пройдя по ссылке из письма, отправленного Вам на почту</b>'; else $act_txt = '';
if (!$_SESSION['do_vklogin'] && !$_SESSION['do_steamlogin']) {
	header('Location: register.php');
	die();
}
if ($_SESSION['do_vklogin']) {
	$photo = $_SESSION['vk_photo'];
	$realname = $_SESSION['vk_name'];
} else
if ($_SESSION['do_steamlogin']) {
	$photo = $_SESSION['steam_photo'];
	$realname = $_SESSION['steam_name'];
}
$error=0;
$errtext='';
$goreg = (isset($_POST['goreg']))?$_POST['goreg']:0;
$reflink = GetLink().'register.php?ref=';
function getdata($s1,$s2) {
	return ('
      <tr>
         <td width="150px" align="right" valign="top"><font color="#aa0000">'.$s1.':</font></td>
         <td align="left" valign="top">'.$s2.'</td>
      </tr>');	
}
function GenPassw(){
	global $passw_min_len;
	$chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP"; 
	$max = $passw_min_len;
	$size = StrLen($chars) - 1; 
	$password = ''; 
	while ($max--) 
		$password.=$chars[rand(0,$size)];
	return $password;
}
function GenVKLogin($inc){
	$login = 'vk'.$_SESSION['vk_id'];
	if ($inc) $login .= $inc;
	return $login;
}
function GenSteamLogin($inc){
	$login = 'steam'.substr($_SESSION['steam_id'], 8);
	if ($inc) $login .= $inc;
	return $login;
}
if (isset($_COOKIE[$cookie_name.'_reg_ref'])) $referal = intval($_COOKIE[$cookie_name.'_reg_ref']); else
$referal=0;
$postanswer = (isset($_POST['answer']))?$_POST['answer']:'';
$postquestion = (isset($_POST['question']))?$_POST['question']:'';
$postemail = (isset($_POST['email']))?$_POST['email']:'';
$postreferal = (isset($_POST['referal']))?$_POST['referal']:'';
if ($goreg==1 && $register_active) {
	//Проверки
	if (!$captcha->Validate())
	{
		$error=1;
		$errtext.=' - неправильно введен код безопасности<br>';
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
	if (!checker($postemail)) {
		$error=1;
		$errtext.=' - введите корректный E-Mail<br>';
	}
	$referal = intval($postreferal);
	//Отправка запроса на регистрацию
	if ($error==0){
		if ($referal!=0) $referal = ($referal+1)*16;
		if ($register_gold > 0 && $get_gold_btn) $register_gold = 0;
		$inc = 0;
		repeat_reg:
		if ($_SESSION['do_vklogin']) $login = GenVKLogin($inc);	else $login = GenSteamLogin($inc);
		$password = GenPassw();
		$postdata = array( 'op' => 'reg',
			   'ip' => getIP(),
			   'login' => $login,
	                   'pass' => base64_encode(GetPasswHash($login, $password)),
			   'ip_max_reg' => $ip_max_reg,
			   'email_max_reg' => $email_max_reg,
			   'email' => $postemail,
			   'email_confirm' => $email_confirm,
			   'question' => $postquestion,
			   'realname' => $realname,
	                   'answer' => $postanswer,
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
		if ($result === false) {
			$error=1;
			$errtext='Ошибка инициализации соединения с сервером';
		} else
		if ($result=='needauth') {
			header('Location: index.php');
			die();
		} else
		if ($result=='exists') {
			$inc++;
			goto repeat_reg;
		} else
		if ($result=='time') {
			$error=1;
			$errtext = sprintf($auth_errors[8], $_SERVER['REMOTE_ADDR']);
		} else
		if ($result=='10') {
			$error=1;
			$errtext='Ошибка входящих данных';
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
		if (substr($result, 0, 2)=='ok') {
			$realid = substr($result, 2);
			$idacc = intval(($realid/16)-1);
			$reflink = $reflink.$idacc;
			$error=0;
			$to = $postemail;
			$subj = "Регистрация аккаунта на сервере ".$logotext;
			if ($email_confirm) {
				$confirm_link = GetLink().'register.php?activate='.mycrypt(encode(sprintf('%d|%s|%d', $servid, $login, $realid)));
			} else $confirm_link = '';
			$t = new templates();
			$text = $t->RegisterMail($login, $password, $postquestion, $postanswer, $realname, $confirm_link, $reflink);
			@send_mail($to, $subj, $text);			
			$lg = $login;
			$postquestion=''; $postanswer=''; $postname=''; $postemail='';	
					
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
		<div class="box span12">
			<div class="box-header well" data-original-title>
				<h2><i class="icon icon-color icon-user"></i> Добро пожаловать на сервер <?=$logotext?></h2> <div style="float:right"><a href="logout.php" class="btn btn-inverse">Перейти к авторизации</a></div>						
			</div>
			<div class="box-content">
				<h2><img src="<?=$photo?>"> К данной учетной записи не привязано ни одного игрового аккаунта :(</h2>
				<div class="row-fluid">
					<div class="box span12">
						<div class="box-header well" data-original-title>
							<h2><i class="icon icon-color icon-check"></i> Если у Вас уже есть игровой аккаунт</h2>
						</div>
						<div class="box-content">
							<p>Авторизуйтесь, используя логин и пароль и сделайте привязку данной учетной записи из личного кабинета</p>
							<a href="logout.php" class="btn btn-inverse">Перейти к авторизации</a>
						</div>
					</div>
				</div><?php			
				if ($register_active) { ?>
				<div class="row-fluid">
					<div class="box span12">
						<div class="box-header well" data-original-title>
							<h2><i class="icon icon-color icon-page"></i> Если у Вас еще нет игрового аккаунта</h2>
						</div>
						<div class="box-content">
							<p>Выберите один из вариантов регистрации:</p><div class="row-fluid">
							<ul class="nav nav-tabs" id="myTab">							
								<li><a href="#fast_reg"><i class="icon icon-color icon-note"></i> Упрощенная регистрация</a></li>
								<li><a href="#normal_reg"><i class="icon icon-color icon-clipboard"></i> Обычная регистрация</a></li>
							</ul>
							<div id="myTabContent" class="tab-content">
								<div class="tab-pane active" id="fast_reg">
									<p>Вам будут автоматически сгенерированы логин и пароль аккаунта, которые после завершения регистрации будут отправлены на почту</p>
					<?php
				if (($goreg==1)&&($error==0)) echo '<div class="alert alert-success">Поздравляем, аккаунт <b><font color=#0000ff>'.$lg.'</font></b> успешно зарегистрирован!<br> Данные от аккаунта отправлены Вам на почту.'.$act_txt.'</div><div style="float:right"><a href="index.php" class="btn btn-inverse">Перейти в личный кабинет</a></div>'; else
				if ($error==1) echo '<div class="alert alert-error">'.$errtext.'</div>';
	?>			
				<form class="form-horizontal" id="register" name="reg" method="POST">
				  <input type="hidden" name="goreg" value="1">
				  <input type="hidden" name="referal" value="<?=$referal?>">
				  <fieldset> 
	
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
				        <button class="btn btn-primary" onclick="return fastsubm()">Упрощенная регистрация</button>
				      </div>
				    </div>
				  </fieldset>
				</form>  
								</div>
	
								<div class="tab-pane" id="normal_reg">
									<p>Зарегистрируйте игровой аккаунт обычным способом, вручную указав данные аккаунта</p>
										<a href="register.php" class="btn btn-success">Перейти к обычной регистрации</a>
								</div>
							</div></div>			
						</div>
						<?php } else
				echo '<div class="alert alert-error">Регистрация временно недоступна, попробуйте повторить попытку позже.</div>';
	?>
					</div>
				</div>				
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