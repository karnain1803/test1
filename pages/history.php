<?php
	if (!defined('main_def')) die();
?>
<table class="table table-bordered table-striped" id="historytable">
<thead><tr>
	<th style="width:80px">ID</th>
	<th style="width:125px">Дата</th>
	<th style="width:70px">IP</th>	
	<th style="width:90px">Стоимость</th>
	<th style="width:100px">Остаток</th>
	<th>Описание</th>
</tr>
</thead>
<tfoot>
<tr>
	<th><input name="search_id" value="Поиск ID..." class="search_init" /></th>
	<th><input name="search_date" value="Поиск по дате..." class="search_init" /></th>
	<th><input name="search_ip" value="Поиск IP..." class="search_init" /></th>
	<th><input name="search_cost" value="Поиск цены..." class="search_init" /></th>
	<th><input name="search_rest" value="Поиск остатка..." class="search_init" /></th>
	<th><input name="search_desc" value="Поиск комментария..." class="search_init" /></th>
</tr>
</tfoot>
</table>
<script type="text/javascript">

var oTable = $('#historytable').dataTable( {
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
	"sAjaxSource": "pages/history_process.php?op=history",
	"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
	        nRow.className=aData[6]+' '+nRow.className;
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