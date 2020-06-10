<html>
    <head><title>Test of form self submission</title></head>
    <body>
        <form>
            <input type="hidden" name="secret" value="Wrong form">
        </form>
        <p>[<?php echo (isset($_GET['visible']) ? $_GET['visible'] : ''); ?>]</p>
        <p>[<?php echo (isset($_GET['secret']) ? $_GET['secret'] : ''); ?>]</p>
        <p>[<?php echo (isset($_GET['again']) ? $_GET['again'] : ''); ?>]</p>
        <form>
            <input type="text" name="visible">
            <input type="hidden" name="secret" value="Submitted">
            <input type="submit" name="again">
        </form>
        <!-- Bad form closing tag --></form>
    </body>
</html>