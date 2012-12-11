<?php include("templates/_header.inc.php"); ?>

<div class="span10">
	<h2>Archive of Detector Profiles</h2>
	<p>The following profiles were created by Detector when the first user with that particular browser visited the system:</p>
	<ul>
	<?php
		if ($uaListJSON = @file_get_contents(__DIR__."/../../lib/Detector/user-agents/core/ua.list.json")) {
			$uaList = (array) json_decode($uaListJSON);
			asort($uaList);
			foreach ($uaList as $key => $value) {
				print "<li> <a href=\"/?pid=".$key."\">".strip_tags($value)."</a></li>";
			}
		}
	?>
	</ul>
	<?php include("templates/_about.inc.php"); ?>
	<?php include("templates/_moreinfo.inc.php"); ?>
	<?php include("templates/_credits.inc.php"); ?>
</div>

<?php include("templates/_footer.inc.php"); ?>