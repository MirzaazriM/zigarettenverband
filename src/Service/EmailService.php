<?php

namespace App\Service;

use App\Model\DatabaseCommunicator;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Symfony\Component\Yaml\Yaml;

class EmailService
{
    private $mail;

    public function __construct() // PHPMailer $mail
    {
        // $this->mail = $mail;
    }


    /**
     * Send email function
     *
     * @param string $senderEmail
     * @param string $receivingEmail
     * @param string $emailText
     * @param string $password
     * @param string|null $associationName
     * @param string|null $associationCode
     */
    public function sendEmail(string $senderEmail, string $receivingEmail, string $emailText, string $password, string $associationName = null,  string $associationCode = null) {

        try {
            // create global PHPMailer object
            $this->mail = new PHPMailer(true);

            // check first if user camed from one of the Associations page, take Association code and fetch appropriete Gutscheincode from database
            if (!is_null($associationCode)) {
                // get valid Gutscheincode from database
                $dc = new DatabaseCommunicator();
                $gutscheinData = $dc->getGutscheinCode($associationCode);
            }

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
            $this->mail->Body =  (is_null($associationCode) or !isset($gutscheinData['code'])) ? $emailText : ($emailText . (' <br/> Gutscheincode is: ' . $gutscheinData['code']) );

            // send email
            $this->mail->send();

            // if script execution comes to this line it means that mail is successfully sent (if email address is valid),
            // check if Gutscheincode is sent and set that code as used (false) in database
            if (!is_null($associationCode) && isset($gutscheinData['code'])) {
                // update used gutscheinCode in database
                $dc->setCodeAsUsed($gutscheinData['id']);

                // check if number of valid codes for specific Association dropped below the limit - demo 10
                if (($gutscheinData['left'] - 1) == 10) {
                    // if yes, send alert email
                    $this->sendAlertEmail($senderEmail);
                }
            }

        } catch (Exception $e) {
            die($this->mail->ErrorInfo);
        }

    }


    /**
     * Send alert email
     *
     * @param $receivingEmail
     */
    public function sendAlertEmail($receivingEmail) {
        // load developer configuration data
        $yaml = Yaml::parse(file_get_contents('../config/configuration/developer-info.yml'));
        $developerConfig = $yaml['info'];

        // load email template for alert emails
        $emailAlertText = file_get_contents('../uploaded_resources/alert_email.txt');

        // create new PHPMailer object
        //$this->mail = new PHPMailer();
        $this->mail->clearAddresses();

        // send alert email
        $this->sendEmail($developerConfig['email'], $receivingEmail, $emailAlertText, $developerConfig['password'], $developerConfig['name'], null);
    }
}