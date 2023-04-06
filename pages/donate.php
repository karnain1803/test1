<?php
	if (!defined('main_def')) die();
	if (isset($_GET['r'])) {
		echo GetErrorTxt($_GET['r']);
	}
	define('donate_def', true);
?><center>
<h3>Пожертвование на развитие проекта</h3><?php
function DrawDonateHeader() { 
	global $don_kurs, $bonus_system, $bonus_param; ?>
<img src="img/money.png" border=0 align=left>
<p>Уважаемые игроки, для поддержания стабильной работоспособности сервера, его улучшения и рекламы, а также для оплаты оборудования, мы принимаем добровольные пожертвования. В знак благодарности мы предоставляем некоторое количество монет для Личного Кабинета.</p>
<p><strong>Получить монеты для покупки товаров в личном кабинете можно по следующим условиям <font color="#ff0000">*</font> :</strong></p>
<p><span class="label label-success"><?=$don_kurs?> руб</span> = <span class="label label-inverse"><span class="gold">1</span></span> <font color="#ff0000">* *</font></p><br><?php if ($bonus_system && count($bonus_param)>0) {
echo "<p><b>Также действует система дополнительных бонусов:</b></p>\r\n";
	foreach ($bonus_param as $i => $val){
		printf('<p>При сумме пожертвований <span class="label label-success">от %d руб</span> - <span class="label label-inverse">бонус <span class="gold">%d%%</span></span> <font color="#ff0000">* *</font></p>', $val->summ, $val->bonus);
	}
}

function DrawDonateFooter(){
	global $donate_system; ?>
	<p><font color="#ff0000">*</font> <i>факт перевода средств является акцептом подтверждения Вашего полного согласия с тем, что Вы добровольно, по собственному желанию, жертвуете средства нашему проекту, соглашаетесь с правилами проекта, а мы, в свою очередь, в обмен на Вашу отзывчивость, предоставляем Вам те или иные поощрения.</i></p>
	<p><font color="#ff0000">* *</font> <i>Суммы указаны без учета комиссии сервиса приема платежей <?=$donate_system?>, комиссия системы зависит от выбранного типа платежной системы:</i></p>
	<?php
}

 ?></center>
<p><b>Минимальная сумма пожертвования для получения бонуса <span class="label label-important"><?=$don_kurs?> руб</span></b> <font color="#ff0000">* *</font></p><?php 
}
$ak = @AssignPData(act_key);
if (!$ak) die();
switch ($donate_system) {
	case 'WayToPay':
		include('donate_w2p.php');
		break;
	case 'UnitPay':
		include('donate_unitpay.php');
		break;
	case 'Free-Kassa':
		include('donate_freekassa.php');
		break;
	default:
		echo '<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">×</button>Прием пожертвований временно недоступен.</div>';
		break;
}
