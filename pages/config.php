<?php
if (!defined('main_def')) die();
$data = pathinfo($_SERVER['SCRIPT_FILENAME']);
$path = (isset($data['dirname']) && $data['dirname'] != '.')?$data['dirname']:'';
$data = pathinfo($_SESSION['script']);
$path_serv = (isset($data['dirname']) && $data['dirname'] != '.')?$data['dirname']:'';
$saved_params_count = 0;
if (isset($_POST) && count($_POST)) {
	// Сохранение параметров
	$error = false;
	$errortext = '';
	
	$params = array();
	$res = $db->query("SELECT `id`,`type`,`value` FROM `".$config_tbl_name."` ORDER by `id`");
	if ($db->errno) {
		$error = true;
		$errortext = $db->error;
	} else
	{
		while ($row = mysqli_fetch_assoc($res))
		{
			$data = array(
				'type' => $row['type'],
				'value' => $row['value']
			);
			$params[$row['id']] = $data;
		}
		foreach ($_POST as $i => $val){
			$value = $val;
			if (strpos($i, '_gold')) {
				$a = explode('_', $i);
				if (count($a)==2){
					$id = $a[0];
					$value = $val.'|'.$_POST[$id.'_silver'];
				}
			} else $id = $i;
			if (CheckNum($id)) continue;
			if (!array_key_exists($id, $params)) {
				$error = true;
				$errortext = "Параметр с ID $id не найден";
				break;
			}
			$row = $params[$id];
			switch ($row['type']) {
				case 'itemid':
				case 'int':
				case 'mask':
				case 'proctype':
					$value = intval($value);
					break;
				case 'float':
					$value = (float)$value;
					break;
				case 'bool':
					$value = ($value=='on')?1:0;
					break;
				case 'arraylist':
					$a = explode("\r\n", $value);
					$b = array();
					if (count($a)>0)
					foreach ($a as $i1 => $val1){
						if ($val1=='') continue;
						$c = explode('-',$val1);
						if (count($c)!=2) continue;
						$b[$c[0]] = $c[1];
					}
					$value = @serialize($b);
					break;
				case 'select':
					$c = explode(',',$row['value']);
					$c[count($c)-1] = $value;
					$value = implode(',',$c);
					break;
				case 'item':
					$pp = new Protocols();
					$item = array('id' => intval($value));
					foreach ($Structures['GRoleInventory'] as $ii => $val1)
					{
						if ($ii == 'id') continue;
						$item[$ii] = $_POST[$i.'_'.$ii];
					}
					$value = my_bin2hex($pp->marshal($item, $Structures['GRoleInventory']));
					unset($pp);					
					break;
			}
			if ($row['value'] != $value)
			{
				$query = sprintf("UPDATE `".$config_tbl_name."` SET `value`='%s' WHERE `id`=%d", $db->real_escape_string($value), $id);
				$res = $db->query($query);
				if ($db->errno) {
					$error = true;
					$errortext = $db->error;
					break;
				}
				$saved_params_count++;
			}
			unset($params[$id]);
		}
		// Проверяем оставшиеся булены
		foreach($params as $i => $val)
		{
			if ($val['type']=='bool' && $val['value'] != 0)
			{
				$query = sprintf("UPDATE `".$config_tbl_name."` SET `value`=0 WHERE `id`=%d", $i);
				$res = $db->query($query);
				if ($db->errno) {
					$error = true;
					$errortext = $db->error;
					break;
				}
				$saved_params_count++;
			}
		}
	}
}

// Вывод параметров
function ShowSection($n){
	global $init_itemid, $db, $config_tbl_name, $Structures;
	$res = $db->query("SELECT * FROM `".$config_tbl_name."` WHERE `section`=".intval($n).' ORDER by `group`,`id`');
	if ($db->errno) {
		echo GetErrorTxt(32);
		return;
	}
	echo '
	<table class="table table-bordered table-striped">
	';
	while ($row = mysqli_fetch_assoc($res)){
		$desc = str_replace("\r\n", '</code><br><code>', $row['desc']);
		$desc = str_replace("  ", '&nbsp;&nbsp;', $desc);
		$desc = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $desc);
		$name = $row['id'];
		switch ($row['type']) {
			case 'int':
				$e = '<input type="text" name="'.$name.'" value="'.intval($row['value']).'">';
				break;
			case 'mask':
				$e = '<input onkeyup="fillmask(this.value)" onfocus="showmask(this);" type="text" name="'.$name.'" value="'.intval($row['value']).'">';
				break;
			case 'proctype':
				$e = '<input onkeyup="fillproctype(this.value)" onfocus="showproctype(this);" type="text" name="'.$name.'" value="'.intval($row['value']).'">';
				break;
			case 'itemid':
				$e = '<input type="text" id="item_'.$name.'" name="'.$name.'" value="'.intval($row['value']).'" class="config_itemid" onchange="getName(\'item_'.$name.'\', \'loader_'.$name.'\', \'param_'.$name.'\');"> <span style="display:none" id="loader_'.$name.'"><img src="img/ajax-loaders/ajax-loader-1.gif" border="0" align="absmiddle"/></span> <span id="param_'.$name.'"></span>';
				$init_itemid.="getName('item_$name', 'loader_$name', 'param_$name');\r\n";
				break;
			case 'item':
				$pp = new Protocols();
				$v = $pp->unmarshal(my_hex2bin($row['value']), $Structures['GRoleInventory']);				
				unset($pp);
				$cnt = $v['count'].' x ';
				$e = '<span class="item_cnt" id="item_'.$name.'_count_span">'.$cnt.'</span><input type="text" id="item_'.$name.'" name="'.$name.'" value="'.intval($v['id']).'" class="config_itemid" onchange="getName(\'item_'.$name.'\', \'loader_'.$name.'\', \'param_'.$name.'\');" onclick="EditItem(\'item_'.$name.'\', \''.$name.'\')"> <span style="display:none" id="loader_'.$name.'"><img src="img/ajax-loaders/ajax-loader-1.gif" border="0" align="absmiddle"/></span> <span id="param_'.$name.'"></span>';
				foreach ($v as $ii => $vv)
				{
					if ($ii == 'id') continue;
					$e.=sprintf('<input type="hidden" id="item_%s_%s" name="%s_%s" value="%s">',$name, $ii, $name, $ii, $vv);
				}
				$init_itemid.="getName('item_$name', 'loader_$name', 'param_$name');\r\n";
				break;
			case 'bool':
				$c = ($row['value'])?'checked':'';
				$e = '<center><input data-no-uniform="true" '.$c.' type="checkbox" class="iphone-toggle" name="'.$name.'"></center>';
				break;
			case 'arraystring':
			case 'text':
				$wdh = '';
				if ($n == 5) $wdh = ' style="width:600px"';
				$e = '<textarea class="autogrow" name="'.$name.'"'.$wdh.'>'.$row['value'].'</textarea>';
				break;
			case 'desc':
				$e = '<h3>'.$row['value'].'</h3>';
				break;
			case 'arraylist':
				$a = @unserialize($row['value']);
				$it = '';
				if (is_array($a))
				foreach ($a as $i => $val){
					$it.=sprintf("%d-%d\r\n", $i, $val);
				}
				$e = '<textarea class="autogrow" name="'.$name.'">'.$it.'</textarea>';
				break;
			case 'cost':
				$c = explode('|', $row['value']);
				$gold = (int)$c[0];
				if (count($c) > 1) $silver = (int)$c[1]; else $silver = 0;
				$e = '<span class="gold"> <input type="text" name="'.$name.'_gold" value="'.$gold.'" class="config_cost"></span> ';
				$e .= '<span class="silver"> <input type="text" name="'.$name.'_silver" value="'.$silver.'" class="config_cost"></span>';
				break;
			case 'select':
				$c = explode(',', $row['value']);
				$e = '<select name="'.$name.'" data-rel="chosen" style="width:230px">';
				foreach ($c as $i => $val){
					if ($i>=(count($c)-1)) break;
					if ($i==$c[count($c)-1]) $ss = ' selected'; else $ss='';
						$e .= sprintf('<option value="%s"%s>%s</option>', $i, $ss, $val);
				}
				$e .= '</select>';
				break;
			default:
				$e = '<input type="text" name="'.$name.'" value="'.$row['value'].'">';
				break;
		}
		printf('
		<tr>
			<td><h3>%s</h3></td>
			<td>%s</td>
			<td style="text-align:left"><code>%s</code></td>
		</tr>
		',
		$row['name'], $e, $desc);
	}
	echo '</table>';
}
if (isset($_GET['r'])) {
	if (intval($_GET['r'])>=6) echo GetErrorTxt($_GET['r']);
}
if (isset($_GET['res'])) {
	if ($_GET['res']=='1') printf('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><strong>Готово!</strong> %s.</div>', 'Данные успешно сохранены. Изменено значений: '.$_GET['count']); else
	printf('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">×</button><strong>Ошибка!</strong> %s.</div>', $_GET['res']);
}
if (isset($_POST) && count($_POST)) {
	if (!$error) $errortext = '1';
	if (strpos($_SERVER['REQUEST_URI'], 'doconfig=1')>0) $uri = GetLink().'index.php?doconfig=1&res='.urlencode($errortext).'&count='.$saved_params_count; else
		$uri = GetLink().'index.php?op=config&res='.urlencode($errortext).'&count='.$saved_params_count;
	?>
	<script type="text/javascript">
		window.location.href = "<?=$uri?>";
	</script>
	<?php
	die('</div></body></html>');	
}
clearstatcache();
if (!is_writable($uploaddir))
	echo GetErrorTxt(88, 'Нет прав на запись в папку <b>'.$uploaddir.'</b>. Если не исправить эту ошибку, не будет возможности устанавливать значки кланов.');
if (!is_writable('top'))
	echo GetErrorTxt(88, 'Нет прав на запись в папку <b>top</b>. Если не исправить эту ошибку, не будет возможности обновлять топ100.');
?>
						<ul class="nav nav-tabs" id="myTab">							
							<li><a href="#basic" onclick="show_save_but(true)">Основные</a></li>
							<li><a href="#register" onclick="show_save_but(true)">Регистрация</a></li>
							<li><a href="#costs" onclick="show_save_but(true)">Стоимость и настройка услуг</a></li>
							<li><a href="#donate" onclick="show_save_but(true)">Настройка доната</a></li>
							<li><a href="#topbonus" onclick="show_save_but(true)">Бонусы за голосование</a></li>
							<li><a href="#mail" onclick="show_save_but(true)">Почта</a></li>
							<li><a href="#captcha" onclick="show_save_but(true)">Капча</a></li>
							<li><a href="#promo" onclick="show_save_but(false)">Промо коды</a></li>
							<li><a href="#system" onclick="show_save_but(false)">Системный раздел</a></li>
							<li><a href="#refresh" onclick="show_save_but(false)"><span id="upd_notice1"></span>Обновления<span id="upd_notice2"></span></a></li>							</ul>
						<form name="lk_config" action="#" method="post">						
						<div id="myTabContent" class="tab-content">							
							<div class="tab-pane active" id="basic">
								<?php ShowSection(0) ?>
							</div>

							<div class="tab-pane" id="register">
								<?php ShowSection(1) ?>
							</div>
							<div class="tab-pane" id="costs">
								<?php ShowSection(2) ?>
							</div>							
							<div class="tab-pane" id="donate">
								<?php ShowSection(3) ?>
							</div>
							<div class="tab-pane" id="topbonus">
								<?php ShowSection(4) ?>
							</div>
							<div class="tab-pane" id="mail">
								<?php ShowSection(5) ?>
								<div class="row-fluid">
									<div class="box span12">
										<div class="box-header well" data-original-title>
											<h2><i class="icon icon-color icon-refresh"></i> Отправка пробного письма</h2>
										</div>
										<div class="box-content">
											<p>Перед отправкой письма убедитесь, что сохранили изменения настроек</p>
											Куда: <input id="test_email" type="text">
											Шаблон: <select id="test_template">
												<option value="0">test_mail_template</option>
												<option value="1">reg_template</option>
												<option value="2">act_template</option>
												<option value="3">reset_template</option>
												<option value="4">rem_ip_process_template</option>
												<option value="5">rem_template</option>
												<option value="6">acc_data_template</option>
												<option value="7">change_passw_template</option>
											</select>
											<a href="#" id="send_mail_but" onclick="send_test_email()" class="btn btn-primary" target="_blank">Отправить пробное письмо</a> <span id="send_mail_progr" style="display:none"><div class="userinfo"><center><img src="img/ajax-loaders/ajax-loader-1.gif" border=0> Отправка пробного письма <img src="img/ajax-loaders/ajax-loader-1.gif" border=0></center></div></span>
										</div>
									</div>
								</div>
							</div>
							<div class="tab-pane" id="captcha">
								<?php ShowSection(6) ?>
							</div>
							</form>
							<div class="tab-pane" id="promo">							
								<?php require_once('config_promo.php');?>
							</div>							
							<div class="tab-pane" id="system">
		<p>Убедитесь, что у вас установлен <code>php5-cli</code> и <code>php5-curl</code></p>
		<div class="row-fluid">
			<div class="box span12">
				<div class="box-header well" data-original-title>
					<h2><i class="icon icon-color icon-refresh"></i> Обновление списка иконок для магазина</h2>
				</div>
				<div class="box-content">
					<p>Подготовка файлов:</p>
					<ul>
						<li>конвертируйте файл <code>iconlist_ivtr.dds</code><font color="#808080">(из surfaces/iconset)</font> в <code>iconlist_ivtr.png</code> с помощью утилиты <code>DDSConverter.exe</code></li>
						<li>залейте файлы <code>iconlist_ivtr.png</code> и <code>iconlist_ivtr.txt</code> в папку <code><?=$path?>/</code> клиент части</li>
					</ul>					
					<span class="label label-important">Обратите внимание!</span>
					<ul align="left" type="disc">
						<li>Если на хостинге стоит ограничение по объему памяти, воспользуйтесь <a href="http://alexdnepro.net/lk/icon_maker_jd.php" target="_blank">внешним генератором</a></li>
						<li>При использовании внешнего генератора, 
сгенерированный SQL файл импортируйте в базу данных клиент части</li>
					</ul>
					<center><a href="index.php?cron_act=make_icons&cron_passw=<?=$cron_act_passw?>" class="btn btn-primary" target="_blank">Обновить иконки магазина</a></center>
				</div>
			</div>							
		</div>		
		<div class="row-fluid">
			<div class="box span6">
				<div class="box-header well" data-original-title>
					<h2><i class="icon icon-color icon-refresh"></i> Обновление списка названий предметов для магазина</h2>
				</div>
				<div class="box-content">
					<p>Подготовка файлов:</p>
					<ul>
 						<li>сгенерируйте файл <code>items_ext.txt</code> с помощью утилиты <code>ItemNamesGen.exe</code></li>
						<li>залейте полученный файл на сервер часть в папку <code><?=$path_serv?>/</code></li>
					</ul>					
					 <center><a href="index.php?cron_act=make_names&cron_passw=<?=$cron_act_passw?>" target="_blank" class="btn btn-primary">Обновить названия</a></center>
				</div>
			</div>
			<div class="box span6">
				<div class="box-header well" data-original-title>
					<h2><i class="icon icon-color icon-script"></i> Чистка кеша иконок клана</h2>
				</div>
				<div class="box-content">
					<p>Чистку кеша нужно делать после замены файлов <code>iconlist_guild.png</code> и <code>iconlist_guild.txt</code> на сервер сайде</p>										
					 <center><a href="index.php?cron_act=clean_klan_cache&cron_passw=<?=$cron_act_passw?>" target="_blank" class="btn btn-primary">Очистить кеш значков кланов</a></center>
				</div>
			</div>			
		</div>
		<div class="row-fluid">
			<div class="box span12">
				<div class="box-header well" data-original-title>
					<h2><i class="icon icon-color icon-edit"></i> Данные для настройки платежных шлюзов</h2>
				</div>
				<div class="box-content">
					<ul>
						<li>Result URL (обработчик платежей) <code><?=GetLink()?>donres.php</code></li>
						<li>Success URL <code><?=GetLink()?>index.php?op=donsuccess</code></li>
						<li>Fail URL <code><?=GetLink()?>index.php?op=donfail</code></li>
					</ul>
					<p>Метод отправки данных принимается как <b>GET</b>, так и <b>POST</b></p>
				</div>
			</div>
		</div>
		<div class="row-fluid">
			<div class="box span12">
				<div class="box-header well" data-original-title>
					<h2><i class="icon icon-color icon-edit"></i> Данные скриптов, которые нужно добавить в crontab</h2>
				</div>
				<div class="box-content">
					<p><span class="label label-important">Важно!</span> Перед добавлением записей в cron, настройте параметр <code>cron_act_passw</code>, т.к. от него зависят генерируемые ссылки, а если будете его менять - не забывайте потом обновлять ссылки в кроне</p>
					<p>Для редактрирования crontab, введите в консоли <code>crontab -e</code></p>
					<p>Добавлять можно как в клиент части, так и в сервер (на случай если в клиент части нет доступа к крону)</p>
					
					<div class="row-fluid"><div class="box span12">
					<div class="box-header well" data-original-title>
						<h2>Ссылки кликабельны (для ручного запуска нужного скрипта)</h2>
					</div>
					<div class="box-content">
						<table class="table table-bordered table-striped">
						<thead>
						<tr>
							<th colspan=2>Список скриптов и их параметры</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td style="text-align:left"><code>*/<font color="#0000ff">5</font> * * * * <a href="index.php?cron_act=online_stat&cron_passw=<?=$cron_act_passw?>" target="_blank">curl "<?=GetLink()?>index.php?cron_act=online_stat&cron_passw=<?=$cron_act_passw?>"</a></code></td>
							<td style="text-align:left">Добавление записи в статистику онлайна каждые <font color="#0000ff">5</font> минут</td>
						</tr>
						<tr>
							<td style="text-align:left"><code><font color="#ff00ff">0</font> <font color="#0000ff">5</font> * * * <a href="index.php?cron_act=refresh_top&cron_passw=<?=$cron_act_passw?>" target="_blank">curl "<?=GetLink()?>index.php?cron_act=refresh_top&cron_passw=<?=$cron_act_passw?>"</a></code></td>
							<td style="text-align:left">Обновление топа каждый день в <font color="#0000ff">5</font>:<font color="#ff00ff">00</font></td>
						</tr>
						<tr>
							<td style="text-align:left"><code>30 */<font color="#0000ff">2</font> * * * <a href="index.php?cron_act=mmotop_bonus&cron_passw=<?=$cron_act_passw?>" target="_blank">curl "<?=GetLink()?>index.php?cron_act=mmotop_bonus&cron_passw=<?=$cron_act_passw?>"</a></code></td>
	 						<td style="text-align:left">Выдача бонусов за голосование в MMOTOP каждые <font color="#0000ff">2 часа</td>
						</tr>
						<tr>
							<td style="text-align:left"><code>35 */<font color="#0000ff">2</font> * * * <a href="index.php?cron_act=qtop_bonus&cron_passw=<?=$cron_act_passw?>" target="_blank">curl "<?=GetLink()?>index.php?cron_act=qtop_bonus&cron_passw=<?=$cron_act_passw?>"</a></code></td>
	 						<td style="text-align:left">Выдача бонусов за голосование в Q-TOP каждые <font color="#0000ff">2 часа</td>
						</tr>
						</tbody>
						</table>
					</div></div></div>
				</div>
			</div>
		</div>
		<?php $authd_link = sprintf('%sindex.php?cron_act=update_user_info&cron_passw=%s&id=%s', GetLink(), $cron_act_passw, '%d'); ?>
		<div class="row-fluid">
			<div class="box span12">
				<div class="box-header well" data-original-title>
					<h2><i class="icon icon-color icon-edit"></i> Данные, которые нужно добавить в конфиг <code>authd.conf</code> от сервиса Authd (для обновления информации аккаунта при выходе и других функций)</h2>
				</div>
				<div class="box-content">
					<code>[AlexPatch]</code><br>
					<code>logoutshell = curl <a href="<?=$authd_link?>" target="_blank"><?=$authd_link?></a>
					</code>
				</div>
			</div>
		</div>
		<div class="row-fluid">
			
		</div>
	</div>
	<div class="tab-pane" id="refresh">
			<p>Последняя версия ЛК: <span class="label label-info" id="last_ver"></span> <span id="ver_txt"></span></p>
			<div class="row-fluid">
				<div class="box span12">
					<div class="box-header well" data-original-title>
						<h2><i class="icon icon-color icon-book"></i> История обновлений</h2>
					</div>
					<div class="box-content">
						<span id="update_history"></span>
					</div>
				</div>
			</div>
	</div>
</div>
<center><a href="#" id="save_but" onclick="return SaveParams()" class="btn btn-large btn-inverse">Сохранить настройки</a></center>
<div class="popup__overlay">
    <div class="popup">	
        <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3 id="modalHeader">Редактирование предмета</h3>
	</div>
	<div class="modal-body">
		<p id="modalBody">			
			<div class="popup_clean2" id="proctypeBody1"></div>
			<table class="table table-bordered table-striped">
				<tr>
					<td><h3>id</h3></td>
					<td><input type="text" id="EditItem_id" name="EditItem_id" class="config_itemid" onchange="getName('EditItem_id', 'loader_EditItem', 'param_EditItem');"> <span style="display:none" id="loader_EditItem"><img src="img/ajax-loaders/ajax-loader-1.gif" border="0" align="absmiddle"/></span> <span id="param_EditItem"></span></td>
				</tr>
				<?php
				foreach ($Structures['GRoleInventory'] as $i => $val)
				{
					if ($i == 'id') continue;
					$sh = '';
					if ($i == 'pos' || $i == 'guid1' || $i == 'guid2') $sh = ' style="display:none"';
					if ($i == 'proctype') $pr = ' onkeyup="fillproctype(this.value)" onfocus="showproctype1(this,\'.popup_clean2\',\'#proctypeBody1\');"'; else $pr = '';
					printf('<tr%s><td><h3>%s</h3></td><td><center><input type="text" name="EditItem_%s" id="EditItem_%s"%s></center></td></tr>', $sh, $i, $i, $i, $pr);
				}
?>				<tr><td colspan=2><center><span class="btn btn-large btn-primary" onclick="SaveItem()">Сохранить</span></center></td>
			</table>
		</p>
	</div>	
    </div>
</div>
<script type="text/javascript">
var CurItemName;
var CurItemParamID;
function SaveItem()
{
<?php
	foreach ($Structures['GRoleInventory'] as $i => $val)
	{
		if ($i == 'id') $i1 = ''; else $i1 = '_'.$i;
		printf("\t$('#'+CurItemName+'%s').val($('#EditItem_%s').val());\n", $i1, $i);
	}
?>
	$('#'+CurItemName+'_count_span').html($('#EditItem_count').val()+' x');
	getName(CurItemName, 'loader_'+CurItemParamID, 'param_'+CurItemParamID);
	p = $('.popup__overlay');
	$('html').css('overflow', 'auto');
	$('body').css('overflow', 'auto');
	p.css('display', 'none');	
}

function EditItem(i, i1)
{
	CurItemName = i;
	CurItemParamID = i1;
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
<?php
	foreach ($Structures['GRoleInventory'] as $i => $val)
	{
		if ($i == 'id') $i1 = ''; else $i1 = '_'.$i;		
		printf("\t$('#EditItem_%s').val($('#'+i+'%s').val());\n", $i, $i1, $i1);
	}	
?>	getName('EditItem_id', 'loader_EditItem', 'param_EditItem');	
}

function show_save_but(b){
	var QQ = "";
	if (!b) QQ = "none";
	document.getElementById("save_but").style.display = QQ;
}
function getName(id, id1, id2) {
	if ($("#"+id).val() == '0' || $("#"+id).val() == '')
	{
		document.getElementById(id2).innerHTML='';
		return;
	}
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

function InitItemID(){
	<?=$init_itemid?>
}

function SaveParams(){
	if (window.confirm('Сохранить настройки?'))
		document.lk_config.submit();
}
function send_test_email(){
	var tmail = $('#test_email').val();
	if (tmail == '') {
		alert('Укажите e-mail');
		return;
	}
	var tt = $('#test_template').val();
	if (!window.confirm('Вы действительно хотите отправить пробное письма на почту '+tmail+'?')) return;	
	$('#send_mail_but').css('display' , 'none');
	$('#send_mail_progr').css('display', 'inline-block');
	var X = new XMLHttpRequest();
	X.open("POST", "index.php?op=act&n=76&num=0&mail="+tmail+"&temp="+tt);
	X.onreadystatechange = function() {
		if (X.readyState == 4) {				
			if(X.status == 200) {
				$('#send_mail_progr').css('display' , 'none');
				$('#send_mail_but').css('display', 'inline-block');
				var txt = "error"; var txt1 = X.responseText;
				if (txt1 == "16") {
					txt = "success";
					txt1 = "Письмо успешно отправлено";
				}
				noty({"text":txt1,"layout":"top","type":txt});
			}
		}
	};
	X.send();
}
function GetLastVer(){
	var AJAX_IMG = ' <img src="img/ajax-loaders/ajax-loader-1.gif" border="0" align="absmiddle"/> ';
	$('#last_ver').html(AJAX_IMG);
	$('#update_history').html(AJAX_IMG);
	$.ajax({
		url: "pages/server_processing.php?update_info",
		type: 'GET',
		dataType: 'json',		
		success: function(data) {
			$('#last_ver').html(data.last_ver);
			$('#ver_txt').html(data.ver_txt);
			$('#update_history').html(data.history);
			$('#upd_notice1').html(data.upd_notice);
			$('#upd_notice2').html(data.upd_notice);
		},
		error: function() {
			$('#last_ver').html('Произошла ошибка! Попробуйте позже');
			$('#update_history').html('');
			$('#upd_notice1').html('');
			$('#upd_notice2').html('');
		}
	})	
}
setTimeout(InitItemID, 500);
setTimeout(GetLastVer, 1500);
</script>