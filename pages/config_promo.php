<?php
if (!defined('main_def')) die();
?>
<style>
.userinfo_table {
  background-color: inherit;
}
.userinfo_table p {
  padding: 0px;
  line-height: 16px;
  margin: 0px;
}
</style>
<table class="table table-bordered table-striped" id="promo_table">
	<thead>
		<tr>
			<th style="width:50px">ID</th>
			<th>Код</th>
			<th style="width:120px">Срок годности</th>
			<th style="width:100px">Группа</th>
			<th style="width:70px"><span class="gold gold_dark">Money</span></th>
			<th style="width:70px"><span class="silver gold_silver">Money</span></th>
			<th style="width:150px">Item</th>
			<th style="width:80px">Многоразовый</th>
			<th style="width:80px">Used UserID UsedCount</th>
			<th>Описание</th>
		</tr>
	</thead>
	<tfoot id="promo_foot">
		<tr>
			<th><input name="search_id" value="ID..." class="search_init" /></th>
			<th><input name="search_code" value="Код..." class="search_init" /></th>
			<th><input name="search_data" value="Дата..." class="search_init" /></th>
			<th><input name="search_group" value="Группа..." class="search_init" /></th>
			<th><input name="search_gold" value="ЛК голд..." class="search_init" /></th>
			<th><input name="search_silver" value="ЛК серебро..." class="search_init" /></th>
			<th><input name="search_itemid" value="ItemID..." class="search_init" /></th>
			<th><input name="search_multiuser" value="MultiUser..." class="search_init" /></th>
			<th><input name="search_userid" id="promo_userid" value="UserID..." class="search_init" /></th>
			<th><input name="search_desc" value="Описание..." class="search_init" /></th>
		</tr>
	</tfoot>
</table>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header well"><h2>Генератор промо-кодов</h2></div>
		<div class="box-content">	
			<form method="post" name="promo_frm" action="index.php?op=act&n=68&num=0" id="promo_gen">
			<table class="table table-bordered table-striped">
			<tr>
				<td><h3>Количество</h3></td>
				<td><input type="text" name="promo_count" value="1"></td>
				<td style="text-align:left"><code>Количество кодов для генерации</code></td>
			</tr>
			<tr>
				<td><h3>Срок годности</h3></td>
				<td><input type="text" name="promo_expire" value="0"></td>
				<td style="text-align:left"><code>Срок действия кода в секундах (начиная со времени генерации)</code><br><code>Если 0 - без срока действия</code></td>
			</tr>
			<tr>
				<td><h3>Группа кодов</h3></td>
				<td><input type="text" name="promo_group" value="0"></td>
				<td style="text-align:left"><code>0 - без группы и ограничений использования</code><br><code>Используется для объединения кодов в группы</code><br><code>Для кодов из одной группы действует ограничение использования не более одного кода из группы на аккаунт</code><br><code>Обратите внимание! Номер новой группы всегда делайте уникальным, который не использовался даже на ранее удаленных кодах</code></td>
			</tr>	
			<tr>
				<td><h3>Бонус ЛК</h3></td>
				<td><span class="gold"> <input type="text" name="promo_gold" value="0" class="config_cost"></span> <span class="silver"> <input type="text" name="promo_silver" value="0" class="config_cost"></span></td>
				<td style="text-align:left"><code>Награда за код в ЛК монетах</code></td>
			</tr>
			<tr>
				<td><h3>Бонус предмет</h3></td>
				<td><input type="text" id="promo_item_id" name="promo_item_id" value="0" class="config_itemid" onchange="getName('promo_item_id', 'promo_loaderid', 'promo_paramid');"> <span style="display:none" id="promo_loaderid"><img src="img/ajax-loaders/ajax-loader-1.gif" border="0" align="absmiddle"/></span> <span id="promo_paramid"></span></td>
				<td style="text-align:left"><code>ID предмета награды за код</code></td>
			</tr>
			<tr>
				<td><h3>Count</h3></td>
				<td><input type="text" name="promo_item_count" value="0"></td>
				<td style="text-align:left"><code>Количество предметов</code></td>
			</tr>
			<tr>
				<td><h3>Max count</h3></td>
				<td><input type="text" name="promo_item_maxcount" value="0"></td>
				<td style="text-align:left"><code>Максимальное количество в ячейке</code></td>
			</tr>
			<tr>
				<td><h3>Data</h3></td>
				<td><input type="text" class="span12" name="promo_item_data" value=""></td>
				<td style="text-align:left"><code>Октет предмета</code></td>
			</tr>
			<tr>
				<td><h3>Client size</h3></td>
				<td><input type="text" name="promo_item_client_size" value="0"></td>
				<td style="text-align:left"></td>
			</tr>
			<tr>
				<td><h3>Proctype</h3></td>
				<td><input type="text" name="promo_item_proctype" value="0" onkeyup="fillproctype(this.value)" onfocus="showproctype(this);"></td>
				<td style="text-align:left"><code>Привязка предмета</code></td>
			</tr>	
			<tr>
				<td><h3>Expire</h3></td>
				<td><input type="text" name="promo_item_expire" value="0"></td>
				<td style="text-align:left"><code>Срок действия предмета в секундах</code><br><code>Если 0 - без срока действия</code></td>
			</tr>
			<tr>
				<td><h3>Многоразовый</h3></td>
				<td><center><input name="promo_multi_user" type="checkbox" data-no-uniform="true" class="iphone-toggle"></center></td>
				<td style="text-align:left"><code>Если включено - код может быть использован несколькими игроками (не более одного использования на аккаунт)</code><br><code>а в поле Used UserID будет записываться количество использований, а не ID аккаунта</code></td>
			</tr>
			<tr>
				<td><h3>Desc</h3></td>
				<td><center><textarea name="promo_desc"></textarea></center></td>
				<td style="text-align:left"><code>Описание для администрации</code></td>
			</tr>					
			<tr>
				<td colspan="3"><center><br><span id="genbut"><input type="button" onclick="GeneratePromo()" class="btn btn-large btn-primary" value="Сгенерировать промо-коды"><br></span><span id="genprogress" style="display:none"><img src="img/ajax-loaders/ajax-loader-1.gif" border="0" align="absmiddle"/> Генерируем промо коды <img src="img/ajax-loaders/ajax-loader-1.gif" border="0" align="absmiddle"/></span><br></center></td>
			</tr>
			</table>
			</form>
		</div>
	</div>
</div>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header well"><h2>Удаление промо-кодов</h2></div>
		<div class="box-content">
			<center><br><span id="delbut"><input type="button" onclick="DelTimeoutPromo()" class="btn btn-large btn-danger" value="Удалить просроченные, но не иcпользованные промо-коды"> <input type="button" onclick="DelUsedPromo()" class="btn btn-large btn-danger" value="Удалить ипользованные промо-коды"><br></span><span id="delprogress" style="display:none"><img src="img/ajax-loaders/ajax-loader-1.gif" border="0" align="absmiddle"/> Удаляем промо коды <img src="img/ajax-loaders/ajax-loader-1.gif" border="0" align="absmiddle"/></span><br></center>
		</div>
	</div>
</div>	
<script type="text/javascript">
var timeout_id = 0;

function GeneratePromo(){
	var f = document.getElementById("promo_gen");	
	var formData = new FormData(f);	
	var xhr = new XMLHttpRequest();
	$('#genbut').css('display' , 'none');
	$('#genprogress').css('display', 'inherit');
	//f.parentNode.removeChild(f);
	xhr.open("POST", f.action);
	xhr.onreadystatechange = function() {
		if (xhr.readyState == 4) {				
			if(xhr.status == 200) {				
				$('#genprogress').css('display' , 'none');
				$('#genbut').css('display', 'inherit');
				var txt = "error"; var txt1 = xhr.responseText;
				if (txt1 == "ok") {
					txt = "success";
					txt1 = "Промо коды успешно сгенерированы";
				}
				noty({"text":txt1,"layout":"top","type":txt});					
			}			
			oTable_promo._fnAjaxUpdate();
		}
	};
	xhr.send(formData);
}

var oTable_promo = $('#promo_table').dataTable( {
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
	"sAjaxSource": "pages/server_processing.php?promo_codes",		
	"aaSorting": [[ 0, "desc" ]],
	"fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre )  {
		$('[data-rel="tooltip"]').tooltip({"placement":"bottom",delay: { show: 400, hide: 200 }});
		return "Показаны записи " + iStart + "-" + iEnd + " из " + iTotal;
	},
	"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {		
		if (aData[10]!='') nRow.className=aData[10]+' '+nRow.className;		
	}
} );

var CurUserInfoPromoID = 1;

function EditPromoRecord ( nTr )
{
	var aData = oTable_promo.fnGetData( nTr );
	var sOut = '<div id="info_'+CurUserInfoPromoID+'"><center><img src="img/ajax-loaders/ajax-loader-1.gif" border=0> Получение данных с сервера <img src="img/ajax-loaders/ajax-loader-1.gif" border=0></center></div>';
	CurId = '#info_'+CurUserInfoPromoID;
	CurUserInfoPromoID++;
	$.ajax({
	     url: 'pages/server_processing.php?edit_promo_code='+aData[11]+'&r='+Math.random(),             // указываем URL и
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

$('#promo_table tbody td i').live( 'click', function () {
	var nTr = this.parentNode.parentNode;
	if ( this.className.match('icon-cancel') )
	{
		/* This row is already open - close it */
		this.className = "icon icon-color icon-edit";
		oTable_promo.fnClose( nTr );
	}
	else
	if ( this.className.match('icon-edit') )
	{
		/* Open this row */
		this.className = "icon icon-color icon-cancel";
		oTable_promo.fnOpen( nTr, EditPromoRecord(nTr), 'details' );
	}
} );

function PromoShowUser ( nTr )
{
	var aData = oTable_promo.fnGetData( nTr );
	var sOut = '<div id="info_'+CurUserInfoPromoID+'" class="userinfo"><center><img src="img/ajax-loaders/ajax-loader-1.gif" border=0> Получение данных с сервера <img src="img/ajax-loaders/ajax-loader-1.gif" border=0></center></div>';
	CurId = '#info_'+CurUserInfoPromoID;
	CurUserInfoPromoID++;
	$.ajax({	     
	     url: 'pages/server_processing.php?id='+aData[12]+'&r='+Math.random(),             // указываем URL и
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

$('#promo_table tbody td img').live( 'click', function () {
	var nTr = this.parentNode.parentNode;
	if ( this.src.match('details_close') )
	{
		/* This row is already open - close it */
		this.src = "img/details_open.png";
		oTable_promo.fnClose( nTr );
	}
	else
	if ( this.src.match('details_open') )
	{
		/* Open this row */
		this.src = "img/details_close.png";
		oTable_promo.fnOpen( nTr, PromoShowUser(nTr), 'details' );
	}
} );

var asInitVals_promo = new Array();
	$("#promo_foot input").keyup( function () {
		oTable_promo.fnFilter( this.value, $("#promo_foot input").index(this) );
	} );	
	$("#promo_foot input").each( function (i) {
		asInitVals_promo[i] = this.value;
	} );
	
	$("#promo_foot input").focus( function () {
		if ( this.className == "search_init" )
		{
			this.className = "search_focus";
			this.value = "";
		}
	} );
	
	$("#promo_foot input").blur( function (i) {
		if ( this.value == "" )
		{
			this.className = "search_init";
			this.value = asInitVals_promo[$("#promo_foot input").index(this)];
		}
	} );
function DelTimeoutPromo(n){
	if (!window.confirm('Вы действительно хотите удалить просроченные и не использованные промо коды из базы?')) return;
	$('#delbut').css('display' , 'none');
	$('#delprogress').css('display', 'inherit');
	var X = new XMLHttpRequest();
	X.open("POST", "index.php?op=act&n=71&num=0");
	X.onreadystatechange = function() {
		if (X.readyState == 4) {				
			if(X.status == 200) {
				$('#delprogress').css('display' , 'none');
				$('#delbut').css('display', 'inherit');
				var txt = "error"; var txt1 = X.responseText;
				if (txt1 == "16") {
					txt = "success";
					txt1 = "Записи успешно удалены";
				}
				noty({"text":txt1,"layout":"top","type":txt});
			}
			oTable_promo._fnAjaxUpdate();
		}
	};
	X.send();
}
function DelUsedPromo(n){
	if (!window.confirm('Вы действительно хотите удалить использованные промо коды из базы?')) return;
	$('#delbut').css('display' , 'none');
	$('#delprogress').css('display', 'inherit');
	var X = new XMLHttpRequest();
	X.open("POST", "index.php?op=act&n=72&num=0");
	X.onreadystatechange = function() {
		if (X.readyState == 4) {				
			if(X.status == 200) {
				$('#delprogress').css('display' , 'none');
				$('#delbut').css('display', 'inherit');
				var txt = "error"; var txt1 = X.responseText;
				if (txt1 == "16") {
					txt = "success";
					txt1 = "Записи успешно удалены";
				}
				noty({"text":txt1,"layout":"top","type":txt});
			}
			oTable_promo._fnAjaxUpdate();
		}
	};
	X.send();
}
function CheckDeletePromo(n){
	if (!window.confirm('Вы действительно хотите удалить этот промо-код из базы данных?')) return;
	var X = new XMLHttpRequest();
	X.open("POST", "index.php?op=act&n=70&num=0&record_id="+n);
	X.onreadystatechange = function() {
		if (X.readyState == 4) {				
			if(X.status == 200) {
				var txt = "error"; var txt1 = X.responseText;
				if (txt1 == "16") {
					txt = "success";
					txt1 = "Запись успешно удалена";
				}
				noty({"text":txt1,"layout":"top","type":txt});
			}
			oTable_promo._fnAjaxUpdate();
		}
	};
	X.send();

}
function fnuserid(s) {
	$('#promo_userid').val(s);
	$('#promo_userid').addClass("search_focus");
	$('#promo_userid').keyup();
}

</script>		
<span class="label label-important">Обратите внимание!</span>
<ul align="left" type="disc">
	<li>после генерации кодов, на сервер сайде будет создан текстовый файл со списком сгенерированных кодов</li>
</ul>