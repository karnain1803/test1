<?php
if (!defined('donateres_def')) die();
$unitpayIp = array(
    '31.186.100.49',
    '178.132.203.105',
    '52.29.152.23',
    '52.19.56.234'
);
if (!in_array(getIP(), $unitpayIp, false)) {
    die('IP address Error');
}
//donate status code: 0 - ожидает оплаты, 1 - данные получены, ожидаем ответа сервера, 2 - Неполные входящие данные
//		3 - логин не найден, 4 - ошибка оплаты, 100 - успешный ответ сервера, 101 - повторный запрос, без выдачи бонусов

// Формирование цифровой подписи для массива параметров
function md5sign($method, $params, $secretKey) {
    $delimiter = '{up}';
    ksort($params);
	unset($params['sign'], $params['signature']);
	foreach ($params as $i => $p)
	{
		if (preg_match('/^\d+$/', $i))
		{
			// Удаляем числовые ключи для фикса бага параметра 9223372036854775807
			unset($params[$i]);
		}
	}
    $params[] = $secretKey;
    array_unshift($params, $method);
    return hash('sha256', implode($delimiter, $params));
} 
// Ошибочный ответ партнера
function responseError($message) {
    $error = array(
        "jsonrpc" => "2.0",
        "error" => array(
            "code" => -32000,
            "message" => $message
        ),
        'id' => 1
    );
    echo json_encode($error); exit();
} 
// Успешный ответ партнера
function responseSuccess($message) {
    $success = array(
        "jsonrpc" => "2.0",
        "result" => array(
            "message" => $message
        ),
        'id' => 1
    );
    echo json_encode($success); exit();
}
function query2mysql($sql){
	global $db;
	$res = $db->query($sql);	
	if ($db->errno>0) {
		//echo $sql;
		responseError("MysqlError: ".$db->error."\n");		
	}
	return $res;
}

// HTTP параметры:
$method = $_GET['method'];
$params = $_GET['params'];
if (!is_array($params)) responseError("Input data error");
//Сверяем подписи
if ($params['signature'] != md5sign($method, $params, $unitpay_secret_key)) {
    responseError("Sign verify error");
}

$out_summ = $params['orderSum'];
query2mysql('set names utf8');
switch($method) {
    case 'check':
        // Проверяем что можем оказать абоненту 
	$sql = sprintf("SELECT * FROM `donate_unitpay` WHERE `id`='%s'", $db->real_escape_string($params['account']));
	$res = query2mysql($sql);
	if ($res->num_rows==0) responseError("Record ".$params['account'].' not found');
        break;
    case 'pay':
        // Оказываем услугу
	// Bonux Fix begin	
	$clean_act_bonus = $act_bonus;
	// Bonus Fix end
	ret:
	$act_bonus = $clean_act_bonus;
	$sql = sprintf("SELECT * FROM `donate_unitpay` WHERE `id`='%s'", $db->real_escape_string($params['account']));
	$res = query2mysql($sql);
	if ($res->num_rows==0) responseError("Record ".$params['account'].' not found');
	$row = mysqli_fetch_assoc($res);
	$cur_user_donate = GetUserDonateSumm($row['login'], false);
	$don = CalcDonate($out_summ);
	$don['op'] = 'don';
	$don['ip'] = $row['ip'];
	$don['serv'] = $row['serv'];
	$don['out_summ'] = $out_summ;
	$don['inv_id'] = intval($params['account']);
	$don['p_sys_id'] = $params['unitpayId'];
	$don['don_kurs'] = $don_kurs;
	$don['act_bonus'] = $act_bonus+$dopbon;
	$don['ip'] = getIP();
	$don['send_bonus_item'] = $send_bonus_item;
	$don['item'] = serialize($bonus_item);
	$don['login'] = $row['login'];
	$don['userid'] = $row['userid'];
	$don['don_system'] = $donate_system;
	$don['ref_don_bonus_enable'] = $ref_don_bonus_enable;
	$don['ref_don_bonus'] = $ref_don_bonus;
	$don['ref_don_bonus_timeused'] = $ref_don_bonus_timeused;
	//print_r($don);
	if (!isset($params['phone'])) $params['phone'] = '';
	if ($params['phone'] == '' && isset($params['purse'])) $params['phone'] = $params['purse'];
	if ($params['phone'] == '' && isset($params['email'])) $params['phone'] = $params['email'];
	if ($row['status'] >= 100) $status = $row['status']; else $status = 1;
	query2mysql(sprintf("UPDATE `donate_unitpay` SET `status`=%d, `out_summ`='%s', `profit`='%s', `money`='%s', `bonus_money`='%s', `operator`='%s', `paymentType`='%s', `phone`='%s', `unitpayId`='%s' WHERE `id`=%d", $status, $params['sum'], $params['profit'], $don['moneycount'], $don['bonus'], $db->real_escape_string($params['operator']), $db->real_escape_string($params['paymentType']), $db->real_escape_string($params['phone']), $db->real_escape_string($params['unitpayId']), $don['inv_id']));
	$res = CurlPage($don, 20);
	if ($res == 'NeedNewID') {
		// Создаем новую заявку
		$sql = sprintf("INSERT INTO `donate_unitpay` (`data`,`out_summ`,`don_kurs`,`money`,`act_bonus`,`bonus_money`,`login`,`userid`,`ip`,`serv`,`status`) VALUES (now(),'%s','%s','%s','%s','%s','%s','%s','%s','%s', 0)", $db->real_escape_string($out_summ), $don_kurs, $don['moneycount'], $don['act_bonus'], $don['bonus'], $db->real_escape_string($don['login']), $don['userid'], $don['ip'], $don['serv']);
		$res = $db->query($sql);
		$params['account'] = $db->insert_id;
		goto ret;
	}
	query2mysql("UPDATE `donate_unitpay` SET `status`='$res' WHERE `id`=".$don['inv_id']);
	if ($res!=100 && $res!=101) responseError("Server error ".$res);
        break;
    case 'error':
        // Фиксируем ошибку оплаты
	if (!isset($params['errorMessage'])) $params['errorMessage'] = '';
	if (!isset($params['phone'])) $params['phone'] = '';
	$sql = sprintf("SELECT `status` FROM `donate_unitpay` WHERE `id`='%s'", $db->real_escape_string($params['account']));
	$res = query2mysql($sql);
	if ($res->num_rows==0) responseError("Record ".$params['account'].' not found');
	$row = mysqli_fetch_assoc($res);
	if ($row['status']!=100 && $row['status']!=101){		
		query2mysql(sprintf("UPDATE `donate_unitpay` SET `status`=4, `out_summ`='%s', `profit`='%s', `operator`='%s', `paymentType`='%s', `phone`='%s', `unitpayId`='%s', `errMsg`='%s' WHERE `id`=%d", $params['sum'], $params['profit'], $db->real_escape_string($params['operator']), $db->real_escape_string($params['paymentType']), $db->real_escape_string($params['phone']), $db->real_escape_string($params['unitpayId']), $db->real_escape_string($params['errorMessage']), intval($params['account'])));				
	}
        break;
    default:
        responseError("Incorrect method, supported methods: error, check and pay"); exit;
}

responseSuccess("Успех");

