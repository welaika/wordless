<?php

switch ($_SERVER['REQUEST_METHOD']) {

    case 'HEAD':
        header("HTTP/1.1 202 Accepted");
    break;

    case 'DELETE':
        header("HTTP/1.1 202 Accepted");
        echo "Your delete request was accepted.";
    break;

    case 'POST':
    case 'PUT':
        if (in_array($_SERVER['CONTENT_TYPE'], array("text/xml", "application/xml"))) {
            $data = fopen("php://input", "r");
            $content = "";
            while ($chunk = fread($data, 1024)) {
                $content .= $chunk;
            }
            fclose($data);
            
            if ($content == "<a><b>c</b></a>") {
                header("HTTP/1.1 201 Created");
                header("Content-Type: text/xml");
                echo strip_tags($content);
            }
        } else {
            header("HTTP/1.1 406 Invalid Encoding");
            header("Content-Type: text/plain");
            echo "Please ensure content type is an XML format.";
        }
    break;
    
    default:
        header("HTTP/1.1 405 Method Not Allowed");
        header("Content-Type: text/plain");
        echo "Method Not Allowed";
    break;
    
}
