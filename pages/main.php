<?php
	if (!defined('main_def')) die();
	$idacc = intval(($_SESSION['id']/16)-1);
	$reflink = GetLink().'register.php?ref='.$idacc;
	if (isset($_GET['r'])) {			
		echo GetErrorTxt($_GET['r']);
	}	

$postdata = array(
			'op' => 'checkban',
			'id' => $_SESSION['id'],	
			'mmotop1_item' => serialize($mmotop1_item),
			'mmotop2_item' => serialize($mmotop2_item),
			'mmotop3_item' => serialize($mmotop3_item),
			'mmotop4_item' => serialize($mmotop4_item),
			'qtop1_item' => serialize($qtop1_item),
			'qtop2_item' => serialize($qtop2_item),
			'ip' => $_SERVER['REMOTE_ADDR']
		);
		$result = CurlPage($postdata, 15);
		$a = UnpackAnswer($result);
?>
<h3>Добро пожаловать в личный кабинет игрока</h3>
<?php 
if (!$a['errorcode'])
{
	if (!$a['roles_count']) {
		echo '<div class="alert alert-error">Чтобы получать предметы за голосование в топах и промо коды, нужно создать персонажа в игре.</div>';
	} else {
		echo '<span class="label label-info">TOP & PROMO</span> Выберите персонажа, на которого будут отправляться награды за голосование в топе, а также награды за использование промо кодов<p><span class="label label-important">Обратите внимание!</span> Если Вы не выберете персонажа, награды будут приходить на персонажа, который в данный момент онлайн или на того, у кого больше часов онлайна в игре.</p>
		<div class="well">
		';
		foreach ($a['roles'] as $i => $val){		
			if ($a['cur_reward_role_index'] == $i) printf('<span class="btn btn-success">%s</span> ',$val['name']); else
			printf('<a class="btn" href="index.php?op=act&n=800&num=%s&rand=%s'.'">%s</a> ',$i,time(),$val['name']);
		}
		echo '</div>';
	}
}

if ($promo_enabled) { ?>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header well"><h2>Введите промо-код для получения бонуса</h2></div>
		<div class="box-content">
			<form name="enterpromo" method="post" action="index.php?op=act&n=73&num=0">
			<input type="text" name="promo_code" maxlength=50> <a href="#" onclick="checkpromo()" class="btn btn-large btn-inverse">Получить бонус</a>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
function checkpromo(){
	if (document.enterpromo.promo_code.value.length < 1) alert('Введите промо-код'); else
	document.enterpromo.submit();
}
</script>
<?php } ?>
<div class="well">		
		<?php		
		
		if ($a['errorcode'] == 0) {
			$ip = $a['lastip'];
			$lastlogin = $a['lastlogin'];
			if ($ip=='127.0.0.1') $ip='Системный';
			if ($ip != '') printf('<p>Дата последнего входа на аккаунт: <span class="label label-info">%s</span></p>', @date("d.m.Y H:i:s",$lastlogin));
			if ($lastlogin != '') printf('<p>IP последнего входа на аккаунт: <span class="label label-inverse">%s</span></p>',$ip);			
			if ($a['bancount'] == 0) {
				echo '<div class="alert alert-success">На аккаунте нет банов.</div>';
			} else {
				echo '<div class="alert alert-error">На аккаунте имеются следующие баны:</div>';
				WriteBanTable($a);
			}
			if ($a['refcount'] == 0) {
				echo '<div class="alert alert-error">На данный момент у Вас нет приглашенных игроков</div>';
			} else {
				$ref_stat = array('<span class="label label-important">Развивается</span>','<span class="label label-success">'.$ref_require_level.' Up бонус начислен</span>','<span class="label label-important">Накрутка</span>');
				echo '<div class="alert alert-success">Вы пригласили следующих игроков:</div>';
				echo '<div align="center">
				<table class="table table-bordered table-striped table-condensed" style="max-width:550px">
					<thead><tr>
						<th>Номер счета</th>
						<th>Дата регистрации</th>
						<th>Статус</th>
						<th>Начислено бонусов</th>
					</tr></thead>
					<tbody>';
				foreach ($a['refdata'] as $i => $val){
					printf('
					<tr>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td><i class="icon-shopping-cart"></i><span class="green"><b>%s</b></span></td>
					</tr>',$val['id'], $val['creatime'], $ref_stat[$val['ref_status']], $val['ref_bonus']);
				}
				echo '</tbody></table></div>';
			}
		}
		//if ($result==11) echo GetErrorTxt($result); else
		//echo $result;		
?>
</div>
<?php if ($get_gold_btn && $register_gold > 0) { ?>
<h3>Стартовый голд</h3>
<div class="well">
	<p>Вы можете получить <span class="label label-important"><?=$register_gold?></span> игрового голда один раз после регистрации аккаунта</p><?php
	if (isset($a['allow_reg_gold'])){
		if (!$a['allow_reg_gold']) echo '<div class="alert alert-success">Готово</div>'; else
		if ($a['allow_reg_gold'] && $a['roles_item_count'] < 1) echo '<div class="alert alert-error">Сначала создайте персонажа в игре</div>'; else
		if ($a['allow_reg_gold']) echo '<a href="index.php?op=act&n=74&num=0" class="btn btn-large btn-danger">Получить голд</a>';
	}
?>	
</div>
<?php }

if ($accumulation_system) { ?>
<h3>Накопительная система</h3>
<div class="well">
<table class="table table-bordered table-striped table-condensed">
	<thead>
		<tr>
			<th>Ваш бонус</th>
			<th>Сумма всех пожертвований</th>
			<th>Бонус</th>
		</tr>
	</thead>	
<?php
	foreach ($acc_param as $i => $val){
		if ($cur_accum_id == $i) {
			$c = ' class="success"';
			$s = 'Cумма пожертвований: <b>'.$cur_user_donate.' руб</b>';
		} else {
			$c = '';
			$s = '';
		}
		printf('
			<tr%s>
				<td>%s</td>
				<td>от %s руб</td>
				<td>+%s%%</td>
			</tr>', $c, $s, $val->summ, ShowCost($val->bonus, true, true));
	}	
?>
</table>
<br>
<?php
if ($cur_accum_id < (count($acc_param)-1)) {
		$rest = $acc_param[$cur_accum_id+1]->summ - $cur_user_donate;
		printf('До следующего уровня бонусов осталось <span class="label label-important">%s</span> руб. (без учета комиссии платежной системы)', $rest);
	} else echo 'На данный момент, у Вас <b>максимальная скидка</b>.';
	echo '</div>';
} 
?>

<p>Приглашайте друзей регистрироваться на проекте по реферальной ссылке и получайте бонусы!</p>
<p>Ваша реферальная ссылка: <a href="<?=$reflink?>" target="_blank"><?=$reflink?></a></p><?php
$mmotop_reward1 = ShowCost($mmotop_cost1, false, true);
if ($send_mmotop_bonusitem && $mmotop1_item['id'] > 0) {	
	@$mmotop_reward1 .= ' + <span class="label label-success">'.$mmotop1_item['count'].'</span> '.GetIconHTML($a['mmotop1_itemicon']).'<span class="label label-inverse">'.$a['mmotop1_itemname'].'</span>';
	if ($mmotop1_item['expire_date'] > 0) $mmotop_reward1 .= ' на '.GetTime($mmotop1_item['expire_date']);
}
$mmotop_reward2 = ShowCost($mmotop_cost2, false, true);
if ($send_mmotop_bonusitem && $mmotop2_item['id'] > 0) {	
	@$mmotop_reward2 .= ' + <span class="label label-success">'.$mmotop2_item['count'].'</span> '.GetIconHTML($a['mmotop2_itemicon']).'<span class="label label-inverse">'.$a['mmotop2_itemname'].'</span>';
	if ($mmotop2_item['expire_date'] > 0) $mmotop_reward2 .= ' на '.GetTime($mmotop2_item['expire_date']);
}
$mmotop_reward3 = ShowCost($mmotop_cost3, false, true);
if ($send_mmotop_bonusitem && $mmotop3_item['id'] > 0) {	
	@$mmotop_reward3 .= ' + <span class="label label-success">'.$mmotop3_item['count'].'</span> '.GetIconHTML($a['mmotop3_itemicon']).'<span class="label label-inverse">'.$a['mmotop3_itemname'].'</span>';
	if ($mmotop3_item['expire_date'] > 0) $mmotop_reward3 .= ' на '.GetTime($mmotop3_item['expire_date']);
}
$mmotop_reward4 = ShowCost($mmotop_cost4, false, true);
if ($send_mmotop_bonusitem && $mmotop4_item['id'] > 0) {	
	@$mmotop_reward4 .= ' + <span class="label label-success">'.$mmotop4_item['count'].'</span> '.GetIconHTML($a['mmotop4_itemicon']).'<span class="label label-inverse">'.$a['mmotop4_itemname'].'</span>';
	if ($mmotop4_item['expire_date'] > 0) $mmotop_reward4 .= ' на '.GetTime($mmotop4_item['expire_date']);
}
$qtop_reward1 = ShowCost($qtop_cost1, false, true);
if ($send_qtop_bonusitem && $qtop1_item['id'] > 0) {	
	@$qtop_reward1 .= ' + <span class="label label-success">'.$qtop1_item['count'].'</span> '.GetIconHTML($a['qtop1_itemicon']).'<span class="label label-inverse">'.$a['qtop1_itemname'].'</span>';
	if ($qtop1_item['expire_date'] > 0) $qtop_reward1 .= ' на '.GetTime($qtop1_item['expire_date']);
}
$qtop_reward2 = ShowCost($qtop_cost2, false, true);
if ($send_qtop_bonusitem && $qtop2_item['id'] > 0) {	
	@$qtop_reward2 .= ' + <span class="label label-success">'.$qtop2_item['count'].'</span> '.GetIconHTML($a['qtop2_itemicon']).'<span class="label label-inverse">'.$a['qtop2_itemname'].'</span>';
	if ($qtop2_item['expire_date'] > 0) $qtop_reward2 .= ' на '.GetTime($qtop2_item['expire_date']);
}
if ($mmotop_link != '') echo '<p><a href="'.$mmotop_link.'" target="_blank">Ссылка на голосование в MMOTOP</a><br> Награда за обычный голос: '.$mmotop_reward1.'<br>Награда за SMS (10 голосов): '.$mmotop_reward2.'<br>Награда за SMS (50 голосов): '.$mmotop_reward3.'<br>Награда за SMS (100 голосов): '.$mmotop_reward4.'</p>';
if ($qtop_link != '') echo '<p><a href="'.$qtop_link.'" target="_blank">Ссылка на голосование в Q-TOP</a><br> Награда за обычный голос: '.$qtop_reward1.'<br>Награда за SMS голос: '.$qtop_reward2.'</p>';
if ($mmotop_link != '' || $qtop_link != '') {	
	echo '<p>Также, Вы можете оставить приятный отзыв о проекте, это поможет сделать правильный выбор гостям и пользователям топа.</p>';
}

?>
<br>
<span class="label label-important">Обратите внимание!</span>
<ul align="left" type="disc">
	<li>На данный момент ЛК находится в стадии разработки, и в дальшейшем тут появится больше полезных функций.</li>
	<li>Будем рады Вашим пожеланиям по улучшению качества и функциональности данного ЛК, а также сообщениям о найденных ошибках и багах.</li>
</ul>