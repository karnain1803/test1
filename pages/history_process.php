<?php
include('../config.php');
if (!isset($_SESSION['id']) || $_SESSION['id'] < 16) die();
if (isset($_GET['op'])) {
	$op = $_GET['op'];
	if ($op == 'proctypeform') {
		echo '<div id="proctypeform">
	<table border=0 cellpadding=0 cellspacing=0>
	<tr style="vertical-align:top">
		<td style="width: 240px">
			<input id="proctype_1" onclick="getproctype()" type="checkbox"> No PvP Drop<br>
			<input id="proctype_2" onclick="getproctype()" type="checkbox"> No Discard<br>
			<input id="proctype_3" onclick="getproctype()" type="checkbox"> Unknown 4<br>
			<input id="proctype_4" onclick="getproctype()" type="checkbox"> Unknown 8<br>
			<input id="proctype_5" onclick="getproctype()" type="checkbox"> No Trade<br>
			<input id="proctype_6" onclick="getproctype()" type="checkbox"> Unknown 32<br>
			<input id="proctype_7" onclick="getproctype()" type="checkbox"> Bind When Equip<br>
			<input id="proctype_8" onclick="getproctype()" type="checkbox"> Unknown 128<br>
			<input id="proctype_9" onclick="getproctype()" type="checkbox"> No Sell NPC<br>			
			<input id="proctype_10" onclick="getproctype()" type="checkbox"> Disappear On Instance Leaving<br>
		</td>
	</tr>
	</table>
    </div>';
		die();
	} else
	if ($op == 'maskform') {
		echo '<div id="maskform">
	<table border=0 cellpadding=0 cellspacing=0>
	<tr style="vertical-align:top">
		<td style="width: 185px">
			<input id="mask_1" onclick="getmask()" type="checkbox"> Оружие<br>
			<input id="mask_2" onclick="getmask()" type="checkbox"> Шлем<br>
			<input id="mask_3" onclick="getmask()" type="checkbox"> Ожерелье<br>
			<input id="mask_4" onclick="getmask()" type="checkbox"> Плащ<br>
			<input id="mask_5" onclick="getmask()" type="checkbox"> Броня<br>
			<input id="mask_6" onclick="getmask()" type="checkbox"> Пояс<br>
			<input id="mask_7" onclick="getmask()" type="checkbox"> Бриджи<br>
			<input id="mask_8" onclick="getmask()" type="checkbox"> Сапоги<br>
			<input id="mask_9" onclick="getmask()" type="checkbox"> Наручи<br>
			<input id="mask_10" onclick="getmask()" type="checkbox"> Кольцо - Левое<br>
			<input id="mask_11" onclick="getmask()" type="checkbox"> Кольцо - Правое<br>
			<input id="mask_12" onclick="getmask()" type="checkbox"> Стрелы<br>
			<input id="mask_13" onclick="getmask()" type="checkbox"> Полет<br>
			<input id="mask_14" onclick="getmask()" type="checkbox"> Стиль - Верх<br>
			<input id="mask_15" onclick="getmask()" type="checkbox"> Стиль - штаны<br>
			<input id="mask_16" onclick="getmask()" type="checkbox"> Камень | Стиль - сапоги
		</td>
		<td>
			<input id="mask_17" onclick="getmask()" type="checkbox"> Стиль - Наручи<br>
			<input id="mask_18" onclick="getmask()" type="checkbox"> Знак атаки<br>
			<input id="mask_19" onclick="getmask()" type="checkbox"> Трактат<br>
			<input id="mask_20" onclick="getmask()" type="checkbox"> Смайлы<br>
			<input id="mask_21" onclick="getmask()" type="checkbox"> Хирка HP<br>
			<input id="mask_22" onclick="getmask()" type="checkbox"> Хирка MP<br>
			<input id="mask_23" onclick="getmask()" type="checkbox"> Цитатник<br>
			<input id="mask_24" onclick="getmask()" type="checkbox"> Джин<br>
			<input id="mask_25" onclick="getmask()" type="checkbox"> Торговая лавка<br>
			<input id="mask_26" onclick="getmask()" type="checkbox"> Стиль - Головной убор<br>
			<input id="mask_27" onclick="getmask()" type="checkbox"> Грамота альянса<br>
			<input id="mask_28" onclick="getmask()" type="checkbox"> Печать воителя - верх<br>
			<input id="mask_29" onclick="getmask()" type="checkbox"> Печать воителя - низ<br>
			<input id="mask_30" onclick="getmask()" type="checkbox"> Стиль оружие<br>
			<input id="mask_31" onclick="getmask()" type="checkbox"> Надет/Снят<br>
			<input id="mask_32" onclick="getmask()" type="checkbox"> Неизв.
		</td>
	</tr>
	</table>
    </div>';
		die();
	} else
	if ($op != 'history' && $op != 'loginlog') die();
	$postdata = array(
		'op' => $op,	
		'id' => $_SESSION['id'],
		'ip' => $_SERVER['REMOTE_ADDR']
	);
	$postdata = array_merge($_GET, $postdata);
	CurlPage($postdata, 10, 0, true);
} else
if (isset($_GET['subact'])){
	$subact = $_GET['subact'];	
	if ($unbind_enable && $subact == 'prctp'){
		if ( !isset($_GET['num']) || CheckNum($_GET['num']) ) GetErrorTxt(10); else {
			$poststr = array(
				'op'	=> 'act',
				'id'	=> $_SESSION['id'],
				'n'	=> 3,
				'num'	=> $_GET['num'],
				'ip'	=> GetIP(),
				'proctype_list' => implode(',', $proctype_list)
			);
			$result = CurlPage($poststr, 5);
			$a = UnpackAnswer($result);	
			if (!is_array($a) && !CheckNum($result)) echo GetErrorTxt($result); else		
			if ($a['errorcode'] == 0) {
				if ($a['item_count'] > 0){
					echo '<script language="javascript">
					function conf1(){
						if (window.confirm(\'Вы уверены, что хотите отвязать данную вещь?\')) {
							ModalLoad();
							return true;
						} else return false;
					}
					</script>';					
					echo '<br><div align="center"><span class="label label-success">Вещи в инвентаре персонажа '.htmlspecialchars($a['rolename']).', которые можно отвязать</span>
					<table class="auth shop_items" cellspacing="0" cellpadding="0" border="0" style="width:100%">
						<tbody>
							<tr>
								<td class="auth">
									<table class="table table-bordered table-shop">
										<thead><tr>
											<td>Название вещи</td>
											<td>Позиция в инвентаре</td>
											<td>Действие</td>
										</tr></thead>
										<tbody>';					
					foreach ($a['items'] as $i => $val) {
						$cost = getcost($val['id'],$val['list']); 
						printf('
						<tr>
						<td>%s</td>
						<td>%s</td>
						<td>
						<a class="btn btn-mini btn-inverse w200" onclick="return conf1()" href="index.php?op=act&n=4&num=%s&i=%s&id='.$val['id'].'&l='.$val['list'].'&rand='.time().'">Отвязать вещь '.ShowCost($cost).'</a></td>
					</tr>',
					'<div align="left"><img title="'.htmlspecialchars($val['name']).'" data-content="'.str_replace('"',"'",$val['desc']).'" data-rel="popover" src="getitemicon.php?i='.urlencode(base64_encode($val['icon'])).'"> <span class="item_name">'.$val['name'].'</span></div>',
					$val['pos']+1,
					$a['rolenum'],
					$val['index'],
					$a['rolenum'],
					$val['index']);
					}					
					echo '</tbody></table></td></tr></tbody></table></div>';
				}
			}
		}
	}
}
?>