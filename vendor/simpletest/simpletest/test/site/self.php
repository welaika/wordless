<?php

function my_path()
{
    return preg_replace('|/[^/]*.php$|', '/', 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
}

    