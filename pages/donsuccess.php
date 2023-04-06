<?php
	if (!defined('main_def')) die();
	if (isset($_GET['r'])) {
		echo GetErrorTxt($_GET['r']);
	}
?><center>
<h3>Пожертвования на развитие проекта</h3>
<img src="img/big_smile.png" border=0 align=left>
<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button>Спасибо за поддержку проекта. Бонус будет начислен в ближайшее время.<br>
Если монеты не будут начислены в течение часа, свяжитесь с нами <?=$contacts?></div>
</center>