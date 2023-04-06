<?php
	if (!defined('main_def')) die();
	if (isset($_GET['r'])) {
		echo GetErrorTxt($_GET['r']);
	}
	$_SESSION['answer_'] = (isset($_SESSION['answer_']))?$_SESSION['answer_']:'';
?>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header well" data-original-title>
			<h2><i class="icon-edit"></i> Логи авторизации на аккаунт</h2>						
		</div>
		<div class="box-content">
			<table class="table table-bordered table-striped" id="loginlogtable">
			<thead><tr>
				<th style="width:125px">Дата</th>
				<th style="width:70px">IP</th>	
				<th>Описание</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th><input name="search_date" value="Поиск по дате..." class="search_init" /></th>
				<th><input name="search_ip" value="Поиск IP..." class="search_init" /></th>
				<th></th>
			</tr>
			</tfoot>
			</table>
		</div>
	</div>
</div>
<script type="text/javascript">
var Actions = ['Неизвестное действие', 'Вход в игру', 'Выход из игры', 'Вход в ЛК', 'Попытка входа в игру блокирована по IP', 'Попытка входа в ЛК блокирована по IP'];
var oTable = $('#loginlogtable').dataTable( {
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
		"sInfoFiltered": ""
	},
	"bProcessing": true,
	"bServerSide": true,
	"sAjaxSource": "pages/history_process.php?op=loginlog",
	"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
	        nRow.className=aData[3]+' '+nRow.className;
		$('td:eq(2)', nRow).html(Actions[aData[2]]);
	    },
	"aaSorting": [[ 0, "desc" ]]
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
</script>			
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header well" data-original-title>
			<h2><i class="icon-edit"></i> Смена пароля от аккаунта</h2>						
		</div>
		<div class="box-content">
			<form class="form-horizontal" name="chpassform" id="chpassform" method="post" action="index.php?op=act&n=20&num=0">
				<fieldset><?php if ($email_require_change) { ?>
					<label class="control-label1" for="email">E-mail аккаунта</label>
					<div class="input-prepend" title="E-mail указанный при регистрации аккаунта" data-rel="tooltip">
						<span class="add-on"><i class="icon-envelope"></i></span><input id="email" name="email" type="text" value="<?=$_SESSION['email']?>">
					</div><div class="clearfix"></div>	<?php } ?>
					<label class="control-label1" for="curpassw">Текущий пароль</label>
					<div class="input-prepend">
						<span class="add-on"><i class="icon-lock"></i></span><input id="curpassw" name="curpassw" type="password" maxlength="<?=$passw_max_len?>">
					</div><div class="clearfix"></div>					
					<label class="control-label1" for="newpassw">Новый пароль</label>
					<div class="input-prepend">
						<span class="add-on"><i class="icon-lock"></i></span><input id="newpassw" name="newpassw" type="password" maxlength="<?=$passw_max_len?>">
					</div><div class="clearfix"></div>	
					<label class="control-label1" for="newpassw1">Новый пароль ещё раз</label>
					<div class="input-prepend" title="Введите повторно новый пароль для проверки ошибок" data-rel="tooltip">
						<span class="add-on"><i class="icon-lock"></i></span><input id="newpassw1" name="newpassw1" type="password" maxlength="<?=$passw_max_len?>">
					</div><div class="clearfix"></div><?php if ($answer_require_change) { ?>
					<label class="control-label1" for="answer"><font color="#0000ff" title="Этот вопрос был указан при регистрации аккаунта" data-rel="tooltip"><?=$_SESSION['question']?></font></label>
					<div class="input-prepend" title="Ответ на вопрос" data-rel="tooltip">
						<span class="add-on"><i class="icon-question-sign"></i></span><input id="answer" name="answer" type="text" value="<?=$_SESSION['answer_']?>">
					</div><div class="clearfix"></div>	<?php } ?>	

					<div class="form-actions">
						<button type="submit" class="btn btn-primary" id="BSubmit">Продолжить</button>
					</div>					
				</fieldset>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
function confaddip(){
	return window.confirm('Добавить IP/подсеть в список разрешенных?');
}
function confdelip(){
	return window.confirm('Удалить IP/подсеть из списка разрешенных?');
}
function confenbtn(){
	return window.confirm('После активации, зайти на аккаунт в ЛК можно будет только с IP/подсетей, указанных в списке разрешенных. Включить ограничение доступа?');
}
function confenbtn1(){
	return window.confirm('После активации, зайти на аккаунт в игру можно будет только с IP/подсетей, указанных в списке разрешенных. Включить ограничение доступа?');
}
</script>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header well" data-original-title>
			<h2><i class="icon-edit"></i> Безопасность аккаунта</h2>						
		</div>
		<div class="box-content">
			<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert">×</button>Если у Вас изменится IP адрес, его можно будет добавить к разрешенным через <a href="remember.php" target="_blank">восстановление доступа</a></div>
			<p>В данном разделе можно установить ограничение на вход в личный кабинет данного аккаунта только с определенных IP или подсетей.</p>		
			<p>Ваш текущий IP адрес <code><?=$_SERVER['REMOTE_ADDR']?></code></p><p>Если у Вас меняются последние цифры IP адреса (динамический IP), то можете добавить в список разрешенных адресов подсеть, указав только статическую часть IP адреса, включая точку (например <code>192.168.1.</code>)</p><?php
			if (count($_SESSION['ipdata'][1]) > 0 && !$_SESSION['ipdata'][0]) $enbtn = ' <a href="index.php?op=act&n=58&num=0&mode=1" onclick="return confenbtn()" class="btn btn-inverse">Включить ограничение</a>'; else $enbtn = '';
			if ($_SESSION['ipdata'][0]) $enbtn = ' <a href="index.php?op=act&n=58&num=0&mode=0" class="btn btn-inverse">Отключить ограничение</a>';
			if (count($_SESSION['ipdata'][1]) > 0 && !$_SESSION['ipdata'][2]) $enbtn1 = ' <a href="index.php?op=act&n=59&num=0&mode=1" onclick="return confenbtn1()" class="btn btn-inverse">Включить ограничение</a>'; else $enbtn1 = '';
			if ($_SESSION['ipdata'][2]) $enbtn1 = ' <a href="index.php?op=act&n=59&num=0&mode=0" class="btn btn-inverse">Отключить ограничение</a>';
			if (!$_SESSION['ipdata'][0]) echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><strong>На данный момент вход на аккаунт в ЛК не ограничен</strong>'.$enbtn.'</div>'; else echo '<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">×</button><strong>Вход на аккаунт в ЛК ограничен!</strong>'.$enbtn.'</div>';
			if (!$_SESSION['ipdata'][2]) echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><strong>На данный момент вход на аккаунт в игру не ограничен</strong>'.$enbtn1.'</div>'; else echo '<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">×</button><strong>Вход на аккаунт в игру ограничен!</strong>'.$enbtn1.'</div>';
			echo '<p>Список разрешенных IP/подсетей: ';
			if (count($_SESSION['ipdata'][1]) > 0)
			foreach ($_SESSION['ipdata'][1] as $i => $val){
				printf('<code>%s<a href="index.php?op=act&n=57&num=0&i=%d" data-rel="tooltip" data-original-title="Удалить запись" onclick="return confdelip()"><i class="icon icon-color icon-trash"></i></a></code> ', $val, $i);
			} else echo '<code>нет записей</code>';
			echo '</p>';			
			?><center>
			<form name="addip" action="index.php" method="get">
			<input type="hidden" name="op" value="act">
			<input type="hidden" name="num" value="0">
			<input type="hidden" name="n" value="56">
			Введите IP адрес или его часть: <input name="addip" type="text" value="<?=$_SERVER['REMOTE_ADDR']?>">
			<input type="submit" name="sendaddip" value="Добавить в список разрешенных" class="btn btn-danger" onclick="return confaddip()"> 
			</form>
			</center><br>
			<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">×</button>
				<strong>Обратите внимание!</strong> Активация ограничения сделает невозможным вход в ЛК с других адресов, восстановить доступ при смене IP адреса можно будет только имея доступ к почте, на которую был зарегистрирован аккаунт!
			</div>
		</div>
	</div>
</div>