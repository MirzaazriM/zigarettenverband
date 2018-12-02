<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 02/12/2018
 * Time: 14:24
 */

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $senderEmail;
    private $password;
    private $receivingEmail;
    private $subjectMessage;
    private $emailText;
    private $mail;
    private $code;

    public function __construct(string $senderEmail, string $password, string $receivingEmail, string $subjectMessage, string $emailText, PHPMailer $mail, string $code = null)
    {
        $this->senderEmail = $senderEmail;
        $this->password = $password;
        $this->receivingEmail = $receivingEmail;
        $this->subjectMessage = $subjectMessage;
        $this->emailText = $emailText;
        $this->mail = $mail;
        $this->code = $code;
    }


    public function sendEmail() {

        try {
            // server settings
            $this->mail->isSMTP();
            $this->mail->Host = 'smtp.gmail.com';
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $this->senderEmail;
            $this->mail->Password = $this->password;
            $this->mail->SMTPSecure = 'tls';
            $this->mail->Port = 587;

            // set recipient/s
            $this->mail->setFrom($this->senderEmail, 'SmartLab');
            $this->mail->addAddress($this->receivingEmail, 'Receiver');

            // set content
            $this->mail->isHTML(true);
            $this->mail->Subject = $this->subjectMessage;
            $this->mail->Body    =  $this->emailText;
            // $this->mail->AltBody = 'This is the body in plain text for non-HTML mail clients';  // if mail provider blocks HTML content set alternative body with no HTML

            // send email
            $this->mail->send();

        } catch (Exception $e) {
            die($this->mail->ErrorInfo);
        }

        die("mail sended");
    }
}