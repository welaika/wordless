<?php
    require_once '../page_request.php';
?><html>
    <head><title>Simple test page with links</title></head>
    <body>
        Simple test page with links
        <h1>Links</h1>
        <a href="a_page.php?action=index">Self</a>
        <a href="?action=no_page">No page</a>
        <a href="?action">Bare action</a>
        <a href="?">Empty query</a>
        <a href="">Empty link</a>
        <a href=".">Current directory</a>
        <a href="..">Down one</a>
        <h1>Forms</h1>
        <form action="a_page.php"><input type="submit" name="action" value="Self"></form>
        <form action="."><input type="submit" name="action" value="Same directory"></form>
        <form action=""><input type="submit" name="action" value="Empty action"></form>
        <form><input type="submit" name="action" value="No action"></form>
        <form action=".."><input type="submit" name="action" value="Down one"></form>
        <?php include __DIR__ . '/show_request.php'; ?>
    </body>
</html>