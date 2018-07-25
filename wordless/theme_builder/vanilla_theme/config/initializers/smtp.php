<?php

add_action( 'phpmailer_init', 'wl_phpmailer_init' );
function wl_phpmailer_init( PHPMailer $phpmailer ) {
    $mailhog = getenv('MAILHOG');

    if ($mailhog !== "true")
      return false;

    $phpmailer->IsSMTP();
    $phpmailer->Host = 'localhost';
    $phpmailer->Port = 1025;
    // $phpmailer->SMTPAuth = true;
    // $phpmailer->Username = 'user';
    // $phpmailer->Password = 'password';
    // $phpmailer->SMTPSecure = 'ssl'; // enable if required, 'tls' is another possible value
}
