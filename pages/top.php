<?php
	if (!defined('main_def')) die();
	if (isset($_GET['r'])) {
		echo GetErrorTxt($_GET['r']);
	}
?><center>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header well" data-original-title>
					<h2>Классы</h2>
				</div>
				<div class="box-content">
					<?=ReadDataFromFile('top/top_class.html')?>
				</div>
	</div>
</div>
<div class="row-fluid">
	<div class="box span6">
				<div class="box-header well" data-original-title>
					<h2>Уровень</h2>
				</div>
				<div class="box-content">
					<?=ReadDataFromFile('top/top_level.html')?>
				</div>
	</div>
	<div class="box span6">
				<div class="box-header well" data-original-title>
					<h2>Онлайн</h2>
				</div>
				<div class="box-content">
					<?=ReadDataFromFile('top/top_online.html')?>
				</div>
	</div>
</div>
<div class="row-fluid">	
	<div class="box span6">
				<div class="box-header well" data-original-title>
					<h2>ПК</h2>
				</div>
				<div class="box-content">
					<?=ReadDataFromFile('top/top_pk.html')?>
				</div>
	</div>
	<div class="box span6">
				<div class="box-header well" data-original-title>
					<h2>Кланы</h2>
				</div>
				<div class="box-content">
					<?=ReadDataFromFile('top/top_klan.html')?>
				</div>
	</div>
</div>
</center>