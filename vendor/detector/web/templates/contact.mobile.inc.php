<?php include("templates/_header.inc.php"); ?>

<div class="span10">
	<?php
		if ($_POST['post']) {
			include("templates/_contactty.inc.php");
		} else {
			include("templates/_contactform.inc.php");
		}
	?>
	<?php include("templates/_about.inc.php"); ?>
	<?php include("templates/_moreinfo.inc.php"); ?>
	<?php include("templates/_credits.inc.php"); ?>
</div>

<?php include("templates/_footer.inc.php"); ?>