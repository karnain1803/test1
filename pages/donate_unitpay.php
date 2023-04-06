<?php
// UnitPay donate calculator
if (!defined('donate_def')) die();
//print_r($_POST);

function getFormSignature($account, $currency, $desc, $sum, $secretKey) {
    $hashStr = $account.'{up}'.$currency.'{up}'.$desc.'{up}'.$sum.'{up}'.$secretKey;
    return hash('sha256', $hashStr);
}

if (isset($_POST['oSum']) && isset($_POST['iSum']) && isset($_POST['sSum'])) {	// Формирование счета
	if ( CheckNum($_POST['iSum']) || CheckNum($_POST['oSum'])) die(GetErrorTxt(10));
	$outsumm = (int)$_POST['oSum'];
	$res = CalcDonate($outsumm);
	$moneycount = $res['moneycount'];
	if ($moneycount <= 0) die(GetErrorTxt(10));
	$bonus = $res['bonus'];		
	$sql = sprintf("INSERT INTO `donate_unitpay` (`data`,`out_summ`,`don_kurs`,`money`,`act_bonus`,`bonus_money`,`login`,`userid`,`ip`,`serv`) VALUES (now(),'%s','%s','%s','%s','%s','%s','%s','%s','%s')",
	$db->real_escape_string($outsumm),$don_kurs, $moneycount, $act_bonus, $bonus, $db->real_escape_string($_SESSION['login']), $_SESSION['id'], $_SERVER['REMOTE_ADDR'], $servid);
	$res = $db->query($sql);	
	if ($db->errno>0) die(GetErrorTxt(32));
	$inv_id = $db->insert_id;
	if (!$inv_id) die(GetErrorTxt(32));	
	$currency = 'RUB';
	$desc = 'Пожертвование на развитие проекта';
	$signature = getFormSignature($inv_id, $currency, $desc, $outsumm, $unitpay_secret_key);
	$url = $unitpay_form_url.'/yandex?sum='.$outsumm.'&signature='.$signature.'&currency='.$currency.'&account='.$inv_id.'&desc='.urlencode($desc);
?><br>
	<div align="center">
		<span class="label label-success">Предварительные данные платежа</span>
		<table class="table table-bordered table-striped table-condensed" style="width:500px">
			<tr>
				<td>№ заказа</td>
				<td><?=$inv_id?></td>
			</tr>
			<tr>
				<td>Количество монет</td>
				<td><span class="gold gold_dark"><?php echo $moneycount.'</span>';
				if ($bonus>0) echo ' + <span class="label label-important">бонус <span class="gold">'.$bonus.'</span></span>';?></td>
			</tr>
			<tr>
				<td>К оплате</td>
				<td><b><?=$outsumm?></b> <font color="#a0a0a0">(без учета комиссии платежной системы)</font></td>
			</tr>				
			<tr>
				<td colspan="2"><a class="btn btn-danger" href="<?=$url?>">Перейти к оплате</a></td>
			</tr>
		</table>
	</div>
	</center>
<?php } else { 
	DrawDonateHeader();
?>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header well" data-original-title>
			<h2><i class="icon-edit"></i> Введите количество монет и выберите способ оплаты</h2>						
		</div>
		<div class="box-content">
			<form class="form-horizontal" name="f0" id="f0" method="post" action="">
				<input id="oSum" name="oSum" type="hidden" value="15">
				<fieldset>
					<label class="control-label" for="iSum">Количество монет</label>
					<div class="controls">
						<input id="iSum" name="iSum" type="text" class="w200" onkeyup="curr_change()" value="1" style="margin-bottom:5px"> <span id="bonus"></span>
					</div>
					<div class="clearfix"></div>
					<label class="control-label" for="sSum">Сумма к оплате, руб</label>
					<div class="controls">
						<input id="sSum" class="input uneditable-input w200" readonly="readonly" name="sSum" type="text" value="0"> <font color="#ff0000">* *</font>
					</div>

					<div class="form-actions">
						<button type="submit" class="btn btn-primary" id="BSubmit">Продолжить</button>
					</div>					
				</fieldset>
			</form>
		</div>
	</div>

	<?php DrawDonateFooter(); ?><center>		
</div>

</center>
<script language="javascript" type="text/javascript">

	var l2_item = new Array("ЛК Монет", <?=$don_kurs?>);
	var actbon = <?=$act_bonus?>;

	$("#IncCurr").chosen().change = function ()
	{
	  curr_change();
	}

	function mround(num){
		var sDo = num.toFixed(2) * 1;
		var sPo = num - sDo;
		var sSs = 0;
		if(sPo*100000000000000 > 0)
		sSs = 0.01;
		var sum = sDo + sSs;
		return sum.toFixed(2);
	}

	function curr_change(){
		var incCurr = document.getElementById('IncCurr');	
		
		var bonus = document.getElementById('bonus');
		var iSum = document.getElementById('iSum');	
		if (parseInt(iSum.value)<=0) iSum.value = 1; 	
		bonus.innerHTML = '';
		var incSum = document.getElementById('sSum');
		var outSum = document.getElementById('oSum');
		iSum.value = parseInt(iSum.value);
		if (isNaN(iSum.value)) iSum.value = 0;
		if (iSum.value>(20000/<?=$don_kurs?>)) iSum.value = 20000/<?=$don_kurs?>;
		outSum.value = iSum.value*<?=$don_kurs?>;
		actbon1 = 0;
<?php
		if ($bonus_system && count($bonus_param)>0) {
			foreach ($bonus_param as $i => $val){
				printf("\t\t\t\tif (outSum.value >= %d) actbon1 = %d;\r\n", $val->summ, $val->bonus);						
			}
			echo "\t\t\t\tactbon1 += actbon;\r\n";
		}
?>		var sum = outSum.value * 1;
		if (actbon1 > 0) {
			var bonval = Math.round((iSum.value/100)*actbon1);
			bonus.innerHTML = '+ <span class="label label-inverse">бонус <span class="gold">'+bonval+'</span></span>';
		}
		incSum.value = mround(sum);		
	}
	curr_change();
</script>
<?php } ?>