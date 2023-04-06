<?php
session_start();
date_default_timezone_set('Europe/Moscow');
$_SESSION['script'] = '';
include('config_db.php');
if (!isset($ext_users)) $ext_users = array();
// Версия ЛК
$lk_ver = '1.0';
// Копирайт футера справа
$footer_right = 'Powered by: <a href="http://usman.it/free-responsive-admin-template" target="_blank">Charisma</a>';
// Меню: Иконка^Название^Ссылка^Доступ, для разделителя Иконка^Название
// Доступ: 0 - для всех, 1 - для ГМ, 2 - для админов, 3 - для списка юзеров
if (!isset($menu)) {
$menu = array(
	'^Управление^^0',
		'icon-home^Главная^main^0',
		'icon-gear^Настройки аккаунта^account^0',
		'icon-users^Персонажи^pers^0',
		'icon-cart^Магазин^shop^0',
		'icon-contacts^Управление монетами^money^0',
		'icon-flag^Клановый раздел^klan^0',
		'icon-basket^Донат^donate^0',		
	'^Статистика^^0',
		'icon-globe^Топ 100^top^0',
		'icon-document^История действий^history^0',
	'^Админ раздел^^2',
		'icon-note^Статистика онлайна^online^2',
		'icon-script^Статистика доната^stat^2',
		'icon-document^Статистика голосования^stattop^2',
		'icon-date^Логи входа^loginlogs^2',
		'icon-book^Администрирование^accounts^2',
		'icon-book^Логи ЛК^lklogs^2',
		'icon-gear^Настройки ЛК^config^2'
);
}

$global_admin = false;
if (!isset($_SESSION['vk_id'])) $_SESSION['vk_id'] = '';
if (!isset($_SESSION['vk_name'])) $_SESSION['vk_name'] = '';
if (!isset($_SESSION['vk_photo'])) $_SESSION['vk_photo'] = '';
if (!isset($_SESSION['do_vklogin'])) $_SESSION['do_vklogin'] = false;
if (!isset($_SESSION['steam_id'])) $_SESSION['steam_id'] = '';
if (!isset($_SESSION['steam_name'])) $_SESSION['steam_name'] = '';
if (!isset($_SESSION['steam_photo'])) $_SESSION['steam_photo'] = '';
if (!isset($_SESSION['do_steamlogin'])) $_SESSION['do_steamlogin'] = false;

function getIP() {
//if(isset($_SERVER['HTTP_X_REAL_IP'])) return $_SERVER['HTTP_X_REAL_IP'];
   return $_SERVER['REMOTE_ADDR'];
}

function GetOccupationName($o)
{
	$occ = array(0 => 'Новичок', 1 => 'Вим I', 2 => 'Вим II', 3 => 'Вим III', 4 => 'Хаккан I', 5 => 'Хаккан II', 6 => 'Хаккан III', 7 => 'Айне I', 8 => 'Айне II', 9 => 'Айне III', 10 => 'Скайя I', 11 => 'Скайя II', 12 => 'Скайя III', 13 => 'Вим IV', 14 => 'Вим V', 16 => 'Хаккан IV', 17 => 'Хаккан V', 19 => 'Айне IV', 20 => 'Айне V', 22 => 'Скайя IV', 23 => 'Скайя V', 25 => 'Морто I', 26 => 'Морто II', 27 => 'Морто III', 28 => 'Морто IV', 29 => 'Морто V', 33 => 'Титаны I', 34 => 'Титаны II', 35 => 'Титаны III', 36 => 'Титаны IV', 37 => 'Титаны V', 39 => 'Арден I', 40 => 'Арден II', 41 => 'Арден III', 42 => 'Арден IV', 43 => 'Арден V', 45 => 'Умбра I', 46 => 'Умбра II', 47 => 'Умбра III', 48 => 'Умбра IV', 49 => 'Умбра V', 51 => 'Вайлин I', 52 => 'Вайлин II', 53 => 'Вайлин III', 54 => 'Вайлин IV', 55 => 'Вайлин V', 56 => 'Ниру I', 57 => 'Ниру II', 58 => 'Ниру III', 59 => 'Ниру IV', 60 => 'Ниру V', 64 => 'Тайо I', 65 => 'Тайо II', 66 => 'Тайо III', 67 => 'Тайо IV', 68 => 'Тайо V', 96 => 'Войда I', 97 => 'Войда II', 98 => 'Войда III', 99 => 'Войда IV', 100 => 'Войда V', 102 => 'Психея I', 103 => 'Психея II', 104 => 'Психея III', 105 => 'Психея IV', 106 => 'Психея V', 108 => 'Кентавр I', 109 => 'Кентавр II', 110 => 'Кентавр III', 111 => 'Кентавр IV', 112 => 'Кентавр V', 117 => 'Гидран I', 118 => 'Гидран II', 119 => 'Гидран III', 120 => 'Гидран IV', 121 => 'Гидран V');
	if (array_key_exists($o, $occ)) return $occ[$o];
	return $o;
}

class ExtItem {
	var	$name='';
	var	$icon='';
	var	$list=0;
}

class AccumItem {
	var	$summ;
	var	$bonus;
}

$Structures = array(
	'GRoleInventory' => array(
		'id' => 'unsigned int',
		'pos' => 'int',
		'count' => 'int',
		'client_size' => 'short',
		'max_count' => 'short',
		'data' => 'Octets',
		'proctype' => 'int',
		'expire_date' => 'int',
		'guid1' => 'int',
		'guid2' => 'int'
	)
);

function GetIconHTML($i, $size = 24)
{
	return sprintf('<img src="getitemicon.php?i=%s" border="0" class="item_icon" style="width: %spx; margin: 0"> ', urlencode(base64_encode($i)), $size);
}

function my_hex2bin($d)
{
	if ($d == '') return '';
	return @hex2bin($d);
}

function my_bin2hex($d)
{
	if ($d == '') return '';
	return @bin2hex($d);
}

function cuint($data)
{
        if($data <= 0x7F)
                return pack("C", $data);
        else if($data < 16384)
                return pack("n", ($data | 0x8000));
        else if($data < 536870912)
                return pack("N", ($data | 0xC0000000));
        return pack("c", -32) . pack("N", $data);
}

class PacketStream {	
        private $count;
	public $buffer;
	public $done;		// Окончание процесса чтения пакета
	public $overflow;	// Перебор чтения из пакета
        public $pos;	

	function __construct($s='',$nulpos=true){
		$this->buffer = $s;
		if ($nulpos) $this->pos = 0;
		$this->count = strlen($s);
		$this->done = false;
		$this->overflow = false;
	}

	function Clear(){
		$this->buffer = '';
		$this->pos = 0;
	}

	function ReadUChar(){
		if ($this->pos<$this->count) {
			$t=unpack("C",substr($this->buffer,$this->pos,1));
			$this->pos++;
			if ($this->pos>=$this->count) $this->done=true;
			return $t[1];
		} else {
			$this->overflow=true;
			return 0;
		}
	}
	
	function ReadChar(){
		if ($this->pos<$this->count) {
			$t=unpack("c",substr($this->buffer,$this->pos,1));
			$this->pos++;
			if ($this->pos>=$this->count) $this->done=true;
			return $t[1];
		} else {
			$this->overflow=true;
			return 0;
		}
	}

	function UpdateWriteCount(){
		$this->wcount=cuint(strlen($this->buffer));
	}

	function WriteChar($b){
		$this->buffer.=pack("c",$b);
	}

	function WriteUChar($b){
		$this->buffer.=pack("C",$b);
	}

	function ReadInt32($bigendian=true){
		if ($this->pos+3<$this->count) {
			if ($bigendian==true) $t = unpack("i",strrev(substr($this->buffer,$this->pos,4))); else
			$t=unpack("i",substr($this->buffer,$this->pos,4));
			$this->pos+=4;
			if ($this->pos>=$this->count) $this->done=true;
			return $t[1];
		} else {
			$this->overflow=true;
			return 0;
		}
	}

	function ReadUInt32($bigendian=true){
		if ($this->pos+3<$this->count) {
			if ($bigendian==true) $t = unpack("I",strrev(substr($this->buffer,$this->pos,4))); else
			$t=unpack("I",substr($this->buffer,$this->pos,4));
			$this->pos+=4;
			if ($this->pos>=$this->count) $this->done=true;
			return $t[1];
		} else {
			$this->overflow=true;
			return 0;
		}
	}

	function ReadInt64($bigendian = true){
		if ($this->pos+7 < $this->count) {
			$data = substr($this->buffer, $this->pos, 8);
			if ($bigendian) $data = strrev($data);
			//if (PHP_VERSION_ID >= 50603 && PHP_INT_SIZE != 4) {
			//	// Если пыха 5.6.3+
			//	$t = unpack("q", $data);
			//	$result = $t[1];
			//} else 
			//if (PHP_INT_SIZE != 4) {
			//	// Если нет - используем велосипед
			//	$int_parts = unpack('V2', $data);
			//        $result = $int_parts[1] << 32 | $int_parts[2];
			//} else {
				$result = '0x'.my_bin2hex($data);
			//}
			$this->pos += 8;
			if ($this->pos >= $this->count) $this->done = true;
			return $result;
		} else {
			$this->overflow = true;
			return 0;
		}
	}

	function WriteInt32($b,$bigendian=true){
		$data = pack("i",$b);
		if ($bigendian==true) $this->buffer.=strrev($data); else $this->buffer.=$data;
	}

	function WriteUInt32($b,$bigendian=true){
		if ($bigendian==true) $this->buffer.=pack("N",$b); else $this->buffer.=pack("V",$b);
	}

	function WriteInt64($b,$bigendian=true){
		//if (PHP_VERSION_ID >= 50603 && PHP_INT_SIZE != 4) {
		//	// Если пыха 5.6.3+
		//	if ($bigendian) $this->buffer .= pack("J",$b); else $this->buffer .= pack("P",$b);
		//} else 
		//if (PHP_INT_SIZE != 4) {
		//	$t = pack("i", $b).pack("i", $b >> 32);
		//	if ($bigendian) $t = strrev($t);
		//	$this->buffer .= $t;
		//} else {
			$b = substr($b, 2);
			$t = my_hex2bin($b);
			if ($bigendian) $t = strrev($t);
			$this->buffer .= $t;
		//}
	}

	function ReadInt16($bigendian=true){
		if ($this->pos+1<$this->count) {
			if ($bigendian) $t = unpack("s",strrev(substr($this->buffer,$this->pos,2))); else
			$t = unpack("s",substr($this->buffer,$this->pos,2));
			$this->pos+=2;
			if ($this->pos>=$this->count) $this->done = true;
			return $t[1];
		} else {
			$this->overflow = true;
			return 0;
		}
	}

	function ReadUInt16($bigendian=true){
		if ($this->pos+1<$this->count) {
			if ($bigendian) $t=unpack("n",substr($this->buffer,$this->pos,2)); else
			$t=unpack("v",substr($this->buffer,$this->pos,2));
			$this->pos+=2;
			if ($this->pos>=$this->count) $this->done=true;
			return $t[1];
		} else {
			$this->overflow=true;
			return 0;
		}
	}

	function WriteInt16($b,$bigendian=true){
		$data = pack("s",$b);
		if ($bigendian==true) $this->buffer.=strrev($data); else $this->buffer.=$data;
	}

	function WriteUInt16($b,$bigendian=true){
		if ($bigendian==true) $this->buffer.=pack("n",$b); else $this->buffer.=pack("v",$b);
	}

	function ReadFloat($bigendian=true){
		if ($this->pos+3<$this->count) {
			if ($bigendian==true) $t=unpack("f",strrev(substr($this->buffer,$this->pos,4))); else
			$t=unpack("f",substr($this->buffer,$this->pos,4));
			$this->pos+=4;
			if ($this->pos>=$this->count) $this->done=true;
			return $t[1];
		} else {
			$this->overflow=true;
			return 0;
		}
	}

	function WriteFloat($b,$bigendian=true){
		if ($bigendian==true) $this->buffer.=strrev(pack("f",$b)); else $this->buffer.=pack("f",$b);
	}

	function ReadCUInt32(){
		$b = $this->ReadUChar();
		if ($this->overflow==true) return 0;
		$this->pos-=1;
		switch ($b & 0xE0){
		case 224:
                    $b=$this->ReadUChar();
                    return $this->ReadInt32();				
                case 192:
                    return $this->ReadInt32() & 0x3FFFFFFF;				
                case 128:
                case 160:
                    return $this->ReadInt16() & 0x7FFF;				
		}
		return $this->ReadUChar();
	}

	function WriteCUInt32($b, $bigendian=true){
		if ($b <= 127) {
			$this->WriteChar($b);
      		} else
      		if ($b < 16384) {
			$this->WriteInt16($b | 0x8000, $bigendian);
      		} else
      		if ($b < 536870912) {
			$this->WriteInt32($b | 0xC0000000, $bigendian);
      		}		
	}

	function ReadOctets(){
		if ($this->pos<$this->count) {			
			$size=$this->ReadCUInt32();
			$t=substr($this->buffer,$this->pos,$size);
			$this->pos+=$size;
			if ($this->pos>=$this->count) $this->done=true;
			return $t;
		};
	}

	function ReadString(){
		if ($this->pos<$this->count) {			
			$size=$this->ReadCUInt32();
			$t=substr($this->buffer,$this->pos,$size);
			$this->pos+=$size;
			$t=iconv("UTF-16","UTF-8",$t);
			if ($this->pos>=$this->count) $this->done=true;
			return $t;
		};
	}

	function WriteOctets($b){		
		$this->buffer.=cuint(strlen($b)).$b;	
	}

	function WriteString($b){
		$a=iconv("UTF-8","UTF-16LE",$b);
		$this->buffer.=cuint(strlen($a)).$a;	
	}
}

class Protocols
{
	function __construct()
	{		
	}

	public function WriteVal($value, $data, $key, $p)
	{
		if (substr($value, -1) == '_') 
		{
			$endian = false;
			$value = substr($value, 0, -1);
		} else $endian = true;		
		if (!isset($data[$key])) $data[$key] = false;
		$result = true;
		switch ($value) 
		{
			case 'char':
				$p->WriteChar($data[$key]);					
			break;

			case 'unsigned char':
				$p->WriteUChar($data[$key]);					
			break;

			case 'short':
				$p->WriteInt16($data[$key], $endian);					
			break;

			case 'unsigned short':
				$p->WriteUInt16($data[$key], $endian);					
			break;

			case 'int':
				$p->WriteInt32($data[$key], $endian);
			break;

			case 'unsigned int':
				$p->WriteUInt32($data[$key], $endian);
			break;

			case 'long':
			case 'int64':
			case 'int64_t':
				$p->WriteInt64($data[$key], $endian);
			break;

			case 'string':
				$p->WriteString($data[$key]);
			break;

			case 'Octets':
				$p->WriteOctets(my_hex2bin($data[$key]));
			break;

			case 'float':
				$p->WriteFloat($data[$key], $endian);
			break;

			default:
				$result = false;
			break;
		}
		return $result;
	}

	public function marshal(array $data, array $structure)
	{
		global $Structures;
		$p = new PacketStream();
		foreach ($structure as $key => $value) 
		{
			if (is_array($value)) 
			{
				$p->buffer .= $this->marshal($data[$key], $value);				
			} else 
			if (strlen($value)>7 && substr($value, -6) == 'Vector')
			{
				$value = substr($value, 0, -6);
				$count = (isset($data[$key]) ? count($data[$key]) : 0);
				$p->buffer .= cuint($count);
				for ($i = 0; $i < $count; ++$i) 
				{
					$r = $this->WriteVal($value, $data, $key, $p);
					if ($r) continue;
					if (!array_key_exists($value, $Structures)) exit('Wrong structure type '.$value);
					$p->buffer .= $this->marshal($data[$key][$i], $Structures[$value]);
				}
				continue;
			} else
			{
				$rr = $this->WriteVal($value, $data, $key, $p);
				if (!$rr)
				{
					// Search by all structures
					if (!array_key_exists($value, $Structures)) exit('Wrong structure type '.$value);
					$p->buffer .= $this->marshal($data[$key], $Structures[$value]);
				}				
			}
		}		
		return $p->buffer;
	}

	public function ReadVal($value, $p)
	{
		if (substr($value, -1) == '_') 
		{
			$endian = false;
			$value = substr($value, 0, -1);
		} else $endian = true;
		switch ($value) 
		{
			case 'char':
				$result = $p->ReadChar();					
			break;

			case 'unsigned char':
				$result = $p->ReadUChar();					
			break;

			case 'short':
				$result = $p->ReadInt16($endian);					
			break;

			case 'unsigned short':
				$result = $p->ReadUInt16($endian);					
			break;

			case 'int':
				$result = $p->ReadInt32($endian);
			break;

			case 'unsigned int':
				$result = $p->ReadUInt32($endian);
			break;

			case 'long':
			case 'int64':
			case 'int64_t':
				$result = $p->ReadInt64($endian);
			break;

			case 'string':
				$result = $p->ReadString();
			break;

			case 'Octets':
				$result = my_bin2hex($p->ReadOctets());
			break;

			case 'float':
				$result = $p->ReadFloat($endian);
			break;

			default:
				$result = false;
			break;
		}
		return $result;
	}

	public function unmarshal($data, $structure, $ps = false)
	{
		global $Structures;
		$result = [];
		if ($ps === false) {
			$p = new PacketStream($data);
		} else $p = $ps;		
		foreach ($structure as $key => $value) 
		{
			if (is_array($value)) 
			{
				$result[$key] = $this->unmarshal($data, $value, $p);
			} else 
			if (strlen($value)>7 && substr($value, -6) == 'Vector')
			{
				$value = substr($value, 0, -6);
				$count = $p->ReadCUInt32();
				$result[$key] = array();
				for ($i = 0; $i < $count; ++$i) 
				{
					$r = $this->ReadVal($value, $p);
					if ($r !== false) continue;
					if (!array_key_exists($value, $Structures)) exit('Wrong structure type '.$value);
					$result[$key][$i] = $this->unmarshal($data, $Structures[$value], $p);
				}
				continue;
			} else
			{
				$result[$key] = $this->ReadVal($value, $p);
				if ($result[$key] === false)
				{
					if (!array_key_exists($value, $Structures)) exit('Wrong structure type '.$value);
					$result[$key] = $this->unmarshal($data, $Structures[$value], $p);
				}				
			}
		}		
		return $result;
	}
}

function AddAccum($summ, $bonus, &$acc_param){
	$item = new AccumItem();
	$item->summ = $summ; $item->bonus = $bonus;
	array_push($acc_param, $item);
}

function encode($String)
{
    global $cookie_pasw, $encoder_Salt;    
    $StrLen = strlen($String);
    $Gamma = '';
    while (strlen($Gamma)<$StrLen)
    {
        $Seq = pack("H*",sha1($Gamma.$cookie_pasw.$encoder_Salt)); 
        $Gamma.=substr($cookie_pasw,0,8);
    }
    
    return $String^$Gamma;
}

function GetUserDonateSumm($login, $userid='',$to_user_id=false){	
	global $db, $act_bonus, $accumulation_system, $acc_param, $bon, $cur_accum_id, $servid;
	$bon = 0; $cur_accum_id = 0;
	if ($login=='' && $userid=='') return 0;
	if (!$accumulation_system && !$to_user_id) return 0;
	$sum = 0;	
	if ($userid=='') $sql = sprintf("SELECT sum(`out_summ`) as `sum` FROM `donate_unitpay` WHERE `login`='%s' AND `status`>=100 AND `serv`=".$servid, $db->real_escape_string($login)); else $sql = sprintf("SELECT sum(`out_summ`) as `sum` FROM `donate_unitpay` WHERE `userid`='%d' AND `status`>=100 AND `serv`=".$servid, $userid);
	$res = $db->query($sql);
	if ($res) {
		$row = mysqli_fetch_assoc($res);
		if (!is_null($row['sum'])) $sum += $row['sum'];	
	}
	if ($userid=='') $sql = sprintf("SELECT sum(`out_summ`) as `sum` FROM `donate_freekassa` WHERE `login`='%s' AND `status`>=100 AND `serv`=".$servid, $db->real_escape_string($login)); else $sql = sprintf("SELECT sum(`out_summ`) as `sum` FROM `donate_freekassa` WHERE `userid`='%d' AND `status`>=100 AND `serv`=".$servid, $userid);
	$res = $db->query($sql);
	if ($res) {
		$row = mysqli_fetch_assoc($res);
		if (!is_null($row['sum'])) $sum += $row['sum'];	
	}
	if ($sum > 0 && count($acc_param) > 0){
		foreach ($acc_param as $i => $val){
			if ($sum >= $val->summ) {
				$bon = $val->bonus;
				$cur_accum_id = $i;
			}
		}
	}
	$act_bonus += $bon;
	return $sum;
}

function CalcDonate($outsumm){
	global $don_kurs;
	global $act_bonus;
	$res = array();
	$res['moneycount'] = round($outsumm/$don_kurs);
	$res['bonus'] = CalcBonus($outsumm, $res['moneycount']);
	return $res;	
}

function CalcBonus($outsumm, $moneycount){
	global $act_bonus, $bonus_system, $bonus_param, $dopbon;
	$bonus = $act_bonus;
	$dopbon = 0;
	if ($bonus_system && count($bonus_param)>0) {
		foreach ($bonus_param as $i => $val){
			if ($outsumm >= $val->summ) $dopbon = $val->bonus;
		}
		$bonus += $dopbon;
	}
	$res = round(($moneycount/100)*$bonus);
	return $res;
}

function GetLink($http = true){
	if (!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != 'on' ) $ht = 'http://'; else $ht = 'https://';
	$res = $ht.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	$m = parse_url($res); $p = pathinfo($m['path']);
	if ($p['dirname']!='/') $p = $p['dirname'].'/'; else $p = '/';
	if ($http) $res = $m['scheme'].'://'; else $res = '';
	$res .= $m['host'].$p;
	return $res;
}

function checker($email){
	if (preg_match("/^[0-9a-z_\-\.]+@[0-9a-z_\-^\.]+\.[a-z]{2,4}$/i", $email)) $h=1; else $h=0;
	return($h);
}
function GetErrorTxt($num,$custom=''){
	global $db, $passw_min_len, $passw_max_len, $config_tbl_name, $klan_pic_size, $lk_transfer_min_role_lvl;
	$err = '<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">×</button><strong>Ошибка!</strong> %s</div>';
	$succ = '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><strong>Готово!</strong> %s</div>';
	switch ($num){
		case 1: return sprintf($succ, 'Картинка успешно загружена');
		case 2: return sprintf($err, 'Произошла ошибка при загрузке файла');
		case 3: return sprintf($err, 'Допускаемые фоматы изображений для значка клана <strong>png, gif, jpg</strong>');
		case 4: return sprintf($err, 'Размер файла не должен превышать <strong>200 Кбайт</strong>');
		case 5: return sprintf($err, 'Размер изображения должен быть <strong>'.$klan_pic_size.'х'.$klan_pic_size.' пискелей</strong>');
		case 6: return sprintf($err, 'Перед подачей заявки загрузите картинку');
		case 7: return sprintf($err, 'Установить значок клану может только мастер');
		case 8: return sprintf($succ, 'Значок установлен, запустите лаунчер игры для обновления значков');
		case 9: return sprintf($err, 'Произошла ошибка при подаче заявки на установку значка');
		case 10: return sprintf($err, 'Ошибка входящих данных');
		case 11: return sprintf($err, 'Ошибка инициализации соединения с сервером');
		case 12: return sprintf($err, 'Ошибка получения списка персонажей');
		case 13: return sprintf($err, 'Ошибка получения персонажа');
		case 14: return sprintf($err, 'Ошибка записи персонажа');
		case 15: return sprintf($err, 'Ошибка запроса в базу аккаунтов');
		case 16: return sprintf($succ, 'Действие было успешно выполнено');
		case 17: return sprintf($err, 'У вас на счету недостаточно монет');
		case 18: return sprintf($err, 'У вас на счету недостаточно серебрянных монет');
		case 19: return sprintf($err, 'Введите корректный номер счета получателя');
		case 20: return sprintf($err, 'Нельзя отправлять монеты самому себе');
		case 21: return sprintf($succ, 'Перевод монет на другой аккаунт успешно завершен');
		case 22: return sprintf($err, 'Комментарий не должен быть больше 20 символов');
		case 23: return sprintf($err, 'В комментарии использованы недопустимые символы');
		case 24: return sprintf($err, 'Выбранный персонаж не находится в данже');
		case 25: return sprintf($err, 'Выйдите из игры, прежде чем совершать действия над персонажем');
		case 26: return sprintf($err, 'На выбранном персонаже, дух не находится в минусе');
		case 27: return sprintf($err, 'На выбранном персонаже, опыт не находится в минусе');
		case 28: return sprintf($err, 'Недостаточно предметов на складе, ожидайте следующего поступления');
		case 29: return sprintf($succ, 'Предмет отправлен выбранному персонажу на почту');
		case 30: return sprintf($err, 'У вас недостаточно прав, для совершения данного действия');
		case 31: return sprintf($err, 'Ошибка соединения с базой данных');
		case 32: return sprintf($err, 'Ошибка запроса в базу данных');
		case 33: return sprintf($err, 'У вас недостаточно прав для просмотра данного раздела');
		case 34: return sprintf($err, 'В пароле использованы недопустимые символы');
		case 35: return sprintf($err, 'Пароль должен быть от '.$passw_min_len.' до '.$passw_max_len.' символов');
		case 36: return sprintf($err, 'Введите корректный E-mail');
		case 37: return sprintf($err, 'Ответ должен быть не менее 3 и не более 25 символов');
		case 38: return sprintf($err, 'В ответе использованы недопустимые символы');
		case 39: return sprintf($err, 'Неверный E-mail, введите E-mail, указанный при регистрации аккаунта');
		case 40: return sprintf($err, 'Пароли не совпадают, будьте внимательней');
		case 41: return sprintf($err, 'Неверный текущий пароль');
		case 42: return sprintf($err, 'Неверный ответ на вопрос');
		case 43: return sprintf($succ, 'Пароль успешно изменен, данные отправлены Вам на почту');
		case 44: return sprintf($succ, 'Голд успешно отправлен и будет доступен в течении 5-ти минут. Возможно потребуется перезайти на аккаунт в игре.');
		case 45: return sprintf($err, 'На банке данного персонажа нет пароля');
		case 46: return sprintf($err, 'Ошибка получения инвентаря персонажа');
		case 47: return sprintf($err, 'Ошибка записи инвентаря персонажа');
		case 48: return sprintf($err, 'Данная вещь не может быть отвязана');
		case 49: return sprintf($err, 'На данном персонаже недостаточно предметов для обмена');
		case 50: return sprintf($err, 'Произошла ошибка при обмене предметов');
		case 51: return sprintf($err, 'У выбранного персонажа заполнен почтовый ящик, очистите его и попробуйте снова');
		case 52: return sprintf($err, 'Произошла ошибка при отправке предмета');
		case 53: return sprintf($err, 'Ошибка получения статуса персонажа');
		case 54: return sprintf($err, 'Ошибка записи статуса персонажа');
		case 55: return sprintf($err, 'Ошибка получения базы персонажа');
		case 56: return sprintf($err, 'Ошибка записи базы персонажа');		
		case 74: return sprintf($err, 'Превышен лимит максимального уровня');
		case 75: return sprintf($err, 'Данный персонаж уже прокачан до запрашиваемого уровня');
		case 76: return sprintf($err, 'Купить данную вещь может только мастер клана');
		case 77: return sprintf($err, 'На данном персонаже недостаточно требуемых предметов');
		case 78: return sprintf($err, 'Произошла ошибка при снятии требуемых предметов с персонажа');
		case 80: return sprintf($err, 'Ошибка ответа сервера');
		case 81: return sprintf($err, 'Ваш аккаунт забанен');
		case 82: return sprintf($err, 'В персонажах не найдено ни одного мастера клана');
		case 83: return sprintf($err, 'Cоединение с базой данных: '.$db->error.'<br>Проверьте настройки в файле <b>config_db.php</b>');
		case 84: return sprintf($err, 'Нет записей в таблице `'.$config_tbl_name.'`');
		case 85: return sprintf($succ, 'Приглашение успешно отправлено на почту');
		case 86: return sprintf($err, 'Неправильно введен код безопасности');
		case 87: return sprintf($succ, 'Приглашение успешно отправлено на почту');		
		case 88: return sprintf($err, $custom);
		case 89: return sprintf($succ, $custom);
		case 90: return sprintf($err, 'Аккаунт не найден');
		case 91: return sprintf($succ, 'Монеты успешно выданы');
		case 94: return sprintf($err, 'Нет прав на запись файлов со значками');
		case 95: return sprintf($err, 'Сначала создайте персонажа в игре');		
		case 99: return sprintf($err, 'Промо код не найден, проверьте правильность ввода');
		case 100: return sprintf($err, 'Данный промо код уже был использован');
		case 101: return sprintf($err, 'Срок действия данного промо кода закончился');
		case 102: return sprintf($err, 'Ошибка отправки запроса на сервер часть');
		case 103: return sprintf($err, 'Вы не можете использовать более одного кода данной группы на аккаунт');
		case 104: return sprintf($err, 'Данный аккаунт уже активирован');		
		case 108: return sprintf($err, 'С момента выхода из игры, должно пройти не менее одной минуты');
		case 109: return sprintf($err, 'Выбранная учетная запись VK уже привязана к другому аккаунту');
		case 110: return sprintf($err, 'Выбранная учетная запись Steam уже привязана к другому аккаунту');
		case 111: return sprintf($err, 'Для перевода монет, на аккаунте должен быть персонаж с уровнем не ниже '.$lk_transfer_min_role_lvl);
		case 112: return sprintf($err, 'Для перевода монет, Вам нужно сначала привязать к аккаунту учетную запись VK');
		case 113: return sprintf($err, 'Для перевода монет, Вам нужно сначала привязать к аккаунту учетную запись Steam');
		case 114: return sprintf($err, 'Указанный персонаж не найден');
		case 115: return sprintf($err, 'Указанный персонаж не находится в нужном клане');
	}
}

function CurlPage($postdata, $timeout, $into_var=1, $disable_show = false){
	global $server_side_script_path, $show_result, $curl_error_msg, $lk_ver, $curl_messages;
	$postdata['lk_ver'] = $lk_ver;
	$curl_error_msg = '';
	$ch = curl_init();
	if (!$ch) {
		echo GetErrorTxt('11');
		die();
	}
	curl_setopt($ch, CURLOPT_URL, $server_side_script_path.'auth.php'); // set url to post to  
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,$into_var); // return into a variable  
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); // times out after 30s  
	curl_setopt($ch, CURLOPT_POST, 1); // set POST method  		
	@curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);  		
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata); // add POST fields  
	$result = curl_exec($ch); // run the whole process  
	if ($result === false) {
		$curl_error_msg = curl_error($ch);
		$result = 102;
	}
	curl_close($ch);
	if ($show_result && !$disable_show && isset($_SESSION['isadmin']) && $_SESSION['isadmin']) 
	{
		if ($curl_error_msg === '') $show_txt = $result; else $show_txt = $curl_error_msg;
		$curl_messages .= '
		<div class="alert alert-info">
		<button type="button" class="close" data-dismiss="alert">×</button>
		<h4 class="alert-heading">Ответ сервера</h4>
		<pre>'.$show_txt.'</pre>
		</div>
		'; 
	}
	return $result;
}

function _CurlPage($link, $postdata = array(), $timeout = 15){
	global $CurlRet;
	$CurlRet = '';
	$ch = curl_init();
	if (!$ch) {
		$CurlRet='Curl init error';
		return false;
	}
	curl_setopt($ch, CURLOPT_URL, $link);
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	if (count($postdata)) {
		curl_setopt($ch, CURLOPT_POST, 1); // set POST method  			
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata); // add POST fields  
	}
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	$data = curl_exec($ch);
	if ($data === false) {
		$CurlRet = curl_error($ch);
		return false;
	}
	curl_close($ch);
	return $data;
}

function UpdatePage($op, $timeout=5){
	global $lk_ver, $curl_error_msg;
	$curl_error_msg = false;
	$ch = curl_init();
	if (!$ch) {
		echo GetErrorTxt('11');
		die();
	}
	if (!isset($_SESSION['act_key'])) $_SESSION['act_key'] = '';
	$postdata = array(
	'act_key'	=>	$_SESSION['act_key'],
	'lk_ver'	=>	$lk_ver,
	'host'		=>	GetLink(),
	'op'		=>	$op
	);
	curl_setopt($ch, CURLOPT_URL, 'http://alexdnepro.net/license_cp_jd.php'); // set url to post to  
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable  
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); // times out after 30s  
	curl_setopt($ch, CURLOPT_POST, 1); // set POST method  			
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata); // add POST fields  
	$result = curl_exec($ch); // run the whole process  
	if ($result === false) {	
		$curl_error_msg = curl_error($ch);
	}
	curl_close($ch);	
	return $result;
}

function mround($num){
		$sDo = round($num,2);
		$sPo = $num - $sDo;
		$sSs = 0;
		if($sPo*100000000000000 > 0)
		$sSs = 0.01;
		$sum = $sDo + $sSs;
		return round($sum,2);
}

function ReadDataFromFile($fname){
	$file_data = '';
	$fd = @fopen( $fname, 'r');
	if (!$fd) return $file_data;
	while  (! feof($fd))   {
	$file_data .= @fgets($fd,  5000);
	}
	@fclose ($fd); 
	return $file_data;
}

function CheckNum($val){
	if (preg_match("/[^0-9]/", $val)) return true;
	return false;
}

function CheckPassw($val){
	global $passw_filter;
	if (preg_match($passw_filter, $val)) return true;
	return false;
}

function GetPasswHash($login, $pass){
	global $auth_type;
	switch ($auth_type) {
		case 1: $md = md5($login.$pass,true); break;
		case 2: $md = '0x'.md5($login.$pass,false); break;
		case 3: $md = base64_encode(md5($login.$pass,true)); break;
		default: $md = md5($login.$pass,true); break;
	}
	return $md;
}

function mycrypt($s){
	$s1 = '';	
	for ($i=0; $i < strlen($s); $i++){
		$s2 = my_bin2hex(chr(ord($s[$i])-150+($i+1)*12));
		$s1 .= $s2;
	}
	return $s1;
}

function decrypt($s){
	$s1 = pack('H*', $s);
	for ($i=0; $i < strlen($s1); $i++){		
		$s1[$i] = chr(ord($s1[$i])+150-($i+1)*12);
	}
	return $s1;
}

function GetHash($login, $pass, $email){
	global $servid;
	$s = sprintf('%d|%s|%s|%s', $servid, $login, md5($pass), $email);
	return mycrypt(encode($s));
} 

function GetTime($t,$color="00aa00",$calc_days=true){	
	if ($calc_days) $pinkdays = floor($t/86400); else $pinkdays = 0;
	$pinkhours = floor(($t-$pinkdays*86400)/3600);
	$pinkmin = floor(($t-$pinkdays*86400-$pinkhours*3600)/60);
	$pinksec = round($t-$pinkdays*86400-$pinkhours*3600-$pinkmin*60,0);
	if ($pinkhours<10) $pinkhours='0'.$pinkhours;
	if ($pinkmin<10) $pinkmin='0'.$pinkmin;
	if ($pinksec<10) $pinksec='0'.$pinksec;
	$timeused = '';
	if ($pinkdays>0) $timeused='<font color="#'.$color.'"><b>'.$pinkdays.'</b></font> дн ';
	if (($pinkhours!="00")||($pinkmin!="00")||($pinksec!="00")) $timeused .= $pinkhours.':'.$pinkmin.':'.$pinksec;
	if ($timeused=='') $timeused = 0;
	return $timeused;
}

function WriteBanTable($a) {
	$ban = array(
		100 => 'Бан аккаунта',
		101 => 'Бан чата'
	);
	echo '<div align="center">
	<table class="table table-bordered table-striped table-condensed" style="width:550px">
	<thead><tr>
		<th>Тип</th>
		<th>Дата выдачи</th>
		<th>Длительность</th>
		<th>Осталось</th>
		<th>Причина</th>
	</tr></thead>
	<tbody>';
	foreach ($a['bans'] as $i => $val){
		$time = date("d.m.Y H:i:s", $val['createtime']);
		$pink = GetTime($val['time']);
		$rest = $val['createtime'] + $val['time'] - time();
		if ($rest<=0) $rest='<span class="label label-success">Время бана истекло</span>'; else $rest = sprintf('<span class="label label-important">%s</span>',GetTime($rest,'ffff00'));
		printf('
		<tr>
			<td><span class="label label-important">%s</span></td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
		</tr>',$ban[$val['type']], $time, $pink, $rest, $val['reason']);
	}
	echo '</tbody></table></div><br>';
}

function UnpackAnswer($val){
	$a = @unserialize($val);
	$err = '<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">×</button><strong>Ошибка!</strong> %s.</div>';
	if (!is_array($a)) return $val;
	if (!isset($a['errorcode'])) {
		echo GetErrorTxt(80);
		$a['errorcode'] = 80;
		return $a;
	}
	if ($a['errorcode']>0){
		if ($a['errorcode']==1000) echo sprintf($err, sprintf($a['errtxt'],$a['errparam1'],$a['errparam2'],$a['errparam3'])); else
		echo GetErrorTxt($a['errorcode']);
	}
	return $a;
}

function ParseCost($cost, &$gold, &$silver){
	$c = explode('|', $cost);
	$gold = (int)$c[0];
	if (count($c) > 1) $silver = (int)$c[1]; else $silver = 0;
}

function IsAdmin($id){
	global $admins, $admins_ip, $global_admin;
	$global_admin = false;	
	if (count($admins_ip))
	foreach ($admins_ip as $i => $val){
		if (!$val) continue;
		if (strpos(getIP(), $val) === 0) {
			$global_admin = true;			
			return true;
		}
	}
	if (in_array($id, $admins)) return true;
	return false;
}

function ShowCost($cost, $shownull = false, $dark = false, $br = false){
	$res = '';
	ParseCost($cost, $gold, $silver);
	if (!$dark) {
		$dg = '';
		$ds = '';
	} else {
		$dg = ' gold_dark';
		$ds = ' gold_silver';
	}
	if ($gold != 0 || $shownull) $res = ' <span class="gold'.$dg.'">'.$gold.'</span>';
	if ($silver != 0) $res .= ' <span class="silver'.$ds.'">'.$silver.'</span>';
	if ($br && $res != '') $res = '<br>'.$res;
	return $res;
}

define('p_k', base64_decode('LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0KTUlJQklqQU5CZ2txaGtpRzl3MEJBUUVGQUFPQ0FROEFNSUlCQ2dLQ0FRRUEwVU8vWVNIMndUQkdtV2tXSVJLSQpKUllucjJqTzh3UXpyQkZnRmYrYmo2M2lBaUtlVW5LeWM4cFNMMVAyN1NzMWpYbTFQSkVVMWZVOUpOYWthVHZKCjBKTXFjSkZMQ0RRdHBjWjZPQVpuaWgyM1ZZSHdHdFdlQS8zT1ZubkNjaGsrUTZQYnBTejhoMUpPQjZGSXB0eU4KeUFOclpZK2M0bkpFWUdGKzJWOTkzemd4MlVGNlFWQWxZSWVTRWdtYkxGbHd1TkU5SUlqWmJRaG0wcGJZczVCRgptT2QyVEJBMmpianloTDZPMm5lQkU0VW5PVGRIVnhNNC94ZHllQjRjMkNGRGoxeFc4QTR1UTNWQy95elZnbk92CklTQ0hxaHJ5VWxLV3RWcGpnOWFCMjc2U25DWk5IQ21DUXAzRFhtVlcxYzlpTGpOWGQ2YTd4UVVNRWFGTlo0TmYKK1FJREFRQUIKLS0tLS1FTkQgUFVCTElDIEtFWS0tLS0tCg=='));

function AssignPData($data){
	$result = '';
	$Split = str_split($data, 344);
	foreach($Split as $Part){
		@openssl_public_decrypt(base64_decode($Part), $PartialData, p_k);
		$result .= $PartialData;
	}
	return $result;
}

function PrepareData($data){
	$Split = str_split($data, 117);
	$PartialData = '';
	$EncodedData = '';
	foreach ($Split as $Part){
		openssl_public_encrypt($Part, $PartialData, p_k);
		$EncodedData .= base64_encode($PartialData);
	}
	return $EncodedData;
}

function smtpmail($mail_to, $subject, $message, $headers='') {
	global $smtp_charset, $smtp_username, $admin_email, $smtp_password, $smtp_from, $smtp_host, $smtp_port, $smtp_debug, $smtp_timeout, $smtp_secure;	
	require 'Exception.php';
	require 'PHPMailer.php';
	require 'SMTP.php';
	$mail = new PHPMailer\PHPMailer\PHPMailer;
	$mail->SMTPDebug = $smtp_debug;			// Enable verbose debug output
	$mail->isSMTP();				// Set mailer to use SMTP
	$mail->Host = $smtp_host;			// Specify main and backup SMTP servers
	$mail->SMTPAuth = true;				// Enable SMTP authentication
	$mail->Username = $smtp_username;		// SMTP username
	$mail->Password = $smtp_password;		// SMTP password
	if ($smtp_secure == 0) $smtp_secure = '';
	$mail->SMTPSecure = $smtp_secure;		// Enable TLS encryption, `ssl` also accepted
	$mail->Port = $smtp_port;			// TCP port to connect to
	$mail->Timeout = $smtp_timeout;
	$mail->Timelimit = $smtp_timeout;
	$mail->From = $admin_email;
	$mail->FromName = $smtp_from;
	$mail->addAddress($mail_to);
	$mail->isHTML(true);				// Set email format to HTML
	$mail->CharSet = $smtp_charset;				// кодировка письма
	$mail->Subject = $subject;
	$mail->Body    = $message;
	return $mail->SendMail($mail_to);	
}
 
function send_mail($mail_to, $subject, $message){
	global $mail_type, $admin_email;
	if ($mail_type == 'PhpMail') {
		$from="Content-type: text/html; charset=utf-8 \r\n";
		$from.="From: ".$admin_email."\r\n";
		return mail($mail_to, $subject, $message, $from);
	}
	if ($mail_type == 'SMTP') return smtpmail($mail_to, $subject, $message);
	return false;
}

function HeaderTemplate($title, $description, $favicon, $auth = false){
	$header_templ = '<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<title>'.$title.'</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="'.$description.'">
	<meta name="author" content="alexdnepro">
	<!-- The fav icon -->
	<link rel="shortcut icon" href="'.$favicon.'">
';
	if (!$auth) $header_templ .= '
	<!-- The styles -->
	<link id="bs-css" href="css/bootstrap-classic.css" rel="stylesheet">
';
$header_templ .='	<style type="text/css">
	  body {
		padding-bottom: 40px;
	  }
	  .sidebar-nav {
		padding: 9px 0;
	  }
	</style>	

	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->	
	<link href="css/bootstrap-responsive.css" rel="stylesheet">
	<link href="css/charisma-app.css" rel="stylesheet">
	<link href="css/jquery-ui-1.8.21.custom.css" rel="stylesheet">
	<link href="css/chosen.css" rel="stylesheet">
	<link href="css/uniform.default.css" rel="stylesheet">
	<link href="css/colorbox.css" rel="stylesheet">
	<link href="css/jquery.cleditor.css" rel="stylesheet">
	<link href="css/jquery.noty.css" rel="stylesheet">
	<link href="css/noty_theme_default.css" rel="stylesheet">
	<link href="css/elfinder.min.css" rel="stylesheet">
	<link href="css/elfinder.theme.css" rel="stylesheet">
	<link href="css/jquery.iphone.toggle.css" rel="stylesheet">
	<link href="css/opa-icons.css" rel="stylesheet">
	<link href="css/uploadify.css" rel="stylesheet">
	<!-- jQuery -->
	<script src="js/jquery-1.7.2.min.js"></script>
	<!-- jQuery UI -->
	<script src="js/jquery-ui-1.8.21.custom.min.js"></script>
	<!-- transition / effect library -->
	<script src="js/bootstrap-transition.js"></script>
	<!-- alert enhancer library -->
	<script src="js/bootstrap-alert.js"></script>
	<!-- modal / dialog library -->
	<script src="js/bootstrap-modal.js"></script>
	<!-- custom dropdown library -->
	<script src="js/bootstrap-dropdown.js"></script>
	<!-- scrolspy library -->
	<script src="js/bootstrap-scrollspy.js"></script>
	<!-- library for creating tabs -->
	<script src="js/bootstrap-tab.js"></script>
	<!-- library for advanced tooltip -->
	<script src="js/bootstrap-tooltip.js"></script>
	<!-- popover effect library -->
	<script src="js/bootstrap-popover.js"></script>
	<!-- button enhancer library -->
	<script src="js/bootstrap-button.js"></script>	
	<script src="js/bootstrap-collapse.js"></script>
	<!-- autocomplete library -->
	<script src="js/bootstrap-typeahead.js"></script>
	<!-- library for cookie management -->
	<script src="js/jquery.cookie.js"></script>
	<!-- data table plugin -->
	<script src="js/jquery.dataTables.min.js"></script>
	<!-- chart libraries start -->
	<script src="js/excanvas.js"></script>
	<script src="js/jquery.flot.min.js"></script>
	<script src="js/jquery.flot.pie.min.js"></script>
	<script src="js/jquery.flot.stack.js"></script>
	<script src="js/jquery.flot.resize.min.js"></script>
	<!-- chart libraries end -->
	<!-- select or dropdown enhancer -->
	<script src="js/jquery.chosen.min.js"></script>
	<!-- checkbox, radio, and file input styler -->
	<script src="js/jquery.uniform.min.js"></script>
	<!-- plugin for gallery image view -->
	<script src="js/jquery.colorbox.min.js"></script>	
	<!-- rich text editor library -->
	<script src="js/jquery.cleditor.min.js"></script>
	<!-- notification plugin -->
	<script src="js/jquery.noty.js"></script>
	<!-- file manager library -->
	<script src="js/jquery.elfinder.min.js"></script>
	<!-- star rating plugin -->
	<script src="js/jquery.raty.min.js"></script>
	<!-- for iOS style toggle switch -->
	<script src="js/jquery.iphone.toggle.js"></script>
	<!-- autogrowing textarea plugin -->
	<script src="js/jquery.autogrow-textarea.js"></script>	
	<!-- history.js for cross-browser state change on ajax -->
	<script src="js/jquery.history.js"></script>
';
	if (!$auth) $header_templ .= '		
	<script src="js/charisma.js"></script>
'; else
	$header_templ .= '	<link rel="stylesheet" href="css/login.css" type="text/css" />
';
	$header_templ .= '</head>
';
	return $header_templ;
}

class my_db extends mysqli {
	function query($sql, $resultmode = NULL) {
		$res = parent::query($sql, $resultmode);
		if (!$res) {
			$msg = $this->error;
			$dh = fopen ('mysql_error.log', 'a+');
			if ($dh) {
				fwrite($dh, date("Y-m-d H:i:s").' '.$sql."\n");
				fwrite($dh, $msg."\n\n");
				fclose($dh);
			}
		}
		return $res;
	}
}

$db = @new my_db($mysql_host, $mysql_user, $mysql_pass, $mysql_dbname);
if ($db->connect_errno) {	
	echo HeaderTemplate('', '', '');
	echo GetErrorTxt(83);
	die();
}
$db->query('set names utf8');

function ReadConfigFromBase(){
	global $db, $config_tbl_name, $Structures;
	$res = $db->query("SELECT * FROM `".$db->real_escape_string($config_tbl_name)."` WHERE `type`<>'desc'");
	if ($db->errno > 0) {
		echo HeaderTemplate('', '', '');
		echo GetErrorTxt(83);
		die();
	}
	if (!$res->num_rows){
		echo HeaderTemplate('', '', '');
		echo GetErrorTxt(84);
		die();
	}
	$results = array();
	while ($row = mysqli_fetch_assoc($res)){
		switch ($row['type']) {
			case 'itemid':
			case 'int':
			case 'mask':
			case 'proctype':
				$results[$row['name']] = intval($row['value']);
				break;
			case 'bool':
				$results[$row['name']] = ($row['value']);
				break;
			case 'float':
				$results[$row['name']] = (float)$row['value'];
				break;
			case 'cost':
			case 'text':
			case 'string':
				$results[$row['name']] = $row['value'];
				break;
			case 'arraystring':
				$results[$row['name']] = explode(',',$row['value']);
				break;
			case 'arrayint':
				$results[$row['name']] = explode(',',$row['value']);
				if (count($results[$row['name']])>0)
				foreach ($results[$row['name']] as $i1 => $val1){
					$results[$row['name']][$i1] = intval($val1);
				}
				break;
			case 'arraylist':				
				$results[$row['name']] = array();
				$a = @unserialize($row['value']);
				if (is_array($a))
				foreach ($a as $i1 => $val1){
					AddAccum($i1, $val1, $results[$row['name']]);					
				}				
				break;
			case 'select':				
				$results[$row['name']] = '';				
				$a = explode(',',$row['value']);
				if (count($a)>1) $results[$row['name']] = $a[$a[count($a)-1]];
				break;
			case 'item':
				$pp = new Protocols();
				$results[$row['name']] = $pp->unmarshal(my_hex2bin($row['value']), $Structures['GRoleInventory']);
				unset($pp);
				break;
		}
	}

	return $results;
}
$config_array = ReadConfigFromBase();

foreach ($config_array as $name_ => $value_) {
	$$name_ = $value_;
}

$auth_errors = @array('Неверные данные, попробуйте ещё раз', 'Авторизация успешно завершена', 'В логине использованы недопустимые символы', 'В пароле использованы недопустимые символы', 'Логин должен быть от '.$login_min_len.' до '.$login_max_len.' символов', 'Пароль должен быть от '.$passw_min_len.' до '.$passw_max_len.' символов', 'Ошибка соединения, попробуйте позже', 'Ваш IP адрес <b>%s</b> заблокирован на 10 минут за превышение лимита запросов', 'Введите корректный E-Mail', 'Личный кабинет временно недоступен из-за технических работ.<br>Попробуйте зайти позже', 'Вход на данный аккаунт с IP адреса <b>%s</b> запрещен настройками аккаунта', 'Установите расширение ioncube на сервер часть ЛК', 'У вас нет прав использовать данный ЛК', 'Неверный ключ активации', 'Данный аккаунт не активирован. Пройдите по ссылке в письме, которое пришло на почту. <a class=\"resend\" href=\"register.php?resendmail\"> >>Отправить письмо ещё раз<< </a>', 102 => 'Ошибка отправки запроса на сервер часть', 1000 => 'Данный аккаунт используется на другом компьютере, для продолжения работы требуется повторная авторизация', 1001 => 'Срок действия Вашей лицензии истек');

// Название платежных систем
$curr_names = array();
if (isset($donate_system)){
	switch ($donate_system) {
		case 'Free-Kassa':
			$curr_names[0] = '';
			$curr_names[1] = 'WMR';
			$curr_names[2] = 'WMZ';
			$curr_names[3] = 'WME';
			$curr_names[63] = 'QIWI';
			$curr_names[64] = 'Perfect Money USD';
			$curr_names[45] = 'Яндекс.Деньги';
			$curr_names[60] = 'OKPAY RUB';
			$curr_names[61] = 'OKPAY EUR';
			$curr_names[62] = 'OKPAY USD';
			$curr_names[65] = 'LiqPAY USD';
			$curr_names[69] = 'Perfect Money EUR';
			$curr_names[71] = 'LiqPAY RUR';
			$curr_names[73] = 'W1 USD';
			$curr_names[74] = 'W1 RUR';
			$curr_names[79] = 'Альфа-банк RUR';
			$curr_names[80] = 'Сбербанк RUR';
			$curr_names[82] = 'Мобильный Платеж Мегафон';
			$curr_names[84] = 'Мобильный Платеж МТС';
			$curr_names[87] = 'Cash4WM USD';
			$curr_names[94] = 'VISA/MASTERCARD';
			$curr_names[97] = 'LiqPAY UAH';
			$curr_names[81] = 'ВТБ24 RUR';
			$curr_names[99] = 'Терминалы России';
			$curr_names[107] = 'Egopay USD';
			$curr_names[103] = 'Wire Transfer';
			$curr_names[104] = 'СВЯЗНОЙ Банк';
			$curr_names[106] = 'Cash4WM RUR';
			$curr_names[108] = 'Egopay EUR';
			$curr_names[109] = 'Cash4WM EUR';
			$curr_names[112] = 'Тинькофф Кредитные Системы';
			$curr_names[115] = 'PAYEER USD';
			$curr_names[114] = 'PAYEER RUB';
		break;			
	}
}
$months = array('', 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь');
$curl_messages = '';
