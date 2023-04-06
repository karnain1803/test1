<?php
if (!defined('main_def')) die();
if (!$_SESSION['isadmin']) die('Access denied');
$data = pathinfo($_SERVER['SCRIPT_FILENAME']);
$path = (isset($data['dirname']) && $data['dirname'] != '.')?$data['dirname']:'';
$data = pathinfo($_SESSION['script']);
$path_serv = (isset($data['dirname']) && $data['dirname'] != '.')?$data['dirname']:'';
require_once('pclzip.lib.php');

function CheckArchive($fn){
	global $path;
	$error = '';
	$res = true;
	$error = '';
	$archive = new PclZip($fn);
	$files = $archive->listContent();
	$addmsg = false;
	if (!is_array($files) || count($files)==0 || !file_exists($fn)) {
		$error = 'Ошибка открытия файла '.$fn;
		$res = false;
	} else {
		foreach ($files as $i => $val)
		{
			if (file_exists($val['filename'])) {
				if (!is_writable($val['filename'])) {
					$res = false;
					$error .= 'Нет прав на запись файла <code>'.$val['filename'].'</code><br>';
					$addmsg = true;
				}
			} else {
				$data = pathinfo($val['filename']);			
				$p = (isset($data['dirname']) && $data['dirname'] != '.')?$data['dirname']:'';
				$p = $path.'/'.$p;
				if (!is_writable($p)) {
					$res = false;
					$error .= 'Нет прав на запись в папку <code>'.$p.'</code><br>';
					$addmsg = true;
				}
			}
		}
		if ($addmsg) {
			$userinfo = posix_getpwuid(posix_getuid());
			$error .= '<br>Выполните команду <code>chown -R '.$userinfo['name'].' '.$path.'</code> (или установите права на запись) и попробуйте запустить обновление ещё раз';
		}
	}
	if (!$res) echo GetErrorTxt(88, $error);
	return $res;
}

function TrySave($fn, $data){
	$fp = @fopen($fn, "w");
	if (!$fp) {
		echo GetErrorTxt(88, 'Ошибка создания файла '.$fn);
		return false;
	}
	fwrite($fp, $data);
	fclose($fp);
	return true;
}

function SaveLogs($version, $client_log, $server_log){
	if ($client_log == '' && $server_log == '') return true;
	$fn = sprintf('update_%s.log', $version);
	$fp = @fopen($fn, "w");
	if (!$fp) {
		echo GetErrorTxt(88, 'Ошибка создания файла '.$fn);
		return false;
	}
	if ($client_log != '') {
		fwrite($fp, "\tCLIENT SIDE\n");
		fwrite($fp, $client_log."\n\n");
	}
	if ($server_log != '') {
		fwrite($fp, "\tSERVER SIDE\n");
		fwrite($fp, $server_log);
	}
	fclose($fp);
	return true;
}

if (!is_writable($path)) {
	echo GetErrorTxt(88, 'Нет прав на запись в папку '.$path);
	die();
}

$state_head = '<div class="clear"></div>
<div class="box-header well" data-original-title>
	<h2><i class="%s"></i> %s</h2>
	<div class="box-icon">
		<a href="#" class="btn btn-minimize btn-round"><i class="icon-chevron-down"></i></a>
	</div>
</div>
<div class="box-content" style="display:none">';
$state_footer = '</div><div class="clear"></div>';
$d = UpdatePage('getupdates', 520);
$updates = @unserialize($d);
$cur_ver = $lk_ver;
$server_log = '';
$client_log = '';
if (is_array($updates)) {	
	foreach ($updates as $i => $val){
		$cur_ver = $val['version'];
		$server_log = '';
		$client_log = '';
		echo '<div class="row-fluid">
			<div class="box span12">
				<div class="box-header well" data-original-title>
					<h2><i class="icon icon-color icon-refresh"></i> Обновление до версии '.$val['version'].'</h2>
				</div>
				<div class="box-content">';
		if ($val['client_zip'] != '') {			
			$fn = $path.'/client.zip';
			if (!TrySave($fn, base64_decode($val['client_zip']))) break;
			if (!CheckArchive($fn)) {
				if (!unlink($fn)) echo GetErrorTxt(88, 'Ошибка удаления файла '.$fn);
				break;
			}
		}
		echo '<h2>Server Side</h2>';
		$postdata = array(
			'op' => 'update',
			'ip' => getIP(),
			'id' => $_SESSION['id'],
			'server_sql' => $val['server_sql'],
			'server_zip' => $val['server_zip']
		);		
		$res = CurlPage($postdata, 560, 1, true);
		$r = @unserialize($res);
		if (!is_array($r)) {
			echo $res;
			break;
		}		
		echo $r['text'];
		$server_log = $r['log'];
		if ($r['errorcode'] != 1) break;
		flush();
		echo '<h2>Client Side</h2>';
		if ($val['client_zip'] != '') {			
			$archive = new PclZip($fn);
			$r = $archive->extract(PCLZIP_OPT_REPLACE_NEWER);
			$succ_files = 0; $fail_files = 0;
			echo sprintf($state_head, 'icon-folder-open', 'Обновление файлов');
			if (!unlink($fn)) echo GetErrorTxt(88, 'Ошибка удаления файла '.$fn);
			if ($r == 0) {
				echo GetErrorTxt(88, $archive->errorInfo(true));				
				break;
			} else {
				$client_log .= "\tОбновление файлов\n";
				foreach ($r as $i1 => $val1){
					if ($val1['status']=='ok' || $val1['status'] == 'already_a_directory') {
						if ($val1['folder'] == 0) $succ_files++; 
						$num = 89;
					} else {
						if ($val1['folder'] == 0) $fail_files++;
						$num = 88;
					}
					if ($val1['folder'] == 1) $f = 'Папка: '; else $f = 'Файл: ';
					$f .= $val1['filename'];
					$client_log .= sprintf("%s Status: %s\n", $f, $val1['status']);
					echo GetErrorTxt($num, sprintf('<code>%s</code> Status: <code>%s</code>', $f, $val1['status']));
				}				
			}
			echo $state_footer;
			if ($fail_files > 0) printf('<div class="alert alert-error">Обновлено файлов: <span class="label label-inverse">%d</span><span class="label label-success">успешно</span>, <span class="label label-inverse">%d</span><span class="label label-important">с ошибкой</span></div>', $succ_files, $fail_files); else printf('<div class="alert alert-success">Обновлено файлов: <span class="label label-inverse">%d</span><span class="label label-success">успешно</span></div>', $succ_files);
		}
		if ($val['client_sql'] != '') {
			echo sprintf($state_head, 'icon-hdd', 'SQL запросы в базу данных');
			$succ_sql = 0; $fail_sql = 0;
			$client_sql = explode("\n", str_replace('`config`', '`'.$config_tbl_name.'`', $val['client_sql']));
			$client_log .= "\tSQL запросы в базу данных\n";
			foreach ($client_sql as $i1 => $val1){
				$res = $db->query($val1);
				if (!$res) {
					$fail_sql++;
					echo GetErrorTxt(88, sprintf('<code>%s</code><br>Запрос: <code>%s</code>', $db->error, $val1));
					$client_log .= sprintf("Ошибка: %s\nЗапрос: %s\n", $db->error, $val1);
				} else {
					echo GetErrorTxt(89, sprintf('<code>%s</code>', $val1));
					$client_log .= sprintf("%s\n", $val1);
					$succ_sql++;
				}
			}
			echo $state_footer;
			if ($fail_sql > 0) printf('<div class="alert alert-error">Выполнено запросов в базу данных: <span class="label label-inverse">%d</span><span class="label label-success">успешно</span>, <span class="label label-inverse">%d</span><span class="label label-important">с ошибкой</span></div>', $succ_sql, $fail_sql); else printf('<div class="alert alert-success">Выполнено запросов в базу данных: <span class="label label-inverse">%d</span><span class="label label-success">успешно</span></div>', $succ_sql);
		}		
		echo '</div></div></div>';
		flush();
		SaveLogs($cur_ver, $client_log, $server_log);
		$client_log = ''; $server_log = '';
	}
	SaveLogs($cur_ver, $client_log, $server_log);
} else echo GetErrorTxt(88, 'Ошибка получения обновлений '.$d);
