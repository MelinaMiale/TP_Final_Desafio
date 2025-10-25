<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class MailManager {
    public function sendEmailConfirmation($email, $confirmationCode) {
        $config = parse_ini_file(__DIR__ . '/../config/mail_config.ini');
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
            $mail->SMTPSecure = $config['secure'];
            $mail->Port = $config['port'];

            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($email);

            $subject = "DESAFIO UNLAM - Verifica tu dirección de correo electrónico";
            $link = "http://localhost/TP_Final_Desafio/index.php?controller=registration&method=validateNewUser&confirmationCode=" . $confirmationCode;
            $message = "Gracias por registrarte. Haz clic en el siguiente enlace para verificar tu cuenta:\n\n" . $link;

            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body = $message;

            $mail->send();
            return true;
        } catch (Exception $ex) {
            error_log("Error al enviar correo: {$mail->ErrorInfo}");
            return false;
        }
    }
}