<?php
$auth_errno=0;
require_once('config.php');
$cookie = array();
if (isset($_COOKIE[$cookie_name])) {
	$cookie = @unserialize(encode(base64_decode($_COOKIE[$cookie_name])));
	if (!is_array($cookie)) $cookie = array();
}
define('main_def', true);
require_once('auth.php');
if (isset($_SESSION['id'])) $cur_user_donate = GetUserDonateSumm($_SESSION['login']); else $cur_user_donate = 0;
require_once('menu.php');
if (isset($_GET['op'])) $op = $_GET['op']; else $op='main';
if ($auth_errno==1){	// Если авторизованы
	switch ($op){
		case 'upload': include('pages/upload.php'); die();
		break;
		case 'act': include('pages/action.php'); die();
		break;
	}
}
if(!isset($no_visible_elements) || !$no_visible_elements)
echo HeaderTemplate($title, $description, $favicon); else echo HeaderTemplate($title, $description, $favicon, true);
?>

<body>
	<?php if(!isset($no_visible_elements) || !$no_visible_elements)	{ ?>
	<!-- topbar starts -->
	<div class="popup_clean" id="maskBody"></div>
	<div class="popup_clean1" id="proctypeBody" style="z-index:2000"></div>
	<div class="navbar">
		<div class="navbar-inner">
			<div class="container-fluid">
				<a class="btn btn-navbar" data-toggle="collapse" data-target=".top-nav.nav-collapse,.sidebar-nav.nav-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</a>				
				
				<a class="brand" href="index.php"> <img alt="Logo" src="<?=$logo?>" /> <span><?=$logotext?></span></a>

				

				<?php print_account_info(); ?>							
				
				<div class="top-nav nav-collapse">
					<ul class="nav">
						<li><a href="<?=$cite_link?>" target="_blank">Перейти на сайт</a></li>						
					</ul>
				</div><!--/.nav-collapse -->
			</div>
		</div>
	</div>
	<!-- topbar ends -->
	<?php } ?>
	<div class="container-fluid">
		<div class="row-fluid">
		<?php if(!isset($no_visible_elements) || !$no_visible_elements) { 
			print_menu();
			?>	
			
			<noscript>
				<div class="alert alert-block span10">
					<h4 class="alert-heading">Warning!</h4>
					<p>Вам нужно разрешить использование <a href="http://en.wikipedia.org/wiki/JavaScript" target="_blank">JavaScript</a> в вашем браузере для правильного просмотра данного сайта.</p>
				</div>
			</noscript>
			
			<div id="content" class="span10">
			<!-- content starts -->
			<?php } 