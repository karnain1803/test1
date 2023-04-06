<?php
if (!defined('main_def')) die();

function print_account_info(){
	global $login;
	global $gold_point;
	global $lk_active_for_all;
	global $vk_login_enable, $steam_login_enable;
	?>

				<!-- user dropdown starts -->
				<div class="btn-group pull-right" >
					<a class="btn dropdown-toggle btn-danger" data-toggle="dropdown" href="#">
						<i class="icon-user icon-white"></i><span class="hidden-phone"> <?php
						echo $_SESSION['login'];
						?></span>
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
						<li><a href="index.php?op=account"><i class="icon icon-color icon-gear"></i> Настройки</a></li>
						<li class="divider"></li>
						<li><a href="logout.php"><i class="icon icon-color icon-cancel"></i> Выйти</a></li>
					</ul>
				</div>
				<!-- user dropdown ends -->	

				<!-- money dropdown starts -->
				<div class="btn-group pull-right" >
					<a class="btn dropdown-toggle btn-inverse" data-toggle="dropdown" href="#">
						<i class="icon-white icon-shopping-cart"></i> Монеты на счету: <?=ShowCost($_SESSION['lkgold'].'|'.$_SESSION['lksilver'], true)?>
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
						<li><a href="index.php?op=donate"><i class="icon icon-color icon-basket"></i> Получить монеты</a></li>
						<li><a href="index.php?op=money"><i class="icon icon-color icon-sent"></i> Управление монетами</a></li>
					</ul>
				</div>
				<!-- money dropdown ends -->	

				<!-- account number starts -->
				<div class="btn-group pull-right" >
					<a class="btn btn-link" href="#">
						Счет № <span class="red"><b><?=($_SESSION['id']/16-1)?></b></span>
					</a>					
				</div>
				<!-- account number ends -->	

	<?php
	if ($_SESSION['vk_id'] && $vk_login_enable) { ?>
				<div class="btn-group pull-right" >
					<a href="https://vk.com/id<?=$_SESSION['vk_id']?>" target="_blank"><img src="<?=$_SESSION['vk_photo']?>" class="auth_but_succ" data-rel="tooltip" border="0" data-original-title="<font class='auth_text'><font color=#a0b5ff>Привязанный аккаунт VK</font><br><b><?=$_SESSION['vk_name']?></b></font>"></a>
				</div>
	<?php } else if ($vk_login_enable) { ?>
				<div class="btn-group pull-right" >
					<a href="index.php?addvk"><img src="img/vk_login.png" class="auth_but" data-rel="tooltip" border="0" data-original-title="<font class='auth_text' color=#a0b5ff>Привязать аккаунт ВКонтакте</font>"></a>
				</div>
	<?php } 
	if ($_SESSION['steam_id'] && $steam_login_enable) { ?>
				<div class="btn-group pull-right" >
					<a href="http://steamcommunity.com/profiles/<?=$_SESSION['steam_id']?>/" target="_blank"><img src="<?=$_SESSION['steam_photo']?>" class="auth_but_succ" data-rel="tooltip" border="0" data-original-title="<font class='auth_text'><font color=#d0d0d0>Привязанный аккаунт Steam</font><br><b><?=$_SESSION['steam_name']?></b></font>"></a>
				</div>
	<?php } else if ($steam_login_enable) { ?>
				<div class="btn-group pull-right" >
					<a href="index.php?addsteam"><img src="img/steam_login.png" class="auth_but" data-rel="tooltip" border="0" data-original-title="<font class='auth_text' color=#d0d0d0>Привязать аккаунт Steam</font>"></a>
				</div>
	<?php } 
	if ($_SESSION['isadmin']) {
	?>				
				<div class="btn-group pull-right" >
					<img src="img/admin.png" style="margin-top:2px" border="0" alt="Админ">				
				</div>
	<?php 
		if ($_SESSION['is_timed']) { ?>
				<div class="btn-group pull-right" style="margin-top: 5px;">
					<span class="label label-important" style="font-size:15px; vertical-align: middle" data-rel="tooltip" data-original-title="Оставшееся время лицензии"><?php echo $_SESSION['rest_time']; ?></span>				
				</div>
		<?php
		}
	} 
	if ($_SESSION['rules_cnt']>0) {
	?>
				<div class="btn-group pull-right" >
					<img src="img/gm.png" style="margin-top:6px" border="0" alt="Права ГМ">				
				</div>	<?php
	} 
	if (!$lk_active_for_all) {
	?>
	
				<div class="btn-group pull-left" >
					<span class="label label-important">ЛК закрыт</span>
				</div>
	<?php } 
}

function print_menu(){ ?>	
	<!-- left menu starts -->
			<div class="span2 main-menu-span">
				<div class="well nav-collapse sidebar-nav">
					<ul class="nav nav-tabs nav-stacked main-menu">
	<?php
	global $cur_icon;
	global $cur_name;
	global $cur_dost;
	global $menu;
	global $op;
	global $act_bonus;	
	global $contacts, $donate_system, $ext_users;
	foreach ($menu as $i => $val ) {
		$val = @explode('^', $val);
		if (count($val)!=4) continue;
		if ($val[2]=='donate' && $act_bonus > 0) $bon = ' <span class="label label-inverse">БОНУС! + <span class="gold">'.$act_bonus.'%</span></span>'; else $bon = '';
		$check = true;
		if ((!$_SESSION['isadmin'] && $val[3]==2) || (!$_SESSION['isadmin'] && $_SESSION['rules_cnt']==0 && $val[3]==1)) $check = false;
		if ($val[3]==3 && !in_array($_SESSION['id'], $ext_users)) $check = false;
		if ($val[2]=='donate' && $donate_system == 'Disabled') $check = false;		
		if ($check) {
			$link = sprintf("index.php?op=%s", $val[2]);
			if (strripos($val[2], 'http') !== false) $link = $val[2];
			if ($val[2]=='') printf('<li class="nav-header hidden-tablet">%s</li>', $val[1]); else 
			printf('<li><a href="%s"><i class="icon icon-color %s"></i><span class="hidden-tablet"> %s</span>%s</a></li>', $link, $val[0], $val[1],$bon);
		}
		if ($op == $val[2]) {
			$cur_icon = $val[0];
			$cur_name = $val[1];
			$cur_dost = $val[3];		
		}
	}
	?>						
					</ul>								
				</div><!--/.well --><br>
				<center><?=$contacts?></center>
			</div><!--/span-->
			
			<!-- left menu ends -->

	<?php
}
