<?php
	function convertTF($value) {
		if ($value == "true") {
			return "<span class='label success'>true</span>";
		} else if ($value) {
			return "<span class='label warning'>".$value."</span>";
		} else {
			return "<span class='label important'>false</span>";
		}
	}
?>