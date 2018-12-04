<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 02/12/2018
 * Time: 14:24
 */

namespace App\Service;

use App\Model\DatabaseCommunicator;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $mail;

    public function __construct(PHPMailer $mail)
    {
        $this->mail = $mail;
    }


    /**
     * Send email function
     *
     * @param string $senderEmail
     * @param string $receivingEmail
     * @param string $emailText
     * @param string $password
     * @param string|null $associationName
     * @param string|null $code
     */
    public function sendEmail(string $senderEmail, string $receivingEmail, string $emailText, string $password, string $associationName = null,  string $code = null) {

        try {
            // server settings
            $this->mail->isSMTP();
            $this->mail->Host = 'smtp.gmail.com';
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $senderEmail;
            $this->mail->Password = $password;
            $this->mail->SMTPSecure = 'tls';
            $this->mail->Port = 587;

            // set recipient/s
            $this->mail->setFrom($senderEmail, $associationName);
            $this->mail->addAddress($receivingEmail, 'Receiver');

            // set content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Nachricht von ' . $associationName;
            $this->mail->Body    =  is_null($code) ? $emailText : ($emailText . (' <br/> Gutscheincode is: ' . $code) );
            // $this->mail->AltBody = 'This is the body in plain text for non-HTML mail clients';  // if mail provider blocks HTML content set alternative body with no HTML

            // send email
            $this->mail->send();

        } catch (Exception $e) {
            die($this->mail->ErrorInfo);
        }

    }
}