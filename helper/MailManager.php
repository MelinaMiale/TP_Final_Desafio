<?php

class MailManager {
    public function sendEmailConfirmation($email, $confirmationCode) {
        $subject = "DESAFIO UNLAM - Verifica tu dirección de correo electrónico";
        $link = "http://localhost/TP_Final_Desafio/index.php?controller=registration&method=validateNewUser&confirmationCode=" . $confirmationCode;;
        $message = "Gracias por registrarte. Haz clic en el siguiente enlace para verificar tu cuenta:\n\n" . $link;

        mail($email, $subject, $message, "From: desafio_unlam@unlam.edu.ar");
    }
}