<?php
	if (!defined('main_def')) die();
	if (isset($_GET['r'])) {
		echo GetErrorTxt($_GET['r']);
	}
?><center>
<h3>Пожертвования на развитие проекта</h3>
<img src="img/scorn.png" border=0 align=left>
<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">×</button>Спасибо за поддержку проекта. Оплата была отменена.<br>
Если у Вас возникают трудности с совершением платежа, свяжитесь с нами <?=$contacts?></div>
</center>