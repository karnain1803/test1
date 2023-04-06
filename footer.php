		<?php 
		require_once('config.php');
		if(!isset($no_visible_elements) || !$no_visible_elements)	{ ?>
			<!-- content ends -->
			</div><!--/#content.span10-->
		<?php } ?>
		</div><!--/fluid-row-->
		<?php if(!isset($no_visible_elements) || !$no_visible_elements)	{ ?>
		
		<hr>

		<footer>
		<table cellpadding=0 cellspacing=0 style="width:100%; border:0">
		<tr>
			<td>by <a href="#" target="_blank">alexdnepro</a> <?php echo @date('Y') ?><br><font color="#a0a0a0">ver <?=$lk_ver?></font></td>
			<td style="text-align:center"><!-- begin WebMoney Transfer : accept label -->
<a href="http://www.megastock.ru/" target="_blank"><img src="img/acc_blue_on_white_ru.png" alt="www.megastock.ru" border="0"></a>
<!-- end WebMoney Transfer : accept label --></td>
			<td style="text-align:right"><?=$footer_right?></td>
		</tr>
		</table>			
		</footer>
		<?php } ?>

	</div><!--/.fluid-container-->
</body>
</html>
