<?php
if (!defined('main_def')) die();
$stat = $_SESSION['cur_top'];
function my_bcmod( $x, $y ) 
{ 
    // how many numbers to take at once? carefull not to exceed (int) 
    $take = 5;     
    $mod = ''; 

    do 
    { 
        $a = (int)$mod.substr( $x, 0, $take ); 
        $x = substr( $x, $take ); 
        $mod = $a % $y;    
    } 
    while ( strlen($x) ); 

    return (int)$mod; 
} 
if (!function_exists('bcdiv')) {
  function bcdiv($_ro, $_lo, $_scale=0) {
    return round($_ro/$_lo, $_scale);
  }
}
function dec2hex($number)
 {
     $hexvalues = array('0','1','2','3','4','5','6','7',
                '8','9','A','B','C','D','E','F');
     $hexval = '';
      while($number != '0')
      {
         $hexval = $hexvalues[my_bcmod($number,'16')].$hexval;
         $number = bcdiv($number,'16',0);
     }
	if ($hexval == '') $hexval = '00';
     return $hexval;
 }
function CheckSum(&$sum, $value){
	
	if ($sum<$value) $sum = sprintf('<font color="#ff0000"><b>%s</b></font>', $sum); else
	{
		$del = floor($sum/$value);
		$col = $del * 0x15;
		if ($col>0xb0) $col = 0xb0;
		$col1 = 0xb0 - $col;
		$sum = sprintf('<font color="#00%s%s"><b>%s</b></font>', dec2hex($col), dec2hex($col1), $sum);
	}
}
?>
<script language="javascript" type="text/javascript" src="js/jquery.flot.categories.min.js"></script>
<table class="table table-bordered table-striped" id="logtable">
<thead>
<tr>
	<th style="width:100px">ID</th>
	<th style="width:100px">VoteID</th>
	<th style="width:120px">Дата</th>
	<th style="width:100px">IP</th>
	<th style="width:120px">VoteName</th>
	<th>Login</th>
	<th style="width:100px">UserID</th>
	<th style="width:90px">№ счета</th>
	<th style="width:90px">Тип</th>
	<th style="width:90px">Монеты</th>
	<th style="width:90px">Предмет</th>
	<th style="width:100px">Статус</th>
</tr>
</thead>
<tfoot>
<tr>
	<th><input name="search_id" value="Поиск ID..." class="search_init" /></th>
	<th><input name="search_voteid" value="Поиск VoteID..." class="search_init" /></th>
	<th><input name="search_date" value="Поиск по дате..." class="search_init" /></th>
	<th><input name="search_ip" id="search_ip" value="Поиск IP..." class="search_init" /></th>
	<th><input name="search_name" id="search_name" value="Поиск VoteName..." class="search_init" /></th>
	<th><input name="search_login" id="search_login" value="Поиск Login..." class="search_init" /></th>
	<th><input name="search_userid" id="search_userid" value="Поиск UserID..." class="search_init" /></th>
	<th><input name="search_lkid" id="search_lkid" value="Поиск № ЛК..." class="search_init" /></th>
	<th></th>
	<th></th>
	<th></th>
	<th></th>
</tr>
</tfoot>
</table>
<script type="text/javascript">

var VoteStatus = ['User not found', 'Ok', 'Limit IP', 'Limit login', 'No Active Role', 'Error send item', 'Mailbox is full', 'Ok NickName', 'No VK', 'No Steam'];
var VoteType = ['0', 'Обычный', 'SMS(10)', 'SMS(50)', 'SMS(100)', 'Админ'];

var oTable = $('#logtable').dataTable( {
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
	"sAjaxSource": "pages/server_processing.php?<?=$stat?>",
	"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
		nRow.cells[8].innerHTML = VoteType[aData[8]];
		if (aData[11] >= 0 && aData[11] <= 9) nRow.cells[11].innerHTML = VoteStatus[aData[11]];
		if (aData[12]!='') nRow.className=aData[12]+' '+nRow.className;		
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
	     url: 'pages/server_processing.php?id='+aData[13]+'&r='+Math.random(),             // указываем URL и
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

	$('#logtable tbody td img').live( 'click', function () {
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
</script><?php
$postdata = array(
	'op' => 'topstat',
	'servid' => $servid,
	'id' => $_SESSION['id'],
	'tblname' => $stat.'_data`',
	'ip' => $_SERVER['REMOTE_ADDR']
);
$result = CurlPage($postdata, 5);
$a = UnpackAnswer($result);
if ($a['errorcode'] == 0) {	
?>
<div class="row-fluid">
	<div class="box span6">
		<div class="box-header well" data-original-title>
			<h2>Дневной отчёт</h2>
		</div>
		<div class="box-content">
			<table class="table table-striped table-bordered">
				<thead>
        				<tr>
         						<th><b>День</b></th>
	 						<th><b>Всего голосов</b></th>
							<th><b>Успешно выдано</b></th>
							<th><b>Ошибок выдачи</b></th>
        				</tr>
				</thead><?php
	foreach($a['day_sum'] as $i => $val){
	CheckSum($val['total'], 250); CheckSum($val['success'], 250);  CheckSum($val['fail'], 250);
	printf('
        <tr>
          <td><b>%s</b></td>
          <td>%s</td>
	  <td>%s</td>
	  <td>%s</td>
        </tr>', $i, $val['total'], $val['success'], $val['fail']);
	}            
	?>
     			</table>	
		</div>
	</div><!--/span-->
		
	<div class="box span6">
		<div class="box-header well" data-original-title>
			<h2>Месячный отчёт</h2>
		</div>
		<div class="box-content">
    			<table class="table table-striped table-bordered">
				<thead>
        				<tr>
        					<th><b>Месяц</b></th>
	 					<th><b>Всего голосов</b></th>
						<th><b>Успешно выдано</b></th>
						<th><b>Ошибок выдачи</b></th>
        				</tr>
				</thead><?php
					foreach($a['month_sum'] as $i => $val){
					CheckSum($val['total'], 1000); CheckSum($val['success'], 1000);  CheckSum($val['fail'], 1000);
					printf('
        				<tr>
          					<td><b>%s</b></td>
          					<td>%s</td>
          					<td>%s</td>
          					<td>%s</td>
        				</tr>', $months[$i], $val['total'], $val['success'], $val['fail']);
					}            
					?>
      			</table>	
		</div>
	</div><!--/span-->
</div>
<div class="chart-container span5">
	<div id="m_stat" class="chart-placeholder" style="height:400px"></div>
</div>
<div class="chart-container span6">
	<div id="d_stat" class="chart-placeholder" style="height:400px"></div>
</div>
<script type="text/javascript">
<?php echo 'var d_data = [ '.implode(', ', $a['d_txt']).' ];';
	echo 'var m_data = [ '.implode(', ', $a['m_txt']).' ];';
?>
	$.plot("#m_stat", [ m_data ], {
		series: {
			bars: {
				show: true,
				barWidth: 0.9,
				align: "center"
			}
		},
		xaxis: {
			mode: "categories",
			tickLength: 0
		}
	});
	$.plot("#d_stat", [ d_data ], {
		series: {
			bars: {
				show: true,
				barWidth: 0.9,
				align: "center"
			}
		},
		xaxis: {
			mode: "categories",
			tickLength: 0
		}
	}); 	
</script>
<?php
}
?>