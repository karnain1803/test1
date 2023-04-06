<?php
	if (!defined('main_def')) die();
	if (isset($_GET['r'])) {
		echo GetErrorTxt($_GET['r']);
	}
require_once('captcha.php');
$captcha = new captcha();
echo $captcha->GetKaptchaScript();
?>
	        <p><b>Что я могу получить за ЛК монеты: </b>различные услуги в разделе <a href="index.php?op=pers">Персонажи</a>, игровые ценности в разделе <a href="index.php?op=shop">Магазин</a>, а также возможность <a href="index.php?op=klan">установить значок клану</a>.</p><br>
	        <?php	
		$postdata = array(
			'op' => 'persuah',
			'id' => $_SESSION['id'],
			'allow_lk_gold_exchange' => $allow_lk_gold_exchange,
			'gold_itemid' => $gold_itemid,
			'gold_item_exchange_rate' => $gold_item_exchange_rate,
			'allow_lk_silver_exchange' => $allow_lk_silver_exchange,
			'silver_itemid' => $silver_itemid,
			'silver_item_exchange_rate' => $silver_item_exchange_rate,
			'ip' => $_SERVER['REMOTE_ADDR']
		);
		$result = CurlPage($postdata, 5);
		$a = UnpackAnswer($result);		
		if (!is_array($a) && !CheckNum($result)) echo GetErrorTxt($result); else
		if ($a['errorcode'] == 81 || (isset($a['bancount']) && $a['bancount']>0)) {
			if ($a['bancount']>0) echo GetErrorTxt(81);
			WriteBanTable($a);
		} else 
		if ($a['errorcode'] == 0) { ?>
		
	<script language="javascript">
	function checksend(){				
		if ((document.sendform.id.value.length < 1)||(document.sendform.id.value.length > 7)) { alert ("Введите корректный номер счета получателя");
		} else 
		if (document.sendform.gold.value == 0){ alert ("Нельзя отправить 0 монет");
		} else 
		if ((document.sendform.gold.value.length < 1)||(document.sendform.gold.value.length > 5)){ alert ("Введите корректное значение монет");
		} else {
			if (window.confirm("Убедитесь, что правильно указали номер счета получателя\r\rПередать монеты на аккаунт №"+document.sendform.id.value+"?")) 
				if (window.confirm("Это последнее предупреждение, если вы все же ошиблись номером счета получателя, просьба не писать потом об ошибочном переводе администрации, подобные сообщения будут игнорироваться!\r\n\r\nПродолжить перевод?")) {document.sendform.submit();}
		}
	}
	function checkoutform(){
		if ((document.outform.goldcount.value.length < 1)||(document.outform.goldcount.value.length > 7)||(document.outform.goldcount.value < 1)) { alert ("Введите корректное количество голда");
		} else {	
			if (window.confirm("Вы действительно хотите обменять "+document.outform.goldcount.value+" ЛК монет на игровой голд?")) document.outform.submit();
		}
	}	
	function checkdigit(field)
	{
	  var val;
 
	  if (field.value == "")
	      return false;
 
	  val = parseInt(field.value);		
	  if (isNaN(val))
	      return false; 	
	  if (val != field.value)
	      return false; 		
	  if (val <= 0)
	      return false;
 	  return true;
	}
	</script>

	<div align="center">
		<table border=0 cellpadding=2px cellspacing=0>
		<tr><td>
	<?php $ak = @AssignPData(act_key); if (!$ak) die('13');
	if ($allow_lk_transfer) { ?>
		<form name="sendform" method="post" action="?op=act&n=10&num=0&rand=<?=time()?>">
			<span class="label label-success">Перевод монет на другой аккаунт</span>
			<table class="table table-bordered table-striped table-condensed" style="width:300px">
				<tr>
					<td><div title="№ счета получателя" data-rel="tooltip"><input name="id" maxlength="7" type="text" placeholder="№ счета получателя в ЛК"></div></td>
				</tr>
				<tr>
					<td><div title="Сумма перевода" data-rel="tooltip"><input name="gold" type="text" maxlength="5" placeholder="Сумма перевода"></div></td>
				</tr>
				<tr>
					<td><div title="Комментарий" data-rel="tooltip"><input name="comment" type="text" maxlength="20" placeholder="Комментарий (не обязательно)"></div></td>
				</tr>				
				<tr>
					<td><?=$captcha->ShowCaptcha()?></td>
				</tr>
				<tr>
					<td><input type="button" class="btn btn-danger" value="Передать монеты" onclick="checksend();"></td>
				</tr>				
		</table>
		</form><?php } ?></td>
		<td style="vertical-align:top"><?php if ($allow_lk2game) { ?>
		<form name="outform" method="post" action="?op=act&n=11&num=0&rand=<?=time()?>">
			<span class="label label-success">Вывести монеты в игру</span>
			<table class="table table-bordered table-striped table-condensed" style="width:250px">		
				<tr>
					<td><div title="Количество монет" data-rel="tooltip"><input type="text" name="goldcount" maxlength="7" placeholder="Количество монет" onkeyup="calc_gold()"></div></td>					
				</tr>
				<tr>
					<td><div title="Будет начислено голда в игру" data-rel="tooltip">Голд: <span id="plus_gold">0</span></div></td>
				</tr>
				<tr>
					<td><input type="button" class="btn btn-danger" value="Обменять на голд шоп" onclick="checkoutform();"></td></tr>
			</table>
		</form>
		<script type="text/javascript">
		function calc_gold(){
			var lk2game_exchange_rate = <?=$lk2game_exchange_rate?>;
			var Q1 = document.outform.goldcount;
			var Q2 = document.getElementById('plus_gold');
			x = Q1.value * lk2game_exchange_rate;
			if (isNaN(x) || x<0) x = 0;
			Q2.innerHTML = parseInt(x);
		}
		</script>
<?php } ?>
		</td></tr>
		</table>
		<?php if ($allow_lk_gold_exchange || $allow_lk_silver_exchange) { ?>
		<div class="row-fluid">
			<div class="box span12">
				<div class="box-header well" data-original-title>
					<h2>Обмен монет</h2>
				</div>
				<div class="box-content"><?php
				if (count($a['roles']) < 1) echo '<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">×</button>На персонажах не найдено достаточное количество предметов для обмена.</div>'; else {
					echo '
					<table class="table table-bordered table-striped table-condensed" style="width:98%">
					<thead><tr>
						<th>Персонаж</th>';
					if ($allow_lk_gold_exchange) echo '	<th>'.$a['gold_item_name'].', шт</th>';
					if ($allow_lk_silver_exchange) echo '	<th>'.$a['silver_item_name'].', шт</th>';
					echo '	<th>Действия</th>		
					</tr>
					</thead>
					<tbody>';
					foreach ($a['roles'] as $i => $val){ 
						$t = '';	?>
						<?php if ($allow_lk_gold_exchange) $t = '
						<script language="javascript"> 
							function changeg'.$i.'(){
								a=document.goldform'.$i.';
								c=document.getElementById("gold'.$i.'");
								x=a.i.value*'.$gold_item_exchange_rate.';
								c.innerHTML=x;
							}			
							function checkg'.$i.'(){
								a=document.goldform'.$i.';
								if (!checkdigit(a.i)) {
									alert("Введите корректное значение");
								} else {
									x=a.i.value*'.$gold_item_exchange_rate.';
									if (x>'.$val['gold_item_cnt'].') {
										alert("Недостаточно предметов '.$a['gold_item_name'].' для обмена");	
									} else
									if (window.confirm("Вы уверены, что хотите обменять "+x+" '.$a['gold_item_name'].' на "+a.i.value+" голд монет?")) a.submit();
								}
							}
						</script>
						<form name="goldform'.$i.'" method="get">
						<input type="hidden" name="op" value="act"><input type="hidden" name="n" value="5"><input type="hidden" name="num" value="'.$val['index'].'">
						<table border="0px" cellpadding="0" cellspacing="0" style="border:0; margin:0; padding:0">
						<tr>
							<td style="border:0"><input type="button" class="btn btn-danger" value="Обменять" style="width:80px" onclick="checkg'.$i.'();"></td>
							<td style="border:0; padding:3px"><input style="width:40px" name="i" onkeyup="changeg'.$i.'();">&nbsp;<span class="gold"></span></td>
							<td style="border:0">за <b><span id="gold'.$i.'">0</span></b> '.$a['gold_item_name'].'</td>
						</tr>			
						</table>
						</form>
				';
						if ($allow_lk_silver_exchange) $t .= '
						<script language="javascript"> 
							function changes'.$i.'(){
								a=document.silverform'.$i.';
								c=document.getElementById("silver'.$i.'");
								x=a.i.value*'.$silver_item_exchange_rate.';
								c.innerHTML=x;
							}			
							function checks'.$i.'(){
								a=document.silverform'.$i.';
								if (!checkdigit(a.i)) {
									alert("Введите корректное значение");
								} else {
									x=a.i.value*'.$silver_item_exchange_rate.';
									if (x>'.$val['silver_item_cnt'].') {
										alert("Недостаточно предметов '.$a['silver_item_name'].' для обмена");	
									} else
									if (window.confirm("Вы уверены, что хотите обменять "+x+" '.$a['silver_item_name'].' на "+a.i.value+" голд монет?")) a.submit();
								}
							}
						</script>
						<form name="silverform'.$i.'" method="get">
						<input type="hidden" name="op" value="act"><input type="hidden" name="n" value="6"><input type="hidden" name="num" value="'.$val['index'].'">
						<table border="0px" cellpadding="0" cellspacing="0" style="border:0; margin:0; padding:0">
						<tr>
							<td style="border:0"><input type="button" class="btn btn-danger" value="Обменять" style="width:80px" onclick="checks'.$i.'();"></td>
							<td style="border:0; padding:3px"><input style="width:40px" name="i" onkeyup="changes'.$i.'();">&nbsp;<span class="silver"></span></td>
							<td style="border:0">за <b><span id="silver'.$i.'">0</span></b> '.$a['silver_item_name'].'</td>
						</tr>			
						</table>
						</form>
				';	echo '<tr>';
					printf('<td style="color:#000000"><b>%s</b></td>', $val['name']);
					if ($allow_lk_gold_exchange) printf('<td>%d</td>', $val['gold_item_cnt']);
					if ($allow_lk_silver_exchange) printf('<td>%d</td>', $val['silver_item_cnt']);
					printf('<td>%s</td>', $t);
					echo '</tr>';
					}
					echo '</tbody></table>';
				}
				?>
				</div>
			</div>
		</div>
		<?php }
		if ($_SESSION['isadmin']) { ?>
<script>
function GetAccountInfo(){
	sOut = '<div class="userinfo"><center><img src="img/ajax-loaders/ajax-loader-1.gif" border=0> Получение данных с сервера <img src="img/ajax-loaders/ajax-loader-1.gif" border=0></center></div>';
	$('#acc_info').html('');
	$('#acc_info').css('display' , 'none');
	login = document.addlk.acc_login.value.toLowerCase();
	if (login == '') return;
	$('#acc_info').html(sOut);
	$('#acc_info').css({'display' : 'inherit', 'margin-top' : '3px'});
	document.addlk.acc_login.value = login;	
	$.ajax({
	     url: 'pages/server_processing.php?login='+login+'&r='+Math.random(),             // указываем URL и
	     dataType : "text",                     // тип загружаемых данных
	     complete: function (data, textStatus) { // вешаем свой обработчик на complete
		if (textStatus!='success') {
			$('acc_info').html(textStatus);
			return;
		}
	        $('#acc_info').html(data.responseText);
	     }
	});
}	
function CheckAddLkForm(){
	if (document.addlk.acc_login.value.length < <?=$login_min_len?> || document.addlk.acc_login.value.length > <?=$login_max_len?>) alert('Логин должен быть от <?=$login_min_len?> до <?=$login_max_len?> символов'); else
	if (document.addlk.summ_gold.value.length < 1 || isNaN(parseInt(document.addlk.summ_gold.value))) alert('Введите корректное значения голд монет'); else
	if (document.addlk.summ_silver.value.length < 1 || isNaN(parseInt(document.addlk.summ_silver.value))) alert('Введите корректное значения серебряных монет'); else
	if (document.addlk.summ_gold.value == 0 && document.addlk.summ_silver.value == 0) alert('Нельзя выдать 0 монет'); else
	if (document.addlk.summ_gold.value > 10000000 || document.addlk.summ_silver.value > 10000000) alert('Нельзя выдать больше 10000000 монет'); else
	if (document.addlk.summ_gold.value < -10000000 || document.addlk.summ_silver.value < -10000000) alert('Нельзя выдать меньше -10000000 монет'); else
	if (window.confirm('Выдать монеты на аккаунт '+document.addlk.acc_login.value+'?')) document.addlk.submit();
}
var timeout_id = 0;
</script>
		<div class="row-fluid">
			<div class="box span12">
				<div class="box-header well" data-original-title>
					<h2>Выдача ЛК монет <img src="img/admin.png" border=0></h2>
				</div>
				<div class="box-content">
					<form method="get" action="index.php" name="addlk">
					<input type="hidden" name="op" value="act">
					<input type="hidden" name="n" value="52">
					<input type="hidden" name="num" value="0">
					Логин аккаунта: <input type="text" maxlength="<?=$login_max_len?>" name="acc_login" onKeyUp="clearTimeout(timeout_id); timeout_id = setTimeout(GetAccountInfo, 1000);">
					<span class="gold">&nbsp;</span><input type="text" class="config_cost" name="summ_gold" value="0">
					<span class="silver">&nbsp;</span><input type="text" class="config_cost" name="summ_silver" value="0">
					Причина: <input type="text" name="desc" maxlength="20">
					<input type="button" class="btn btn-inverse" onclick="CheckAddLkForm()" value="Выдать ЛК монеты">
					</form>					
					<div id="acc_info" class="userinfo money_accinfo" align="center" style="display: none"></div>
				</div>
			</div>
		</div>
		<?php } ?>
	</div><?php
		}
?>