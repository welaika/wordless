<?php

add_action( 'phpmailer_init', 'wl_phpmailer_init' );
function wl_phpmailer_init( PHPMailer $phpmailer ) {
    $mailhog = getenv('MAILHOG');

    if ($mailhog !== true)
      return false;

    $phpmailer->Host = '127.0.0.1';
    $phpmailer->Port = 1025;
    // $phpmailer->Username = 'your_username@example.com';
    // $phpmailer->Password = 'yourpassword';
    // $phpmailer->SMTPAuth = true;
    // $phpmailer->SMTPSecure = 'ssl'; // enable if required, 'tls' is another possible value

    $phpmailer->IsSMTP();
}
