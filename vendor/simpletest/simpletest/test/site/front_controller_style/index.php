<?php
    require_once '../page_request.php';
?><html>
    <head><title>Simple test front controller</title></head>
    <body>
        Simple test front controller
        <h1>Links</h1>
        <a href="index.php?action=index">Index</a>
        <a href="?action=no_page">No page</a>
        <a href="?action">Bare action</a>
        <a href="?">Empty query</a>
        <a href="">Empty link</a>
        <a href="..">Down one</a>
        
        <h1>Forms</h1>
        <form action="index.php"><input type="submit" name="action" value="Index"></form>
        <form action="."><input type="submit" name="action" value="Same directory"></form>
        <form action=""><input type="submit" name="action" value="Empty action"></form>
        <form><input type="submit" name="action" value="No action"></form>
        <form action=".."><input type="submit" name="action" value="Down one"></form>
        
        <form method="post" action="index.php"><input type="submit" name="action" value="Index post"></form>
        <form method="post" action="."><input type="submit" name="action" value="Same directory post"></form>
        <form method="post" action=""><input type="submit" name="action" value="Empty action post"></form>
        <form method="post"><input type="submit" name="action" value="No action post"></form>
        <form method="post" action=".."><input type="submit" name="action" value="Down one post"></form>
        
        <?php include __DIR__ . '/show_request.php'; ?>
    </body>
</html>