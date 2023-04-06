<?php
if (!defined('main_def')) die();
function go($nam,$a){
	if (CheckNum($a)) {
		$b = UnpackAnswer($a);
		if (!is_array($b)) die($a);
		if (isset($b['errparam1']) && !CheckNum($b['errparam1'])) $a = $b['errparam1'];
	}
	header('Location: index.php?op='.$nam.'&r='.$a.'&t='.time());	
	die();
}
function diemsg($a){
	echo GetErrorTxt($a);
	die();
}
function get_image_info($file = NULL) {
	if(!is_file($file)) return false;  
	if(!$data = getimagesize($file) or !$filesize = filesize($file)) return false;  
	$extensions = array(1 => 'gif',    2 => 'jpg',
                         3 => 'png',    4 => 'swf',
                         5 => 'psd',    6 => 'bmp',
                         7 => 'tiff',    8 => 'tiff',
                         9 => 'jpc',    10 => 'jp2',
                         11 => 'jpx',    12 => 'jb2',
                         13 => 'swc',    14 => 'iff',
                         15 => 'wbmp',    16 => 'xbmp');  
	$result = array('width'        =>    $data[0],
                     'height'    =>    $data[1],
                     'extension'    =>    $extensions[$data[2]],
                     'size'        =>    $filesize,
                     'mime'        =>    $data['mime']);  
	return $result;
}
function getdata($s1,$s2) {
	return ('
      <tr>
         <td width="150px" align="right" valign="top"><font color="#aa0000">'.$s1.':</font></td>
         <td align="left" valign="top">'.$s2.'</td>
      </tr>');	
}
$ak = @AssignPData(act_key);
if (!$ak) die();
if (!isset($_GET['n']) || !isset($_GET['num']) || CheckNum($_GET['n']) || CheckNum($_GET['num'])) go('main',10); else {
	if ($_GET['n']==17) {
		if (!$_SESSION['isadmin'] && $_SESSION['rules_cnt']==0) die('Access denied');
	}
	if ($_GET['n']==800){ // Выбор перса для отправки наград за голосование в топах
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
                   'id' => $_SESSION['id'],
	           'n' => $_GET['n'],
		   'num' => $_GET['num']
		);
		$result = CurlPage($postdata, 15, 1, true);
		go('main',$result);
	} else
	if ($_GET['n']==5){ // Обмен на голд монеты		
		if (!$allow_lk_gold_exchange || !isset($_GET['i']) || CheckNum($_GET['i'])) go('money',10);
		if (!isset($_GET['n']) || CheckNum($_GET['n'])) go('money',10);
		if (!isset($_GET['num']) || CheckNum($_GET['num'])) go('money',10);
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
	           'id' => $_SESSION['id'],
		   'n' => $_GET['n'],
		   'num' => $_GET['num'],
		   'allow_lk_gold_exchange' => $allow_lk_gold_exchange,
		   'gold_itemid' => $gold_itemid,
		   'gold_item_exchange_rate' => $gold_item_exchange_rate,
		   'i' => intval($_GET['i'])
		);
		$result = CurlPage($postdata, 15, 1, true);
		go('money',$result);
	} else
	if ($_GET['n']==6){ // Обмен на серебряные монеты		
		if (!$allow_lk_silver_exchange || !isset($_GET['i']) || CheckNum($_GET['i'])) go('money',10);
		if (!isset($_GET['n']) || CheckNum($_GET['n'])) go('money',10);
		if (!isset($_GET['num']) || CheckNum($_GET['num'])) go('money',10);
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
	           'id' => $_SESSION['id'],
		   'n' => $_GET['n'],
		   'num' => $_GET['num'],		   
		   'allow_lk_silver_exchange' => $allow_lk_silver_exchange,
		   'silver_itemid' => $silver_itemid,
		   'silver_item_exchange_rate' => $silver_item_exchange_rate,
		   'i' => intval($_GET['i'])
		);
		$result = CurlPage($postdata, 15, 1, true);
		go('money',$result);
	} else
	if ($_GET['n']==7){ // Значок клану
		$pic=$uploaddir."/".$_SESSION['login'].".png";
		if (@!fopen($pic, "r")) go('klan',6);
		$upload = $pic;
		$postdata = array( 'op' => 'act',
			   'ip' => getIP(),
	                   'id' => $_SESSION['id'],
	                   'n' => $_GET['n'],
			   'num' => $_GET['num'],			   		   
			   'servid' => $servid,
			   'klancost' => $klancost);
		if (PHP_VERSION_ID >= 50500) {
			$postdata['upload'] = new \CURLFile($upload);
		} else {
			$postdata['upload'] = "@".$upload;
		}
		$result = CurlPage($postdata, 15, 1, true);		
		go('klan',$result);
	} else
	if ($_GET['n']==8){ // шоп
		if (!isset($_GET['sitem']) || !isset($_GET['t']) || CheckNum($_GET['sitem']) || !isset($_GET['count']) || CheckNum($_GET['count']) || $_GET['count'] < 1) go('shop&num='.((isset($_GET['num']))?$_GET['num']:0).'&page='.((isset($_GET['page']))?$_GET['page']:1).'&subcat='.((isset($_GET['subcat']))?$_GET['subcat']:0),10);	
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
                   'id' => $_SESSION['id'],
                   'n' => $_GET['n'],
		   'num' => $_GET['num'],
                   'sitem' => $_GET['sitem'],
		   't' => $_GET['t'],
		   'count' => $_GET['count']
		);	
		$result = CurlPage($postdata, 15, 1, true);
		go('shop&num='.$_GET['num'].'&page='.$_GET['page'].'&subcat='.$_GET['subcat'],$result);		
	} else
	if ($_GET['n']==10){ // Перевод поинтов на акк
		// Проверки
		require_once('captcha.php');
		$captcha = new captcha();
		if (!$allow_lk_transfer || !isset($_POST['id']) || CheckNum($_POST['id']) || !isset($_POST['gold']) || CheckNum($_POST['gold'])) go('money',10);
		if (!$captcha->Validate()) go('money',86);
		if ($_POST['gold']<1) go('money',10);
		$comment = $_POST['comment'];
		if (mb_strlen($comment, 'utf8')>20) go('money',22);
		if (strlen($comment)>0) 
			if (!preg_match($check_rus, $comment)) go('money',23);
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
	           'id' => $_SESSION['id'],
		   'n' => $_GET['n'],
		   'num' => $_GET['num'],
	           'accid' => $_POST['id'],
		   'gold' => $_POST['gold'],
		   'lk_transfer_min_role_lvl' => $lk_transfer_min_role_lvl,
		   'lk_transfer_vk_only' => $lk_transfer_vk_only,
		   'lk_transfer_steam_only' => $lk_transfer_steam_only,
	           'comment' => $comment);
		$result = CurlPage($postdata, 15, 1, true);
		go('money',$result);
	} else 
	if ($_GET['n']==11){ // Отправка голда в игру
		if (!$allow_lk2game || !isset($_POST['goldcount']) || CheckNum($_POST['goldcount']) || $_POST['goldcount'] < 1 || floor($_POST['goldcount'] * $lk2game_exchange_rate) < 1) go('money',10); 
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
                   'id' => $_SESSION['id'],
		   'n' => $_GET['n'],
		   'zoneid' => $zoneid,
		   'aid' => $aid,
		   'num' => $_GET['num'],
		   'lk2game_exchange_rate' => $lk2game_exchange_rate,
                   'goldcount' => $_POST['goldcount']);	
		$result = CurlPage($postdata, 15, 1, true);
		go('money',$result);
	} else
	if ($_GET['n']==12){ // Клан арты
		if (!isset($_GET['artid']) || CheckNum($_GET['artid'])) go('klan',10);
		$name = (isset($_GET['name']))?$_GET['name']:'';
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
                   'id' => $_SESSION['id'],
	           'n' => $_GET['n'],
		   'num' => $_GET['num'],			   
		   'servid' => $servid,
		   'name' => $name,
	           'artid' => $_GET['artid'] );
		$result = CurlPage($postdata, 15, 1, true);
		go('klan',$result);
	} else
	if ($_GET['n']==19){ // Редактирование шоп итема
		if (!$_SESSION['isadmin']) go('shop&num='.$_GET['num'].'&page='.$_GET['page'].'&subcat='.$_GET['subcat'],33);
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
	           'id' => $_SESSION['id'],
		   'n' => $_GET['n'],
		   'num' => $_GET['num']
		);
		$postdata = array_merge($_POST, $postdata);
		$result = CurlPage($postdata, 15, 1, true);
		go('shop&num='.$_GET['num'].'&page='.$_GET['page'].'&subcat='.$_GET['subcat'],$result);		
	} else 
	if ($_GET['n']==20){ // Смена пароля от аккаунта
		if ($email_require_change) {
			if (!checker($_POST['email'])) go('account',36); // Ошибка мыла
			$_SESSION['email'] = htmlspecialchars($_POST['email']);
			if (mb_strtolower($_POST['email'],'utf8')!=mb_strtolower($_SESSION['acc_email'],'utf8')) go('account',39);  // Неверный email
		}
		if (CheckPassw($_POST['curpassw']) || CheckPassw($_POST['newpassw'])) go('account',34); 	// В пароле использованы недопустимые символы
		if ( strlen($_POST['curpassw']) < $passw_min_len || strlen($_POST['curpassw']) > $passw_max_len || strlen($_POST['newpassw']) < $passw_min_len || strlen($_POST['newpassw']) > $passw_max_len ) go('account',35); 	// Пароль должен быть от 6 до 20 символов
		if ($_POST['newpassw']!=$_POST['newpassw1']) go('account',40);  // Пароли не совпадают
		if ($answer_require_change) {
			if ((mb_strlen($_POST['answer'], 'utf8') < 3)||(mb_strlen($_POST['answer'], 'utf8') > 25)) go('account',37);  // Ответ должен быть 3-25 символов
			if (!preg_match($check_rus, $_POST['answer'])) go('account',38);  // В ответе использованы недопустимые символы
			$_SESSION['answer_'] = htmlspecialchars($_POST['answer']);
			if (mb_strtolower($_SESSION['answer'],'utf8')!=mb_strtolower($_POST['answer'],'utf8')) go('account',42);	
		}
		if ($_SESSION['passw'] != base64_encode(GetPasswHash($_SESSION['login'], $_POST['curpassw']))) go('account',41);	
		$md = GetPasswHash($_SESSION['login'], $_POST['newpassw']);	
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
	           'id' => $_SESSION['id'],
		   'login' => $_SESSION['login'],
		   'n' => $_GET['n'],
		   'num' => $_GET['num']
		);
		$postdata = array_merge($_POST, $postdata);
		$postdata['newpassw'] = $md;
		$result = CurlPage($postdata, 15, 1, true);
		$res1 = @unserialize($result);
		if (is_array($res1)) {
			go('account',$res1['errparam1']);
		}		
		if ($result==43) {
			if ($email_require_change) $_SESSION['email'] = '';
			if ($answer_require_change) $_SESSION['answer_'] = '';
			$_SESSION['passw'] = base64_encode($md);
			$to = $_SESSION['acc_email'];
			require_once('templates.php');
			$subj = "Смена пароля. ".$description;
			$t = new templates();
			$text = $t->ChangePasswMail(GetIP(), $_SESSION['login'], $_POST['newpassw'], $logotext);
			@send_mail($to, $subj, $text);
		}		
		go('account',$result);		
	} else 
	if ($_GET['n']==50){ // Добавление записи вывода в статистику электронных платежей
		if (!$_SESSION['isadmin']) go('main',33);
		if (!isset($_GET['summ']) || !is_numeric($_GET['summ']) || !isset($_GET['desc'])) go('stat',10);
		switch ($donate_system) {
			case 'WayToPay':
				$dbtbl = '`donate`';
				$profit_field = 'out_summ';
				break;
			case 'UnitPay':
				$dbtbl = '`donate_unitpay`';
				$profit_field = 'profit';
				break;
			case 'Free-Kassa':
				$dbtbl = '`donate_freekassa`';
				$profit_field = 'out_summ';
				break;
			default:
				go('stat',10);
				break;
		}
		$sql = sprintf("INSERT INTO ".$dbtbl." (`data`,`".$profit_field."`,`money`,`login`,`ip`,`serv`,`status`) VALUES (now(),'-%s',0,'%s','%s',%d,100)", $_GET['summ'], $db->real_escape_string($_GET['desc']), getIP(), $servid);		
		if (!$db->query($sql)) go('stat',32);
		go('stat',16);		
	} else 
	if ($_GET['n']==52){ // Выдача ЛК монет
		if (!$_SESSION['isadmin']) go('money',33);
		if (!isset($_GET['summ_gold']) || !is_numeric($_GET['summ_gold'])) go('money',10);
		if (!isset($_GET['summ_silver']) || !is_numeric($_GET['summ_silver'])) go('money',10);
		if (!isset($_GET['acc_login'])) go('money',10);
		if ($_GET['summ_gold'] < -10000000 || $_GET['summ_gold'] > 10000000 || $_GET['summ_silver'] < -10000000 || $_GET['summ_silver'] > 10000000) go('money',10);
		$login = $_GET['acc_login'];
		if (preg_match($login_filter, $login, $Txt)) go('money',10); // В логине использованы недопустимые символы
		if (strlen($login) < $login_min_len || strlen($login) > $login_max_len ) go('money',10); // Логин должен быть от 4 до 20 символов
		if (!isset($_GET['desc']) || mb_strlen($_GET['desc'],'utf8') > 20) go('money',10);
		$postdata = array( 'op' => 'addlk',
		   'ip' => getIP(),
	           'id' => $_SESSION['id'],
		   'gold' => $_GET['summ_gold'],
		   'silver' => $_GET['summ_silver'],
		   'login' => $login,
		   'desc' => $_GET['desc']
		);	
		$result = CurlPage($postdata, 15, 1, true);	
		go('money',$result);
	} else 
	if ($_GET['n']==53){ // Редактирование клан арт итема
		if (!$_SESSION['isadmin']) go('klan',33);
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
	           'id' => $_SESSION['id'],
		   'n' => $_GET['n'],
		   'num' => $_GET['num']
		);
		$postdata = array_merge($_POST, $postdata);
		$result = CurlPage($postdata, 15, 1, true);
		go('klan',$result);		
	} else 
	if ($_GET['n']==54){ // Добавление клан арт итема
		if (!$_SESSION['isadmin']) go('klan',33);
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
	           'id' => $_SESSION['id'],
		   'n' => $_GET['n'],
		   'num' => $_GET['num']
		);
		$postdata = array_merge($_POST, $postdata);
		$result = CurlPage($postdata, 15, 1, true);
		go('klan',$result);		
	} else 
	if ($_GET['n']==55){ // Удаляем клан арт итема
		if (!$_SESSION['isadmin']) go('klan',33);
		if (!isset($_GET['kitem']) || CheckNum($_GET['kitem'])) go('klan',10);
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
	           'id' => $_SESSION['id'],
		   'n' => $_GET['n'],
		   'kitem' => $_GET['kitem'],
		   'num' => $_GET['num']
		);
		$postdata = array_merge($_POST, $postdata);
		$result = CurlPage($postdata, 15, 1, true);
		go('klan',$result);		
	} else 
	if ($_GET['n']==56){ // Добавляем айпи в список разрешенных
		if (!isset($_GET['addip']) || strlen($_GET['addip'])>15) go('account',10);
		if (preg_match("/[^0-9\.]/", trim($_GET['addip']))) go('account',10);
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
	           'id' => $_SESSION['id'],
		   'n' => $_GET['n'],
		   'addip' => trim($_GET['addip']),
		   'num' => $_GET['num']
		);		
		$result = CurlPage($postdata, 15, 1, true);
		go('account',$result);		
	} else 
	if ($_GET['n']==57){ // Удаляем айпи из списка разрешенных
		if (!isset($_GET['i']) || CheckNum($_GET['i'])) go('account',10);
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
	           'id' => $_SESSION['id'],
		   'n' => $_GET['n'],
		   'i' => $_GET['i'],
		   'num' => $_GET['num']
		);		
		$result = CurlPage($postdata, 15, 1, true);
		go('account',$result);		
	} else
	if ($_GET['n']==58){ // Активируем/отключаем режим ограничения по айпи в ЛК
		if (!isset($_GET['mode'])) go('account',10);
		if ((bool)$_GET['mode'] && count($_SESSION['ipdata'][1]) == 0) go('account',10);
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
	           'id' => $_SESSION['id'],
		   'n' => $_GET['n'],
		   'num' => $_GET['num'],
		   'mode' => (bool)$_GET['mode']
		);		
		$result = CurlPage($postdata, 15, 1, true);
		go('account',$result);		
	} else
	if ($_GET['n']==59){ // Активируем/отключаем режим ограничения по айпи в игре
		if (!isset($_GET['mode'])) go('account',10);
		if ((bool)$_GET['mode'] && count($_SESSION['ipdata'][1]) == 0) go('account',10);
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
	           'id' => $_SESSION['id'],
		   'n' => $_GET['n'],
		   'num' => $_GET['num'],
		   'mode' => (bool)$_GET['mode']
		);		
		$result = CurlPage($postdata, 15, 1, true);
		go('account',$result);		
	} else
	if ($_GET['n']==68){ // Генератор промо-кодов		
		if (!$_SESSION['isadmin']) go('main',33);
		if (!isset($_POST['promo_count']) || !isset($_POST['promo_expire']) || !isset($_POST['promo_group']) || !isset($_POST['promo_gold']) || !isset($_POST['promo_silver']) || !isset($_POST['promo_item_id']) || !isset($_POST['promo_item_count']) || !isset($_POST['promo_item_maxcount']) || !isset($_POST['promo_item_data']) || !isset($_POST['promo_item_client_size']) || !isset($_POST['promo_item_proctype']) || !isset($_POST['promo_item_expire']) || !isset($_POST['promo_desc'])) die('Input data error');
		$time_limit = round($_POST['promo_count']/10);
		set_time_limit($time_limit);
		$postdata = array( 'op' => 'AdminGenPromo',
		   'ip' => getIP(),
	           'id' => $_SESSION['id']
		);
		$postdata = array_merge($_POST, $postdata);
		$result = CurlPage($postdata, $time_limit, 1, true);		
		echo $result;
		die();	
	} else 
	if ($_GET['n']==69){ // Редактирование промо кода
		if (!$_SESSION['isadmin']) go('main',33);
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
	           'id' => $_SESSION['id'],
		   'n' => $_GET['n'],
		   'num' => $_GET['num'],
		);
		$postdata = array_merge($_POST, $postdata);
		$result = CurlPage($postdata, 15, 1, true);
		$res1 = @unserialize($result);
		if (is_array($res1)) {
			echo $res1['errtxt'];	
		} else 
		echo $result;
		die();
	} else 
	if ($_GET['n']==70){ // Удаляем промо-код из базы
		if (!$_SESSION['isadmin']) go('main',33);
		if (!isset($_GET['record_id']) || CheckNum($_GET['record_id'])) diemsg(10);
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
	           'id' => $_SESSION['id'],
		   'n' => $_GET['n'],
		   'record_id' => $_GET['record_id'],
		   'num' => $_GET['num']
		);
		$postdata = array_merge($_POST, $postdata);
		$result = CurlPage($postdata, 15, 1, true);
		$res1 = @unserialize($result);
		if (is_array($res1)) {
			echo $res1['errtxt'];	
		} else 
		echo $result;
		die();	
	} else 
	if ($_GET['n']==71 || $_GET['n']==72){ // Удаляем просроченные, но не использованные промо-коды из базы и удаление использованных
		if (!$_SESSION['isadmin']) go('main',33);
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
	           'id' => $_SESSION['id'],
		   'n' => $_GET['n'],
		   'num' => $_GET['num']
		);
		$postdata = array_merge($_POST, $postdata);
		$result = CurlPage($postdata, 15, 1, true);
		$res1 = @unserialize($result);
		if (is_array($res1)) {
			echo $res1['errtxt'];	
		} else 
		echo $result;
		die();	
	} else 
	if ($_GET['n']==73){ // Используем промо-код
		if (!isset($_POST['promo_code']) || !$promo_enabled) go('main',10);
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
	           'id' => $_SESSION['id'],
		   'n' => $_GET['n'],
		   'num' => $_GET['num'],
		   'promo_code' => $_POST['promo_code']
		);
		$postdata = array_merge($_POST, $postdata);
		$result = CurlPage($postdata, 15, 1, true);
		go('main',$result);
	} else 
	if ($_GET['n']==74){ // Получение стартового голда
		if (!$get_gold_btn || $register_gold <= 0) go('main',10);
		$postdata = array( 'op' => 'GetRegGold',
		   'ip' => getIP(),
	           'id' => $_SESSION['id'],
		   'register_gold' => $register_gold,
		   'zoneid' => $zoneid,
		   'aid' => $aid
		);
		$postdata = array_merge($_POST, $postdata);
		$result = CurlPage($postdata, 15, 1, true);
		$res1 = @unserialize($result);
		if (is_array($res1)) {
			echo $res1['errtxt'];	
		} else
		go('main',$result);
		die();	
	} else 
	if ($_GET['n']==75){ // Отключаем режим ограничения по айпи в ЛК
		if (!$_SESSION['isadmin']) go('main',33);
		if (!isset($_GET['id'])) go('main',10);
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
	           'id' => intval($_GET['id']),
		   'n' => 58,
		   'num' => $_GET['num'],
		   'mode' => false
		);		
		$result = CurlPage($postdata, 15, 1, true);
		echo GetErrorTxt($result);		
	} else
	if ($_GET['n']==76){ // Отправка пробного письма
		if (!$_SESSION['isadmin']) go('main',33);
		if (!isset($_GET['mail'])) die('E-Mail parameter is required');
		if (!isset($_GET['temp']) || CheckNum($_GET['temp'])) die('Template parameter is required');
		if (!checker($_GET['mail'])) die('Введите корректный E-Mail'); // Ошибка мыла
		require_once('templates.php');
		$subj = "Проверка настроек почты";
		$t = new templates();
		$login = 'test_login'; $passw = 'test_password'; $question = 'test_question'; $answer = 'test_answer'; $ip = getIP(); $servname = $logotext; $act_link = 'http://act_link'; $ref_link = 'http://ref_link'; $name = 'test_name';
		$test_acc = array(
			'login' => $login,
			'question' => $question,
			'answer' => $answer,
			'reset_passw_link' => 'http://reset_passw_link',
			'reset_ip_link' => 'http://reset_ip_link'
		);
		switch ($_GET['temp'])
		{
			case 0:
				$text = $t->TestMail();
			break;
			case 1:
				$text = $t->RegisterMail($login, $passw, $question, $answer, $name, $act_link, $ref_link);
			break;
			case 2:
				$text = $t->ActTemplate($act_link);
			break;
			case 3:
				$text = $t->ResetPasswMail($ip, $login, $passw, $question, $answer, $servname);
			break;
			case 4:
				$text = $t->ResetIPMail($ip, $login, $servname);
			break;
			case 5:
				$text = $t->RememberMail($ip, $servname, [$test_acc]);
			break;
			case 6:
				$text = $t->AccountsDataTemplate([$test_acc]);
			break;
			case 7:
				$text = $t->ChangePasswMail($ip, $login, $passw, $servname);
			break;
			default:
				die('Unknown template');
			break;
		}
		if (!@send_mail($_GET['mail'], $subj, $text)) die('Произошла ошибка при отправке письма');
		die('16');
	} else
	if ($_GET['n']==77){ // Активация аккаунта
		if (!$_SESSION['isadmin']) go('main',33);
		if (!isset($_GET['userid'])) die('Userid parameter is required');
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
	           'id' => $_SESSION['id'],
		   'n' => $_GET['n'],
		   'num' => $_GET['num'],
		   'userid' => intval($_GET['userid'])
		);
		$postdata = array_merge($_POST, $postdata);
		$result = CurlPage($postdata, 15, 1, true);
		$res1 = @unserialize($result);
		if (is_array($res1)) {
			echo $res1['errtxt'];	
		} else {
			if (!CheckNum($result)) echo GetErrorTxt($result); else
			echo $result;
		}
		die();
	} else
	{
		$i = (isset($_GET['i']))?intval($_GET['i']):0;
		$postdata = array( 'op' => 'act',
		   'ip' => getIP(),
	           'id' => $_SESSION['id'],
		   'n' => $_GET['n'],
		   'num' => $_GET['num'],   		   
		   'nullbankpass' => $nullbankpass,
		   'i' => $i
		);
		$result = CurlPage($postdata, 15, 1, true);
		if ($_GET['n']==17) die($result);
		if ($_GET['n']==18) go('pvp',$result); else
		if ($_GET['n']== 5 || $_GET['n']== 6) go('money',$result); else
		go('pers',$result);		
	}
}
?>