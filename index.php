<?php
require 'settings/global.php';
require 'vendor/autoload.php';

//$router = new App\Router;
//include $router->getController();

$mail = new \PHPMailer\PHPMailer\PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = "localhost";                     //Set the SMTP server to send through
    $mail->SMTPAuth   = false;
    $mail->Username   = null;                     //SMTP username
    $mail->Password   = null;                               //SMTP password
    $mail->SMTPSecure = "";            //Enable implicit TLS encryption
    $mail->Port       = 1025;                                //TCP port to 

    //Recipients
    $mail->setFrom('from@example.com', 'Mailer');
    $mail->addAddress('joe@example.net', 'Joe User');     //Add a recipient


    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = 'Here is the subject';
    $mail->Body    = 'fff';
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    echo 'Message has been sent';
} catch (\Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}