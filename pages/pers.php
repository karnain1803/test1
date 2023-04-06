<?php
	if (!defined('main_def')) die();
	if (isset($_GET['r'])) {
		echo GetErrorTxt($_GET['r']);
	}	
	if (!isset($_GET['i'])) $_GET['i']='';	
	$ak = @AssignPData(act_key); if (!$ak) die('');
	$postdata = array(
			'op' => 'pers',
			'servid' => $servid,
			'id' => $_SESSION['id'],
			'ip' => $_SERVER['REMOTE_ADDR']
		);
	$result = CurlPage($postdata, 5);
	$a = UnpackAnswer($result);
	if ($a['errorcode'] == 81) {
		WriteBanTable($a);
	} else 
	if ($a['errorcode'] == 0) {
		echo '<script language="javascript">
		function conf(){
			return window.confirm(\'Вы уверены, что хотите выполнить это действие?\');
		}
		</script>';
		$confirm='onClick="return conf()"';
		$ban[100]='Бан персонажа';
		$ban[101]='Бан чата';
		$class = array("Воин","Маг","Шаман","Друид","Обор","Убийца","Лучн","Жрец","Страж","Мистик","Призрак","Жнец");
		$factrole = array(2=>"Глава",3=>"Маршал",4=>"Капитан",5=>"Сержант",6=>"");
		//echo '<p>Персонажей на аккаунте: <b>'.$a['roles_count'].'</b></p>';
		echo '<br>';
		if ($a['roles_count'] > 0){
			$n = 0;
			foreach ($a['roles'] as $i => $val){
				if ($n == 0) echo '<div class="row-fluid">';				
				if ($val['factionid'] == 0) {
					$faction_txt = '';					
				} else {
					$val['factionrole'] = $factrole[$val['factionrole']];
					$klan_icon = '<img src="klan/geticon.php?servid='.$servid.'&klan='.$val['factionid'].'&t='.time().'" align="absmiddle">';
					$faction_txt = $klan_icon.' <span class="label label-info">'.$val['factionname'].'</span> '.$val['factionrole'];
				}
				$time = GetTime($val['time_used'], 'ffff00');
				$forbid_txt = '';			
				if (count($val['forbids']) > 0) {
					foreach ($val['forbids'] as $ii => $val1){
						$rest = $val1['createtime'] + $val1['time'] - time();
						if ($rest <= 0) $rest = '<font color=green><b>Время бана истекло</b></font>'; else $rest = sprintf('<font color=red><b>%s</b></font>',GetTime($rest, 'ffff00'));
						$forbid_txt .= '<span class="label label-important">'.$ban[$val1['type']].'</span><br>';
						$forbid_txt .= "Длительность: <font color=\"00ff00\">".GetTime($val1['time'], 'ffff00')."</font><br>";
						$forbid_txt .= "Дата выдачи: <font color=\"00ffff\">".@date("d.m.Y H:i:s",$val1['createtime'])."</font><br>";
						$forbid_txt .= "Осталось: ".$rest.'<br>';
						$forbid_txt .= "Причина: <font color=\"ffff00\">".$val1['reason']."</font><br>";
						$forbid_txt .= '<hr>';
					}					
				}
				if ($nullbankpass_enable && $val['storehousepasswd']) $n_pass = '<a class="btn btn-mini btn-inverse wfull" href="index.php?op=act&n=2&num='.$val['index'].'&rand='.time().'" '.$confirm.'">Убрать пароль банка '.ShowCost($nullbankpass, false, false, false).'</a><br>'; else $n_pass='';				
				printf('
	<span class="well span3 shop-block pers-block">
		<!-- Level and shop button -->		
		<span class="notification yellow notification_adm_edit" data-rel="tooltip" data-original-title="Приобрести вещи"><a href="index.php?op=shop&num=%d&page=1&rand='.time().'"><i class="icon icon-black icon-cart"></i></a></span>
		<!-- Name and class -->
		<div data-rel="tooltip" data-original-title="%s" style="float:left; margin-left: 5px"><i class="class_icon %sclass_%d"></i> %s</div>&nbsp;<span class="label label-info" data-rel="tooltip" data-original-title="Уровень персонажа">%d</span>
		<!-- Faction -->
		<div data-rel="tooltip" data-original-title="Клан" style="float:right">%s</div>		
		<hr>
		<!-- Role info section -->
		Онлайн: <font color="#00ff00">%s</font><br>
		<hr>		
		<!-- Ban info section -->
		%s
		<!-- Actions -->
		%s
	</span>
',
	$val['index'], GetOccupationName($val['occupation']), ($val['gender'])?'f':'', $val['occupation'], htmlspecialchars($val['name']), $val['level'], $faction_txt, $time, $forbid_txt, $n_pass
);

				$n++;
					if ($n > 3) {
						echo '</div>';
						$n = 0;
					}
				}
				if ($n) echo '</div>';
		}
	}
?><br>

<div class="popup__overlay">
    <div class="popup">
        <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3 id="modalHeader">Settings</h3>
	</div>
	<div class="modal-body">
		<p id="modalBody"></p>
	</div>	
    </div>
</div>

<script type="text/javascript">

function ModalLoad(){
	var LoadTxt = '<center><img src="img/ajax-loaders/ajax-loader-1.gif" border=0> Получение данных с сервера <img src="img/ajax-loaders/ajax-loader-1.gif" border=0></center>';
	$('#modalBody').html(LoadTxt);
}

function ShowSubActWnd(a, b, c){
	$('html').css('overflow', 'hidden');
	$('body').css('overflow', 'hidden');
	$('.modal-body').css('max-height', document.body.clientHeight - 200);
	p = $('.popup__overlay');
	p.css('display', 'none').fadeIn();
	p.click(function(event) {
	e = event || window.event;
	if (e.target == this) {
	    $(p).css('display', 'none');
	    $('html').css('overflow', 'auto');
	    $('body').css('overflow', 'auto');
	}
	});
	$('.close').click(function() {
	    $('html').css('overflow', 'auto');
	    $('body').css('overflow', 'auto');
	    p.css('display', 'none');
	});
	$('#modalHeader').html(b);
	ModalLoad();	
	var X = new XMLHttpRequest();
	X.open("POST", "pages/history_process.php?subact="+c+"&num="+a+"&rand="+Math.random());
	X.onreadystatechange = function() {
		if (X.readyState == 4) {				
			if(X.status == 200) {
				$('#modalBody').html(X.responseText);
				$('[rel="popover"],[data-rel="popover"]').popover();
				if (c == 'show_skills') {
					$('.datatable').dataTable({
						"sDom": "<'row-fluid'<'span6'l><'span6'f>r><'label'i>t<'row-fluid'<'span12 center'p>>",
						"sPaginationType": "bootstrap",
						"bJQueryUI": true,
						"oLanguage": {
							"sSearch": "Поиск:",
							"sLengthMenu": "_MENU_ скиллов на странице",
							"sZeroRecords": "Не найдено",
							"sInfo": "Показаны скиллы _START_-_END_ из _TOTAL_",
							"sInfoEmpty": "Нет данных",
							"oPaginate": {
								"sFirst": "В начало",
								"sLast": "В конец",
								"sNext": ">>",
								"sPrevious": "<<"
							},
							"sInfoFiltered": "(фильтр из _MAX_ скиллов)"
						}
					} );
				}
			} else $('#modalBody').html('<div class="alert alert-error">Ошибка '+X.status+'</div>');
		}
	};
	X.send();
}
</script>
<span class="label label-important">Обратите внимание!</span>
<ul align="left" type="disc">
<li>Перед совершением каких либо действий над персонажем, <u><b>выйдите из игры и не заходите</b></u> до завершения выбранных действий.</b></li>
</ul>