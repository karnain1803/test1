<?php
if (!defined('main_def')) die();
?>
<div class="row-fluid">
	<div class="box span6">
		<div class="box-content">
			Поиск по ID персонажа <input type="number" id="role_id_edit"> <span class="btn btn-small btn-inverse" onclick="FindRoleID()">Искать</span> <span id="role_id_result"></span>
		</div>
	</div>
	<div class="box span6">
		<div class="box-content">
			Поиск по нику персонажа <input type="text" id="role_name_edit"> <span class="btn btn-small btn-primary" onclick="FindRoleName()">Искать</span> <span id="role_name_result"></span>
		</div>
	</div>
</div>
<table class="table table-bordered table-striped" id="acctable">
<thead>
<tr>
	<th style="width:50px">ID</th>
	<th style="width:140px">Login</th>
	<th style="width:50px">№ счета</th>
	<th style="width:130px">VK</th>
	<th style="width:170px">Steam</th>
	<th style="width:90px">Имя</th>
	<th style="width:120px">E-Mail</th>
	<th style="width:90px">IP</th>
	<th style="width:70px">Дата реги</th>
	<th style="width:70px"><span class="gold">&nbsp;</span></th>
	<th style="width:70px"><span class="silver">&nbsp;</span></th>
	<th style="width:80px">Referal</th>
	<th style="width:30px">Ref. stat</th>
	<th style="width:50px"><span class="gold">Ref. bonus</span></th>
	<th>Bonus Data</th>
</tr>
</thead>
<tfoot>
<tr>
	<th><input name="search_userid" id="search_userid" value="Поиск ID..." class="search_init" /></th>
	<th><input name="search_login" id="search_login" value="Поиск Login..." class="search_init" /></th>
	<th><input name="search_lkid" id="search_lkid" value="Поиск №..." class="search_init" /></th>
	<th><input name="search_vk" id="search_quest" value="Поиск VK ID..." class="search_init" /></th>
	<th><input name="search_steam" id="search_answ" value="Поиск SteamID..." class="search_init" /></th>
	<th><input name="search_name" id="search_name" value="Поиск Имени..." class="search_init" /></th>
	<th><input name="search_email" id="search_email" value="Поиск Email..." class="search_init" /></th>
	<th><input name="search_ip" id="search_ip" value="Поиск IP..." class="search_init" /></th>
	<th><input name="search_date" value="Поиск по дате..." class="search_init" /></th>	
	<th><input name="search_cost_gold" value="Голд..." class="search_init" /></th>
	<th><input name="search_cost_silver" value="Серебро..." class="search_init" /></th>
	<th><input name="search_referal" value="Реферал..." class="search_init" /></th>
	<th><input name="search_referal" value="Статус..." class="search_init" /></th>
	<th><input name="search_referal" value="Бонус..." class="search_init" /></th>
	
	<th></th>
</tr>
</tfoot>
</table>
<style type="text/css">
.popup {width: 98%}
.edit_role_form {height: 98%}
.modal-body {overflow-x: hidden;}
.tab-pane {height: 98%}
.edit_role_form textarea {width:98%; height: 98%}
.form-horizontal .control-label {width: 150px; margin-right: 5px}
.form-horizontal .input-prepend {margin-bottom: 3px}
.form-horizontal div.checker {margin-top:5px; float: left}
.form-horizontal .form-actions {padding-left:20px}
.role_edit, .role_ban {cursor:pointer; vertical-align: sub;}
.alert {margin-bottom: 2px}
</style>
<script type="text/javascript">
var NeedOpen = false;
var CurEditRoleID = 0;
var oTable = $('#acctable').dataTable( {
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
	"sAjaxSource": "pages/server_processing.php?accmanage",
	"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
		if (aData[15]!='') nRow.className=aData[15]+' '+nRow.className;		
	},
	"aaSorting": [[ 0, "desc" ]]
} );

var CurUserInfoID = 1;
var sOut = '<div class="userinfo"><center><img src="img/ajax-loaders/ajax-loader-1.gif" border=0> Получение данных с сервера <img src="img/ajax-loaders/ajax-loader-1.gif" border=0></center></div>';

function FindRoleID()
{
	roleid = $('#role_id_edit').val();
	if (roleid == '') return;
	$('#role_id_result').html(sOut);
	$('#role_id_result').css({'display' : 'inline', 'margin-top' : '3px'});
	$.ajax({
	     url: 'pages/server_processing.php?rid2uid='+roleid+'&r='+Math.random(),             // указываем URL и
	     dataType : "text",                     // тип загружаемых данных
	     complete: function (data, textStatus) { // вешаем свой обработчик на complete
		if (textStatus!='success') {
			$('role_id_result').html(textStatus);
			return;
		}
		if (data.responseText == 0) data.responseText = 'Аккаунт не найден'; else
		{
			NeedOpen = true;
			fnuser(data.responseText);
			data.responseText = 'UserID: ' + data.responseText;
		}
	        $('#role_id_result').html(data.responseText);
	     }
	});
}

function FindRoleName()
{
	rolename = $('#role_name_edit').val();
	if (rolename == '') return;
	$('#role_name_result').html(sOut);
	$('#role_name_result').css({'display' : 'inline', 'margin-top' : '3px'});
	$.ajax({
	     url: 'pages/server_processing.php?findrolename='+rolename+'&r='+Math.random(),             // указываем URL и
	     dataType : "json",                     // тип загружаемых данных
	     success: function (data) { // вешаем свой обработчик на complete
		var answ_text = '';
		if (data.roleid == 0) answ_text = 'Персонаж не найден'; else
		{
			NeedOpen = true;
			fnuser(data.userid);
			answ_text = 'RoleID: ' + data.roleid + ', UserID: ' + data.userid;
		}
	        $('#role_name_result').html(answ_text);
	     },
	     error: function() {
		$('role_name_result').html('Произошла ошибка! Попробуйте еще раз')
	     }
	});
}

function act_acc(id)
{	
	if (!window.confirm('Вы действительно хотите активировать аккаунт '+id+'?')) return;
	var X = new XMLHttpRequest();
	X.open("POST", "index.php?op=act&n=77&num=0&userid="+id);
	X.onreadystatechange = function() {
		if (X.readyState == 4) {				
			if(X.status == 200) {
				var txt = "error"; var txt1 = X.responseText;
				if (txt1 == "ok") {
					txt = "success";
					txt1 = "Аккаунт успешно активирован";
				}
				noty({"text":txt1,"layout":"top","type":txt});				
			}
			oTable._fnAjaxUpdate();
		}
	};
	X.send();
}

/* Formating function for row details */
function fnFormatDetails ( nTr )
{
	var aData = oTable.fnGetData( nTr );
	var sOut = '<div id="info_'+CurUserInfoID+'" class="userinfo"><center><img src="img/ajax-loaders/ajax-loader-1.gif" border=0> Получение данных с сервера <img src="img/ajax-loaders/ajax-loader-1.gif" border=0></center></div>';
	CurId = '#info_'+CurUserInfoID;
	CurUserInfoID++;
	$.ajax({
	     url: 'pages/server_processing.php?id='+aData[16]+'&r='+Math.random(),             // указываем URL и
	     dataType : "text",                     // тип загружаемых данных
	     complete: function (data, textStatus) { // вешаем свой обработчик на complete
		if (textStatus!='success') {
			$(CurId).html(textStatus);
			return;
		}
	        $(CurId).html(data.responseText);
		$('[data-rel="tooltip"]').tooltip({"placement":"top",delay: { show: 50, hide: 50 }});
	     }
	});
	return sOut;
}

	$('#acctable tbody td img').live( 'click', function () {
		var nTr = this.parentNode.parentNode;
		if ( this.src.match('details_close') )
		{
			/* This row is already open - close it */
			this.src = "img/details_open.png";
			oTable.fnClose( nTr );
		}
		else
		if ( this.src.match('details_open') )
		{
			/* Open this row */
			this.src = "img/details_close.png";
			oTable.fnOpen( nTr, fnFormatDetails(nTr), 'details' );
		}
	} );
	
	function OpenRow()
	{
		$('#acctable tbody td img').click();
	}

	var asInitVals = new Array();
	$("tfoot input").keyup( function () {
		oTable.fnFilter( this.value, $("tfoot input").index(this) );
		if (NeedOpen)
		{
			NeedOpen = false;
			setTimeout(OpenRow, 700);
		}
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

	function fnuser(s) {
		$('#search_userid').val(s);
		$('#search_userid').addClass("search_focus");
		$('#search_userid').keyup();
	}
	function fnemail(s) {
		$('#search_email').val(s);
		$('#search_email').addClass("search_focus");
		$('#search_email').keyup();
	}
	function fnlkid(s) {
		$('#search_lkid').val(s);
		$('#search_lkid').addClass("search_focus");
		$('#search_lkid').keyup();
	}
	function fnip(s) {
		$('#search_ip').val(s);
		$('#search_ip').addClass("search_focus");
		$('#search_ip').keyup();
	}

function BanRole(roleid, rolename)
{
	$('#process_ban_btn').html("Выдать бан");
	$('#process_ban_btn').prop( "disabled", false );
	$('#modalBody_edit_ban_res').html('');
	$('html').css('overflow', 'hidden');
	$('body').css('overflow', 'hidden');
	$('.modal-body').css('max-height', document.body.clientHeight - 200);
	$('#ban_roleid').val(roleid);
	$('#ban_rolename').val(rolename);
	$('#pop_role_id').html(roleid);
	$('#pop_role_name').html(rolename);
	$('.popup').css({'height':280, 'width': 410 });
	p = $('#ban_overlay');
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
}

function ModalLoad(a = ''){
	var LoadTxt = '<center><img src="img/ajax-loaders/ajax-loader-1.gif" border=0> Ожидание ответа сервера <img src="img/ajax-loaders/ajax-loader-1.gif" border=0></center>';
	$('#modalBody_edit'+a).html(LoadTxt);
}

function ProcessBan()
{
	if (!window.confirm('Забанить игрока?')) return false;	
	var f = $("#ban_form");	
	var formData = new FormData(f[0]);
	ModalLoad('_ban_res');
	$.ajax({
	     url: 'pages/server_processing.php?banrole&r='+Math.random(),             // указываем URL и
	     data: formData,
             processData: false,
	     contentType: false,
	     type: 'POST',
	     success: function (data) {	
		$('#modalBody_edit_ban_res').html(data);
	     },
	     error: function() {
		$('#modalBody_edit_ban_res').html('Произошла ошибка! Попробуйте еще раз')
	     }
	});
}

function SaveRole()
{
	if (!window.confirm('Сохранить изменения?')) return false;	
	var f = $("#edit_role_form");	
	var formData = new FormData(f[0]);
	ModalLoad('_res');
	$.ajax({
	     url: 'pages/server_processing.php?saveeditrole='+CurEditRoleID+'&r='+Math.random(),             // указываем URL и
	     data: formData,
             processData: false,
	     contentType: false,
	     type: 'POST',
	     success: function (data) { 	
		$('#modalBody_edit_res').html(data);
	     },
	     error: function() {
		$('#modalBody_edit_res').html('Произошла ошибка! Попробуйте еще раз')
	     }
	});
}

function EditRole(roleid, rolename){
	CurEditRoleID = roleid;
	$('#modalBody_edit_res').html('');
	$('html').css('overflow', 'hidden');
	$('body').css('overflow', 'hidden');
	$('.modal-body').css('max-height', screen.height - 50);
	p = $('#edit_overlay');
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
	$('#modalHeader_edit').html(rolename+', ID: ' + roleid);
	ModalLoad();	
	$.ajax({
	     url: 'pages/server_processing.php?editrole='+roleid+'&r='+Math.random(),             // указываем URL и
	     dataType : "json",                     // тип загружаемых данных
	     success: function (data) { // вешаем свой обработчик на complete
		if (data.roleid == 0) data.content = 'Персонаж не найден';
		$('#modalBody_edit').html(data.content);
		$('.popup').css({'height':document.documentElement.clientHeight - 65, 'width': '98%' });
		$('.edit_role_form').css({'height':document.documentElement.clientHeight - 95 });
		$('#EditTab a:first').tab('show');
		$('#EditTab a').click(function (e) {
		  e.preventDefault();
		  $(this).tab('show');
		});
	     },
	     error: function() {
		$('#modalBody_edit').html('Произошла ошибка! Попробуйте еще раз')
	     }
	});	
}

</script>

<div class="popup__overlay" id="edit_overlay">
    <div class="popup">
        <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3>Редактирование игрока <span id="modalHeader_edit"></span></h3>
		<div id="modalBody_edit_res"></div>
	</div>
	<div class="modal-body">		
		<div id="modalBody_edit"></div>
	</div>	
    </div>
</div>

<div class="popup__overlay" id="ban_overlay">
    <div class="popup">
        <div class="modal-header">
		<button type="button" class="close" id="ban_close" data-dismiss="modal">×</button>
		<h3 id="modalHeader_ban">Бан игрока <font color="#ff0000"><span id="pop_role_id"></span></font> <font color="#0000ff"><span id="pop_role_name"></span></font></h3>
		<div id="modalBody_edit_ban_res"></div>
	</div>
	<div class="modal-body_ban">
		<form class="form-horizontal" name="ban_form" id="ban_form" method="post">
			<input type="hidden" name="roleid" id="ban_roleid">
			<input type="hidden" name="rolename" id="ban_rolename">
			<fieldset>
				<label class="control-label" for="type">Тип бана</label>
				<div class="input-prepend">
					<span class="add-on"><i class="icon-lock"></i></span><select name="type" type="text"><option value="chat">Бан чата</option><option value="role">Бан персонажа</option></select>
				</div><div class="clearfix"></div>

				<label class="control-label" for="time">Время бана</label>
					<div class="input-prepend">
						<span class="add-on"><i class="icon-time"></i></span><select name="time" type="text"><option value=900>15 минут</option><option value=1800>30 минут</option><option value=3600>1 час</option><option value=10800>3 часа</option><option value=43200>12 часов</option><option value=86400>1 день</option><option value=172800>2 дня</option><option value=259200>3 дня</option><option value=345600>4 дня</option><option value=432000>5 дней</option><option value=604800>1 неделя</option><option value=907200>1,5 недели</option><option value=1209600>2 недели</option><option value=2592000>1 месяц</option><option value=99999999>Перманент</option><option value=1>Разбан (1 сек)</option></select>
					</div><div class="clearfix"></div>					
					<label class="control-label" for="reason">Причина</label>
					<div class="input-prepend">
						<span class="add-on"><i class="icon-envelope"></i></span><input name="reason" type="text" maxlength="20">
					</div><div class="clearfix"></div>
					<label class="control-label" for="broadcast">Оповещение в мир</label>
					<input name="broadcast" type="checkbox">
					<div class="clearfix"></div>
					<div class="form-actions">
						<span class="btn btn-primary" id="process_ban_btn" onclick="return ProcessBan()"></span>
					</div>					
				</fieldset>
			</form>
	</div>	
    </div>
</div>