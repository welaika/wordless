<?php
	$i       = 0;
	$include = '';
	if ($uaListJSON = @file_get_contents(__DIR__."/../../lib/Detector/user-agents/core/ua.list.json")) {
		$uaList = (array) json_decode($uaListJSON);
		asort($uaList);
		foreach ($uaList as $key => $value) {
			$include .= "<li> <a href=\"/?pid=".$key."\">".trim(substr(strip_tags($value), 0, 28))."...</a></li>";
			$i++;
		}
	}
?>
<h3>Archive</h3>
<p>
	The following <strong><?=$i?></strong> user agent profiles are already in the system (<a href="archive.php">readable list</a>):
</p>
<ul>
	<?=$include?>
</ul>
