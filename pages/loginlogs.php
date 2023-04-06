<?php
if (!defined('main_def')) die();
?>
<table class="table table-bordered table-striped" id="loginlogtable">
<thead><tr>
	<th style="width:80px">ID</th>
	<th style="width:125px">Дата</th>
	<th style="width:100px">IP</th>	
	<th style="width:100px">UserID</th>
	<th style="width:120px">Login</th>
	<th style="width:90px">№ счета</th>
	<th>Описание</th>
</tr>
</thead>
<tfoot>
<tr>
	<th><input name="search_id" value="Поиск ID..." class="search_init" /></th>
	<th><input name="search_date" value="Поиск по дате..." class="search_init" /></th>
	<th><input name="search_ip" id="search_ip" value="Поиск IP..." class="search_init" /></th>
	<th><input name="search_userid" id="search_userid" value="Поиск UserID..." class="search_init" /></th>
	<th><input name="search_login" id="search_login" value="Поиск Login..." class="search_init" /></th>
	<th><input name="search_lkid" id="search_lkid" value="Поиск № ЛК..." class="search_init" /></th>
	<th></th>
</tr>
</tfoot>
</table>
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
		"sInfoFiltered": "(обработано _MAX_ записей)"
	},
	"bProcessing": true,
	"bServerSide": true,
	"sAjaxSource": "pages/server_processing.php?loginlog",
	"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
	        nRow.className=aData[7]+' '+nRow.className;
		$('td:eq(6)', nRow).html(Actions[aData[6]]);
	    },
	"aaSorting": [[ 0, "desc" ]]
} );

var CurUserInfoID = 1;

/* Formating function for row details */
function fnFormatDetails ( nTr )
{
	var aData = oTable.fnGetData( nTr );
	var sOut = '<div id="info_'+CurUserInfoID+'" class="userinfo"><center><img src="img/ajax-loaders/ajax-loader-1.gif" border=0> Получение данных с сервера <img src="img/ajax-loaders/ajax-loader-1.gif" border=0></center></div>';
	CurId = '#info_'+CurUserInfoID;
	CurUserInfoID++;
	$.ajax({
	     url: 'pages/server_processing.php?id='+aData[8]+'&r='+Math.random(),             // указываем URL и
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

	$('#loginlogtable tbody td img').live( 'click', function () {
		var nTr = this.parentNode.parentNode;
		if ( this.src.match('details_close') )
		{
			/* This row is already open - close it */
			this.src = "img/details_open.png";
			oTable.fnClose( nTr );
		}
		else
		{
			/* Open this row */
			this.src = "img/details_close.png";
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
	function fnuser(s) {
		$('#search_userid').val(s);
		$('#search_userid').addClass("search_focus");
		$('#search_userid').keyup();
	}
	function fnlogin(s) {
		$('#search_login').val(s);
		$('#search_login').addClass("search_focus");
		$('#search_login').keyup();
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
</script>	