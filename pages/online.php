<?php
	if (!defined('main_def')) die();
class online_stat {
	var	$total;
	var	$min;
	var	$max;
}
?><script language="javascript" type="text/javascript" src="js/jquery.flot.categories.min.js"></script>
<style type="text/css">
.chart-container {
	box-sizing: border-box;
	width: 850px;
	height: 450px;
	padding: 20px 15px 15px 15px;
	margin: 15px auto 30px auto;
	border: 1px solid #ddd;
	background: #fff;
	background: linear-gradient(#f6f6f6 0, #fff 50px);
	background: -o-linear-gradient(#f6f6f6 0, #fff 50px);
	background: -ms-linear-gradient(#f6f6f6 0, #fff 50px);
	background: -moz-linear-gradient(#f6f6f6 0, #fff 50px);
	background: -webkit-linear-gradient(#f6f6f6 0, #fff 50px);
	box-shadow: 0 3px 10px rgba(0,0,0,0.15);
	-o-box-shadow: 0 3px 10px rgba(0,0,0,0.1);
	-ms-box-shadow: 0 3px 10px rgba(0,0,0,0.1);
	-moz-box-shadow: 0 3px 10px rgba(0,0,0,0.1);
	-webkit-box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.chart-placeholder {
	width: 100%;
	height: 100%;
	font-size: 14px;
	line-height: 1.2em;
}
</style>
<?php
	if (isset($_GET['r'])) {
		echo GetErrorTxt($_GET['r']);
	}
$error='';
//mysql_query('set names utf8');
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

function ShowResult($result,$img){
	global $servname;
	while($row = mysqli_fetch_assoc($result)) {
	global $show, $per_page, $CUR_PAGE;	
	printf ('
	<tr>
	        <td>%s</td>
	        <td>%s</td>
		<td>%s</td>
	        <td>%s</td>
		<td>%s</td>
		<td>%s</td>
        </tr>',
$row['id'],$row['data'],$row['online_acc'],$row['online_pers'],$row['online_world'],$row['online_instance']);
	}
}
$curmonth = @date('n');
$curday = @date('j');
if (!isset($_GET['m'])) $m = $curmonth; else $m = intval($_GET['m']);
if (!isset($_GET['d'])) $d = $curday; else $d = intval($_GET['d']);
$month_sum = array();
$day_sum = array();
if (isset($_GET['show'])) $show = $_GET['show']; else $show = 1;
if (isset($_GET['per_page'])) $per_page=($_GET['per_page']); else $per_page = 35;
if (is_int($per_page)) $per_page = 15;
if ($per_page < 1) $per_page=15;
if (isset($_GET['page'])) $CUR_PAGE=($_GET['page']); else {$CUR_PAGE=1;}
$sel1='';$sel2='';$sel3='';$sel4='';
if ($show <= 0 or $show > 4) {$sh = '`id`>0';} else {		// Всего
	if ($show == 1) $sh = sprintf("`data`>='%s' AND `data`<'%s'", @date('Y-m-d H:i:s', mktime(0,0,0,$curmonth,$curday,@date('Y'))), @date('Y-m-d H:i:s', mktime(0,0,0,$curmonth,$curday+1,@date('Y'))));				// За текущий день
	if ($show == 2) $sh = sprintf("`data`>='%s' AND `data`<'%s'", @date('Y-m-d H:i:s', mktime(0,0,0,$curmonth,1,@date('Y'))), @date('Y-m-d H:i:s', mktime(0,0,0,$curmonth+1,1,@date('Y'))));			// За текущий месяц
	if ($show == 3) $sh = sprintf("`data`>='%s' AND `data`<'%s'", @date('Y-m-d H:i:s', mktime(0,0,0,$m,$d,@date('Y'))), @date('Y-m-d H:i:s', mktime(0,0,0,$m,$d+1,@date('Y'))));		// За выбранный день
	if ($show == 4) $sh = sprintf("`data`>='%s' AND `data`<'%s'", @date('Y-m-d H:i:s', mktime(0,0,0,$m,1,@date('Y'))), @date('Y-m-d H:i:s', mktime(0,0,0,$m+1,1,@date('Y'))));		// За выбранный месяц
}
$start=abs(($CUR_PAGE-1)*$per_page);
$uri=strtok($_SERVER['REQUEST_URI'],"?")."?";
if (count($_GET)) {
  foreach ($_GET as $k => $v) {
    if ($k != "page") $uri.=urlencode($k)."=".urlencode($v)."&";
  }
}
function CheckMin($sum){
	if ($sum=='') return 0; else return $sum;
}
// Дневная статистика
$d_txt = '';
if (@date('n')!=$m) $dd = @date("t", mktime(0,0,0,$m,1,@date('Y'))); else $dd = $curday;
for ($a=$dd; $a>0; $a--){
	$tmp = sprintf("WHERE `data`>='%s' AND `data`<'%s'", @date('Y-m-d H:i:s', mktime(0,0,0,$m,$a,@date('Y'))), @date('Y-m-d H:i:s', mktime(0,0,0,$m,$a+1,@date('Y'))));
	// Total
	$result = $db->query('SELECT round(sum(`online_acc`)/count(`online_acc`)) as `sum`, min(`online_acc`) as `min`, max(`online_acc`) as `max` FROM `online_stat` '.$tmp);
	$row = mysqli_fetch_assoc($result);
	$day_sum[$a] = new online_stat;
	$day_sum[$a]->total = CheckMin($row['sum']);
	$day_sum[$a]->min = CheckMin($row['min']);	
	$day_sum[$a]->max = CheckMin($row['max']);		
	$d_txt[$a] = sprintf('["%s", %d]', $a, $row['max']);
}
$d_txt = array_reverse($d_txt);
echo '<script type="text/javascript"> var d_data = [ '.implode($d_txt, ', ').' ]; </script>';
// Месячная статистика
$m_txt = '';
for ($a=$curmonth; $a>0; $a--){
	$tmp = sprintf("WHERE `data`>='%s' AND `data`<'%s'", @date('Y-m-d H:i:s', mktime(0,0,0,$a,1,@date('Y'))), @date('Y-m-d H:i:s', mktime(0,0,0,$a+1,1,@date('Y'))));
	// Total
	$result = $db->query('SELECT round(sum(`online_acc`)/count(`online_acc`)) as `sum`, min(`online_acc`) as `min`, max(`online_acc`) as `max` FROM `online_stat` '.$tmp);
	$row = mysqli_fetch_assoc($result);
	$month_sum[$a] = new online_stat;
	$month_sum[$a]->total = CheckMin($row['sum']);	
	$month_sum[$a]->min = CheckMin($row['min']);
	$month_sum[$a]->max = CheckMin($row['max']);
	$m_txt[$a] = sprintf('["%s", %d]', $months[$a], $row['max']);
}
$m_txt = array_reverse($m_txt);
echo '<script type="text/javascript"> var m_data = [ '.implode($m_txt, ', ').' ]; </script>';
if (isset($_GET['ok'])) $ok=($_GET['ok']); else $ok=2;
$dbtbl = '`online_stat`';
$result = $db->query("SELECT count(*) FROM $dbtbl"); // Всего записей
$row = mysqli_fetch_array($result);
$d0 = $row[0];
$result = $db->query(sprintf("SELECT count(*) FROM $dbtbl WHERE `data`>='%s' AND `data`<'%s'", @date('Y-m-d H:i:s', mktime(0,0,0,$curmonth,$curday,@date('Y'))), @date('Y-m-d H:i:s', mktime(0,0,0,$curmonth,$curday+1,@date('Y'))))); // За текущий день
$row = mysqli_fetch_array($result);
$d1 = $row[0];
$result = $db->query(sprintf("SELECT count(*) FROM $dbtbl WHERE `data`>='%s' AND `data`<'%s'", @date('Y-m-d H:i:s', mktime(0,0,0,$curmonth,1,@date('Y'))), @date('Y-m-d H:i:s', mktime(0,0,0,$curmonth+1,1,@date('Y'))))); // За текущий месяц
$row = mysqli_fetch_array($result);
$d2 = $row[0];
$result = $db->query(sprintf("SELECT count(*) FROM $dbtbl WHERE `data`>='%s' AND `data`<'%s'", @date('Y-m-d H:i:s', mktime(0,0,0,$m,$d,@date('Y'))), @date('Y-m-d H:i:s', mktime(0,0,0,$m,$d+1,@date('Y'))))); // За выбранный день
$row = mysqli_fetch_array($result);
$d3 = $row[0];
$result = $db->query(sprintf("SELECT count(*) FROM $dbtbl WHERE `data`>='%s' AND `data`<'%s'", @date('Y-m-d H:i:s', mktime(0,0,0,$m,1,@date('Y'))), @date('Y-m-d H:i:s', mktime(0,0,0,$m+1,1,@date('Y'))))); // За выбранный месяц
$row = mysqli_fetch_array($result);
$d4 = $row[0];

 if ($show == 0) $total_rows=$d0;
 if ($show == 1) $total_rows=$d1;
 if ($show == 2) $total_rows=$d2;
 if ($show == 3) $total_rows=$d3;
 if ($show == 4) $total_rows=$d4;
$num_pages=ceil($total_rows/$per_page);
$PAGES = array();
for($i=1;$i<=$num_pages;$i++) $PAGES[$i]=$uri.'page='.$i;
?>

<div class="span3">
	<div class="dataTables_filter">
		Найдено записей: <span class="label label-success"><?=$total_rows?></span>
	</div>
</div>
<div class="span3">
	<div class="dataTables_length">
		<form name="form1" method="get" action="<?=$uri?>">
		<input type="hidden" name="op" value="<?=$op?>">
	  	<input type="hidden" name="show" value="<?=$show?>">
		<input type="hidden" name="m" value="<?=$m?>">
		<input type="hidden" name="d" value="<?=$d?>">
		<label>
			<select name="per_page" size="1" onchange="document.form1.submit()">
				<option value="5"<?php if ($per_page == 5) echo ' selected="selected"';?>>5</option>
				<option value="10"<?php if ($per_page == 10) echo ' selected="selected"';?>>10</option>
				<option value="15"<?php if ($per_page == 15) echo ' selected="selected"';?>>15</option>
				<option value="20"<?php if ($per_page == 20) echo ' selected="selected"';?>>20</option>
				<option value="25"<?php if ($per_page == 25) echo ' selected="selected"';?>>25</option>
				<option value="50"<?php if ($per_page == 50) echo ' selected="selected"';?>>50</option>
				<option value="100"<?php if ($per_page == 100) echo ' selected="selected"';?>>100</option>
			</select>
			Записей на странице
		</label>
		</form>
	</div>
</div>
<table class="table table-striped table-bordered">
	<thead>
        <tr>
          <th>№</th>
          <th>Дата</th>
          <th>Аккаунты</th>
	  <th>Персонажи</th>
	  <th>Мир</th>
	  <th>Данжи</th>
        </tr> 
	</thead>            
        <?php		
		if ($total_rows > 0) {		
		$sql = "SELECT * FROM ".$dbtbl." WHERE ".$sh." ORDER by id DESC LIMIT $start,$per_page";
		$result = $db->query($sql);
		ShowResult($result,'<img src="images/ok.png" align="absmiddle">');
		}
		?>
      </table>
	</td>
  </tr>
  </table>
<?php 
							if (count($PAGES)>1) { ?>
	<div class="pagination pagination-centered">

		<ul>
							<?php 
							if ($CUR_PAGE>1) {
								$dis1 = $uri.'page='.($CUR_PAGE-1);
								$dis2 = '';
							} else {
								$dis1='#';
								$dis2=' class="prev disabled"';
							} ?><li<?=$dis2?>><a href="<?=$dis1?>">← Пред</a></li><?php
							$max_page_visible = 15;
							if ($num_pages<=$max_page_visible) {
								foreach ($PAGES as $i => $link) {
									if ($i == $CUR_PAGE) echo '<li class="active"><a href="#">'.$i.'</a></li>'; else
									echo '<li><a href="'.$link.'">'.$i.'</a></li>';
								}
							} else {
								$first = round( $CUR_PAGE - $max_page_visible/2);
								if ($first<1) $first = 1;
								if ($first==$CUR_PAGE) $last = $CUR_PAGE + $max_page_visible - 1; else
								$last = ($CUR_PAGE + $max_page_visible/2);
								if ($last>$num_pages) $last = $num_pages;
								if ($last == $num_pages && ($last-$first)<$max_page_visible-1) $first = $CUR_PAGE - $max_page_visible + 1;
								if ($first<1) $first = 1;
								if ($first>1) {
									echo '<li><a href="'.$PAGES[1].'">1</a></li>';
									echo '<li class="active"><a href="#">...</a></li>';
								}
								for ($i=$first; $i<$CUR_PAGE; $i++) echo '<li><a href="'.$PAGES[$i].'">'.$i.'</a></li>';
								echo '<li class="active"><a href="#">'.$CUR_PAGE.'</a></li>';
								for ($i=$CUR_PAGE+1; $i<=$last; $i++) echo '<li><a href="'.$PAGES[$i].'">'.$i.'</a></li>';
								if ($last<$num_pages) {
									echo '<li class="active"><a href="#">...</a></li>';
									echo '<li><a href="'.$PAGES[$num_pages].'">'.$num_pages.'</a></li>';
								}
							}
							?>							
							<?php if ($CUR_PAGE<$num_pages) {
								$dis1 = $uri.'page='.($CUR_PAGE+1);
								$dis2 = '';
							} else {
								$dis1='#';
								$dis2=' class="prev disabled"';
							} ?><li<?=$dis2?>><a href="<?=$dis1?>">След →</a></li>							
		</ul>
	</div>  	
<?php } ?>
	<div class="row-fluid">
		<div class="box span4">
			<div class="box-header well" data-original-title>
				<h2>Дневной отчёт</h2>
			</div>
			<div class="box-content">
				<table class="table table-striped table-bordered">
					<thead>
        					<tr>
          						<th><b>День</b></th>
	  						<th><b>Средний онлайн</b></th>
							<th><b>Мин онлайн</b></th>
							<th><b>Макс онлайн</b></th>
        					</tr>
					</thead><?php
	foreach($day_sum as $i => $val){
	CheckSum($val->total, 250);
	CheckSum($val->min, 100);
	CheckSum($val->max, 300);
	printf('
        <tr>
          <td><a href="index.php?op=online&show=3&m=%s&d=%s">%s</a></td>
          <td>%s</td>
          <td>%s</td>
          <td>%s</td>
        </tr>', $m, $i, $i, $val->total, $val->min, $val->max);
	}            
	?>
     				</table>	
			</div>
		</div><!--/span-->
		
		<div class="box span4">
			<div class="box-header well" data-original-title>
				<h2>Месячный отчёт</h2>
			</div>
			<div class="box-content">
    				<table class="table table-striped table-bordered">
					<thead>
        					<tr>
          						<th><b>Месячный отчёт</b></th>
	  						<th><b>Средний онлайн</b></th>
							<th><b>Мин онлайн</b></th>
							<th><b>Макс онлайн</b></th>
        					</tr>
					</thead><?php
					foreach($month_sum as $i => $val){
					CheckSum($val->total, 250);
					CheckSum($val->min, 100);
					CheckSum($val->max, 300);
					printf('
        				<tr>
          					<td><a href="index.php?op=online&show=4&m=%s">%s</a></td>
          					<td>%s</td>
          					<td>%s</td>
          					<td>%s</td>
        				</tr>', $i, $months[$i], $val->total, $val->min, $val->max);
					}            
					?>
      					</table>	
				</div>
		</div><!--/span-->

		<div class="box span4">
			<div class="box-header well" data-original-title>
				<h2>Статистика</h2>
			</div>
			<div class="box-content">
				<table class="table table-striped table-bordered">
					<thead>
        					<tr>
          						<th colspan="2"><b>Отчёт по онлайну</i></b></th>
        					</tr>
					</thead>
					<tr>
          					<td><a href="index.php?op=online&show=0&per_page=<?=$per_page?>">Всего записей</a></td>
          					<td><?=$d0?></td>
        				</tr>
        				<tr>
          					<td><a href="index.php?op=online&show=1&per_page=<?=$per_page?>">За текущий день</a></td>
          					<td><?=$d1?></td>
        				</tr>
        				<tr>
          					<td><a href="index.php?op=online&show=2&per_page=<?=$per_page?>">За текущий месяц</a></td>
          					<td><?=$d2?></td>
        				</tr>        				     
      				</table>    
			</div>
		</div>
	</div>
<script type="text/javascript">
	$(function() {
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
	});
</script>
			<div class="chart-container span5">
				<div id="m_stat" class="chart-placeholder"></div>
			</div>
			<div class="chart-container span6">
				<div id="d_stat" class="chart-placeholder"></div>
			</div>
			
