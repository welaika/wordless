<html>
    <head><title>Test of form submission</title></head>
    <body>      
        <p>_GET : [<?php echo (isset($_GET['test']) ? $_GET['test'] : ''); ?>]</p>
        <p>_POST : [<?php echo (isset($_POST['test']) ? $_POST['test'] : ''); ?>]</p>
		<form method="post" name="form_project" id="form_project" action="">
			<input type="hidden" value="test" name="test" />
			<input type="submit" value="Submit Post With Empty Action" name="submit" />
		</form>
    </body>
</html>