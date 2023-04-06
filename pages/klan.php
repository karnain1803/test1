<?php
if (!defined('main_def')) die();
$pic = $uploaddir."/".$_SESSION['login'].".png";
if (@fopen($pic, "r")) {
	$pic_loaded = true;
} else $pic_loaded = false;
?>		
<style type="text/css">
hr {
	margin: 0px;
	margin-top: 5px;
	margin-bottom: 2px;
	border-top: 1px solid #a0a0a0;
}	
</style>

<h3>Заявка на установку значка клана</h3>
		<?php	
		if (isset($_GET['r'])) {
			if (intval($_GET['r'])>=6) echo GetErrorTxt($_GET['r']);
		}
		?>	       
	        <p>Стоимость установки значка составляет <?=ShowCost($klancost,false,true)?></p><br>
		<span class="label label-info">Шаг 1</span> Загрузите картинку <?php if ($pic_loaded) echo '<i class="icon icon-color icon-check"></i>'; ?>
	<div class="well">		
		<form name="upload" action="index.php?op=upload" method="POST" ENCTYPE="multipart/form-data">
		 Выберите картинку для загрузки: <input type="file" name="userfile">
		 <input type="submit" name="upload" class="btn btn-primary" value="Загрузить...">
		</form>
	        <?php	
		if (isset($_GET['r'])) {
			if (intval($_GET['r'])<6) echo GetErrorTxt($_GET['r']);
		}
		$ak = @AssignPData(act_key); if (!$ak) die();
		if ($pic_loaded) {
		    echo '<span class="label label-success">Текущая картинка для установки:</span> <img src="'.$pic.'?t='.time().'" border="0" align="absmiddle">';
		} else {
		    echo '<span class="label label-important">У вас нет загруженных картинок!</span>';
		}	
		?>
		<br></div><?php
		//if ($pic_loaded) { ?>
		<span class="label label-info">Шаг 2</span> Подайте заявку на установку значка
	<div class="well">
		<?php
		$postdata = array(
			'op' => 'persklan',
			'servid' => $servid,
			'id' => $_SESSION['id'],
			'ip' => $_SERVER['REMOTE_ADDR']
		);
		$result = CurlPage($postdata, 5);
		$a = UnpackAnswer($result);
		if (!is_array($a) && !CheckNum($result)) echo GetErrorTxt($result); else
		if ($a['errorcode'] == 81) {
			WriteBanTable($a);
		} else 
		if ($a['errorcode'] == 0) {
			if ($a['factions']) {
				echo '<p><b>Кланы Ваших персонажей, мастерам которых доступна покупка клан-артов:</b></p>';
				foreach ($a['factions'] as $i => $val){
					printf('<span class="label label-info">%s</span>: ', $val['faction_name']);
					foreach ($val['items'] as $i1 => $val1){
						$cost = ShowCost($val1['costgold'].'|'.$val1['costsilver'], false, true, false);
						$costitem = '';
						if ($val1['cost_item_count']) $costitem = '<span class="label label-important">'.$val1['cost_item_count'].'</span> '.GetIconHTML($val1['cost_icon']).' <span class="label label-inverse">'.$val1['cost_name'].'</span>';
						printf('<span class="label label-important">%d</span> %s <span class="label label-success">%s</span> за %s %s | ', $val1['count'], GetIconHTML($val1['icon']), $val1['item'], $cost, $costitem);
					}
					echo '<br>';
				}
			}
			if ($a['roles_count']==0) echo GetErrorTxt(82); else {				
				echo '<script language="javascript">
				function conf(){
					return window.confirm(\'Вы уверены, что хотите подать заявку на установку загруженного значка клана?\');
				}	
				function conf1(){
					return window.confirm(\'Вы уверены, что хотите купить данный предмет?\');
				}
function conf2(t){
	
	var person = prompt("Укажите ник соклана, которому отправить предмет с учетом регистра", "");
	if (person == null || person == "") return false;	
	if (window.confirm(\'Вы уверены, что хотите купить данный предмет для игрока \'+person+\'?\'))
	{
		t.href += "&name="+encodeURI(person);
		return true;
	}
	return false;
}
				</script>';
				$confirm='onClick="return conf()"'; $confirm1='onClick="return conf1()"';
				echo '<br><div align="center">
				<span class="label label-success">Список мастеров кланов на аккаунте</span>
				<table class="table table-bordered table-striped table-condensed" style="width:700px">
				<thead><tr>
					<th>Персонаж</td>
					<th>Название клана</th>
					<th>Действия</th>
				</tr>
				</thead>
				<tbody>';
				foreach ($a['roles'] as $i => $val){
					$klanit = '';
					if (count($val['klanitems'])>0) {
						foreach($val['klanitems'] as $i1 => $val1) {
							//if ($klanit == '') $klanit = '<hr>';
							$cost = ShowCost($val1['costgold'].'|'.$val1['costsilver'], false, false, true);
							if ($val1['itemid'] == 0 || $val1['cost_item_count'] == 0) $costitem = ''; else
								$costitem = ' за '.$val1['cost_item_count'].' '.GetIconHTML($val1['cost_icon']).' '.$val1['cost_name'];
							$klanit.='<hr><a class="btn btn-mini btn-inverse" style="margin-top:2px" href="index.php?op=act&n=12&num='.$val['roleindex'].'&artid='.$val1['id'].'&rand='.time().'" '.$confirm1.'>Купить '.$val1['count'].' '.GetIconHTML($val1['icon']).' '.$val1['name'].$costitem.$cost.' <font color="#00ff00">для себя</font></a><br>';
							$klanit.='<a class="btn btn-mini btn-danger" style="margin-top:2px" href="index.php?op=act&n=12&num='.$val['roleindex'].'&artid='.$val1['id'].'&rand='.time().'" onClick="return conf2(this)">Купить '.$val1['count'].' '.GetIconHTML($val1['icon']).' '.' '.$val1['name'].$costitem.$cost.' <font color="#00ffff">для соклана</font></a><br>';
						}
					}					
					$t='<a class="btn btn-mini btn-primary" href="index.php?op=act&n=7&num='.$val['factionid'].'&rand='.time().'" '.$confirm.'">Установить значок '.ShowCost($klancost).'</a><br>'.$klanit;					
					printf('			
					<tr>
						<td><span class="label label-inverse">%s</span></td>
						<td>%s <span class="label label-info">%s</span></td>
						<td>%s</td>
					</tr>',$val['rolename'],'<img src="klan/geticon.php?servid='.$servid.'&klan='.$val['factionid'].'&t='.time().'" align="absmiddle">', $val['factionname'],$t);		
				}
				echo '</tbody></table></div>';
			}
		}
		
?>
	</div><?php //} 		
		if ($_SESSION['isadmin']) { ?>
		<div class="row-fluid">
			<div class="box span12">
				<div class="box-header well" data-original-title>
					<h2>Управление клан-артами <img src="img/admin.png" border=0></h2>
				</div>
				<div class="box-content">
					<table class="table table-bordered table-striped" id="klanarttable">
					<thead>
					<tr>
						<th style="width:50px">ID</th>
						<th style="width:110px">Клан</th>
						<th style="width:150px">ItemID</th>
						<th style="width:50px">Count</th>
						<th style="width:50px">MaxCount</th>
						<th style="width:100px">Octet</th>
						<th style="width:80px">Client size</th>
						<th style="width:80px">Proctype</th>
						<th style="width:80px">Expire</th>
						<th style="width:100px">Стоимость</th>
						<th style="width:100px">Забирать при выходе из клана</th>
						<th style="width:80px">Купили раз</th>
						<th>Описание</th>
					</tr>
					</thead>
					<tfoot>
					<tr>
						<th><input name="search_id" value="ID..." class="search_init" /></th>
						<th><input name="search_userid" id="search_lkanid" value="КланID..." class="search_init" /></th>
						<th><input name="search_itemid" id="search_itemid" value="ItemID..." class="search_init" /></th>
						<th><input name="search_count" id="search_count" value="Count..." class="search_init" /></th>
						<th><input name="search_maxcount" value="MaxCount..." class="search_init" /></th>
						<th><input name="search_octet" id="search_octet" value="Octet..." class="search_init" /></th>
						<th><input name="search_client_size" value="Client_size..." class="search_init" /></th>
						<th><input name="search_proctype" value="Proctype..." class="search_init" /></th>
						<th><input name="search_expire" value="Expire..." class="search_init" /></th>
						<th><input name="search_cost" value="Цена..." class="search_init" /></th>
						<th><input name="search_remove" value="..." class="search_init" /></th>
						<th><input name="search_buycount" value="Покупки..." class="search_init" /></th>
						<th><input name="search_desc" value="Комментарий..." class="search_init" /></th>
					</tr>
					</tfoot>
					</table>

					<center><a href="index.php?op=act&n=54&num=0" class="btn btn-inverse">Добавить новый клан арт</a></center>	
				</div>
			</div>
		</div>
<script type="text/javascript">
var oTable = $('#klanarttable').dataTable( {
	"sDom": '<"row-fluid"<"span10 pagination1"p><"span2"l>><"label"i>tr',
	"iDisplayLength": 25,
	"aLengthMenu": [[15, 25, 50, 75, 100, 200, 500], [15, 25, 50, 75, 100, 200, 500]],	
	"sPaginationType": "bootstrap1",
	"bJQueryUI": true,
	"oLanguage": {
		"sSearch": "Поиск:",
		"sLengthMenu": "_MENU_ записей",
		"sZeroRecords": "Не найдено",
		"sInfo": "Показаны записи _START_-_END_ из _TOTAL_",
		"sInfoEmpty": "Нет данных",
		"oPaginate": {
			"sFirst": "В начало",
			"sLast": "В конец",
			"sNext": "&rarr;",
			"sPrevious": "&larr;"
		},
		"sProcessing": "<center><img src=\"img/ajax-loaders/ajax-loader-1.gif\" border=0> Получение данных с сервера <img src=\"img/ajax-loaders/ajax-loader-1.gif\" border=0></center>",
		"sInfoFiltered": "(обработано _MAX_ записей)"
	},
	"bProcessing": true,
	"bServerSide": true,
	"sAjaxSource": "pages/server_processing.php?klanart",		
	"aaSorting": [[ 0, "desc" ]],
	"fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre )  {
		$('[data-rel="tooltip"]').tooltip({"placement":"bottom",delay: { show: 400, hide: 200 }});
		return "Показаны записи " + iStart + "-" + iEnd + " из " + iTotal;
	}
} );

var CurUserInfoID = 1;

function fnFormatDetails ( nTr )
{
	var aData = oTable.fnGetData( nTr );
	var sOut = '<div id="info_'+CurUserInfoID+'"><center><img src="img/ajax-loaders/ajax-loader-1.gif" border=0> Получение данных с сервера <img src="img/ajax-loaders/ajax-loader-1.gif" border=0></center></div>';
	CurId = '#info_'+CurUserInfoID;
	CurUserInfoID++;
	$.ajax({
	     url: 'pages/server_processing.php?edit_klanart='+aData[13]+'&r='+Math.random(),             // указываем URL и
	     dataType : "text",                     // тип загружаемых данных
	     complete: function (data, textStatus) { // вешаем свой обработчик на complete
		if (textStatus!='success') {
			$(CurId).html(textStatus);
			return;
		}
	        $(CurId).html(data.responseText);
	     }
	});
	return sOut;
}

$('#klanarttable tbody td i').live( 'click', function () {
	var nTr = this.parentNode.parentNode;
	if ( this.className.match('icon-cancel') )
	{
		/* This row is already open - close it */
		this.className = "icon icon-color icon-edit";
		oTable.fnClose( nTr );
	}
	else
	if ( this.className.match('icon-edit') )
	{
		/* Open this row */
		this.className = "icon icon-color icon-cancel";
		oTable.fnOpen( nTr, fnFormatDetails(nTr), 'details' );
	}
} );

var asInitVals = new Array();
	$("tfoot input").keyup( function () {
		oTable.fnFilter( this.value, $("tfoot input").index(this) );
	} );	
	$("tfoot input").each( function (i) {
		asInitVals[i] = this.value;
	} );
	
	$("tfoot input").focus( function () {
		if ( this.className == "search_init" )
		{
			this.className = "search_focus";
			this.value = "";
		}
	} );
	
	$("tfoot input").blur( function (i) {
		if ( this.value == "" )
		{
			this.className = "search_init";
			this.value = asInitVals[$("tfoot input").index(this)];
		}
	} );
function getName(id, id1, id2) { 
	document.getElementById(id1).style.display="";
	document.getElementById(id2).innerHTML='';
	$.ajax({							
		url: "getitemname.php",
		type: "POST",
		data: {id:$("#"+id).val()},
		success: function(msg){
			document.getElementById(id1).style.display="none";	
			A = document.getElementById(id2);
			A.innerHTML = msg;
		}
	});	  		  
}
function getKlanName(id, id1, id2) { 
	document.getElementById(id1).style.display="";
	document.getElementById(id2).innerHTML='';
	$.ajax({							
		url: "getitemname.php",
		type: "POST",
		data: {id:$("#"+id).val(), klan:1},
		success: function(msg){
			document.getElementById(id1).style.display="none";	
			A = document.getElementById(id2);
			A.innerHTML = msg;
		}
	});	  		  
}
function CheckDelete(){
	return window.confirm('Вы действительно хотите удалить этот клан арт?');
}
</script>
		<?php } ?>
		<span class="label label-important">Обратите внимание!</span>
		<ul align="left" type="disc">
		<li>Подать заявку на установку значка клана может только мастер клана.</li>
		<li>Допускаемые форматы изображений для значка клана <b>png, gif, jpg</b>.</li>
		<li>Размер изображения должен быть <b><?=$klan_pic_size?>х<?=$klan_pic_size?> пискелей</b>.</li>
		<li>Размер файла должен быть <b>не более 200 KБайт</b>.</li>
		</ul>