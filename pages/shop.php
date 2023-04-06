<?php
	if (!defined('main_def')) die();

	function catlink($num,$a,$b,$c,&$curcatname){
		if ($c==$a) {
			$e='btn-danger'; 
			$curcatname=$b;
		}else $e='btn-inverse';
		$d=sprintf('<a href="index.php?op=shop&num=%s&page=%s" class="btn btn-large %s">%s</a> ',$num,$a,$e,$b);
		return $d;
	}
	function subcatlink($num,$a,$b,$c,$sc,&$cursubcatname){
		if ($c==$sc) {
			$e='btn-danger';
			$cursubcatname = $b;
		} else $e='btn-inverse';
		$d=sprintf('<a href="index.php?op=shop&num=%s&page=%s&subcat=%s" class="btn %s">%s</a> ',$num,$a,$sc,$e,$b);
		return $d;
	}

	if (isset($_GET['r'])) {
		echo GetErrorTxt($_GET['r']);
	}
	$subcat = (isset($_GET['subcat']))?intval($_GET['subcat']):'';
	$page = (isset($_GET['page']))?intval($_GET['page']):1;
	$num = (isset($_GET['num']))?intval($_GET['num']):0;
	$newname = (isset($_POST['newname']))?$_POST['newname']:'';
	$sitem = (isset($_GET['sitem']))?intval($_GET['sitem']):0;	
	$curcatname = '';
	$cursubcatname = '';
	$adm = '';
	if (isset($_GET['adm']) && $_SESSION['isadmin']) {
		if ($_GET['adm']!='edititem'){	
			$postdata = array(
					'op' => 'adm',
					'act' => $_GET['adm'],
					'servid' => $servid,
					'id' => $_SESSION['id'],
					'ip' => $_SERVER['REMOTE_ADDR'],
					'num' => $num,
					'page' => $page,
					'subcat' => $subcat,
					'newname' => $newname,
					'sitem' => $sitem
			);
			$result = CurlPage($postdata, 5);
			if ($_GET['adm']=='additem') {
				$q = @explode('|', $result);				
				if (count($q)!=2) echo GetErrorTxt($result); else {
					echo GetErrorTxt($q[0]);
					if ($q[0]==16) {
						$sitem = $q[1];					
						$adm = 'edititem';
					}
				}
			} else 
			echo GetErrorTxt($result);
			
		} else $adm = $_GET['adm'];
	}
	if (CheckNum($num)) echo GetErrorTxt(10); else
		{
		$postdata = array(
				'op' => 'shopheaders',
				'servid' => $servid,
				'id' => $_SESSION['id'],
				'ip' => $_SERVER['REMOTE_ADDR'],
				'num' => $num,
				'page' => $page,
				'subcat' => $subcat
		);
		if (isset($_GET['adm']) && $_SESSION['isadmin']) $postdata['adm'] = $_GET['adm'];
		if ($sitem && $_SESSION['isadmin']) $postdata['sitem'] = $sitem;
		$result = CurlPage($postdata, 5, 1);		
		echo '<span class="label label-info">Шаг 1</span> Выберите персонажа, на которого будут отправляться покупки
		<div class="well">
		';
		$a = UnpackAnswer($result);
		if (!is_array($a) && !CheckNum($result)) echo GetErrorTxt($result); else
		if ($a['errorcode'] == 81 || (isset($a['bancount']) && $a['bancount']>0)) {
			WriteBanTable($a);
		} else 
		if ($a['errorcode'] == 0) {
			foreach ($a['roles'] as $i => $val){		
				if ($num==$i) printf('<span class="btn btn-success">%s</span> ',$val['name']); else
				printf('<a class="btn" href="index.php?op=shop&num=%s&page=%s&subcat=%s&rand=%s'.'">%s</a> ',$i,$page,$subcat,time(),$val['name']);
			}
		}
		echo '</div>';
		if ($a['errorcode'] == 0) {
			echo '<span class="label label-info">Шаг 2</span> Выберите нужный раздел и товар
			<div class="well"><center><div style="margin-bottom:5px">
			';
			// Категории
			foreach ($a['cat'] as $i => $val){
				echo catlink($num,$val['id'],$val['name'],$page,$curcatname).' 
				';
			}
			if ($_SESSION['isadmin']) { ?>
				<a href="index.php?op=shop&num=<?=$num?>&adm=addcat" title="Добавить категорию" data-rel="tooltip" class="btn btn-large btn-success" onClick="return conf2()">+</a> <?php
				if (count($a['cat'])>0) { ?>
				<a href="index.php?op=shop&page=<?=$page?>&num=<?=$num?>&adm=delcat" title="Удалить категорию" data-rel="tooltip" class="btn btn-large btn-danger" onclick="return conf1()">-</a><?php } ?> <br>
				<form method="post" name="rencat" action="index.php?op=shop&page=<?=$page?>&num=<?=$num?>&adm=rencat" style="margin-top:5px">		
				<input type="text" name="newname" maxlength="20" value="<?=$curcatname?>"><button title="Переименовать категорию" data-rel="tooltip" class="btn" type="button" onClick="if (conf3()) document.rencat.submit()"><i class="icon icon-edit"></i></button>
				</form></td>
			<?php
			}
			echo '<br></div><div style="margin-bottom:5px">';
			// Субкатегории
			foreach ($a['subcat'] as $i => $val){
					if ($subcat == '') $subcat = $val['id'];
					echo subcatlink($num,$page,$val['name'],$subcat,$val['id'],$cursubcatname).' 
					';
			}
			if ($_SESSION['isadmin']) { ?>
				<a href="index.php?op=shop&page=<?=$page?>&num=<?=$num?>&adm=addsubcat" title="Добавить субкатегорию" data-rel="tooltip" class="btn btn-success" onClick="return conf2()">+</a> <?php
				if (count($a['subcat'])>0) { ?>
				<a href="index.php?op=shop&page=<?=$page?>&num=<?=$num?>&subcat=<?=$subcat?>&adm=delsubcat" title="Удалить субкатегорию" data-rel="tooltip" class="btn btn-danger" onclick="return conf1()">-</a><?php } ?>
				<br>				
				<form method="post" name="rensubcat" action="index.php?op=shop&page=<?=$page?>&subcat=<?=$subcat?>&num=<?=$num?>&adm=rensubcat" style="margin-top:5px">
				<input type="text" name="newname" maxlength="20" value="<?=$cursubcatname?>"><button title="Переименовать субкатегорию" data-rel="tooltip" class="btn" type="button" onClick="if (conf3()) document.rensubcat.submit()"><i class="icon icon-edit"></i></button>
				</form></td>
			<?php
			} else echo '<br>';
if ($_SESSION['isadmin']) { ?>
<script type="text/javascript">
	function conf1(){
		return window.confirm('Все связанные товары и подразделы будут удалены. Вы уверены, что хотите удалить этот раздел?');
	}
	function conf2(){
		return window.confirm('Добавить новый раздел?');
	} 
	function conf3(){
		return window.confirm('Переименовать раздел?');
	}
	function conf4(){
		return window.confirm('Удалить выбранный предмет?');
	}
	function conf5(){
		return window.confirm('Отменить редактирование?');
	}; 
	function conf6(){
		return window.confirm('Удалить выбранный предмет?');
	}
	function conf7(){
		return window.confirm('Клонировать выбранный предмет?');
	}
</script>
<?php
}
?>
			<br></div><?php
			if (count($a['items'])) {
				if ($_SESSION['isadmin'] && $adm == 'edititem') {
				// Редактирование итема
					foreach ($a['items'] as $i => $val){
						if ($val['id'] == $sitem) {
							$edititem = $val;
							break;
						}
					}
	if (isset($edititem)) { 
		ParseCost($edititem['cost_timeless'], $cost_timeless_gold, $cost_timeless_silver);
		ParseCost($edititem['cost_expire'], $cost_expire_gold, $cost_expire_silver);
		?>
<script type="text/javascript">			
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
function getDesc(id, id1, id2) { 
	document.getElementById(id1).style.display="";
	document.getElementById(id2).innerHTML='';
	$.ajax({							
		url: "pages/server_processing.php?item_desc",
		type: "POST",
		data: {itemid:$("#"+id).val()},
		success: function(msg){			
			document.getElementById(id1).style.display="none";
			var A = document.getElementById(id2);
			A.value = msg;
			var B = $('.cleditor').cleditor();
			B[0].refresh();
		}
	});	  		  
}

function InitItemID(){
	getName('itemid', 'loaderid', 'paramid');
}

setTimeout(InitItemID, 500);
</script>
		<div class="row-fluid">
			<div class="box span12">
				<div class="box-header well" data-original-title>
					<h2><i class="icon icon-color icon-edit"></i> Редактирование записи № <?=$sitem?></h2>
				</div>
				<div class="box-content">
					<form name="editform" method="post" action="index.php?op=act&n=19&num=<?=$num?>&page=<?=$page?>&subcat=<?=$subcat?>">
					<input type="hidden" name="sitem" value="<?=$sitem?>">
					<table class="table table-bordered table-striped">
					<tr>
						<td><h3>Item ID</h3></td>
						<td><input type="text" id="itemid" name="itemid" value="<?=$edititem['itemid']?>" class="config_itemid" onchange="getName('itemid', 'loaderid', 'paramid');"> <span style="display:none" id="loaderid"><img src="img/ajax-loaders/ajax-loader-1.gif" border="0" align="absmiddle"/></span> <span id="paramid"></span></td>
						<td style="text-align:left"><code>ID предмета для продажи</code></td>
					</tr>
					<tr>
						<td><h3>Count</h3></td>
						<td><input type="text" name="count" value="<?=$edititem['count']?>"></td>
						<td style="text-align:left"><code>Количество предметов</code></td>
					</tr>
					<tr>
						<td><h3>Max count</h3></td>
						<td><input type="text" name="maxcount" value="<?=$edititem['maxcount']?>"></td>
						<td style="text-align:left"><code>Максимальное количество в ячейке</code></td>
					</tr>
					<tr>
						<td><h3>Data</h3></td>
						<td><input type="text" class="span12" name="data" value="<?=$edititem['data']?>"></td>
						<td style="text-align:left"><code>Октет предмета</code></td>
					</tr>
					<tr>
						<td><h3>Client size</h3></td>
						<td><input type="text" name="client_size" value="<?=$edititem['client_size']?>"></td>
						<td style="text-align:left"></td>
					</tr>
					<tr>
						<td><h3>Proctype</h3></td>
						<td><input type="text" name="proctype" value="<?=$edititem['proctype']?>" onkeyup="fillproctype(this.value)" onfocus="showproctype(this);"></td>
						<td style="text-align:left"><code>Привязка предмета</code></td>
					</tr>
					<tr>
						<td><h3>SubCat</h3></td>
						<td><input type="text" name="subcat" value="<?=$edititem['subcat']?>"></td>
						<td style="text-align:left"><code>ID субкатегории в магазине</code></td>
					</tr>
					<tr>
						<td><h3>Cost timeless</h3></td>
						<td><span class="gold"> <input type="text" name="cost_timeless_gold" value="<?=$cost_timeless_gold?>" class="config_cost"></span> <span class="silver"> <input type="text" name="cost_timeless_silver" value="<?=$cost_timeless_silver?>" class="config_cost"></span></td>
						<td style="text-align:left"><code>Стоимость предмета без ограничений по сроку действия</code><br><code>Если цена 0 - покупка вечной вещи будет недоступна</code></td>
					</tr>
					<tr>
						<td><h3>Cost expire</h3></td>
						<td><span class="gold"> <input type="text" name="cost_expire_gold" value="<?=$cost_expire_gold?>" class="config_cost"></span> <span class="silver"> <input type="text" name="cost_expire_silver" value="<?=$cost_expire_silver?>" class="config_cost"></span></td>
						<td style="text-align:left"><code>Стоимость предмета c ограничением по сроку действия</code><br><code>Если цена 0 - покупка временной вещи будет недоступна</code></td>
					</tr>
					<tr>
						<td><h3>Expire</h3></td>
						<td><input type="text" name="expire" value="<?=$edititem['expire']?>"></td>
						<td style="text-align:left"><code>Срок действия временной вещи в секундах</code><br><code>Если цена <b>Cost expire</b> не указана, покупка временной вещи будет недоступна</code></td>
					</tr>
					<tr>
						<td><h3>Discount data</h3></td>
						<td><textarea class="autogrow span12" name="discount_data"><?php
						$discount_data = @unserialize($edititem['discount_data']);
						if (!is_array($discount_data)) $discount_data = array();
						if (count($discount_data)>0)
						foreach ($discount_data as $i => $val){
							printf("%d-%d\r\n", $i, $val);
						}
						?></textarea></td>
						<td style="text-align:left"><code>Параметры скидок:</code><br><code><b>количество-скидка</b></code><br><code><b>количество-скидка</b></code><br><code>и т.д.</code><br><br><code>за единицу количества считается значение поля <b>Count</b>, скидка в %</code></td>
					</tr>
					<tr>
						<td><h3>Desc</h3></td>
						<td><center><textarea class="cleditor" name="desc" id="desc"><?=$edititem['clean_desc']?></textarea></center></td>
						<td style="text-align:left"><code>Описание предмета.</code><br><code>Статы оружия, брони, бижутерии и полетов генерируются автоматически из октета предмета</code><a class="btn btn-small btn-inverse" href="#" onclick="getDesc('itemid', 'loaderid1', 'desc')">Вставить название из <font color="#ffff00">item_ext_desc.txt</font></a><br><span style="display:none" id="loaderid1"><img src="img/ajax-loaders/ajax-loader-1.gif" border="0" align="absmiddle"/></span></td>
					</tr>
					<tr>
						<td><h3>Rest</h3></td>
						<td><input type="text" name="rest" value="<?=$edititem['rest']?>"></td>
						<td style="text-align:left"><code>Остаток предметов (<b>-1</b> для покупок без ограничений)</code><br><code>При покупке считается по формуле <b>Остаток</b> = <b>Rest</b> * <b>Count</b></code></td>
					</tr>
					<tr>
						<td><h3>Buy count</h3></td>
						<td><input type="text" name="buycount" value="<?=$edititem['buycount']?>"></td>
						<td style="text-align:left"><code>Счетчик покупок, содержит количество купленных предметов</code><br><code>За единицу берется значение поля <b>Count</b></code></td>
					</tr>
					<tr>
						<td colspan="3"><br><input type="submit" class="btn btn-large btn-primary" value="Сохранить запись"><br><br></td>
					</tr>
					</table>
					</form>
				</div>
			</div>
		</div>
					<?php
						goto drawend;						
					} else echo GetErrorTxt(10);
				}
				// Вывод итемов
				?>
<script type="text/javascript">

function GetTime(t,h){
	pinkdays = Math.floor(t/86400);
	pinkhours = Math.floor((t-pinkdays*86400)/3600);
	pinkmin = Math.floor((t-pinkdays*86400-pinkhours*3600)/60);
	pinksec = Math.round(t-pinkdays*86400-pinkhours*3600-pinkmin*60,0);
	if (pinkhours<10) pinkhours='0'+pinkhours;
	if (pinkmin<10) pinkmin='0'+pinkmin;
	if (pinksec<10) pinksec='0'+pinksec;
	timeused = '';
	if (pinkdays>0) {
		if (h) timeused='<font color="#ffff00"><b>'+pinkdays+'</b></font> дн '; else
		timeused=pinkdays+' дн ';
	}
	if ((pinkhours!="00")||(pinkmin!="00")||(pinksec!="00")) timeused += pinkhours+':'+pinkmin+':'+pinksec;
	if (timeused=='') timeused = 0;
	return timeused;
}

function ShowDiscount(d,c,s){
	val = 0;
	if (window[d].length>0) {
		for (var i in window[d]){
			if (!window[d].hasOwnProperty(i)) continue;
			if (c >= window[d][i][0]) {
				val = window[d][i][1];
				document.getElementById(s).style.display='block';
				document.getElementById(s).innerHTML='Скидка '+window[d][i][1]+'%';
			}
		}
	}
	if (val==0) document.getElementById(s).style.display='none';
	return val;
}

function BtnAct(n,e,p)
{
	discount = 'discount'+n; count = 'count'+n; cur_count_t = 'cur_count_t'+n;
	cur_count_e = 'cur_count_e'+n; max_count = 'max_count'+n; cost = 'cost'+n;
	expire = 'expire'+n; t_gold = 't_gold'+n; t_silver = 't_silver'+n;
	e_gold = 'e_gold'+n; e_silver = 'e_silver'+n; t_count = 't_count'+n;
	e_count = 'e_count'+n;
	if (e){
		if (p) window[cur_count_e]++; else {
			if (window[cur_count_e]<2) return;
			window[cur_count_e]--;
		}
		d = ShowDiscount(discount, window[cur_count_e], discount);
		dg = 0; ds = 0;
		if (d) {
			dg = Math.round(window[cur_count_e]*window[cost][2]/100*d);
			ds = Math.round(window[cur_count_e]*window[cost][3]/100*d);
		}
		document.getElementById(e_count).innerHTML = GetTime(window[cur_count_e]*window[expire],true);	
		document.getElementById(e_gold).innerHTML = window[cur_count_e]*window[cost][2]-dg;
		document.getElementById(e_silver).innerHTML = window[cur_count_e]*window[cost][3]-ds;		
	} else {
		if (p) {
			if ((window[cur_count_t]+1)*window[count] > window[max_count]) return;
			window[cur_count_t]++;
		} else {
			if (window[cur_count_t]<2) return;
			window[cur_count_t]--;
		}		
		d = ShowDiscount(discount, window[cur_count_t], discount);
		dg = 0; ds = 0;
		if (d) {
			dg = Math.round(window[cur_count_t]*window[cost][0]/100*d);
			ds = Math.round(window[cur_count_t]*window[cost][1]/100*d);
		}
		document.getElementById(t_count).innerHTML = window[cur_count_t]*window[count];
		document.getElementById(t_gold).innerHTML = window[cur_count_t]*window[cost][0]-dg;
		document.getElementById(t_silver).innerHTML = window[cur_count_t]*window[cost][1]-ds;				
	}	
}

String.prototype.stripTags = function() {
  return this.replace(/<\/?[^>]+>/g, '');
};

function Buy(n,e){
	name = document.getElementById('name'+n).innerHTML;
	if (e) {
		if (!window.confirm('Вы уверены, что хотите купить '+name.stripTags()+' на '+GetTime(window['cur_count_e'+n]*window['expire'+n],false)+'?')) return;
		l = 't=e&count='+window['cur_count_e'+n];
	} else {
		if (!window.confirm('Вы уверены, что хотите купить '+window['cur_count_t'+n]*window['count'+n]+' x '+name.stripTags()+'?')) return;
		l = 't=t&count='+window['cur_count_t'+n];
	}
	link='index.php?op=act&n=8&num=<?=$num?>&sitem='+n+'&page=<?=$page?>&subcat=<?=$subcat?>&'+l;	
	document.location.href = link;
}
</script>
				<?php
				$n = 0;
				foreach ($a['items'] as $i => $val){
					$costtxt = '<span class="label label-important">Временно недоступно</span>';
					ParseCost($val['cost_timeless'], $cost_timeless_gold, $cost_timeless_silver);
					ParseCost($val['cost_expire'], $cost_expire_gold, $cost_expire_silver);
					//$costs = @unserialize($val['cost_data']);
					$discount_data = @unserialize($val['discount_data']);
					if (!is_array($discount_data)) $discount_data = array();
					$discount_txt = 'var discount'.$val['id'].' = [';
					$curdiscount = 0; $hide_discount = ' style="display:none"';
					if (count($discount_data)){
						foreach ($discount_data as $i1 => $val1){
							if ($i1<=1) {
								$curdiscount = $val1;
								$hide_discount = '';
							}
							$discount_txt .= sprintf("[%s, %s], ", $i1, $val1); 
						}
						$discount_txt = substr($discount_txt, 0, -2);
					}
					$dg = 0; $ds = 0;					
					$discount_txt .= '];';	
					$cost_txt = 'var cost'.$val['id'].' = ';
					if ($cost_timeless_gold || $cost_timeless_silver || $cost_expire_gold || $cost_expire_silver){				
						$costtxt = '';
						$cost_txt .= sprintf("[%s, %s, %s, %s]", $cost_timeless_gold, $cost_timeless_silver, $cost_expire_gold, $cost_expire_silver);
						if ($cost_timeless_gold || $cost_timeless_silver) {
							if ($curdiscount>0) {
								$dg = round($cost_timeless_gold/100*$curdiscount);
								$ds = round($cost_timeless_silver/100*$curdiscount);
							}
							$dis_gold = ($cost_timeless_gold<1)?' style="display:none"':'';
							$dis_silver = ($cost_timeless_silver<1)?' style="display:none"':'';
							$cost = sprintf('<span id="t_gold%d" class="gold"%s>%d</span> <span id="t_silver%d" class="silver"%s>%d</span>', $val['id'], $dis_gold, $cost_timeless_gold - $dg, $val['id'], $dis_silver, $cost_timeless_silver - $ds);
							// Постоянная вещь
							$costtxt.=sprintf('<div style="float:left"><a class="btn btn-mini" href="#" onclick="BtnAct(%d,false,false)">-</a> <span id="t_count%d" class="shop_count">%d</span> шт. <a class="btn btn-mini" href="#" onclick="BtnAct(%d,false,true)">+</a></div> %s <div style="float: right"><a type="button" class="btn btn-mini btn-success" href="#" onclick="Buy(%d,false)">Купить</a></div><hr>', $val['id'], $val['id'], $val['count'], $val['id'], $cost, $val['id']);
						}
						if (($cost_expire_gold || $cost_expire_silver) && $val['expire']){
							if ($curdiscount>0) {
								$dg = round($cost_expire_gold/100*$curdiscount);
								$ds = round($cost_expire_silver/100*$curdiscount);
							}
							$dis_gold = ($cost_expire_gold<1)?' style="display:none"':'';
							$dis_silver = ($cost_expire_silver<1)?' style="display:none"':'';
							$cost = sprintf('<span id="e_gold%d" class="gold"%s>%d</span> <span id="e_silver%d" class="silver"%s>%d</span>', $val['id'], $dis_gold, $cost_expire_gold - $dg, $val['id'], $dis_silver, $cost_expire_silver - $ds);
							// Временная вещь
							$costtxt.=sprintf('<div style="float:left"><a class="btn btn-mini" href="#" onclick="BtnAct(%d,true,false)">-</a>&nbsp;<span id="e_count%d" class="label label-important">%s</span>&nbsp;<a class="btn btn-mini" href="#" onclick="BtnAct(%d,true,true)">+</a></div> %s <div style="float: right"><a type="button" class="btn btn-mini btn-success" href="#" onclick="Buy(%d,true)">Купить на время</a></div><hr>', $val['id'], $val['id'], GetTime($val['expire'],'ffff00'), $val['id'], $cost, $val['id']);
						}
					} else $cost_txt .= '[0, 0, 0, 0]';
					$cost_txt .= ';';	
					if ($n == 0) echo '<div class="row-fluid" align="center">';
					$rest = ($val['rest'] >= 0)?'<span class="notification red">Осталось '.($val['rest']*$val['count']).' шт.</span>':'';
					$admact = ($_SESSION['isadmin'])?'<span class="notification yellow notification_adm_edit" data-rel="tooltip" title="Редактировать запись"><a href="index.php?op=shop&page='.$page.'&subcat='.$subcat.'&sitem='.$val['id'].'&num='.$num.'&adm=edititem"><i class="icon icon-black icon-edit"></i></a></span> <span class="notification red notification_adm_delete" data-rel="tooltip" title="Удалить запись"><a href="index.php?op=shop&page='.$page.'&subcat='.$subcat.'&sitem='.$val['id'].'&num='.$num.'&adm=delitem" onClick="return conf4()"><i class="icon icon-black icon-trash"></i></a></span> <span class="notification green notification_adm_clone" data-rel="tooltip" title="Клонировать запись"><a href="index.php?op=shop&page='.$page.'&subcat='.$subcat.'&sitem='.$val['id'].'&num='.$num.'&sitem='.$val['id'].'&adm=cloneitem" onClick="return conf7()"><i class="icon icon-black icon-copy"></i></a></span> <span class="notification notification_adm_buycount" data-rel="tooltip" title="Купили раз">'.$val['buycount'].'</span>':''; 								
					printf('
					<span class="well span3 shop-block" data-rel="popover" data-placement="bottom" data-animation="false" data-content="%s" title="%s">
						<img src="getitemicon.php?i=%s" border="0"> <div id="name%d">%s</div><hr>
						%s%s%s
						<span class="notification red notification_discount" id="discount%d"%s>Скидка %s%%</span>
					</span>
					', 
					str_replace('"',"'",$val['desc']), htmlspecialchars($val['name']), urlencode(base64_encode($val['icon'])), $val['id'], $val['name'], $costtxt, $rest, $admact, $val['id'], $hide_discount, $curdiscount );
					$n++;
					if ($n > 3) {
						echo '</div>';
						$n = 0;
					}
					echo "<script type=\"text/javascript\">\r\n";
					echo $discount_txt."\r\n";
					printf("var count%d = %d;\r\n", $val['id'], $val['count']);
					printf("var cur_count_t%d = 1;\r\n", $val['id']);
					printf("var cur_count_e%d = 1;\r\n", $val['id']);
					printf("var max_count%d = %d;\r\n", $val['id'], $val['maxcount']);
					echo $cost_txt."\r\n";
					printf("var expire%d = %d;\r\n", $val['id'], $val['expire']);
					echo '</script>';
					
				}
				if ($n) echo '</div>';
			}	
			if ($_SESSION['isadmin'] && count($a['subcat'])>0) {
				echo '<a href="index.php?op=shop&page='.$page.'&subcat='.$subcat.'&num='.$num.'&adm=additem" class="btn btn-primary"><i class="icon icon-white icon-page"></i> Добавить новую запись</a>';
			}	
			drawend:	
			echo '</center></div>';
		}
	}
?><br>
<span class="label label-important">Обратите внимание!</span>
<ul align="left" type="disc">
<li>Перед совершением покупки убедитесь, что выбрали нужного персонажа</li>
<li>Характеристики некоторых вещей в игре могут отличаться из-за особенностей отображения статов клиентом игры</li>
<li>Если указан не срок действия вещи, а количество - значит она не имеет срока действия</li>
<li>Меняя количество или срок действия, можно получить скидку (если она указана Администратором сервера)</li>
</ul><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>