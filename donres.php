<?php
include('config.php');
define('donateres_def', true);
switch ($donate_system) {
	case 'UnitPay':
		include('donres_unitpay.php');
		break;
	case 'Free-Kassa':
		include('donres_freekassa.php');
		break;
	default:
		echo '<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">×</button>Прием пожертвований временно недоступен.</div>';
		break;
}
