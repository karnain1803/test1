<?php
if (!defined('main_def')) die();
function go($a){
	header('Location: index.php?op=klan&r='.$a.'&t='.time());
	die();
}

function get_image_info($file = NULL) {
	if(!is_file($file)) return false;  
	if(!$data = getimagesize($file) or !$filesize = filesize($file)) return false;  
	$extensions = array(1 => 'gif',    2 => 'jpg',
                         3 => 'png',    4 => 'swf',
                         5 => 'psd',    6 => 'bmp',
                         7 => 'tiff',    8 => 'tiff',
                         9 => 'jpc',    10 => 'jp2',
                         11 => 'jpx',    12 => 'jb2',
                         13 => 'swc',    14 => 'iff',
                         15 => 'wbmp',    16 => 'xbmp');  
	$result = array('width'        =>    $data[0],
                     'height'    =>    $data[1],
                     'extension'    =>    $extensions[$data[2]],
                     'size'        =>    $filesize,
                     'mime'        =>    $data['mime']);  
	return $result;
}
$valid_extensions = array('gif', 'jpg', 'png');
$image_info = get_image_info($_FILES['userfile']['tmp_name']);
if (!$image_info) go(2);
if (!in_array($image_info['extension'], $valid_extensions)) go(3);
if ($image_info['size']>204800) go(4);
if (($image_info['width']!=$klan_pic_size)||($image_info['height']!=$klan_pic_size)) go(5);
switch ($image_info['extension']) {
	case 'gif':$img=imageCreateFromGif($_FILES['userfile']['tmp_name']);break;
	case 'png':$img=imageCreateFromPng($_FILES['userfile']['tmp_name']);break;
	case 'jpg':$img=imageCreateFromJpeg($_FILES['userfile']['tmp_name']);break;
}
$uploaddir = $uploaddir."/"; 
imagesavealpha($img, true);
$res = imagePng($img,$uploaddir.$_SESSION['login'].'.png');
if (!$res) go(2);
//$path = $uploaddir.$SafeFile;
go(1);
