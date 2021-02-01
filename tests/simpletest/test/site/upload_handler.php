<?php
    function show($name)
    {
        @unlink(__DIR__ . "/temp/$name");
        @move_uploaded_file($_FILES[$name]['tmp_name'], __DIR__ . "/temp/$name");
        $unsafe = @file_get_contents(__DIR__ . "/temp/$name");
        $safe   = htmlentities($unsafe);
        $handle = fopen(__DIR__ . "/temp/$name", 'w');
        fwrite($handle, $safe);
        fclose($handle);
        print $safe;
    }
?><html>
    <head><title>Test of file upload</title></head>
    <body>
        <p><?php show('content'); ?></p>
        <p><?php show('supplemental'); ?></p>
    </body>
</html>