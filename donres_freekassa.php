<?php
if (!defined('donateres_def')) die();
//donate status code: 0 - ожидает оплаты, 1 - данные получены, ожидаем ответа сервера, 2 - Неполные входящие данные
//		3 - логин не найден, 4 - ошибка оплаты, 100 - успешный ответ сервера, 101 - повторный запрос, без выдачи бонусов

// Формирование цифровой подписи для массива параметров
function make_signature($merchant_id, $out_amount, $secret_word, $order_id)
{
    return md5($merchant_id.":".$out_amount.":".$secret_word.":".$order_id);
}
// Ошибочный ответ партнера
function responseError($message) {
    echo $message; exit();
} 
// Успешный ответ партнера
function responseSuccess($message) {
    echo $message; exit();
}
function query2mysql($sql){
	global $db;
	$res = $db->query($sql);	
	if ($db->errno>0) responseError("MysqlError: ".$db->error."\n");
	return $res;
}

// Проверяем айпи в новой версии
if ($freekassa_new_version && !in_array(getIP(), array('168.119.157.136', '168.119.60.227', '138.201.88.124'))) {
	die("hacking attempt!");
}

// HTTP параметры:
if (!isset($_REQUEST['AMOUNT']) || !isset($_REQUEST['SIGN']) || !isset($_REQUEST['MERCHANT_ORDER_ID']) || !isset($_REQUEST['intid'])) {
	responseError("Input data error");
}
//Сверяем подписи

if ($_REQUEST['SIGN'] != make_signature($freekassa_merchant_id, $_REQUEST['AMOUNT'], $freekassa_secret_word2, $_REQUEST['MERCHANT_ORDER_ID'])) {
    responseError("Signature veriry fail");
}

$out_summ = $_REQUEST['AMOUNT'];

// Оказываем услугу
$sql = sprintf("SELECT * FROM `donate_freekassa` WHERE `id`='%s'", $db->real_escape_string($_REQUEST['MERCHANT_ORDER_ID']));
$res = query2mysql($sql);
if ($res->num_rows==0) responseError("Order ".$_REQUEST['MERCHANT_ORDER_ID'].' not found');
$row = mysqli_fetch_assoc($res);
$cur_user_donate = GetUserDonateSumm($row['login'], false);
$don = CalcDonate($out_summ);
$don['op'] = 'don';
$don['out_summ'] = $out_summ;
$don['p_sys_id'] = '';
$don['inv_id'] = (int)$_REQUEST['MERCHANT_ORDER_ID'];
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
query2mysql(sprintf("UPDATE `donate_freekassa` SET `status`=1, `out_summ`='%s', `currency_id`='%s', `money`='%s', `bonus_money`='%s', `email`='%s', `intid`='%s' WHERE `id`=%d", $out_summ, $_REQUEST['CUR_ID'], $don['moneycount'], $don['bonus'], $db->real_escape_string($_REQUEST['P_EMAIL']), $db->real_escape_string($_REQUEST['intid']), $don['inv_id']));
$res = CurlPage($don, 20);
query2mysql("UPDATE `donate_freekassa` SET `status`='$res' WHERE `id`=".$don['inv_id']);
if ($res!=100 && $res!=101) responseError("Server error ".$res);
        
responseSuccess("YES");
