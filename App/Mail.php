<?php

namespace App;

use App\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Mail
 *
 * PHP version 7.0
 */
class Mail {
    /**
     * Send a message
     * 
     * @param string $to Recipient
     * @param string $subject Subject
     * @param string $text Text-only content of the message
     * @param string $gtml GTML content of the message
     * 
     * @return mixed
     */
    public static function send($to, $subject, $text, $html) {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->CharSet = "UTF-8";
        $mail->Host = 'smtp.wp.pl';
        $mail->SMTPAuth = true;
        $mail->Username = Config::MAIL_ADDRESS;
        $mail->Password = Config::MAIL_PASSWORD;
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
    
        $mail->setFrom(Config::MAIL_ADDRESS);
        $mail->addAddress($to);
    
        $mail->Subject = $subject;
        $mail->Body = $html;
        $mail->AltBody = $text;
    
        $mail->send();
    }
}